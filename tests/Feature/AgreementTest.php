<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $invoice->client->update(['company' => 'Yayasan Pengujian MoU']);
        $invoice->items()->create(['description' => 'Sewa platform ujian', 'qty' => 1, 'price' => 1250000, 'total' => 1250000]);

        $this->actingAs($admin)->get(route('agreements.create', $invoice))->assertOk()
            ->assertSee('Sewa platform ujian')->assertSee('Manajer')->assertSee('Kepala Sekolah');
        $this->post(route('agreements.store', $invoice), $this->terms())->assertSessionHasNoErrors()->assertRedirect();

        $agreement = Agreement::sole();
        $details = route('agreements.show', $agreement);
        $this->assertSame($invoice->id, $agreement->invoice_id);
        $this->assertSame('1250000.00', $agreement->invoice_total);
        $this->assertNull($agreement->finalized_at);
        $this->assertNull($agreement->number);
        $this->assertSame('Yayasan Pengujian MoU', $agreement->signatureCompanyName());

        $this->get(route('invoices.index'))->assertOk()->assertSee('Lihat MoU');
        $this->get(route('agreements.create', $invoice))->assertRedirect($details);
        $this->post(route('agreements.store', $invoice), $this->terms())->assertRedirect($details);
        $this->assertDatabaseCount('agreements', 1);
        $this->get(route('agreements.index'))->assertOk()->assertSee('Belum bernomor');
        $this->get($details)->assertOk()->assertSee('Simpan Draf');

        $pdfRoute = route('agreements.pdf', $agreement);
        $this->get($pdfRoute)->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload('Draf-MoU-'.$agreement->id.'.pdf');

        $this->put(route('agreements.update', $agreement), array_replace($this->terms(), ['scope' => 'Layanan revisi']))
            ->assertSessionHasNoErrors()->assertRedirect($details);
        $invoice->client->update(['company' => 'Yayasan Saat Finalisasi']);
        $this->post(route('agreements.finalize', $agreement))->assertSessionHas('success')->assertRedirect($details);
        $this->assertNotNull($agreement->fresh()->finalized_at);
        $this->put(route('agreements.update', $agreement), $this->terms())->assertSessionHas('error')->assertRedirect($details);

        $invoice->update(['grand_total' => 9000000]);
        $company->update(['legal_name' => 'Nama Baru']);
        $invoice->client->update(['company' => 'Yayasan Setelah Finalisasi']);
        $invoice->delete();
        $agreement->refresh();
        $this->assertNull($agreement->invoice_id);
        $this->assertSame('1250000.00', $agreement->invoice_total);
        $this->assertSame('PT SITYOY IT SOLUTIONS', $agreement->letterhead['legal_name']);
        $this->assertSame('Layanan revisi', $agreement->scope);
        $this->assertSame('Yayasan Saat Finalisasi', $agreement->client_signature_company);
        $html = view('admin.agreements.pdf', [
            'agreement' => $agreement,
            'company' => (object) $agreement->letterhead,
            'logoPath' => public_path('logo/siscombg2.png'),
        ])->render();
        $this->assertMatchesRegularExpression('~<td>Kepala Sekolah</td></tr>\s*<tr><td>[^<]+</td><td>Yayasan Saat Finalisasi</td>~', $html);
        $this->assertStringContainsString('<td><strong>Penandatangan Sekolah</strong></td>', $html);
        $this->get($details)->assertOk()->assertSee('INV-MOU-TEST')->assertDontSee('Simpan Draf');
        $this->assertSame('0001/MOU/SIS/'.$agreement->finalized_at->year, $agreement->number);
        $this->get($pdfRoute)->assertOk()->assertDownload('MoU-'.str_replace('/', '-', $agreement->number).'.pdf');
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

    public function test_super_admin_can_permanently_delete_an_agreement_and_start_over_from_the_invoice(): void
    {
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $owner = User::factory()->create()->assignRole(Role::create(['name' => 'client']));
        $invoice = $this->createInvoice();
        $invoice->client->update(['user_id' => $owner->id]);
        $create = route('agreements.create', $invoice);
        $store = route('agreements.store', $invoice);

        $this->actingAs($admin)->post($store, $this->terms())->assertRedirect();
        $original = Agreement::sole();
        $this->get(route('agreements.show', $original))->assertOk()->assertSee('Hapus MoU Permanen');
        $this->post(route('agreements.finalize', $original))->assertSessionHas('success');
        $original->refresh();

        $remove = route('agreements.destroy', $original);
        $clientDetail = route('client.agreements.show', $original->id);
        $clientPdf = route('client.agreements.pdf', $original->id);
        $this->actingAs($owner)->get($clientDetail)->assertOk();
        $this->delete($remove)->assertForbidden();
        $this->assertDatabaseHas('agreements', ['id' => $original->id]);

        $this->actingAs($admin)->delete($remove)->assertRedirect($create);
        $this->assertModelMissing($original);
        $this->assertModelExists($invoice);
        $this->get($create)->assertOk()->assertSee('Draf MoU Baru')->assertDontSee('Hapus MoU Permanen');

        $this->actingAs($owner)->get($clientDetail)->assertNotFound();
        $this->get($clientPdf)->assertNotFound();
        $this->get(route('client.invoices'))->assertOk()->assertDontSee('Lihat MoU');

        $this->actingAs($admin)->post($store, $this->terms())->assertRedirect();
        $replacement = Agreement::sole();
        $this->assertSame($invoice->id, $replacement->invoice_id);
        $this->assertNull($replacement->finalized_at);
        $this->assertNull($replacement->number);
        $this->post(route('agreements.finalize', $replacement))->assertSessionHas('success');
        $this->assertSame('0002/MOU/SIS/'.$original->finalized_at->year, $replacement->fresh()->number);
    }

    public function test_deleted_drafts_do_not_consume_official_numbers_and_repeated_finalization_does_not_advance_them(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 24));
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $invoice = $this->createInvoice();
        $store = route('agreements.store', $invoice);

        $this->actingAs($admin)->post($store, $this->terms())->assertRedirect();
        $draft = Agreement::sole();
        $this->assertNull($draft->number);
        $this->delete(route('agreements.destroy', $draft))->assertRedirect();

        $this->post($store, $this->terms())->assertRedirect();
        $first = Agreement::sole();
        $finalize = route('agreements.finalize', $first);
        $this->post($finalize)->assertSessionHas('success');
        $this->post($finalize)->assertSessionHas('success');
        $this->assertSame('0001/MOU/SIS/2026', $first->fresh()->number);

        $other = $invoice->replicate()->fill(['invoice_number' => 'INV-MOU-OTHER']);
        $other->save();
        $this->post(route('agreements.store', $other), $this->terms())->assertRedirect();
        $this->post(route('agreements.finalize', $other->agreement))->assertSessionHas('success');
        $this->assertSame('0002/MOU/SIS/2026', $other->agreement->fresh()->number);
    }

    public function test_number_migration_keeps_legacy_numbers_and_reserves_deleted_ids(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('This migration test uses transactional SQLite schema changes.');
        }

        $migration = require database_path('migrations/2026_11_24_000015_issue_agreement_numbers_on_finalization.php');
        $migration->down();
        $invoice = $this->createInvoice();
        $base = $this->terms() + [
            'invoice_reference' => $invoice->invoice_number, 'invoice_total' => $invoice->grand_total,
            'provider_name' => 'SIS.COM', 'client_name' => $invoice->client->name, 'letterhead' => [],
        ];
        $final = Agreement::create($base);
        $final->forceFill(['created_at' => '2025-12-31 12:00:00', 'finalized_at' => '2026-01-01 12:00:00'])->save();
        $deleted = Agreement::create($base);
        $deleted->delete();
        $draft = Agreement::create($base);

        $migration->up();

        $this->assertSame('0001/MOU/SIS/2025', $final->fresh()->number);
        $this->assertNull($draft->fresh()->number);
        $this->assertDatabaseHas('agreement_number_sequences', ['id' => 1, 'last_number' => 2]);
    }

    public function test_detailed_cbt_agreement_is_shared_only_with_its_client_after_finalization(): void
    {
        $admin = User::factory()->create()->assignRole(Role::create(['name' => 'super_admin']));
        $role = Role::create(['name' => 'client']);
        $owner = User::factory()->create()->assignRole($role);
        $other = User::factory()->create()->assignRole($role);
        $otherClient = Client::create(['name' => 'Sekolah Lain', 'user_id' => $other->id]);
        $invoice = $this->createInvoice();
        $invoice->client->update(['user_id' => $owner->id]);
        $company = CompanySetting::create([
            'company_name' => 'SIS.COM', 'legal_name' => 'PT SITYOY IT SOLUTIONS',
        ]);
        $company->bankAccounts()->create([
            'bank_name' => 'Bank Jakarta', 'account_number' => '000-TEST-099',
            'account_holder' => $company->legal_name, 'is_active' => false,
        ]);
        $company->bankAccounts()->create([
            'bank_name' => 'Bank Mandiri', 'account_number' => '000-TEST-001', 'account_holder' => $company->legal_name,
        ]);

        $this->actingAs($admin)->get(route('agreements.create', ['invoice' => $invoice, 'template' => 'cbt']))
            ->assertOk()->assertSee('15000')->assertSee('Hak dan Kewajiban Pihak Pertama')
            ->assertSee('Bank Mandiri')->assertSee('000-TEST-001')->assertDontSee('000-TEST-099');

        $terms = $this->terms() + ['details' => ['fee_per_student' => 15000]];
        $this->from(route('agreements.create', $invoice))->post(route('agreements.store', $invoice), $terms)
            ->assertSessionHasErrors('details.student_count');
        $terms['details'] += [
            'student_count' => 200,
            'provider_address' => 'Tangerang, Banten', 'client_address' => 'Jakarta',
            'provider_obligations' => 'Penyedia menyediakan akses ujian dan mendampingi persiapan teknis sesuai jadwal yang disepakati.',
            'client_obligations' => 'Sekolah menyiapkan peserta, perangkat, jaringan, dan petugas ujian.',
            'support' => 'Laporan gangguan disampaikan melalui kontak teknis kedua pihak.',
            'confidentiality' => 'Akses data peserta dan soal dibatasi kepada petugas yang berwenang.',
            'disputes' => 'Perselisihan dibahas melalui musyawarah kedua pihak.',
        ];
        $this->post(route('agreements.store', $invoice), $terms)->assertRedirect();
        $agreement = Agreement::sole();
        $this->assertSame($invoice->client_id, $agreement->client_id);

        $index = route('client.agreements.index');
        $show = route('client.agreements.show', $agreement->id);
        $pdf = route('client.agreements.pdf', $agreement->id);
        $this->actingAs($owner)->get($index)->assertOk()->assertDontSee($agreement->title);
        $this->get($show)->assertNotFound();
        $this->get($pdf)->assertNotFound();
        $this->get(route('client.invoices'))->assertOk()->assertDontSee('Lihat MoU');

        $this->actingAs($admin)->post(route('agreements.finalize', $agreement))->assertSessionHas('success');
        $this->actingAs($owner)->get($index)->assertOk()->assertSee($agreement->title);
        $this->get(route('client.invoices'))->assertOk()->assertSee('Lihat MoU');
        $this->get($show)->assertOk()->assertSee('Rp3.000.000 per periode')->assertSee('Pasal 10. Ketentuan Tambahan')
            ->assertSee('Kerahasiaan dan Data')->assertSee('Jakarta')->assertDontSee('Simpan Draf');
        $this->get($pdf)->assertOk()->assertDownload('MoU-'.str_replace('/', '-', $agreement->fresh()->number).'.pdf');

        $invoice->update(['client_id' => $otherClient->id]);
        $this->actingAs($other)->get($index)->assertOk()->assertDontSee($agreement->title);
        $this->get(route('client.invoices'))->assertOk()->assertDontSee('Lihat MoU');
        $this->get($show)->assertNotFound();
        $this->get($pdf)->assertNotFound();
        $this->actingAs($owner)->get($show)->assertOk();
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
