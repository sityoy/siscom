<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ProcurementDocument;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
            'status' => 'draft',
            'amount' => 5000000,
            'transaction_method' => 'non_siplah',
        ]);

        $document = ProcurementDocument::firstOrFail();

        $response->assertRedirect(route('procurement-documents.show', $document));
        $this->assertSame('001/SPH/SIS/X/2026', $document->document_number);
        $this->assertSame('draft', $document->status);
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
            'status' => 'awaiting_signature',
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
            'status' => 'draft',
            'amount' => 5000000,
            'transaction_method' => 'non_siplah',
        ])->assertRedirect();

        $document = ProcurementDocument::firstOrFail();
        $this->assertSame('Drs. Kepala Sekolah', $document->school_signer_name);
        $this->assertSame('Kepala Sekolah', $document->school_signer_title);
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
            'status' => 'draft',
            'amount' => 5000000,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('procurement-documents.package.destroy', $project),
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('procurement_documents', ['project_id' => $project->id]);
        Storage::disk('public')->assertMissing($signedPath);
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
}
