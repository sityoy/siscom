<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ProcurementDocument;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
