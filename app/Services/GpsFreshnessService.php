<?php

namespace App\Services;

use App\Models\GpsLocation;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;

class GpsFreshnessService
{
    private const STALE_AFTER_SECONDS = 180;

    public function metadata(?GpsLocation $location): array
    {
        $rawRecordedAt = $location?->getRawOriginal('recorded_at');

        if (!$location || blank($rawRecordedAt)) {
            return [
                'recorded_at' => null,
                'last_updated' => 'Unknown',
                'accuracy_meters' => null,
                'gps_age_seconds' => null,
                'gps_status' => 'missing',
            ];
        }

        try {
            $recordedAt = CarbonImmutable::parse($rawRecordedAt, config('app.timezone'));
        } catch (InvalidFormatException) {
            return [
                'recorded_at' => null,
                'last_updated' => 'Unknown',
                'accuracy_meters' => $location->getAttribute('accuracy_meters')
                    ?? $location->getAttribute('accuracy'),
                'gps_age_seconds' => null,
                'gps_status' => 'invalid',
            ];
        }

        $ageSeconds = now()->getTimestamp() - $recordedAt->getTimestamp();
        if ($ageSeconds < 0) {
            return [
                'recorded_at' => $recordedAt->toISOString(),
                'last_updated' => 'Unknown',
                'accuracy_meters' => $location->getAttribute('accuracy_meters')
                    ?? $location->getAttribute('accuracy'),
                'gps_age_seconds' => null,
                'gps_status' => 'invalid',
            ];
        }

        $freshSeconds = max(1, (int) config('services.muniresq.location_fresh_seconds', 60));

        return [
            'recorded_at' => $recordedAt->toISOString(),
            'last_updated' => $recordedAt->format('M d, Y H:i'),
            'accuracy_meters' => $location->getAttribute('accuracy_meters')
                ?? $location->getAttribute('accuracy'),
            'gps_age_seconds' => $ageSeconds,
            'gps_status' => $ageSeconds > self::STALE_AFTER_SECONDS
                ? 'stale'
                : ($ageSeconds < $freshSeconds ? 'fresh' : 'delayed'),
        ];
    }
}
