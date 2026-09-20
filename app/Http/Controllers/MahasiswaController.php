<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

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
        //
        $mhs = Mahasiswa::create([
            'nim' => $request->nim,
            'nama' => $request->nama,
            'jurusan' => $request->jurusan,
            'angkatan' => $request->angkatan
        ]);

        return response()->json($mhs);
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

        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        Mahasiswa::destroy($id);
        return response()->json(["message" => "deleted"]);
    }
}
