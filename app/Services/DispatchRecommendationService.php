<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Dispatch;
use App\Models\GpsLocation;
use App\Models\Incident;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DispatchRecommendationService
{
    public function eligibleDrivers(): Collection
    {
        $now = CarbonImmutable::now();
        $staleLimitSeconds = max(0, (int) config('services.muniresq.location_stale_limit_minutes', 5) * 60);

        return Driver::dispatchEligible()
            ->whereDoesntHave('dispatches', fn($query) => $query->whereNotIn('status', [
                Dispatch::STATUS_COMPLETED,
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ]))
            ->with('user')
            ->get()
            ->filter(function (Driver $driver) use ($now, $staleLimitSeconds): bool {
                $gps = $this->latestValidGps($driver);

                if (!$gps || !$gps->recorded_at) {
                    return false;
                }

                $recordedAt = CarbonImmutable::instance($gps->recorded_at);

                return !$recordedAt->isFuture()
                    && $recordedAt->greaterThanOrEqualTo($now->subSeconds($staleLimitSeconds));
            })
            ->values();
    }

    public function eligibleVehicles(): Collection
    {
        return Ambulance::available()
            ->whereDoesntHave('dispatches', fn($query) => $query->whereNotIn('status', [
                Dispatch::STATUS_COMPLETED,
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ]))
            ->orderBy('vehicle_name')
            ->get();
    }

    public function recommend(Incident $incident, $drivers = null, $vehicles = null): array
    {
        $eligibleDrivers = $this->eligibleDrivers();
        if ($drivers !== null) {
            $driverIds = collect($drivers)->map(fn(Driver $driver) => $driver->getKey());
            $eligibleDrivers = $eligibleDrivers->whereIn('id', $driverIds)->values();
        }

        $eligibleVehicles = $vehicles === null
            ? $this->eligibleVehicles()
            : $this->eligibleVehicles()->whereIn('id', collect($vehicles)->pluck('id'))->values();

        $hasIncidentCoordinates = $this->validCoordinates($incident->latitude, $incident->longitude);
        $now = CarbonImmutable::now();

        foreach ($eligibleDrivers as $driver) {
            $gps = $this->latestValidGps($driver);
            if (!$gps || !$gps->recorded_at) {
                continue;
            }

            $recordedAt = CarbonImmutable::instance($gps->recorded_at);
            $driver->gps_age_minutes = (int) floor(max(0, $now->diffInSeconds($recordedAt)) / 60);

            if ($hasIncidentCoordinates) {
                $driver->distance = round($this->calculateDistance(
                    $incident->latitude,
                    $incident->longitude,
                    $gps->latitude,
                    $gps->longitude
                ), 2);
            }
        }

        foreach ($eligibleVehicles as $vehicle) {
            if ($hasIncidentCoordinates && $this->validCoordinates($vehicle->latitude, $vehicle->longitude)) {
                $vehicle->distance = round($this->calculateDistance(
                    $incident->latitude,
                    $incident->longitude,
                    $vehicle->latitude,
                    $vehicle->longitude
                ), 2);
            }
        }

        $rankedDrivers = $eligibleDrivers->sortBy(fn(Driver $driver) => $driver->distance ?? PHP_FLOAT_MAX)->values();
        $rankedVehicles = $eligibleVehicles->sortBy(fn(Ambulance $vehicle) => $vehicle->distance ?? PHP_FLOAT_MAX)->values();

        $nearestDriver = $rankedDrivers->first(fn(Driver $driver) => isset($driver->distance));
        $nearestAmbulance = $rankedVehicles->first(fn(Ambulance $vehicle) => isset($vehicle->distance));
        $nearestDriverDistance = $nearestDriver?->distance;
        $nearestAmbulanceDistance = $nearestAmbulance?->distance ?? null;

        return [
            'nearestDriver' => $nearestDriver,
            'nearestDriverDistance' => $nearestDriverDistance,
            'nearestAmbulance' => $nearestAmbulance,
            'nearestAmbulanceDistance' => $nearestAmbulanceDistance,
            'eligibleDrivers' => $eligibleDrivers->all(),
            'rankedDrivers' => $rankedDrivers->all(),
            'eligibleVehicles' => $eligibleVehicles->all(),
            'rankedVehicles' => $rankedVehicles->all(),
        ];
    }

    private function latestValidGps(Driver $driver): ?GpsLocation
    {
        $gps = $driver->gpsLocations()->latest('recorded_at')->first();

        return $gps && $this->validCoordinates($gps->latitude, $gps->longitude)
            ? $gps
            : null;
    }

    private function validCoordinates(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude)
            && is_numeric($longitude)
            && is_finite((float) $latitude)
            && is_finite((float) $longitude)
            && (float) $latitude >= -90
            && (float) $latitude <= 90
            && (float) $longitude >= -180
            && (float) $longitude <= 180;
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
