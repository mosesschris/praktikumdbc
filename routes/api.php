<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\Neo4jController;
use App\Http\Controllers\SpatialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('mahasiswa', MahasiswaController::class);
Route::post('upload', [FileUploadController::class, 'upload'])->name('api.upload');
Route::post('/mahasiswa/generate-embedding', [MahasiswaController::class, 'generateEmbedding']);
Route::post('/mahasiswa/vector-search', [MahasiswaController::class, 'vectorSearch']);
Route::put('/mahasiswa/{id}/lokasi', [SpatialController::class, 'updateLokasi']);
Route::post('/mahasiswa/jarak', [SpatialController::class, 'hitungJarak']);
Route::post('/mahasiswa/radius', [SpatialController::class, 'dalamRadius']);
Route::get(
    '/mahasiswa/{nim}/makanan',
    [Neo4jController::class, 'makanan']
);
Route::get(
    '/mahasiswa/{nim}/rekomendasi',
    [Neo4jController::class, 'rekomendasi']
);
