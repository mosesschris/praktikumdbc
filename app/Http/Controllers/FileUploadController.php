<?php

namespace App\Http\Controllers;

use App\Services\SupabaseStorageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function __construct(
        protected SupabaseStorageService $supabaseStorageService
    ) {}

    /**
     * Handle file upload request to Supabase Storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:102400', // Maximum 100MB (in KB)
            'folder' => 'nullable|string|max:100',
        ]);

        try {
            $folder = $request->input('folder', 'uploads');
            $uploadResult = $this->supabaseStorageService->upload(
                $request->file('file'),
                $folder
            );

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully to Supabase Storage',
                'data' => $uploadResult,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
