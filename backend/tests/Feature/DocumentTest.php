<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\File;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_upload_document()
    {
        Storage::fake('local');

        $role = Role::firstOrCreate(['name' => 'employee']);
        $employee = User::factory()->create();
        $employee->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $file = File::create([
            'client_id' => $client->id,
            'file_type' => 'GST',
            'assessment_year' => '2024-2025',
            'status' => 'assigned',
            'assigned_to' => $employee->id,
        ]);

        $document = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($employee)->postJson("/api/files/{$file->id}/documents", [
            'file' => $document,
            'type' => 'notice',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'original_name' => 'document.pdf',
                'type' => 'notice',
            ]);

        // Assert the file was stored...
        // Note: The path in database will be 'documents/{file_id}/{hash}.pdf'
        $path = $response->json('file_path');
        Storage::assertExists($path);

        $this->assertDatabaseHas('documents', [
            'file_id' => $file->id,
            'uploaded_by' => $employee->id,
            'type' => 'notice',
        ]);
    }

    public function test_can_list_documents_for_file()
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $manager = User::factory()->create();
        $manager->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $file = File::create([
            'client_id' => $client->id,
            'file_type' => 'GST',
            'assessment_year' => '2024-2025',
            'status' => 'assigned',
        ]);

        \App\Models\Document::create([
            'file_id' => $file->id,
            'uploaded_by' => $manager->id,
            'file_path' => 'dummy/path.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'type' => 'challan',
        ]);

        $response = $this->actingAs($manager)->getJson("/api/files/{$file->id}/documents");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'original_name', 'type', 'file_path']
            ]);
    }
}
