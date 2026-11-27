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
use App\Support\JobTitleTranslator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $this->get($report)->assertOk()->assertViewHas('netTotal', '500000.00')->assertViewHas('salaryTotal', '300000.00')
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
        $this->assertMatchesRegularExpression('~<strong>Kepala Sekolah</strong><br>\s+Yayasan Pengujian\s*</td>~', $indonesianHtml);
        $this->assertMatchesRegularExpression('~<strong>Principal</strong><br>\s+Yayasan Pengujian\s*</td>~', $englishHtml);
        $this->assertStringContainsString('<strong style="text-decoration:underline">Kepala Sekolah Pengujian</strong>', $englishHtml);

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
        $this->assertMatchesRegularExpression('~<strong>Principal</strong><br>\s*</td>~', $fallbackHtml);
        $this->assertStringContainsString('<strong style="text-decoration:underline">Kepala Sekolah Pengujian</strong>', $fallbackHtml);
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
