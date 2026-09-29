<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    public function test_file_upload_requires_file_parameter(): void
    {
        $response = $this->postJson('/api/upload', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_file_upload_successfully_uploads_to_supabase(): void
    {
        Config::set('services.supabase.url', 'https://test-project.supabase.co');
        Config::set('services.supabase.key', 'test-api-key');
        Config::set('services.supabase.bucket', 'test-bucket');

        Http::fake([
            'https://test-project.supabase.co/storage/v1/object/test-bucket/*' => Http::response([
                'Id' => 'test-id-123',
                'Key' => 'test-bucket/uploads/test.png',
            ], 200),
        ]);

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/upload', [
            'file' => $file,
            'folder' => 'avatars',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully to Supabase Storage',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'path',
                    'filename',
                    'original_name',
                    'mime_type',
                    'size',
                    'public_url',
                ],
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://test-project.supabase.co/storage/v1/object/test-bucket/avatars/') &&
                $request->hasHeader('Authorization', 'Bearer test-api-key') &&
                $request->hasHeader('apiKey', 'test-api-key');
        });
    }

    public function test_file_upload_handles_supabase_error(): void
    {
        Config::set('services.supabase.url', 'https://test-project.supabase.co');
        Config::set('services.supabase.key', 'test-api-key');
        Config::set('services.supabase.bucket', 'invalid-bucket');

        Http::fake([
            'https://test-project.supabase.co/storage/v1/object/invalid-bucket/*' => Http::response([
                'statusCode' => '404',
                'error' => 'Bucket not found',
                'message' => 'Bucket not found',
            ], 404),
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
    }
}
