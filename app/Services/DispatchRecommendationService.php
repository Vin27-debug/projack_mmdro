<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Incident;

class DispatchRecommendationService
{
    public function recommend(Incident $incident, $drivers = null, $vehicles = null): array
    {
        $drivers = $drivers ?? Driver::where('status', 'available')->get();
        $vehicles = $vehicles ?? Ambulance::where('status', 'available')->get();

        $nearestDriver = null;
        $nearestDriverDistance = null;

        if (!is_numeric($incident->latitude) || !is_numeric($incident->longitude)) {
            return [
                'nearestDriver' => null,
                'nearestDriverDistance' => null,
                'nearestAmbulance' => null,
                'nearestAmbulanceDistance' => null,
            ];
        }

        foreach ($drivers as $driver) {
            $gps = $driver->gpsLocations()->latest('recorded_at')->first();

            if (!$gps || !is_numeric($gps->latitude) || !is_numeric($gps->longitude)) {
                continue;
            }

            $distance = $this->calculateDistance(
                $incident->latitude,
                $incident->longitude,
                $gps->latitude,
                $gps->longitude
            );

            if ($nearestDriver === null || $nearestDriverDistance === null || $distance < $nearestDriverDistance) {
                $nearestDriverDistance = $distance;
                $nearestDriver = $driver;
            }
        }

        $nearestAmbulance = null;
        $nearestAmbulanceDistance = null;

        foreach ($vehicles as $vehicle) {
            if (blank($vehicle->latitude) || blank($vehicle->longitude)) {
                continue;
            }

            $distance = $this->calculateDistance(
                $incident->latitude,
                $incident->longitude,
                $vehicle->latitude,
                $vehicle->longitude
            );

            if ($nearestAmbulance === null || $distance < $nearestAmbulanceDistance) {
                $nearestAmbulanceDistance = $distance;
                $nearestAmbulance = $vehicle;
            }
        }

        return [
            'nearestDriver' => $nearestDriver,
            'nearestDriverDistance' => $nearestDriverDistance,
            'nearestAmbulance' => $nearestAmbulance,
            'nearestAmbulanceDistance' => $nearestAmbulanceDistance,
        ];
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
