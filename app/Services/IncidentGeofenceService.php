<?php

namespace App\Services;

use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class IncidentGeofenceService
{
    public function process(
        Driver $driver,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
        ?CarbonImmutable $recordedAt = null
    ): array {
        if (!$this->validCoordinates($latitude, $longitude)) {
            return [];
        }

        $recordedAt ??= CarbonImmutable::now();

        if ($this->isStale($recordedAt) || !$this->acceptableAccuracy($accuracy)) {
            return [];
        }

        return DB::transaction(function () use ($driver, $latitude, $longitude, $recordedAt): array {
            $dispatch = Dispatch::query()
                ->where('driver_id', $driver->id)
                ->whereIn('status', [
                    Dispatch::STATUS_EN_ROUTE,
                    Dispatch::STATUS_ARRIVED,
                ])
                ->latest('assigned_at')
                ->lockForUpdate()
                ->first();

            if (!$dispatch) {
                return [];
            }

            $incident = Incident::query()
                ->whereKey($dispatch->incident_id)
                ->lockForUpdate()
                ->first();

            if (!$incident || !$this->hasCoordinates($incident)) {
                return [];
            }

            $distanceKm = $this->distanceKm(
                $latitude,
                $longitude,
                (float) $incident->latitude,
                (float) $incident->longitude
            );

            $events = [];

            if (
                $dispatch->status === Dispatch::STATUS_EN_ROUTE
                && $incident->at_scene_at === null
                && $distanceKm <= $this->arrivalRadiusKm()
            ) {
                $now = $recordedAt->toMutable();

                $incident->update([
                    'at_scene_at' => $now,
                    'status' => Incident::STATUS_RESPONDING,
                ]);

                $dispatch->update([
                    'status' => Dispatch::STATUS_ARRIVED,
                    'arrived_at' => $now,
                ]);

                $driver->update([
                    'status' => Driver::STATUS_ON_SCENE,
                ]);

                $events[] = 'at_scene';
            }

            if (
                $dispatch->status === Dispatch::STATUS_ARRIVED
                && $incident->at_patient_at !== null
                && $incident->depart_scene_at === null
                && $distanceKm >= $this->departureRadiusKm()
            ) {
                $incident->update([
                    'depart_scene_at' => $recordedAt->toMutable(),
                ]);

                $events[] = 'depart_scene';
            }

            return $events;
        });
    }

    public function arrivalRadiusKm(): float
    {
        return (float) config('services.muniresq.geofence.arrival_radius_km', 0.15);
    }

    public function departureRadiusKm(): float
    {
        return max(
            $this->arrivalRadiusKm(),
            (float) config('services.muniresq.geofence.departure_radius_km', 0.3)
        );
    }

    private function acceptableAccuracy(?float $accuracy): bool
    {
        return $accuracy !== null
            && $accuracy <= (float) config('services.muniresq.geofence.max_accuracy_meters', 50);
    }

    private function isStale(CarbonImmutable $recordedAt): bool
    {
        return $recordedAt->diffInSeconds(CarbonImmutable::now())
            > (int) config('services.muniresq.geofence.max_age_seconds', 120);
    }

    private function hasCoordinates(Incident $incident): bool
    {
        return is_numeric($incident->latitude)
            && is_numeric($incident->longitude)
            && $this->validCoordinates((float) $incident->latitude, (float) $incident->longitude);
    }

    private function validCoordinates(float $latitude, float $longitude): bool
    {
        return is_finite($latitude)
            && is_finite($longitude)
            && $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180;
    }

    private function distanceKm(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): float
    {
        $earthRadiusKm = 6371;
        $latitudeDelta = deg2rad($targetLatitude - $latitude);
        $longitudeDelta = deg2rad($targetLongitude - $longitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude))
            * cos(deg2rad($targetLatitude))
            * sin($longitudeDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
