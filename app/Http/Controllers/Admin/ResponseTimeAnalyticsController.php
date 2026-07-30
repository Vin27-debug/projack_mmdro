<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;

class ResponseTimeAnalyticsController extends Controller
{
    public function index()
    {
        $dispatches = Dispatch::whereNotNull('arrived_at')
            ->whereNotNull('assigned_at')
            ->get();

        $responseTimes = [];

        foreach ($dispatches as $dispatch) {

            $minutes = $dispatch->assigned_at
                ->diffInMinutes($dispatch->arrived_at);

            $responseTimes[] = $minutes;
        }

        $average = count($responseTimes)
            ? round(array_sum($responseTimes) / count($responseTimes), 2)
            : 0;

        $fastest = count($responseTimes)
            ? min($responseTimes)
            : 0;

        $slowest = count($responseTimes)
            ? max($responseTimes)
            : 0;

        return view(
            'admin.analytics.response-time',
            compact(
                'average',
                'fastest',
                'slowest',
                'dispatches'
            )
        );
    }
}