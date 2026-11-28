<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ProcurementDocument;
use App\Models\ProcurementPackage;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\InvoiceReminderMessageService;
use App\Support\JobTitleTranslator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $clientUser;

    private Client $client;

    private Project $project;

    private Invoice $invoice;

    private Payment $payment;

    private Ticket $ticket;

    private ProcurementDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'client']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->clientUser = User::factory()->create();
        $this->clientUser->assignRole('client');

        $this->client = Client::create([
            'user_id' => $this->clientUser->id,
            'name' => 'SMK Pengujian SIS.COM',
            'company' => 'Yayasan Pengujian',
            'email' => 'client-smoke@example.test',
            'phone' => '6281234567890',
            'address' => 'Jakarta',
            'authorized_official_name' => 'Kepala Sekolah Pengujian',
            'authorized_official_title' => 'Kepala Sekolah',
            'package_name' => 'Standard',
            'package_price' => 1500000,
            'subscription_start' => '2026-11-01',
            'subscription_end' => '2027-10-31',
            'grace_period_days' => 7,
        ]);

        $this->project = Project::create([
            'client_id' => $this->client->id,
            'title' => 'Sistem Informasi Sekolah',
            'description' => 'Data lengkap untuk pengujian seluruh halaman.',
            'budget' => 5000000,
            'late_fee_active' => true,
            'late_fee_per_month' => 100000,
            'deadline' => '2026-12-31',
            'status' => 'progress',
            'progress' => 50,
        ]);

        $this->invoice = Invoice::create([
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'invoice_number' => '#INV-SMOKE-001',
            'invoice_date' => '2026-11-16',
            'subtotal' => 5000000,
            'vat_percent' => 0,
            'vat' => 0,
            'service_fee' => 0,
            'cashback' => 5,
            'grand_total' => 5000000,
            'due_date' => '2026-11-30',
            'status' => 'partial',
            'invoice_type' => 'project',
        ]);

        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Pengembangan sistem sekolah',
            'item_type' => 'service',
            'qty' => 1,
            'unit' => 'Paket',
            'price' => 5000000,
            'total' => 5000000,
            'duration' => 12,
            'duration_type' => 'Bulan',
            'start_date' => '2026-11-16',
            'end_date' => '2027-11-15',
        ]);

        $this->payment = Payment::create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000000,
            'payment_date' => '2026-11-16',
            'payment_method' => 'Transfer Bank',
            'notes' => 'Pembayaran pengujian',
        ]);

        $this->ticket = Ticket::create([
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'subject' => 'Pengujian tiket dukungan',
            'message' => 'Mohon dilakukan pemeriksaan sistem.',
            'status' => 'open',
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_type' => 'client',
            'sender_name' => $this->clientUser->name,
            'message' => 'Pesan pengujian dari client.',
        ]);

        Notification::create([
            'client_id' => $this->client->id,
            'title' => 'Notifikasi pengujian',
            'message' => 'Isi notifikasi pengujian.',
        ]);

        AdminNotification::create([
            'title' => 'Notifikasi admin pengujian',
            'message' => 'Isi notifikasi admin pengujian.',
            'is_read' => false,
        ]);

        CompanySetting::create([
            'company_name' => 'SIS.COM',
            'legal_name' => 'PT SITYOY IT SOLUTIONS',
            'company_email' => 'sityoyitsolutions@gmail.com',
            'company_phone' => '081383205359',
            'company_address' => 'Tangerang, Banten',
            'tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'pp55_active' => true,
            'director_name' => 'Tio Irfan Antoni, S.Kom.',
            'director_title' => 'Direktur',
            'city' => 'Tangerang',
        ]);

        ProcurementPackage::create([
            'project_id' => $this->project->id,
            'package_type' => 'direct',
            'procurement_method' => 'non_siplah',
            'status' => 'documents',
            'contract_value' => 5000000,
        ]);

        $this->document = ProcurementDocument::create([
            'project_id' => $this->project->id,
            'invoice_id' => $this->invoice->id,
            'created_by' => $this->admin->id,
            'type' => 'quotation',
            'document_number' => '001/SPH/SIS/XI/2026',
            'document_date' => '2026-11-16',
            'status' => 'ready',
            'amount' => 5000000,
            'school_signer_name' => 'Kepala Sekolah Pengujian',
            'school_signer_title' => 'Kepala Sekolah',
            'provider_signer_name' => 'Tio Irfan Antoni, S.Kom.',
            'provider_signer_title' => 'Direktur',
        ]);
    }

    public function test_all_primary_admin_pages_have_no_server_error(): void
    {
        $this->actingAs($this->admin);

        $routes = [
            'dashboard' => route('admin.dashboard'),
            'users' => route('users.index'),
            'users.create' => route('users.create'),
            'users.edit' => route('users.edit', $this->admin),
            'clients' => route('clients.index'),
            'clients.create' => route('clients.create'),
            'clients.edit' => route('clients.edit', $this->client),
            'projects' => route('projects.index'),
            'projects.create' => route('projects.create'),
            'projects.edit' => route('projects.edit', $this->project),
            'project.files' => route('project.files', $this->project),
            'invoices' => route('invoices.index'),
            'invoices.create' => route('invoices.create'),
            'invoices.edit' => route('invoices.edit', $this->invoice),
            'invoices.pdf.id' => route('invoices.pdf', [$this->invoice, 'lang' => 'id']),
            'invoices.pdf.en' => route('invoices.pdf', [$this->invoice, 'lang' => 'en']),
            'invoice.view' => route('invoice.view', $this->invoice),
            'payments' => route('payments.index'),
            'payments.create' => route('payments.create'),
            'payments.edit' => route('payments.edit', $this->payment),
            'payments.export' => route('payments.export'),
            'notifications' => route('admin.notifications'),
            'settings' => route('settings.index'),
            'tickets' => route('admin.tickets.index'),
            'tickets.show' => route('admin.tickets.show', $this->ticket),
            'procurement-documents' => route('procurement-documents.index'),
            'procurement-documents.type' => route('procurement-documents.type', 'quotation'),
            'procurement-packages.direct' => route('procurement-documents.packages', ['mode' => 'direct']),
            'procurement-packages.third-party' => route('procurement-documents.packages', ['mode' => 'third_party']),
            'procurement-packages.edit' => route('procurement-documents.packages.edit', $this->project),
            'procurement-tax-documents' => route('procurement-documents.tax-documents'),
            'procurement-documents.create' => route('procurement-documents.create'),
            'procurement-documents.package' => route('procurement-documents.package', $this->project),
            'procurement-documents.show' => route('procurement-documents.show', $this->document),
            'procurement-documents.edit' => route('procurement-documents.edit', $this->document),
            'procurement-documents.pdf' => route('procurement-documents.pdf', $this->document),
        ];

        $this->assertRoutesDoNotReturnServerErrors($routes);
    }

    public function test_client_company_name_matches_the_length_supported_by_documents(): void
    {
        $this->actingAs($this->admin);
        $newUser = User::factory()->create();
        $company = 'Yayasan Pengujian Dokumen';
        $longCompany = str_repeat('a', 256);

        $this->post(route('clients.store'), ['user_id' => $newUser->id, 'company' => $longCompany])
            ->assertSessionHasErrors('company');
        $this->assertDatabaseMissing('clients', ['user_id' => $newUser->id]);

        $this->put(route('clients.update', $this->client), [
            'user_id' => $this->clientUser->id,
            'company' => $longCompany,
        ])->assertSessionHasErrors('company');
        $this->assertSame('Yayasan Pengujian', $this->client->fresh()->company);

        $this->put(route('clients.update', $this->client), [
            'user_id' => $this->clientUser->id,
            'company' => $company,
        ])->assertSessionHasNoErrors();
        $this->assertSame($company, $this->client->fresh()->company);
    }

    public function test_all_primary_client_pages_have_no_server_error(): void
    {
        $this->actingAs($this->clientUser);

        $routes = [
            'dashboard' => route('client.dashboard'),
            'projects' => route('client.projects'),
            'projects.show' => route('client.projects.show', $this->project),
            'project.files' => route('client.project.files', $this->project),
            'invoices' => route('client.invoices'),
            'invoices.pdf' => route('client.invoices.pdf', $this->invoice),
            'payments' => route('client.payments'),
            'procurement-documents' => route('client.procurement-documents.index'),
            'procurement-documents.pdf' => route('client.procurement-documents.pdf', $this->document),
            'profile' => route('client.profile'),
            'notifications' => route('client.notifications'),
            'tickets' => route('client.tickets.index'),
            'tickets.create' => route('client.tickets.create'),
            'tickets.show' => route('client.tickets.show', $this->ticket),
        ];

        $this->assertRoutesDoNotReturnServerErrors($routes);
    }

    public function test_final_payment_locks_only_its_invoice_even_for_a_later_payment_date(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 24));
        $other = $this->invoice->replicate()->fill(['invoice_number' => 'INV-OPEN']);
        $other->save();
        $this->invoice->update(['late_fee_active' => true, 'late_fee_per_month' => 100000]);
        $data = ['invoice_id' => $this->invoice->id, 'payment_date' => '2026-11-24', 'amount' => '4000000'];
        $this->actingAs($this->admin)->get(route('payments.create'))
            ->assertOk()->assertSeeText($this->invoice->invoice_number);
        $this->post(route('payments.store'), array_replace($data, ['amount' => '4000001']))
            ->assertSessionHasErrors('amount');
        $this->post(route('payments.store'), $data)->assertRedirect(route('payments.index'));
        $this->assertSame('paid', $this->invoice->fresh()->status);
        $this->get(route('payments.create'))->assertOk()
            ->assertDontSeeText($this->invoice->invoice_number)->assertSeeText('INV-OPEN');

        $latePayment = array_replace($data, ['amount' => '100000', 'payment_date' => '2027-01-01']);
        $this->post(route('payments.store'), $latePayment)->assertSessionHasErrors('invoice_id');
        $this->assertEquals(5000000, $this->invoice->payments()->sum('amount'));

        $otherPayment = $other->payments()->create(['payment_date' => '2026-11-24', 'amount' => 100000]);
        $this->put(route('payments.update', $otherPayment), $latePayment)->assertSessionHasErrors('invoice_id');
        $this->assertEquals($other->id, $otherPayment->fresh()->invoice_id);

        $this->get(route('payments.edit', $this->payment))->assertOk()->assertSeeText($this->invoice->invoice_number);
        $this->delete(route('payments.destroy', $this->payment))->assertRedirect();
        $this->assertSame('partial', $this->invoice->fresh()->status);
        $this->get(route('payments.create'))->assertOk()->assertSeeText($this->invoice->invoice_number);
    }

    public function test_manual_invoice_can_be_paid_without_a_project_or_proof(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 24));
        $data = [
            'client_id' => $this->client->id,
            'invoice_type' => 'general',
            'invoice_number' => 'INV-MANUAL',
            'invoice_date' => '2026-11-16',
            'due_date' => '2026-11-30',
            'status' => 'unpaid',
            'description' => ['Layanan manual'],
            'qty' => [1],
            'item_type' => ['service'],
            'unit' => ['Paket'],
            'price' => [1000000],
            'income_tax_withheld' => true,
        ];
        $this->actingAs($this->admin)->post(route('invoices.store'), $data)->assertRedirect(route('invoices.index'));
        $invoice = Invoice::where('invoice_number', $data['invoice_number'])->sole();
        $this->assertNull($invoice->project_id);
        $this->assertSame(995000.0, $invoice->net_receivable);

        $create = route('payments.create', ['invoice_id' => $invoice->id]);
        $this->get(route('invoices.index'))->assertOk()->assertSee('Catat pembayaran untuk invoice yang baru dibuat.')->assertSee($create);
        $this->get(route('invoices.edit', $invoice))->assertOk()->assertSee('Tambah Pembayaran')->assertSee($create);
        $html = $this->get($create)->assertOk()->assertSee('Tanpa proyek')->getContent();
        $this->assertMatchesRegularExpression('~<option value="'.$invoice->id.'"[^>]*\bselected\b~s', $html);
        $this->assertMatchesRegularExpression('~<input type="file" id="payment-proof"[^>]*>~', $html);
        $this->assertDoesNotMatchRegularExpression('~<input type="file" id="payment-proof"[^>]*\brequired\b~', $html);

        $payment = ['invoice_id' => $invoice->id, 'payment_date' => '2026-11-24', 'payment_method' => 'Cash'];
        $this->post(route('payments.store'), $payment + ['amount' => '400.000'])->assertRedirect(route('payments.index'));
        $invoice->refresh();
        $receipt = $invoice->payments()->sole();
        $this->assertSame('partial', $invoice->status);
        $this->assertNull($receipt->proof);
        $this->assertSame(595000.0, $invoice->outstandingBalance($invoice->recordedPaymentsTotal()));
        $this->assertStringContainsString('SISA TAGIHAN: Rp 595.000', app(InvoiceReminderMessageService::class)->build($invoice, 'https://example.test/invoice'));
        $this->assertStringContainsString('Rp 595.000', view('admin.invoices.pdf', [
            'invoice' => $invoice, 'company' => CompanySetting::firstOrFail(),
        ])->render());
        $this->get(route('payments.receipt', $receipt))->assertOk();

        $this->post(route('payments.store'), $payment + ['amount' => '595.000'])->assertRedirect(route('payments.index'));
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->get(route('invoices.index'))->assertOk()->assertSee('Invoice sudah lunas.')->assertDontSee($create);
        $this->get($create)->assertRedirect(route('payments.create'));
        $this->post(route('payments.store'), $payment + ['amount' => '1'])->assertSessionHasErrors('invoice_id');
        $this->assertSame(2, $invoice->payments()->count());
    }

    public function test_manual_invoice_can_continue_directly_to_payment_on_creation(): void
    {
        $this->actingAs($this->admin)->get(route('invoices.create', ['type' => 'general']))
            ->assertOk()->assertSee('Buat Invoice & Tambah Pembayaran');

        $data = [
            'client_id' => $this->client->id,
            'invoice_type' => 'general',
            'invoice_number' => 'INV-MANUAL-LANJUT',
            'invoice_date' => '2026-11-16',
            'due_date' => '2026-11-30',
            'status' => 'unpaid',
            'continue_to_payment' => 1,
            'description' => ['Layanan manual'],
            'qty' => [1],
            'item_type' => ['service'],
            'unit' => ['Paket'],
            'price' => [1000000],
        ];
        $this->post(route('invoices.store'), array_replace($data, ['price' => [0]]))
            ->assertSessionHasErrors(['continue_to_payment' => 'Tidak ada sisa tagihan.']);
        $this->post(route('invoices.store'), array_replace($data, ['status' => 'cancelled']))
            ->assertSessionHasErrors(['continue_to_payment' => 'Invoice dibatalkan.']);
        $this->assertDatabaseMissing('invoices', ['invoice_number' => $data['invoice_number']]);

        $response = $this->post(route('invoices.store'), $data);
        $invoice = Invoice::where('invoice_number', $data['invoice_number'])->sole();
        $this->assertNull($invoice->project_id);
        $create = route('payments.create', ['invoice_id' => $invoice->id]);
        $response->assertRedirect($create);
        $html = $this->get($create)->assertOk()->assertSee('Tambah Pembayaran')->assertSee('Tanpa proyek')->getContent();
        $this->assertMatchesRegularExpression('~<option value="'.$invoice->id.'"[^>]*\bselected\b~s', $html);
    }

    public function test_paid_manual_invoice_can_record_received_settlement_once(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 27));
        $data = [
            'client_id' => $this->client->id,
            'invoice_type' => 'general',
            'invoice_number' => 'INV-MANUAL-LUNAS',
            'invoice_date' => '2026-11-27',
            'due_date' => '2026-11-28',
            'status' => 'paid',
            'continue_to_payment' => 1,
            'income_tax_withheld' => 1,
            'late_fee_active' => 1,
            'late_fee_per_month' => 100000,
            'description' => ['Barang'],
            'qty' => [1],
            'item_type' => ['goods'],
            'unit' => ['Pcs'],
            'price' => [140704],
        ];
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), $data);
        $invoice = Invoice::where('invoice_number', 'INV-MANUAL-LUNAS')->sole();
        $paidAt = $invoice->paid_at->toDateString();
        $this->assertSame(140000.48, round($invoice->net_receivable, 2));
        $create = route('payments.create', ['invoice_id' => $invoice->id]);
        $response->assertRedirect($create);

        $this->get(route('invoices.index'))->assertOk()->assertSee('Catat Pelunasan')->assertSee($create);
        $this->get(route('invoices.edit', $invoice))->assertOk()->assertSee('Catat Pelunasan');
        $this->get(route('payments.create'))->assertOk()->assertSee('Lunas, pembayaran belum tercatat');
        $this->get($create)->assertOk()->assertSee('Saya sudah menerima pembayaran sebesar tagihan bersih.');

        $form = ['invoice_id' => $invoice->id, 'payment_date' => '2026-11-27', 'amount' => '140.000'];
        $this->from($create)->post(route('payments.store'), $form)->assertSessionHasErrors('confirm_received');
        $form += ['confirm_received' => 1];
        $this->post(route('payments.store'), array_replace($form, ['amount' => '139.999']))
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, $invoice->payments()->count());

        $this->post(route('payments.store'), $form)->assertRedirect(route('payments.index'));
        $payment = $invoice->payments()->sole();
        $this->assertTrue($payment->recorded_settlement);
        $this->assertEquals(140000, $payment->amount);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->get(route('payments.receipt', $payment))->assertOk();
        $this->get(route('payments.create'))->assertOk()->assertDontSee('INV-MANUAL-LUNAS');
        $this->post(route('payments.store'), $form)->assertSessionHasErrors('invoice_id');
        $this->assertSame(1, $invoice->payments()->count());

        unset($form['confirm_received']);
        $this->put(route('payments.update', $payment), array_replace($form, ['invoice_id' => $this->invoice->id]))
            ->assertSessionHasErrors('invoice_id');
        $this->put(route('payments.update', $payment), array_replace($form, ['payment_date' => '2026-11-29']))
            ->assertRedirect(route('payments.index'));
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame($paidAt, $invoice->fresh()->paid_at->toDateString());
        $this->assertEquals(140000, $payment->fresh()->amount);
    }

    public function test_cancelled_invoices_reject_new_payments_and_keep_their_status(): void
    {
        $invoice = $this->invoice;
        $invoice->update(['status' => 'cancelled']);
        $this->actingAs($this->admin)->get(route('payments.create'))->assertDontSee($invoice->invoice_number);
        $this->post(route('payments.store'), [
            'invoice_id' => $invoice->id, 'payment_date' => '2026-11-24', 'amount' => '1',
        ])->assertSessionHasErrors('invoice_id');
        $this->delete(route('payments.destroy', $this->payment))->assertRedirect();
        $this->assertSame('cancelled', $invoice->fresh()->status);
    }

    public function test_legacy_invoice_backfill_only_restores_manual_invoices_without_current_totals(): void
    {
        $legacy = $this->client->invoices()->create(['invoice_number' => 'INV-OLD-MANUAL', 'status' => 'unpaid']);
        $intentionalZero = $this->client->invoices()->create(['invoice_number' => 'INV-ZERO', 'status' => 'unpaid']);
        $intentionalZero->items()->create(['description' => 'Layanan gratis', 'qty' => 1, 'price' => 0, 'total' => 0]);
        DB::table('invoices')->whereIn('id', [$legacy->id, $intentionalZero->id, $this->invoice->id])
            ->update(['amount' => 1250000]);

        $migration = require database_path('migrations/2026_11_27_000020_sync_legacy_manual_invoice_amounts.php');
        $migration->up();
        $migration->up();

        $this->assertSame('1250000.00', $legacy->fresh()->grand_total);
        $this->assertSame('1250000.00', $legacy->fresh()->subtotal);
        $this->assertSame('0.00', $intentionalZero->fresh()->grand_total);
        $this->assertSame('5000000.00', $this->invoice->fresh()->grand_total);
        $this->actingAs($this->admin)->get(route('payments.create'))->assertOk()
            ->assertSee('INV-OLD-MANUAL')->assertDontSee('INV-ZERO');
    }

    public function test_settled_legacy_invoices_cannot_receive_a_new_payment(): void
    {
        $this->actingAs($this->admin);
        $data = ['invoice_id' => $this->invoice->id, 'payment_date' => '2026-11-24', 'amount' => '1'];
        $this->invoice->update(['status' => 'paid']);
        $this->post(route('payments.store'), $data)->assertSessionHasErrors('invoice_id');

        $this->invoice->update(['status' => 'partial', 'grand_total' => $this->payment->amount]);
        $this->get(route('payments.create'))->assertOk()->assertSeeText('Tidak ada invoice dengan sisa tagihan');
        $this->post(route('payments.store'), $data)->assertSessionHasErrors('invoice_id');
        $this->assertSame(1, $this->invoice->payments()->count());
    }

    public function test_manual_invoice_backfill_restores_missing_totals_from_consistent_items(): void
    {
        $invoices = collect([
            'INV-ITEM-MANUAL' => ['subtotal' => 0, 'status' => 'unpaid'],
            'INV-ITEM-MATCH' => ['subtotal' => 1250000, 'status' => 'partial'],
            'INV-ITEM-AMBIGUOUS' => ['subtotal' => 1000000, 'status' => 'unpaid'],
            'INV-ITEM-CANCELLED' => ['subtotal' => 0, 'status' => 'cancelled'],
            'INV-ITEM-TAXED' => ['subtotal' => 0, 'status' => 'unpaid', 'vat_percent' => 11],
        ])->map(function ($fields, $number) {
            $invoice = $this->client->invoices()->create($fields + ['invoice_number' => $number, 'vat_percent' => 0]);
            $invoice->items()->create(['description' => 'Layanan', 'qty' => 2, 'price' => 625000, 'total' => 1250000]);

            return $invoice;
        });

        $migration = require database_path('migrations/2026_11_27_000021_restore_manual_invoice_totals_from_items.php');
        $migration->up();
        $migration->up();

        $this->assertSame('1250000.00', $invoices['INV-ITEM-MANUAL']->fresh()->grand_total);
        $this->assertSame('1250000.00', $invoices['INV-ITEM-MATCH']->fresh()->grand_total);
        $this->assertSame('0.00', $invoices['INV-ITEM-AMBIGUOUS']->fresh()->grand_total);
        $this->assertSame('0.00', $invoices['INV-ITEM-CANCELLED']->fresh()->grand_total);
        $this->assertSame('0.00', $invoices['INV-ITEM-TAXED']->fresh()->grand_total);
        $this->actingAs($this->admin)->get(route('payments.create'))->assertOk()
            ->assertSee('INV-ITEM-MANUAL')->assertDontSee('INV-ITEM-AMBIGUOUS')->assertDontSee('INV-ITEM-CANCELLED');
    }

    public function test_approval_and_addons_preserve_existing_school_modules(): void
    {
        $db = \Illuminate\Support\Facades\DB::connection();
        $db->statement("ATTACH DATABASE ':memory:' AS db_portalsiscom");
        $db->statement("CREATE TABLE db_portalsiscom.schools (id INTEGER PRIMARY KEY, code TEXT, npsn TEXT, name TEXT, contact TEXT, requested_package TEXT, requested_modules TEXT, active INTEGER, until_date TEXT, modules TEXT, siscom_user_id INTEGER, cbt_status TEXT)");
        $school = $db->table('db_portalsiscom.schools');
        $school->insert(['id'=>1,'name'=>'Sekolah Uji','code'=>'sis-uji','npsn'=>'12345678',
            'active'=>false,'modules'=>'','requested_package'=>'pilihan','requested_modules'=>'keuangan']);
        $this->actingAs($this->admin)->get(route('school-registrations.index'))->assertOk()->assertSeeText('CBT + modul pilihan');
        $this->post(route('school-registrations.approve', 1), [
            'npsn'=>'12345678','until_date'=>today()->addMonth()->toDateString(),'modules'=>['cbt','keuangan'],
        ])->assertSessionHasNoErrors();
        $this->post(route('school-registrations.modules'), ['code'=>'sis-uji','npsn'=>'12345678','modules'=>['guru','cbt']])
            ->assertSessionHasNoErrors();
        $this->assertSame('cbt,keuangan,guru', $school->value('modules'));
        $this->post(route('school-registrations.modules'), ['code'=>'sis-uji','npsn'=>'12345679','modules'=>['sarpras']])
            ->assertSessionHasErrors('code');
        $this->assertSame('cbt,keuangan,guru', $school->value('modules'));
    }

    public function test_completed_project_clears_deadline_in_admin_and_client_views(): void
    {
        $this->project->update(['deadline'=>'2024-04-12','progress'=>100,'status'=>'progress']);
        $this->actingAs($this->admin)->get(route('projects.index'))->assertOk()
            ->assertSeeText('Selesai')->assertDontSeeText('12 Apr 2024');
        $this->actingAs($this->clientUser)->get(route('client.projects'))->assertOk()
            ->assertSeeText('Selesai')->assertDontSeeText('12 Apr 2024');
        $this->get(route('client.projects.show', $this->project))->assertOk()
            ->assertSeeText('Selesai')->assertDontSeeText('12 Apr 2024');
        $this->project->update(['progress'=>90]);
        $this->get(route('client.projects.show', $this->project))->assertSeeText('12 Apr 2024');
    }

    public function test_salary_plan_uses_cash_received_less_capital_and_can_be_adjusted(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 24));
        $this->actingAs($this->admin)->post(route('capital.store'), [
            'invoice_id'=>$this->invoice->id, 'source'=>'Modal jasa', 'amount'=>200000, 'contribution_date'=>'2026-11-20',
        ])->assertSessionHasNoErrors();
        $url = route('salary.store', $this->invoice);
        $this->post($url, ['recipient'=>'Teknisi','mode'=>'percent','value'=>25])->assertSessionHasNoErrors();
        $this->post($url, ['recipient'=>'Operator','mode'=>'fixed','value'=>150000])->assertSessionHasNoErrors();
        $this->get(route('salary.show', $this->invoice))->assertOk()->assertSeeText('Rp 450.000')->assertSeeText('Kembali ke Modal Usaha');
        $this->post($url, ['recipient'=>'Operator','mode'=>'fixed','value'=>700000])->assertSessionHasErrors('value');
        $this->post($url, ['recipient'=>'Operator','mode'=>'fixed','value'=>100000])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('salary_allocations', 2);
        $this->get(route('salary.show', $this->invoice))->assertSeeText('Rp 500.000');
        $report = route('capital.index', ['invoice_id'=>$this->invoice->id, 'month'=>'2026-11']);
        $this->get($report)->assertOk()
            ->assertViewHas('cashInTotal', '1000000.00')->assertViewHas('expenseTotal', '200000.00')
            ->assertViewHas('netTotal', '500000.00')->assertViewHas('salaryTotal', '300000.00')
            ->assertViewHas('invoiceBalances', fn ($rows) => $rows->first()['difference'] === '500000.00');
        $allocation = DB::table('salary_allocations')->where('recipient','Operator')->value('id');
        $this->delete(route('salary.destroy', [$this->invoice,$allocation]))->assertRedirect();
        $this->get($report)->assertViewHas('netTotal', '600000.00');
        $this->actingAs($this->clientUser)->get(route('salary.show', $this->invoice))->assertForbidden();
    }

    public function test_admin_can_download_school_proposal_with_manual_and_blank_prices(): void
    {
        $this->actingAs($this->admin)->get(route('school-registrations.proposal'))->assertOk();
        $data = ['school'=>'Sekolah Uji', 'title'=>'Kepala Sekolah', 'date'=>'2026-11-24',
            'period'=>'semester','packages'=>['cbt','pilihan'],'prices'=>['cbt'=>1500000], 'modules'=>['guru']];
        $this->post(route('school-registrations.proposal.pdf'),$data)->assertOk()->assertDownload('Proposal-Layanan-Sekolah.pdf');
        $this->post(route('school-registrations.proposal.pdf'),array_replace($data,['prices'=>['cbt'=>-1]]))->assertSessionHasErrors('prices.cbt');
        $this->actingAs($this->clientUser)->post(route('school-registrations.proposal.pdf'),$data)->assertForbidden();
    }

    public function test_payment_accepts_indonesian_currency_format(): void
    {
        $this->actingAs($this->admin)
            ->post(route('payments.store'), [
                'invoice_id' => $this->invoice->id,
                'amount' => '896.500,39',
                'payment_date' => '2026-11-16',
                'payment_method' => 'Transfer Bank',
                'notes' => 'Pembayaran dengan format Indonesia',
            ])
            ->assertRedirect(route('payments.index'));

        $payment = Payment::query()
            ->where('notes', 'Pembayaran dengan format Indonesia')
            ->firstOrFail();

        $this->assertEqualsWithDelta(896500.39, (float) $payment->amount, 0.001);

        $this->actingAs($this->admin)
            ->put(route('payments.update', $payment), [
                'invoice_id' => $this->invoice->id,
                'amount' => '1.000.000,50',
                'payment_date' => '2026-11-16',
                'payment_method' => 'Transfer Bank',
                'notes' => 'Pembayaran diperbarui',
            ])
            ->assertRedirect(route('payments.index'));

        $this->assertEqualsWithDelta(1000000.50, (float) $payment->fresh()->amount, 0.001);
    }

    public function test_invoice_pdf_translates_indonesian_signer_titles_to_english(): void
    {
        $this->invoice->update(['client_signature_mode' => null]);
        $invoice = $this->invoice->load(['client.user', 'project', 'items']);
        $company = CompanySetting::firstOrFail();

        $indonesianHtml = view('admin.invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'language' => 'id',
        ])->render();

        $englishHtml = view('admin.invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'language' => 'en',
        ])->render();

        $this->assertStringContainsString('<strong>Kepala Sekolah</strong>', $indonesianHtml);
        $this->assertStringContainsString('<strong>Direktur</strong>', $indonesianHtml);
        $this->assertStringContainsString('<strong>Principal</strong>', $englishHtml);
        $this->assertStringContainsString('<strong>Director</strong>', $englishHtml);
        $this->assertStringContainsString('Issued by,', $englishHtml);
        $this->assertStringContainsString('Approved by,', $englishHtml);
        $this->assertMatchesRegularExpression('~DITAGIHKAN KEPADA</div>\s+<strong>Yayasan Pengujian</strong>~', $indonesianHtml);
        $this->assertMatchesRegularExpression('~BILL TO</div>\s+<strong>Yayasan Pengujian</strong>~', $englishHtml);
        $this->assertMatchesRegularExpression('~<strong>Kepala Sekolah</strong>\s*<br>\s+Yayasan Pengujian\s+<div~', $indonesianHtml);
        $this->assertMatchesRegularExpression('~<strong>Principal</strong>\s*<br>\s+Yayasan Pengujian\s+<div~', $englishHtml);
        $this->assertStringContainsString('<span class="signer-name">Kepala Sekolah Pengujian</span>', $englishHtml);

        $issuedByPosition = strpos($englishHtml, 'Issued by,');
        $approvedByPosition = strpos($englishHtml, 'Approved by,');

        $this->assertIsInt($issuedByPosition);
        $this->assertIsInt($approvedByPosition);
        $this->assertLessThan($approvedByPosition, $issuedByPosition);

        $invoice->client->company = '  ';
        $fallbackHtml = view('admin.invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'language' => 'en',
        ])->render();

        $this->assertMatchesRegularExpression('~BILL TO</div>\s+<strong>SMK Pengujian SIS\.COM</strong>~', $fallbackHtml);
        $this->assertMatchesRegularExpression('~<strong>Principal</strong>\s*<br>\s*<div class="signature-space">~', $fallbackHtml);
        $this->assertStringContainsString('<span class="signer-name">Kepala Sekolah Pengujian</span>', $fallbackHtml);
    }

    public function test_payment_instructions_follow_bank_account_status(): void
    {
        $company = CompanySetting::firstOrFail();
        $invoice = $this->invoice;
        $pdf = fn () => view('admin.invoices.pdf', ['invoice' => $invoice, 'company' => $company->fresh()])->render();
        $message = fn () => app(InvoiceReminderMessageService::class)->build($invoice, 'https://example.test/invoice');
        $data = [
            'bank_name' => 'Bank Pembayaran',
            'account_number' => '000-321-001',
            'account_holder' => 'Pemilik Rekening Uji',
        ];
        $this->actingAs($this->admin)->post(route('settings.bank-accounts.store'), $data)
            ->assertSessionHas('success');
        $account = $company->bankAccounts()->sole();
        $this->assertTrue($account->is_active);
        $this->get(route('settings.index'))->assertOk()->assertSee($data['account_number'])->assertSee('Nonaktifkan');
        $this->post(route('settings.bank-accounts.store'), $data)->assertSessionHasErrorsIn('bankAccount', 'account_number');
        $this->assertSame(1, $company->bankAccounts()->count());
        $this->assertStringContainsString('<table class="banks">', $pdf());
        $this->assertStringContainsString($data['account_number'], $pdf());
        $this->assertStringContainsString('Bank Pembayaran: 000-321-001 (a/n Pemilik Rekening Uji)', $message());

        $status = route('settings.bank-accounts.status', $account);
        $this->actingAs($this->clientUser)->patch($status, ['is_active' => 0])->assertForbidden();
        $this->actingAs($this->admin)->patch($status, ['is_active' => 0])->assertSessionHas('success');
        $this->assertFalse($account->refresh()->is_active);
        $this->get(route('settings.index'))->assertOk()->assertSee($data['account_number'])->assertSee('Aktifkan kembali');
        $this->assertStringNotContainsString($data['account_number'], $pdf());
        $this->assertStringNotContainsString($data['account_number'], $message());
        $this->assertStringContainsString('Silakan hubungi admin untuk informasi rekening.', $message());

        $data['account_holder'] = 'Pemilik Baru';
        $this->put(route('settings.bank-accounts.update', $account), $data)->assertSessionHas('success');
        $this->assertFalse($account->refresh()->is_active);
        $this->patch($status, ['is_active' => 1])->assertSessionHas('success');
        $this->assertStringContainsString('Pemilik Baru', $pdf());
        $this->assertStringContainsString('000-321-001 (a/n Pemilik Baru)', $message());
    }

    public function test_monthly_bank_costs_use_actual_debits_and_keep_the_reserve_out_of_income(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 27));
        $company = CompanySetting::firstOrFail();
        $account = $company->bankAccounts()->create([
            'bank_name' => 'Bank Jakarta',
            'account_number' => '000-456-321',
            'account_holder' => 'Usaha Pengujian',
        ]);
        $settings = route('settings.bank-accounts.update', $account);
        $feeForm = route('settings.index', ['rekening_biaya' => $account->id]);
        $feeUrl = route('settings.bank-accounts.fees.store', $account);
        $report = route('capital.index', ['month' => '2026-11']);

        $this->actingAs($this->admin)->put($settings, [
            'bank_name' => $account->bank_name,
            'account_number' => $account->account_number,
            'account_holder' => $account->account_holder,
            'product_label' => 'Tabungan Bisnis · Platinum',
            'monthly_admin_fee' => '22.000',
            'minimum_balance' => '250.000',
        ])->assertSessionHasNoErrors();
        $this->get($feeForm)->assertOk()->assertSeeText('Tabungan Bisnis · Platinum')->assertSeeText('Rp 250.000');
        $this->get($report)->assertOk()->assertViewHas('bankFeeTotal', '0.00')->assertViewHas('netTotal', '1000000.00');

        $this->from($feeForm)->post($feeUrl, ['debited_on' => '2026-11-28', 'amount' => '22.000'])
            ->assertSessionHasErrorsIn('bankFee', 'debited_on');
        $this->post($feeUrl, ['debited_on' => '2026-10-31', 'amount' => '22.000'])->assertSessionHas('success');
        $this->post($feeUrl, ['debited_on' => '2026-11-25', 'amount' => '22.000'])->assertSessionHas('success');
        $this->post($feeUrl, ['debited_on' => '2026-11-26', 'amount' => '23.000'])->assertSessionHas('success');
        $this->assertSame(2, $account->administrationFees()->count());
        $fee = $account->administrationFees()->where('period', '2026-11')->sole();
        $this->get(route('settings.index', ['biaya' => $fee->id]))->assertOk()->assertSee('2026-11-26');
        $this->post($feeUrl, ['period' => '2026-11', 'debited_on' => '2026-10-25', 'amount' => '23.000'])
            ->assertSessionHasErrorsIn('bankFee', 'debited_on');

        $this->get($report)->assertOk()->assertViewHas('bankFeeTotal', '23000.00')
            ->assertViewHas('cashInTotal', '1000000.00')->assertViewHas('expenseTotal', '0.00')
            ->assertViewHas('netTotal', '977000.00');
        $this->get(route('capital.index'))->assertOk()->assertViewHas('bankFeeTotal', '45000.00');
        $this->get(route('capital.index', ['month' => '2026-11', 'invoice_id' => $this->invoice->id]))
            ->assertOk()->assertViewHas('bankFeeTotal', '0.00')->assertViewHas('netTotal', '1000000.00');

        $this->patch(route('settings.bank-accounts.status', $account), ['is_active' => 0])->assertSessionHas('success');
        $this->get($report)->assertOk()->assertViewHas('bankFeeTotal', '23000.00');
        $this->delete(route('settings.bank-accounts.fees.void', [$account, $fee]))->assertSessionHas('success');
        $this->get($report)->assertOk()->assertViewHas('bankFeeTotal', '0.00');
        $this->assertNotNull($fee->fresh()->voided_at);
        $this->actingAs($this->clientUser)->post($feeUrl, ['debited_on' => '2026-11-26', 'amount' => '22.000'])->assertForbidden();
    }

    public function test_migrating_legacy_bank_accounts_keeps_numbers_and_status_on_retry(): void
    {
        $company = CompanySetting::firstOrFail();
        DB::table('company_settings')->where('id', $company->id)->update([
            'bank_jakarta' => '001-22-001',
            'bank_jakarta_name' => 'Pemilik Rekening Lama',
            'bank_mandiri' => '001-33-002',
            'bank_bca' => '',
        ]);

        $migration = require database_path('migrations/2026_11_27_000018_create_payment_bank_accounts_table.php');
        $migration->down();
        $migration->up();

        $accounts = $company->bankAccounts()->get();
        $this->assertSame(['001-22-001', '001-33-002'], $accounts->pluck('account_number')->all());
        $this->assertSame('Pemilik Rekening Lama', $accounts->first()->account_holder);
        $this->assertTrue($accounts->first()->is_active);
        $accounts->first()->update(['is_active' => false]);

        $migration->up();
        $this->assertSame(2, $company->bankAccounts()->count());
        $this->assertSame(['001-33-002'], $company->activeBankAccounts()->pluck('account_number')->all());
    }

    public function test_invoice_signature_can_be_enabled_per_invoice(): void
    {
        $data = [
            'client_id' => $this->client->id,
            'invoice_type' => 'general',
            'invoice_number' => '#INV-SIGNATURE',
            'invoice_date' => '2026-11-16',
            'due_date' => '2026-12-16',
            'status' => 'unpaid',
            'vat_percent' => 0,
            'description' => ['Layanan CBT'],
            'qty' => [1],
            'price' => [3225000],
            'item_type' => ['service'],
            'unit' => ['Paket'],
        ];

        $this->actingAs($this->admin)->post(route('invoices.store'), $data)
            ->assertRedirect(route('invoices.index'));
        $invoice = Invoice::where('invoice_number', $data['invoice_number'])->firstOrFail();
        $company = CompanySetting::firstOrFail();

        $this->assertSame('none', $invoice->client_signature_mode);
        $html = view('admin.invoices.pdf', compact('invoice', 'company'))->render();
        $this->assertStringContainsString('Dibuat oleh,', $html);
        $this->assertStringNotContainsString('Kepala Sekolah Pengujian', $html);

        $this->put(route('invoices.update', $invoice), $data + ['client_signature_mode' => 'received'])
            ->assertRedirect(route('invoices.index'));
        $invoice->refresh();
        $this->assertSame('received', $invoice->client_signature_mode);
        $html = view('admin.invoices.pdf', compact('invoice', 'company'))->render();
        $this->assertStringContainsString('Diterima oleh,', $html);
        $this->assertStringContainsString('Kepala Sekolah Pengujian', $html);
        $this->assertStringNotContainsString('Disetujui oleh,', $html);

        $this->put(route('invoices.update', $invoice), $data + ['client_signature_mode' => 'invalid'])
            ->assertSessionHasErrors('client_signature_mode');
        $this->assertSame('received', $invoice->fresh()->client_signature_mode);
    }

    public function test_invoice_company_approval_supports_private_artwork_and_revocable_verification(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $upload = route('settings.invoice-artwork.store');
        $images = [
            'signature' => UploadedFile::fake()->image('signature.png', 120, 80),
            'stamp' => UploadedFile::fake()->image('stamp.png', 80, 80),
        ];
        $this->actingAs($this->clientUser)->post($upload, $images)->assertForbidden();

        $invoice = $this->invoice;
        $update = route('invoices.update', $invoice);
        $data = [
            'client_id' => $invoice->client_id,
            'invoice_type' => 'general',
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => '2026-11-16',
            'due_date' => '2026-12-16',
            'status' => 'unpaid',
            'vat_percent' => 0,
            'description' => ['Layanan CBT'],
            'qty' => [1],
            'price' => [3225000],
            'item_type' => ['service'],
            'unit' => ['Paket'],
        ];
        $company = CompanySetting::firstOrFail();
        $render = fn () => view('admin.invoices.pdf', ['invoice' => $invoice, 'company' => $company])->render();

        $this->actingAs($this->admin)->put($update, $data + ['company_signature_mode' => 'artwork'])
            ->assertSessionHasErrors('company_signature_mode');
        $this->assertNull($invoice->fresh()->verification_code);

        $this->post($upload, $images)->assertSessionHas('success');
        Storage::disk('local')->assertExists('company/invoice-approval.json');
        Storage::disk('public')->assertMissing('company/invoice-approval.json');
        $this->get(route('settings.index'))->assertOk()->assertSee('Tanda Tangan dan Stempel Invoice');

        $this->put($update, $data + ['company_signature_mode' => 'artwork'])->assertRedirect(route('invoices.index'));
        $invoice->refresh();
        $link = $invoice->verificationUrl();
        $this->assertNotNull($link);
        $html = $render();
        $this->assertStringContainsString('class="signature-image" src="data:image/png;base64,', $html);
        $this->assertStringContainsString('class="stamp-image" src="data:image/png;base64,', $html);
        $this->assertStringContainsString($link, $html);
        $this->get(route('invoices.pdf', $invoice))->assertOk();

        $this->app['auth']->forgetGuards();
        $this->get($link)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Invoice tercatat di SIS.COM')->assertSee($invoice->invoice_number)
            ->assertSee('Rp 3.225.000')->assertDontSee($this->client->email);

        $this->actingAs($this->admin)->put($update, $data + ['company_signature_mode' => 'qr'])
            ->assertRedirect(route('invoices.index'));
        $invoice->refresh();
        $newLink = $invoice->verificationUrl();
        $this->assertNotNull($newLink);
        $this->assertNotSame($link, $newLink);
        $this->get($link)->assertNotFound();
        $this->assertStringContainsString($newLink, $render());
        $this->assertStringNotContainsString('class="stamp-image"', $render());

        $invoice->update(['status' => 'cancelled']);
        $this->get($newLink)->assertOk()->assertSee('Invoice dibatalkan');
        $this->assertNull($invoice->verificationUrl());
        $this->put($update, $data + ['company_signature_mode' => 'blank'])->assertRedirect(route('invoices.index'));
        $this->assertNull($invoice->fresh()->verification_code);
        $this->get($newLink)->assertNotFound();
    }

    public function test_payment_receipt_downloads_for_admin_and_the_paying_client_only(): void
    {
        $this->invoice->update(['client_signature_mode' => 'approved']);
        $receiptNumber = 'NP-'.str_pad((string) $this->payment->id, 6, '0', STR_PAD_LEFT);
        $filename = "nota-pembayaran-{$receiptNumber}.pdf";

        $this->actingAs($this->admin)->get(route('payments.index'))
            ->assertOk()->assertSee(route('payments.receipt', $this->payment));
        $this->get(route('payments.receipt', $this->payment))->assertOk()->assertDownload($filename);

        $this->actingAs($this->clientUser)->get(route('client.payments'))
            ->assertOk()->assertSee(route('client.payments.receipt', $this->payment));
        $this->get(route('client.payments.receipt', $this->payment))->assertOk()->assertDownload($filename);

        $anotherUser = User::factory()->create()->assignRole('client');
        Client::create(['user_id' => $anotherUser->id, 'name' => 'Sekolah Lain']);
        $this->actingAs($anotherUser)->get(route('client.payments.receipt', $this->payment))->assertNotFound();

        $html = view('payments.receipt', [
            'payment' => $this->payment,
            'company' => CompanySetting::firstOrFail(),
            'receiptNumber' => $receiptNumber,
        ])->render();
        $this->assertStringContainsString('Rp 1.000.000', $html);
        $this->assertStringContainsString('Yayasan Pengujian', $html);
        $this->assertStringContainsString('Tio Irfan Antoni, S.Kom.', $html);
        $this->assertStringNotContainsString('Kepala Sekolah Pengujian', $html);
    }

    public function test_common_company_and_school_job_titles_are_translated(): void
    {
        $translations = [
            'Manajer' => 'Manager',
            'Manajer Operasional' => 'Operations Manager',
            'Manajer Keuangan' => 'Finance Manager',
            'Manajer Proyek' => 'Project Manager',
            'Komisaris Utama' => 'President Commissioner',
            'Direktur Operasional' => 'Operations Director',
            'Kepala Divisi' => 'Division Head',
            'Kepala Cabang' => 'Branch Manager',
            'Kepala Tata Usaha' => 'Head of Administration',
            'Wakasek Kurikulum' => 'Vice Principal for Curriculum',
            'Wakasek Kesiswaan' => 'Vice Principal for Student Affairs',
            'Operator Sekolah' => 'School Operator',
            'Pelanggan' => 'Customer',
            'Perwakilan Client' => 'Client Representative',
            'Pejabat Pembuat Komitmen' => 'Commitment-Making Officer',
        ];

        foreach ($translations as $indonesian => $english) {
            $this->assertSame(
                $english,
                JobTitleTranslator::translate($indonesian, 'en'),
            );
        }

        $this->assertSame(
            'Jabatan Khusus Sekolah',
            JobTitleTranslator::translate('Jabatan Khusus Sekolah', 'en'),
        );
    }

    /**
     * @param  array<string, string>  $routes
     */
    private function assertRoutesDoNotReturnServerErrors(array $routes): void
    {
        $failures = [];

        foreach ($routes as $name => $url) {
            $response = $this->get($url);

            if (! $response->isSuccessful()) {
                $exception = $response->exception;
                $failures[] = sprintf(
                    '%s [%d]: %s',
                    $name,
                    $response->getStatusCode(),
                    $exception?->getMessage() ?? 'Server error tanpa detail exception',
                );
            }
        }

        $this->assertSame([], $failures, implode(PHP_EOL, $failures));
    }
}
