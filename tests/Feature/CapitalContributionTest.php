<?php

namespace Tests\Feature;

use App\Models\CapitalContribution;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CapitalContributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_capital_is_managed_separately_from_customer_payments(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 19));
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $client = Client::create(['name' => 'Client Pengujian']);
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-CAPITAL-TEST',
            'due_date' => '2026-11-30',
            'grand_total' => 3000000,
            'status' => 'partial',
        ]);

        $invoice->payments()->createMany([
            ['amount' => '896500.39', 'payment_date' => '2026-11-08'],
            ['amount' => '100000.00', 'payment_date' => '2026-10-31'],
            ['amount' => '800.00', 'payment_date' => '2026-11-20'],
        ]);

        $setoran = [
            'contribution_date' => '2026-11-01',
            'source' => 'Setoran pemilik',
            'amount' => '1.500.000,50',
            'notes' => 'Transfer ke rekening usaha',
        ];

        $this->actingAs($admin)->post(route('capital.store'), $setoran)
            ->assertSessionHasNoErrors()->assertRedirect(route('capital.index'));

        $contribution = CapitalContribution::sole();
        $this->assertSame('1500000.50', $contribution->amount);

        $november = route('capital.index', ['month' => '2026-11']);
        $this->get($november)->assertOk()
            ->assertViewHas('capitalTotal', '1500000.50')
            ->assertViewHas('paymentsTotal', '896500.39')
            ->assertViewHas('cashInTotal', '2396500.89');

        $this->get(route('capital.index'))->assertOk()->assertViewHas('cashInTotal', '2496500.89');
        $this->get(route('capital.edit', $contribution))->assertOk()->assertSee('1.500.000,50');

        $this->put(route('capital.update', $contribution), array_replace($setoran, ['amount' => '1500001.25']))
            ->assertSessionHasNoErrors()->assertRedirect(route('capital.index'));
        $this->get($november)->assertOk()->assertViewHas('cashInTotal', '2396501.64');

        $this->delete(route('capital.destroy', $contribution))->assertRedirect(route('capital.index'));
        $this->get($november)->assertOk()
            ->assertViewHas('cashInTotal', '896500.39')->assertSee('Dibatalkan');
        $this->get(route('capital.edit', $contribution))->assertNotFound();

        $this->assertSame($admin->id, $contribution->fresh()->voided_by);
        $this->assertSame(3, Payment::count());
    }

    public function test_invalid_contributions_are_rejected(): void
    {
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));

        $this->actingAs($admin)->from(route('capital.index'))->post(route('capital.store'), [
            'contribution_date' => today()->addDay()->toDateString(),
            'source' => 'Setoran pemilik',
            'amount' => '1..500,00',
        ])->assertSessionHasErrors(['amount', 'contribution_date']);

        $this->assertDatabaseCount('capital_contributions', 0);
    }

    public function test_clients_cannot_view_or_record_capital(): void
    {
        $client = User::factory()->create()->assignRole(Role::create(['name' => 'client']));

        $this->actingAs($client)->get(route('capital.index'))->assertForbidden();
        $this->post(route('capital.store'))->assertForbidden();
    }
}
