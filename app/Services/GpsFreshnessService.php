<?php

namespace App\Services;

use App\Models\GpsLocation;

class GpsFreshnessService
{
    public function metadata(?GpsLocation $location): array
    {
        $recordedAt = $location?->recorded_at;

        if (!$recordedAt) {
            return [
                'recorded_at' => null,
                'accuracy_meters' => null,
                'gps_age_seconds' => null,
                'gps_status' => 'missing',
            ];
        }

        $ageSeconds = max(0, now()->getTimestamp() - $recordedAt->getTimestamp());
        $freshSeconds = max(1, (int) config('services.muniresq.location_fresh_seconds', 60));
        $staleLimitSeconds = max(
            $freshSeconds,
            (int) config('services.muniresq.location_stale_limit_minutes', 5) * 60
        );

        return [
            'recorded_at' => $recordedAt->toISOString(),
            'accuracy_meters' => $location->getAttribute('accuracy_meters')
                ?? $location->getAttribute('accuracy'),
            'gps_age_seconds' => $ageSeconds,
            'gps_status' => $ageSeconds < $freshSeconds
                ? 'fresh'
                : ($ageSeconds <= $staleLimitSeconds ? 'delayed' : 'stale'),
        ];
    }
}
