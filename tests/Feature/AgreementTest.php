<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgreementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_prepare_and_finalize_one_agreement_per_invoice(): void
    {
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $company = CompanySetting::create([
            'company_name' => 'SIS.COM', 'legal_name' => 'PT SITYOY IT SOLUTIONS',
            'director_name' => 'Penandatangan SIS', 'director_title' => 'Manager',
        ]);
        $invoice = $this->createInvoice();
        $invoice->items()->create(['description' => 'Sewa platform ujian', 'qty' => 1, 'price' => 1250000, 'total' => 1250000]);

        $this->actingAs($admin)->get(route('agreements.create', $invoice))->assertOk()
            ->assertSee('Sewa platform ujian')->assertSee('Manajer')->assertSee('Kepala Sekolah');
        $this->post(route('agreements.store', $invoice), $this->terms())->assertSessionHasNoErrors()->assertRedirect();

        $agreement = Agreement::sole();
        $details = route('agreements.show', $agreement);
        $this->assertSame($invoice->id, $agreement->invoice_id);
        $this->assertSame('1250000.00', $agreement->invoice_total);
        $this->assertNull($agreement->finalized_at);

        $this->get(route('invoices.index'))->assertOk()->assertSee('Lihat MoU');
        $this->get(route('agreements.create', $invoice))->assertRedirect($details);
        $this->post(route('agreements.store', $invoice), $this->terms())->assertRedirect($details);
        $this->assertDatabaseCount('agreements', 1);
        $this->get(route('agreements.index'))->assertOk()->assertSee($agreement->number);
        $this->get($details)->assertOk()->assertSee('Simpan Draf');

        $pdfRoute = route('agreements.pdf', $agreement);
        $filename = str_replace('/', '-', $agreement->number).'.pdf';
        $this->get($pdfRoute)->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('Draf-MoU-'.$filename);

        $this->put(route('agreements.update', $agreement), array_replace($this->terms(), ['scope' => 'Layanan revisi']))
            ->assertSessionHasNoErrors()->assertRedirect($details);
        $this->post(route('agreements.finalize', $agreement))->assertSessionHas('success')->assertRedirect($details);
        $this->assertNotNull($agreement->fresh()->finalized_at);
        $this->put(route('agreements.update', $agreement), $this->terms())->assertSessionHas('error')->assertRedirect($details);

        $invoice->update(['grand_total' => 9000000]);
        $company->update(['legal_name' => 'Nama Baru']);
        $invoice->delete();
        $agreement->refresh();
        $this->assertNull($agreement->invoice_id);
        $this->assertSame('1250000.00', $agreement->invoice_total);
        $this->assertSame('PT SITYOY IT SOLUTIONS', $agreement->letterhead['legal_name']);
        $this->assertSame('Layanan revisi', $agreement->scope);
        $this->get($details)->assertOk()->assertSee('INV-MOU-TEST')->assertDontSee('Simpan Draf');
        $this->get($pdfRoute)->assertOk()->assertDownload('MoU-'.$filename);
    }

    public function test_invalid_period_cancelled_invoice_and_client_access_are_rejected(): void
    {
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $client = User::factory()->create()->assignRole(Role::create(['name' => 'client']));
        $invoice = $this->createInvoice();

        $this->actingAs($admin)->from(route('agreements.create', $invoice))->post(
            route('agreements.store', $invoice), array_replace($this->terms(), ['ends_on' => '2026-11-01']),
        )->assertSessionHasErrors('ends_on');

        $invoice->update(['status' => 'cancelled']);
        $this->post(route('agreements.store', $invoice), $this->terms())->assertRedirect(route('invoices.index'));
        $this->assertDatabaseCount('agreements', 0);

        $this->actingAs($client)->get(route('agreements.create', $invoice))->assertForbidden();
        $this->get(route('agreements.index'))->assertForbidden();
    }

    private function createInvoice(): Invoice
    {
        $client = Client::create([
            'name' => 'Sekolah Pengujian', 'authorized_official_name' => 'Penandatangan Sekolah',
            'authorized_official_title' => 'Kepala Sekolah',
        ]);

        return $client->invoices()->create([
            'invoice_number' => 'INV-MOU-TEST', 'invoice_date' => '2026-11-20', 'due_date' => '2026-12-01',
            'grand_total' => 1250000, 'status' => 'unpaid',
        ]);
    }

    private function terms(): array
    {
        return [
            'title' => 'Kerja Sama Layanan Sistem Sekolah', 'document_date' => '2026-11-23',
            'starts_on' => '2026-12-01', 'ends_on' => '2027-01-31', 'city' => 'Tangerang',
            'scope' => 'Layanan sistem sekolah dan dukungan teknis.', 'payment_terms' => 'Pembayaran mengikuti invoice rujukan.',
            'additional_terms' => 'Jadwal pelaksanaan disepakati bersama.',
            'provider_signer' => 'Penandatangan SIS', 'provider_title' => 'Manajer',
            'client_signer' => 'Penandatangan Sekolah', 'client_title' => 'Kepala Sekolah',
        ];
    }
}
