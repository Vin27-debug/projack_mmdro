<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Incident;
use Carbon\Carbon;

class DispatchRecommendationService
{
    public function recommend(Incident $incident, $drivers = null, $vehicles = null): array
    {
        $driverQuery = Driver::dispatchEligible()->with('user');
        if ($drivers !== null) {
            $driverQuery->whereKey(collect($drivers)->map(fn(Driver $driver) => $driver->getKey()));
        }
        $drivers = $driverQuery->get();

        $vehicles = $vehicles ?? Ambulance::available()->get();

        if (!is_numeric($incident->latitude) || !is_numeric($incident->longitude)) {
            return [
                'nearestDriver' => null,
                'nearestDriverDistance' => null,
                'nearestAmbulance' => null,
                'nearestAmbulanceDistance' => null,
                'eligibleDrivers' => [],
                'rankedDrivers' => [],
                'eligibleVehicles' => [],
                'rankedVehicles' => [],
            ];
        }

        $staleLimitMinutes = (int) config('services.muniresq.location_stale_limit_minutes', 5);
        $eligibleDrivers = [];

        foreach ($drivers as $driver) {
            $gps = $driver->gpsLocations()->latest('recorded_at')->first();

            if (!$gps || !is_numeric($gps->latitude) || !is_numeric($gps->longitude)) {
                continue;
            }

            $recordedAt = $gps->recorded_at instanceof Carbon ? $gps->recorded_at : Carbon::parse($gps->recorded_at);
            if ($recordedAt->diffInMinutes(now()) > $staleLimitMinutes) {
                continue;
            }

            $distance = $this->calculateDistance(
                $incident->latitude,
                $incident->longitude,
                $gps->latitude,
                $gps->longitude
            );

            $driver->distance = round($distance, 2);
            $driver->gps_age_minutes = (int) $recordedAt->diffInMinutes(now());
            $eligibleDrivers[] = $driver;
        }

        usort($eligibleDrivers, fn($left, $right) => ($left->distance ?? PHP_FLOAT_MAX) <=> ($right->distance ?? PHP_FLOAT_MAX));

        $eligibleVehicles = [];
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

            $vehicle->distance = round($distance, 2);
            $eligibleVehicles[] = $vehicle;
        }

        usort($eligibleVehicles, fn($left, $right) => ($left->distance ?? PHP_FLOAT_MAX) <=> ($right->distance ?? PHP_FLOAT_MAX));

        $nearestDriver = $eligibleDrivers[0] ?? null;
        $nearestDriverDistance = $nearestDriver?->distance ?? null;
        $nearestAmbulance = $eligibleVehicles[0] ?? null;
        $nearestAmbulanceDistance = $nearestAmbulance?->distance ?? null;

        return [
            'nearestDriver' => $nearestDriver,
            'nearestDriverDistance' => $nearestDriverDistance,
            'nearestAmbulance' => $nearestAmbulance,
            'nearestAmbulanceDistance' => $nearestAmbulanceDistance,
            'eligibleDrivers' => $eligibleDrivers,
            'rankedDrivers' => $eligibleDrivers,
            'eligibleVehicles' => $eligibleVehicles,
            'rankedVehicles' => $eligibleVehicles,
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
