<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodingController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json([]);
        }

        $response = Http::withHeaders([
            'User-Agent' => 'MuniResQ/1.0'
        ])->get(
            'https://nominatim.openstreetmap.org/search',
            [
                'q' => $query . ', Nueva Ecija, Philippines',
                'format' => 'jsonv2',
                'limit' => 5,
                'countrycodes' => 'ph',
            ]
        );

        if (!$response->successful()) {
            return response()->json([]);
        }

        return response()->json($response->json());
    }
}