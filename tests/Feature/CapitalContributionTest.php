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

    public function test_capital_expenses_reduce_net_income_and_never_increase_cash_in(): void
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

        $expense = [
            'contribution_date' => '2026-11-01',
            'source' => 'Pembelian perangkat',
            'amount' => '1.500.000,50',
            'notes' => 'Transfer ke pemasok',
        ];

        $this->actingAs($admin)->post(route('capital.store'), $expense)
            ->assertSessionHasNoErrors()->assertRedirect(route('capital.index'));

        $contribution = CapitalContribution::sole();
        $this->assertSame('1500000.50', $contribution->amount);

        $november = route('capital.index', ['month' => '2026-11']);
        $this->get($november)->assertOk()
            ->assertViewHas('expenseTotal', '1500000.50')
            ->assertViewHas('cashInTotal', '896500.39')
            ->assertViewHas('netTotal', '-603500.11')
            ->assertSeeTextInOrder(['Total Dana Masuk', 'Rp 896.500', 'Modal Terpakai', 'Rp 1.500.001'])
            ->assertSeeText('Catat Pengeluaran Modal')
            ->assertDontSeeText('Setoran Modal');

        $this->get(route('capital.index'))->assertOk()->assertViewHas('cashInTotal', '996500.39');
        $this->get(route('capital.edit', $contribution))->assertOk()->assertSee('1500000.50')->assertSee('1.500.001');

        $this->put(route('capital.update', $contribution), array_replace($expense, ['amount' => '1500001.25']))
            ->assertSessionHasNoErrors()->assertRedirect(route('capital.index'));
        $this->get($november)->assertOk()->assertViewHas('cashInTotal', '896500.39')->assertViewHas('netTotal', '-603500.86');

        $this->delete(route('capital.destroy', $contribution))->assertRedirect(route('capital.index'));
        $this->get($november)->assertOk()
            ->assertViewHas('cashInTotal', '896500.39')
            ->assertViewHas('expenseTotal', '0.00')
            ->assertViewHas('netTotal', '896500.39')
            ->assertSee('Dibatalkan');
        $this->get(route('capital.edit', $contribution))->assertNotFound();

        $this->assertSame($admin->id, $contribution->fresh()->voided_by);
        $this->assertSame(3, Payment::count());
    }

    public function test_invalid_contributions_are_rejected(): void
    {
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));

        $this->actingAs($admin)->from(route('capital.index'))->post(route('capital.store'), [
            'contribution_date' => today()->addDay()->toDateString(),
            'source' => 'Pembelian perangkat',
            'amount' => '1..500,00',
            'invoice_id' => 999,
        ])->assertSessionHasErrors(['amount', 'contribution_date', 'invoice_id']);

        $this->assertDatabaseCount('capital_contributions', 0);
    }

    public function test_invoice_balance_uses_only_its_active_capital_and_payments_for_the_selected_period(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 19));
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $client = Client::create(['name' => 'Client Pengujian']);
        $invoice = $client->invoices()->create(['invoice_number' => 'INV-MATCH-001', 'grand_total' => 1000000]);
        $otherInvoice = $client->invoices()->create(['invoice_number' => 'INV-MATCH-002', 'grand_total' => 2000000]);

        $invoice->payments()->createMany([
            ['amount' => '400000.25', 'payment_date' => '2026-11-10'],
            ['amount' => '200000.00', 'payment_date' => '2026-10-22'],
        ]);
        $otherInvoice->payments()->create(['amount' => '500000.00', 'payment_date' => '2026-11-10']);

        $expense = ['contribution_date' => '2026-11-01', 'source' => 'Pembelian perlengkapan', 'amount' => '500.000,50'];
        $this->actingAs($admin)->post(route('capital.store'), $expense + ['invoice_id' => $invoice->id])
            ->assertSessionHasNoErrors()->assertRedirect(route('capital.index'));
        $contribution = CapitalContribution::sole();
        $this->assertSame($invoice->id, $contribution->invoice_id);

        CapitalContribution::create(array_replace($expense, ['invoice_id' => $otherInvoice->id, 'amount' => '500000.50']));
        CapitalContribution::create(array_replace($expense, ['amount' => '500000.50', 'source' => 'Biaya operasional umum']));

        $november = route('capital.index', ['invoice_id' => $invoice->id, 'month' => '2026-11']);
        $this->get($november)->assertOk()
            ->assertViewHas('expenseTotal', '500000.50')
            ->assertViewHas('cashInTotal', '400000.25')
            ->assertViewHas('invoiceBalances', fn ($rows) => $rows->count() === 1 && $rows[0]['difference'] === '-100000.25')
            ->assertSee('−Rp 100.000')->assertDontSee('−Rp 100.000,25')
            ->assertDontSee('Biaya operasional umum');

        $this->delete(route('capital.destroy', $contribution))->assertRedirect(route('capital.index'));
        $this->get($november)->assertOk()->assertViewHas('expenseTotal', '0.00');
        $this->get(route('capital.index', ['invoice_id' => $otherInvoice->id]))->assertOk()
            ->assertViewHas('expenseTotal', '500000.50');

        $invoice->delete();
        $this->assertNull($contribution->fresh()->invoice_id);
        $this->get(route('capital.index'))->assertOk()->assertSee('INV-MATCH-001')->assertSee('(invoice dihapus)');
    }

    public function test_an_invoice_can_be_recorded_again_only_after_its_active_contribution_is_voided(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 20));
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $client = Client::create(['name' => 'Client Pengujian']);
        $invoice = $client->invoices()->create(['invoice_number' => 'INV-LOCK-001']);
        $otherInvoice = $client->invoices()->create(['invoice_number' => 'INV-LOCK-002']);
        $expense = [
            'contribution_date' => '2026-11-01',
            'source' => 'Pembelian perlengkapan',
            'amount' => '500000.50',
            'invoice_id' => $invoice->id,
        ];

        $this->actingAs($admin)->post(route('capital.store'), $expense)->assertRedirect(route('capital.index'));
        $contribution = CapitalContribution::sole();

        $this->get(route('capital.index', ['month' => '2026-10']))->assertOk()->assertSee('(sudah tercatat)');
        $this->from(route('capital.index'))->post(route('capital.store'), array_replace($expense, [
            'contribution_date' => '2026-10-01',
        ]))->assertSessionHasErrors('invoice_id');
        $this->assertDatabaseCount('capital_contributions', 1);

        $editPage = route('capital.edit', $contribution);
        $this->get($editPage)->assertOk()->assertSee('Untuk mengganti invoice, batalkan pengeluaran ini');
        $this->from($editPage)->put(route('capital.update', $contribution), array_replace($expense, [
            'invoice_id' => $otherInvoice->id,
        ]))->assertSessionHasErrors('invoice_id');
        $this->assertSame($invoice->id, $contribution->fresh()->invoice_id);

        $this->put(route('capital.update', $contribution), array_replace($expense, ['amount' => '600000.25']))
            ->assertRedirect(route('capital.index'));
        $this->assertSame('600000.25', $contribution->fresh()->amount);

        $this->delete(route('capital.destroy', $contribution))->assertRedirect(route('capital.index'));
        $this->get(route('capital.index'))->assertOk()->assertDontSee('(sudah tercatat)');
        $this->post(route('capital.store'), $expense)->assertSessionHasNoErrors()->assertRedirect(route('capital.index'));

        $this->assertDatabaseCount('capital_contributions', 2);
        $this->assertSame(1, $invoice->capitalContributions()->whereNull('voided_at')->count());
    }

    public function test_clients_cannot_view_or_record_capital(): void
    {
        $client = User::factory()->create()->assignRole(Role::create(['name' => 'client']));

        $this->actingAs($client)->get(route('capital.index'))->assertForbidden();
        $this->post(route('capital.store'))->assertForbidden();
    }
}
