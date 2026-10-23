<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpatialController extends Controller
{
    /**
     * PUT /api/mahasiswa/{id}/lokasi
     */
    public function updateLokasi(Request $request, int $id)
    {
        $data = $request->validate([
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
        ]);

        $exists = DB::table('mahasiswas')->where('id', $id)->exists();

        if (!$exists) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan'], 404);
        }

        DB::update(
            'UPDATE mahasiswas 
             SET lokasi = extensions.ST_SetSRID(
                 extensions.ST_MakePoint(CAST(? AS double precision), CAST(? AS double precision)), 
                 4326
             ) 
             WHERE id = ?',
            [$data['longitude'], $data['latitude'], $id]
        );

        return response()->json([
            'message'   => 'Lokasi mahasiswa berhasil diperbarui',
            'id'        => $id,
            'longitude' => (float) $data['longitude'],
            'latitude'  => (float) $data['latitude'],
        ]);
    }

    /**
     * POST /api/mahasiswa/jarak
     */
    public function hitungJarak(Request $request)
    {
        $data = $request->validate([
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
        ]);

        $rows = DB::select(
            'SELECT 
                id,
                nim,
                nama,
                extensions.ST_X(lokasi::extensions.geometry) AS longitude,
                extensions.ST_Y(lokasi::extensions.geometry) AS latitude,
                ROUND(
                    extensions.ST_Distance(
                        lokasi::extensions.geography,
                        extensions.ST_SetSRID(extensions.ST_MakePoint(?, ?), 4326)::extensions.geography
                    )::numeric,
                    2
                ) AS jarak_meter
             FROM mahasiswas
             WHERE lokasi IS NOT NULL
             ORDER BY jarak_meter ASC',
            [$data['longitude'], $data['latitude']]
        );

        return response()->json([
            'titik_acuan' => [
                'longitude' => (float) $data['longitude'],
                'latitude'  => (float) $data['latitude'],
            ],
            'data' => $rows,
        ]);
    }

    /**
     * POST /api/mahasiswa/radius
     */
    public function dalamRadius(Request $request)
    {
        $data = $request->validate([
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'radius_meter' => ['required', 'numeric', 'min:1', 'max:100000'],
        ]);

        $rows = DB::select(
            'SELECT 
                id,
                nim,
                nama,
                ROUND(
                    extensions.ST_Distance(
                        lokasi::extensions.geography,
                        extensions.ST_SetSRID(extensions.ST_MakePoint(?, ?), 4326)::extensions.geography
                    )::numeric,
                    2
                ) AS jarak_meter
             FROM mahasiswas
             WHERE lokasi IS NOT NULL
             AND extensions.ST_DWithin(
                 lokasi::extensions.geography,
                 extensions.ST_SetSRID(extensions.ST_MakePoint(?, ?), 4326)::extensions.geography,
                 ?
             )
             ORDER BY jarak_meter ASC',
            [
                $data['longitude'],
                $data['latitude'],
                $data['longitude'],
                $data['latitude'],
                $data['radius_meter'],
            ]
        );

        return response()->json([
            'titik_acuan' => [
                'longitude' => (float) $data['longitude'],
                'latitude'  => (float) $data['latitude'],
            ],
            'radius_meter' => (float) $data['radius_meter'],
            'data'         => $rows,
        ]);
    }
}
