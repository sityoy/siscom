<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProcurementDocument;
use App\Models\ProcurementPackage;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectInvoiceSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_procurement_document_with_automatic_number(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        $response = $this->actingAs($admin)->post(route('procurement-documents.store'), [
            'project_id' => $project->id,
            'type' => 'quotation',
            'document_date' => '2026-10-27',
            'status' => 'ready',
            'amount' => 5000000,
            'transaction_method' => 'non_siplah',
        ]);

        $document = ProcurementDocument::firstOrFail();

        $response->assertRedirect(route('procurement-documents.show', $document));
        $this->assertSame('001/SPH/SIS/X/2026', $document->document_number);
        $this->assertSame('ready', $document->status);
    }

    public function test_client_only_sees_published_documents_for_its_own_project(): void
    {
        [$admin, $project, $clientUser] = $this->createAdminAndProject(true);

        ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'handover',
            'document_number' => '001/BAST/SIS/X/2026',
            'document_date' => '2026-10-27',
            'status' => 'draft',
            'amount' => 5000000,
        ]);

        ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'quotation',
            'document_number' => '002/SPH/SIS/X/2026',
            'document_date' => '2026-10-27',
            'status' => 'ready',
            'amount' => 5000000,
        ]);

        $this->actingAs($clientUser)
            ->get(route('client.procurement-documents.index'))
            ->assertOk()
            ->assertSee('002/SPH/SIS/X/2026')
            ->assertDontSee('001/BAST/SIS/X/2026');
    }

    public function test_school_signer_is_filled_from_the_project_client(): void
    {
        [$admin, $project] = $this->createAdminAndProject();
        $project->client->update([
            'authorized_official_name' => 'Drs. Kepala Sekolah',
            'authorized_official_title' => 'Kepala Sekolah',
        ]);

        $this->actingAs($admin)->post(route('procurement-documents.store'), [
            'project_id' => $project->id,
            'type' => 'quotation',
            'document_date' => '2026-10-27',
            'status' => 'ready',
            'amount' => 5000000,
            'transaction_method' => 'non_siplah',
        ])->assertRedirect();

        $document = ProcurementDocument::firstOrFail();
        $this->assertSame('Drs. Kepala Sekolah', $document->school_signer_name);
        $this->assertSame('Kepala Sekolah', $document->school_signer_title);
    }

    public function test_document_numbers_restart_after_the_spj_package_is_deleted(): void
    {
        Carbon::setTestNow('2026-10-27 10:00:00');
        [$admin, $project] = $this->createAdminAndProject();
        $this->createProjectInvoice($project, '2026-10-27', '#INV-NUMBERING');

        $this->actingAs($admin)
            ->post(route('procurement-documents.generate', $project))
            ->assertRedirect();

        $this->assertSame(
            '001/SPH/SIS/X/2026',
            ProcurementDocument::where('type', 'quotation')->value('document_number'),
        );
        $this->assertSame(
            '001/BAST/SIS/X/2026',
            ProcurementDocument::where('type', 'handover')->value('document_number'),
        );

        $this->actingAs($admin)
            ->delete(route('procurement-documents.package.destroy', $project))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('procurement-documents.generate', $project))
            ->assertRedirect();

        $this->assertSame(
            '001/SPH/SIS/X/2026',
            ProcurementDocument::where('type', 'quotation')->value('document_number'),
        );
        $this->assertSame(
            '001/BAST/SIS/X/2026',
            ProcurementDocument::where('type', 'handover')->value('document_number'),
        );

        Carbon::setTestNow();
    }

    public function test_spj_package_uses_the_latest_active_invoice_date(): void
    {
        Carbon::setTestNow('2026-10-29 10:00:00');
        [$admin, $project] = $this->createAdminAndProject();

        $invoice = Invoice::create([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'invoice_number' => '#INV-TEST-001',
            'invoice_date' => '2026-06-09',
            'due_date' => '2026-07-11',
            'status' => 'unpaid',
            'invoice_type' => 'project',
            'subtotal' => 5000000,
            'grand_total' => 5000000,
        ]);

        $this->actingAs($admin)
            ->post(route('procurement-documents.generate', $project))
            ->assertRedirect();

        $this->assertDatabaseHas('procurement_documents', [
            'project_id' => $project->id,
            'invoice_id' => $invoice->id,
            'document_date' => '2026-06-09',
        ]);

        $this->assertSame(
            ProcurementDocument::where('project_id', $project->id)->count(),
            ProcurementDocument::where('project_id', $project->id)
                ->where('invoice_id', $invoice->id)
                ->whereDate('document_date', '2026-06-09')
                ->count(),
        );

        Carbon::setTestNow();
    }

    public function test_spj_package_cannot_be_generated_before_project_invoice_exists(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        $this->actingAs($admin)
            ->post(route('procurement-documents.generate', $project))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('procurement_documents', [
            'project_id' => $project->id,
        ]);
    }

    public function test_project_cannot_open_a_second_invoice_creation_form(): void
    {
        [$admin, $project] = $this->createAdminAndProject();
        $invoice = $this->createProjectInvoice(
            $project,
            '2026-10-29',
            '#INV-SINGLE-PROJECT',
        );

        $this->actingAs($admin)
            ->get(route('invoices.create', ['project_id' => $project->id]))
            ->assertRedirect(route('invoices.edit', $invoice))
            ->assertSessionHas('warning');
    }

    public function test_editing_invoice_date_resynchronizes_existing_spj_documents(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        $invoice = Invoice::create([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'invoice_number' => '#INV-TEST-002',
            'invoice_date' => '2026-06-09',
            'due_date' => '2026-07-11',
            'status' => 'unpaid',
            'invoice_type' => 'project',
            'subtotal' => 5000000,
            'grand_total' => 5000000,
        ]);

        $document = ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'handover',
            'document_number' => '001/BAST/SIS/X/2026',
            'document_date' => '2026-10-29',
            'status' => 'ready',
            'amount' => 5000000,
        ]);

        $invoice->update(['invoice_date' => '2026-06-15']);

        app(ProjectInvoiceSyncService::class)
            ->syncAfterInvoiceSaved($invoice->fresh('project'));

        $document->refresh();

        $this->assertSame($invoice->id, $document->invoice_id);
        $this->assertSame('2026-06-15', $document->document_date->toDateString());
    }

    public function test_super_admin_can_delete_an_entire_project_spj_package(): void
    {
        Storage::fake('public');
        [$admin, $project] = $this->createAdminAndProject();
        $signedPath = 'procurement-documents/signed/signed-bast.pdf';
        Storage::disk('public')->put($signedPath, 'signed document');

        ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'handover',
            'document_number' => '001/BAST/SIS/X/2026',
            'document_date' => '2026-10-27',
            'status' => 'signed',
            'amount' => 5000000,
            'signed_file_path' => $signedPath,
        ]);

        ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'quotation',
            'document_number' => '002/SPH/SIS/X/2026',
            'document_date' => '2026-10-27',
            'status' => 'ready',
            'amount' => 5000000,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('procurement-documents.package.destroy', $project),
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('procurement_documents', ['project_id' => $project->id]);
        Storage::disk('public')->assertMissing($signedPath);
    }

    public function test_non_pkp_umkm_tax_profile_is_applied_to_invoice_and_spj(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        CompanySetting::create([
            'company_name' => 'SIS.COM',
            'tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'pp55_active' => true,
        ]);

        $invoice = $this->createProjectInvoice(
            $project,
            '2026-11-10',
            '#INV-TAX-001',
        );

        $document = ProcurementDocument::create([
            'project_id' => $project->id,
            'invoice_id' => $invoice->id,
            'created_by' => $admin->id,
            'type' => 'payment_request',
            'document_number' => '001/SPP/SIS/XI/2026',
            'document_date' => '2026-11-10',
            'status' => 'ready',
            'amount' => 5000000,
        ]);

        $this->actingAs($admin)
            ->put(route('invoices.update', $invoice), [
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => '2026-11-10',
                'due_date' => '2026-11-17',
                'status' => 'unpaid',
                'invoice_type' => 'project',
                'vat_percent' => 11,
                'service_fee' => 10000,
                'cashback' => 0,
                'income_tax_withheld' => 1,
                'late_fee_active' => 0,
                'description' => ['Biaya Project - '.$project->title],
                'qty' => [1],
                'price' => [5000000],
                'duration' => [1],
                'duration_type' => ['Hari'],
                'start_date' => [null],
                'end_date' => [null],
                'notes' => 'Pengujian profil pajak',
            ])
            ->assertRedirect(route('invoices.index'));

        $invoice->refresh();
        $document->refresh();

        $this->assertSame('non_pkp', $invoice->provider_tax_status);
        $this->assertSame('umkm_final', $invoice->income_tax_scheme);
        $this->assertEquals(0, $invoice->vat_percent);
        $this->assertEquals(0, $invoice->vat);
        $this->assertSame('5010000.00', $invoice->income_tax_base);
        $this->assertSame('25050.00', $invoice->income_tax_amount);
        $this->assertSame(4984950.0, $invoice->net_receivable);

        $this->assertSame('umkm_final', $document->provider_tax_status);
        $this->assertSame('5010000.00', $document->tax_base);
        $this->assertEquals(0, $document->vat_amount);
        $this->assertSame('25050.00', $document->income_tax_amount);
    }

    public function test_admin_can_store_pp55_certificate_in_private_storage(): void
    {
        Storage::fake('local');
        [$admin] = $this->createAdminAndProject();

        CompanySetting::create([
            'company_name' => 'SIS.COM',
            'tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'pp55_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('procurement-documents.tax-documents.update'), [
                'tax_status' => 'non_pkp',
                'income_tax_scheme' => 'umkm_final',
                'income_tax_rate' => 0.50,
                'pp55_active' => 1,
                'pp55_certificate_number' => 'KET-TEST/PP55/2026',
                'pp55_certificate_date' => '2026-11-10',
                'pp55_certificate_document' => UploadedFile::fake()
                    ->create('surat-keterangan-pp55.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect();

        $company = CompanySetting::firstOrFail();
        $this->assertSame('KET-TEST/PP55/2026', $company->pp55_certificate_number);
        $this->assertSame('2026-11-10', $company->pp55_certificate_date->toDateString());
        Storage::disk('local')->assertExists($company->pp55_certificate_document);
    }

    public function test_document_type_submenu_only_lists_the_selected_document_type(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'quotation',
            'document_number' => '001/SPH/SIS/XI/2026',
            'document_date' => '2026-11-10',
            'status' => 'ready',
            'amount' => 5000000,
        ]);

        ProcurementDocument::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'type' => 'handover',
            'document_number' => '001/BAST/SIS/XI/2026',
            'document_date' => '2026-11-10',
            'status' => 'ready',
            'amount' => 5000000,
        ]);

        $this->actingAs($admin)
            ->get(route('procurement-documents.type', 'quotation'))
            ->assertOk()
            ->assertSee('001/SPH/SIS/XI/2026')
            ->assertDontSee('001/BAST/SIS/XI/2026');
    }

    public function test_third_party_spj_calculates_vendor_cost_tax_and_net_profit(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        CompanySetting::create([
            'company_name' => 'SIS.COM',
            'tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'pp55_active' => true,
        ]);

        $invoice = $this->createProjectInvoice(
            $project,
            '2026-11-10',
            '#INV-THIRD-PARTY',
        );
        $invoice->update([
            'income_tax_base' => 5000000,
            'income_tax_rate' => 0.50,
        ]);

        $this->actingAs($admin)
            ->put(route('procurement-documents.packages.update', $project), [
                'package_type' => 'third_party',
                'procurement_method' => 'non_siplah',
                'status' => 'documents',
                'vendor_name' => 'Vendor Teknologi Contoh',
                'contract_value' => 5000000,
                'vendor_cost' => 4000000,
                'operational_cost' => 100000,
                'partner_fee' => 0,
                'target_fee_percent' => 10,
                'tax_collection_method' => 'withheld',
            ])
            ->assertRedirect(route('procurement-documents.packages.edit', $project));

        $package = ProcurementPackage::firstOrFail();

        $this->assertSame('third_party', $package->package_type);
        $this->assertSame('1000000.00', $package->management_fee_amount);
        $this->assertSame('25000.00', $package->income_tax_amount);
        $this->assertSame('875000.00', $package->net_profit);
        $this->assertEquals(17.5, $package->actual_fee_percent);
    }

    public function test_admin_can_upload_and_download_a_private_vendor_document(): void
    {
        Storage::fake('local');
        [$admin, $project] = $this->createAdminAndProject();

        $package = ProcurementPackage::create([
            'project_id' => $project->id,
            'package_type' => 'third_party',
            'procurement_method' => 'non_siplah',
            'status' => 'documents',
            'vendor_name' => 'Vendor Teknologi Contoh',
            'contract_value' => 5000000,
            'vendor_cost' => 4000000,
        ]);

        $this->actingAs($admin)
            ->post(route('procurement-documents.packages.files.store', $package), [
                'category' => 'vendor_invoice',
                'title' => 'Invoice Vendor November 2026',
                'file' => UploadedFile::fake()->create(
                    'invoice-vendor.pdf',
                    100,
                    'application/pdf',
                ),
            ])
            ->assertRedirect();

        $file = $package->files()->firstOrFail();

        Storage::disk('local')->assertExists($file->file_path);
        $this->actingAs($admin)
            ->get(route('procurement-documents.packages.files.download', $file))
            ->assertDownload('invoice-vendor.pdf');
    }

    public function test_admin_can_create_a_detailed_general_invoice(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        $this->actingAs($admin)
            ->post(route('invoices.store'), [
                'client_id' => $project->client_id,
                'invoice_type' => 'general',
                'invoice_number' => '#INV-GENERAL-001',
                'invoice_date' => '2026-11-10',
                'due_date' => '2026-11-17',
                'status' => 'unpaid',
                'vat_percent' => 0,
                'service_fee' => 0,
                'late_fee_active' => 0,
                'income_tax_withheld' => 0,
                'description' => ['Laptop Laboratorium'],
                'item_type' => ['goods'],
                'specification' => ['RAM 16 GB, SSD 512 GB'],
                'qty' => [2],
                'unit' => ['Unit'],
                'price' => [7500000],
                'duration' => [1],
                'duration_type' => ['Hari'],
                'start_date' => [null],
                'end_date' => [null],
            ])
            ->assertRedirect(route('invoices.index'));

        $invoice = Invoice::where('invoice_number', '#INV-GENERAL-001')->firstOrFail();
        $item = InvoiceItem::where('invoice_id', $invoice->id)->firstOrFail();

        $this->assertSame('general', $invoice->invoice_type);
        $this->assertSame('goods', $item->item_type);
        $this->assertSame('Unit', $item->unit);
        $this->assertSame('RAM 16 GB, SSD 512 GB', $item->specification);
        $this->assertNull($item->duration);
        $this->assertSame('15000000.00', $invoice->subtotal);
    }

    public function test_spj_invoice_requires_project_and_does_not_use_service_duration(): void
    {
        [$admin, $project] = $this->createAdminAndProject();

        $this->actingAs($admin)
            ->post(route('invoices.store'), [
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'invoice_type' => 'spj',
                'invoice_number' => '#INV-SPJ-001',
                'invoice_date' => '2026-11-10',
                'due_date' => '2026-11-17',
                'status' => 'unpaid',
                'vat_percent' => 0,
                'service_fee' => 0,
                'late_fee_active' => 0,
                'income_tax_withheld' => 0,
                'description' => ['Pengadaan Perangkat Jaringan'],
                'item_type' => ['goods'],
                'specification' => ['Access point dual band'],
                'qty' => [5],
                'unit' => ['Pcs'],
                'price' => [1000000],
                'duration' => [12],
                'duration_type' => ['Bulan'],
                'start_date' => ['2026-11-10'],
                'end_date' => ['2027-11-09'],
            ])
            ->assertRedirect(route('invoices.index'));

        $invoice = Invoice::where('invoice_number', '#INV-SPJ-001')->firstOrFail();
        $item = $invoice->items()->firstOrFail();

        $this->assertSame('spj', $invoice->invoice_type);
        $this->assertSame($project->id, $invoice->project_id);
        $this->assertNull($item->duration);
        $this->assertNull($item->start_date);
        $this->assertNull($item->end_date);
    }

    private function createAdminAndProject(bool $withClientUser = false): array
    {
        $adminRole = Role::create(['name' => 'super_admin']);
        $clientRole = Role::create(['name' => 'client']);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $clientUser = $withClientUser ? User::factory()->create() : null;
        $clientUser?->assignRole($clientRole);

        $client = Client::create([
            'user_id' => $clientUser?->id,
            'name' => 'SMK Contoh',
            'email' => 'client@example.test',
        ]);

        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'Sistem Informasi Sekolah',
            'budget' => 5000000,
            'status' => 'pending',
            'progress' => 0,
        ]);

        return [$admin, $project, $clientUser];
    }

    private function createProjectInvoice(
        Project $project,
        string $invoiceDate,
        string $invoiceNumber,
    ): Invoice {
        return Invoice::create([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'due_date' => $invoiceDate,
            'status' => 'unpaid',
            'invoice_type' => 'project',
            'subtotal' => $project->budget,
            'grand_total' => $project->budget,
        ]);
    }
}
