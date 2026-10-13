<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\MahasiswaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Mahasiswa::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|numeric',
            'nama' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'angkatan' => 'required|numeric'
        ]);

        $mhs = Mahasiswa::create([
            'nim' => $request->nim,
            'nama' => $request->nama,
            'jurusan' => $request->jurusan,
            'angkatan' => $request->angkatan
        ]);

        MahasiswaLog::create([
            'action' => 'CREATE',
            'data' => $mhs->toArray(),
            'created_at' => now()
        ]);

        return response()->json([
            'message' => 'Data mahasiswa berhasil ditambahkan',
            'data' => $mhs
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if (!$mahasiswa) {
            return response()->json([
                'message' => 'Data mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json($mahasiswa, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if (!$mahasiswa) {
            return response()->json([
                'message' => 'Data mahasiswa tidak ditemukan'
            ], 404);
        }

        // Validasi dan update data
        $mahasiswa->update([
            'nim' => $request->nim ?? $mahasiswa->nim,
            'nama' => $request->nama ?? $mahasiswa->nama,
            'jurusan' => $request->jurusan ?? $mahasiswa->jurusan,
            'angkatan' => $request->angkatan ?? $mahasiswa->angkatan,
        ]);

        $method = strtoupper($request->method());

        MahasiswaLog::create([
            'action' => "UPDATE ({$method})",
            'data' => $mahasiswa->toArray(),
            'created_at' => now()
        ]);

        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function generateEmbedding()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
            'apikey' => env('SUPABASE_KEY'),
        ])->post(env('SUPABASE_URL') . '/functions/v1/swift-responder');

        return response()->json($response->json(), $response->status());
    }

    public function vectorSearch(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $url = env('SUPABASE_URL') . '/functions/v1/smart-task';
        $key = env('SUPABASE_KEY');

        $response = Http::withHeaders([
            'apikey' => $key,
        ])->post($url, [
            'text' => $request->text,
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if ($mahasiswa) {
            $data = $mahasiswa->toArray();
            $mahasiswa->delete();

            MahasiswaLog::create([
                'action' => 'DELETE',
                'data' => $data,
                'created_at' => now()
            ]);
        }

        return response()->json(["message" => "deleted"]);
    }
}
