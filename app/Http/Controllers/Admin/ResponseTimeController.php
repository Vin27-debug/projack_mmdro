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
            ->get()
            ->filter(fn(Dispatch $dispatch) => $this->calculateResponseMinutes($dispatch) !== null)
            ->sortByDesc(fn(Dispatch $dispatch) => $dispatch->incident?->at_scene_at ?? $dispatch->arrived_at ?? $dispatch->created_at);

        $completedResponses = $dispatches->count();

        $responseTimes = $dispatches
            ->map(fn(Dispatch $dispatch): ?float => $this->calculateResponseMinutes($dispatch))
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
            $incident = $dispatch->incident;
            return $incident?->at_scene_at?->format('Y-m') ?? $dispatch->arrived_at?->format('Y-m') ?? 'unknown';
        })
            ->map(function (Collection $group): float {
                $times = $group->map(fn(Dispatch $dispatch): ?float => $this->calculateResponseMinutes($dispatch))->filter()->values();

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

    protected function calculateResponseMinutes(Dispatch $dispatch): ?float
    {
        $incident = $dispatch->incident;

        if ($incident && $incident->call_received_at && $incident->at_scene_at) {
            return (float) $incident->call_received_at->diffInMinutes($incident->at_scene_at, false);
        }

        if ($incident && $incident->response_at && $incident->at_scene_at) {
            return (float) $incident->response_at->diffInMinutes($incident->at_scene_at, false);
        }

        if ($dispatch->assigned_at && $dispatch->arrived_at) {
            return (float) $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at, false);
        }

        return null;
    }
}
