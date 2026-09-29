<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected string $url;
    protected string $key;
    protected string $bucket;

    public function __construct()
    {
        $this->url = rtrim((string) (config('services.supabase.url') ?? ''), '/');
        $this->key = (string) (config('services.supabase.key') ?? '');
        $this->bucket = (string) (config('services.supabase.bucket') ?? 'uploads');
    }

    /**
     * Upload an UploadedFile to Supabase Storage.
     *
     * @param UploadedFile $file
     * @param string|null $folder
     * @return array
     * @throws Exception
     */
    public function upload(UploadedFile $file, ?string $folder = null): array
    {
        if (empty($this->url) || empty($this->key)) {
            throw new Exception('Supabase configuration error: SUPABASE_URL and SUPABASE_KEY must be set in your .env file.');
        }

        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filenameWithoutExt);
        
        $fileNameToStore = time() . '_' . $safeFilename . ($extension ? '.' . $extension : '');
        $folderPath = $folder ? trim($folder, '/') : '';
        $path = $folderPath !== '' ? $folderPath . '/' . $fileNameToStore : $fileNameToStore;

        $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
        $fileContents = file_get_contents($file->getRealPath());

        $endpoint = "{$this->url}/storage/v1/object/{$this->bucket}/{$path}";

        $response = Http::timeout(15)->withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'apiKey' => $this->key,
            'Content-Type' => $mimeType,
            'x-upsert' => 'true',
        ])->withBody($fileContents, $mimeType)->post($endpoint);

        if (!$response->successful()) {
            $errorMessage = $response->json('message') ?? $response->json('error') ?? $response->body();
            Log::error('Supabase Storage Upload Error', [
                'status' => $response->status(),
                'response' => $response->body(),
                'path' => $path,
            ]);

            throw new Exception("Failed to upload file to Supabase Storage: {$errorMessage}");
        }

        $publicUrl = "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";

        return [
            'path' => $path,
            'filename' => $fileNameToStore,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'public_url' => $publicUrl,
        ];
    }
}
