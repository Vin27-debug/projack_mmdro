<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use Illuminate\Support\Collection;

class ResponseTimeController extends Controller
{
    public function index()
    {
        $dispatches = Dispatch::query()
            ->with(['incident', 'driver.user', 'vehicle'])
            ->whereNotNull('assigned_at')
            ->whereNotNull('arrived_at')
            ->orderByDesc('arrived_at')
            ->get();

        $completedResponses = $dispatches->count();

        $responseTimes = $dispatches
            ->map(function (Dispatch $dispatch): ?int {
                if (!$dispatch->assigned_at || !$dispatch->arrived_at) {
                    return null;
                }

                return (int) $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at);
            })
            ->filter()
            ->values();

        $averageResponseTime = $responseTimes->isNotEmpty()
            ? round($responseTimes->avg(), 2)
            : 0;

        $fastestResponse = $responseTimes->isNotEmpty()
            ? (int) $responseTimes->min()
            : 0;

        $slowestResponse = $responseTimes->isNotEmpty()
            ? (int) $responseTimes->max()
            : 0;

        $monthlyTrend = $dispatches->groupBy(function (Dispatch $dispatch): string {
            return $dispatch->arrived_at?->format('Y-m') ?? 'unknown';
        })
            ->map(function (Collection $group): float {
                $times = $group->map(function (Dispatch $dispatch): ?int {
                    if (!$dispatch->assigned_at || !$dispatch->arrived_at) {
                        return null;
                    }

                    return (int) $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at);
                })->filter()->values();

                return $times->isNotEmpty() ? round($times->avg(), 2) : 0;
            })
            ->sortKeys()
            ->take(6);

        $labels = $monthlyTrend->keys()->all();
        $series = $monthlyTrend->values()->all();

        return view('admin.response-time', compact(
            'dispatches',
            'completedResponses',
            'averageResponseTime',
            'fastestResponse',
            'slowestResponse',
            'labels',
            'series'
        ));
    }
}
