<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Ambulance;

class NearestVehicleController extends Controller
{
    public function index()
    {
        $incident = Incident::latest()->first();

        if (!$incident) {
            return back()->with('error', 'No incident found.');
        }

        $vehicles = Ambulance::all();

        foreach ($vehicles as $vehicle) {

            $vehicle->distance =
                $this->distance(
                    $incident->latitude,
                    $incident->longitude,
                    $vehicle->latitude,
                    $vehicle->longitude
                );
        }

        $vehicles = $vehicles->sortBy('distance');

        $recommendedVehicle = $vehicles->first();

        return view(
            'admin.nearest-vehicle',
            compact(
                'incident',
                'vehicles',
                'recommendedVehicle'
            )
        );
    }

    private function distance(
        $lat1,
        $lon1,
        $lat2,
        $lon2
    ) {

        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) *
            sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return round(
            $earthRadius * $c,
            2
        );
    }
}
