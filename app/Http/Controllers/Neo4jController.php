<?php

namespace App\Http\Controllers;

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;

class Neo4jController extends Controller
{
    private function client()
    {
        return ClientBuilder::create()
            ->withDriver(
                'neo4j',
                config('services.neo4j.uri'),
                Authenticate::basic(
                    config('services.neo4j.username'),
                    config('services.neo4j.password')
                )
            )
            ->build();
    }

    public function makanan($nim)
    {
        // Mulai buffer output untuk meredam cetakan debug tak terduga
        ob_start();

        $client = $this->client();
        $result = $client->run(
            '
            MATCH (m:Mahasiswa {nim: $nim})-[:SUKA]->(f:Makanan)
            RETURN m.nama AS mahasiswa, f.nama AS makanan
            ',
            ['nim' => $nim],
            'neo4j'
        );

        $data = [];
        foreach ($result as $record) {
            $data[] = [
                'mahasiswa' => $record->get('mahasiswa'),
                'makanan' => $record->get('makanan'),
            ];
        }

        // Bersihkan dan buang semua output teks debug yang tercetak sebelum JSON
        ob_end_clean();

        return response()->json($data);
    }

    public function rekomendasi($nim)
    {
        // Mulai buffer output
        ob_start();

        $client = $this->client();
        $result = $client->run(
            '
            MATCH (m:Mahasiswa {nim: $nim})
            -[:SUKA]->(sama:Makanan)
            <-[:SUKA]-(teman:Mahasiswa)
            -[:SUKA]->(rekomendasi:Makanan)
            WHERE teman <> m
            AND NOT (m)-[:SUKA]->(rekomendasi)
            RETURN DISTINCT rekomendasi.nama AS makanan
            ',
            ['nim' => $nim],
            'neo4j'
        );

        $data = [];
        foreach ($result as $record) {
            $data[] = [
                'makanan' => $record->get('makanan')
            ];
        }

        // Bersihkan dan buang semua output teks debug
        ob_end_clean();

        return response()->json([
            'nim' => $nim,
            'rekomendasi' => $data
        ]);
    }
}
