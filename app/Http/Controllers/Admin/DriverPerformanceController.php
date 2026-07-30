<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\Driver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class DriverPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::query()
            ->with(['user', 'dispatches', 'incidentReports'])
            ->get()
            ->map(function (Driver $driver): object {
                $dispatches = $driver->dispatches;
                $totalDispatches = $dispatches->count();
                $completedDispatches = $dispatches->where('status', Dispatch::STATUS_COMPLETED)->count();
                $responseTimes = $dispatches
                    ->filter(fn(Dispatch $dispatch) => $dispatch->assigned_at && $dispatch->arrived_at)
                    ->map(fn(Dispatch $dispatch) => $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at));
                $arrivalTimes = $dispatches
                    ->filter(fn(Dispatch $dispatch) => $dispatch->accepted_at && $dispatch->arrived_at)
                    ->map(fn(Dispatch $dispatch) => $dispatch->accepted_at->diffInMinutes($dispatch->arrived_at));
                $completionTimes = $dispatches
                    ->filter(fn(Dispatch $dispatch) => $dispatch->arrived_at && $dispatch->completed_at)
                    ->map(fn(Dispatch $dispatch) => $dispatch->arrived_at->diffInMinutes($dispatch->completed_at));
                $incidentsHandled = $driver->incidentReports->count();
                $completionRate = $totalDispatches > 0 ? round(($completedDispatches / $totalDispatches) * 100, 2) : 0;

                $driver->total_dispatches = $totalDispatches;
                $driver->completed_dispatches = $completedDispatches;
                $driver->average_response_time = $responseTimes->count() > 0 ? round($responseTimes->avg(), 2) : 0;
                $driver->average_arrival_time = $arrivalTimes->count() > 0 ? round($arrivalTimes->avg(), 2) : 0;
                $driver->average_completion_time = $completionTimes->count() > 0 ? round($completionTimes->avg(), 2) : 0;
                $driver->completion_rate = $completionRate;
                $driver->incident_count = $incidentsHandled;
                $driver->incidents_handled = $incidentsHandled;
                $driver->acceptance_rate = $completionRate;

                return $driver;
            })
            ->sortByDesc('completed_dispatches')
            ->values();

        $monthlyChart = $this->buildMonthlyChart($drivers);

        return view('admin.reports.driver-performance', compact('drivers', 'monthlyChart'));
    }

    public function exportPdf(Request $request)
    {
        $drivers = $this->buildDriverStats();
        $pdf = Pdf::loadView('admin.reports.pdf.driver-performance', compact('drivers'));

        return $pdf->download('driver-performance-report.pdf');
    }

    public function exportExcel(Request $request)
    {
        $drivers = $this->buildDriverStats();
        $rows = $drivers->map(function (Driver $driver): array {
            return [
                $driver->user?->name ?? 'Unknown',
                $driver->badge_id,
                $driver->total_dispatches,
                $driver->completed_dispatches,
                $driver->average_response_time,
                $driver->average_arrival_time,
                $driver->completion_rate,
                $driver->incident_count,
            ];
        })->toArray();

        return Excel::download(new class($rows) implements FromArray, WithHeadings {
            public function __construct(private array $rows) {}

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return [
                    'Driver',
                    'Badge ID',
                    'Completed Dispatches',
                    'Average Response Time',
                    'Average Arrival Time',
                    'Completion Rate',
                    'Incident Count',
                ];
            }
        }, 'driver-performance-report.xlsx');
    }

    protected function buildDriverStats(): \Illuminate\Support\Collection
    {
        return Driver::query()
            ->with(['user', 'dispatches', 'incidentReports'])
            ->get()
            ->map(function (Driver $driver): object {
                $dispatches = $driver->dispatches;
                $totalDispatches = $dispatches->count();
                $completedDispatches = $dispatches->where('status', Dispatch::STATUS_COMPLETED)->count();
                $responseTimes = $dispatches
                    ->filter(fn(Dispatch $dispatch) => $dispatch->assigned_at && $dispatch->arrived_at)
                    ->map(fn(Dispatch $dispatch) => $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at));
                $arrivalTimes = $dispatches
                    ->filter(fn(Dispatch $dispatch) => $dispatch->accepted_at && $dispatch->arrived_at)
                    ->map(fn(Dispatch $dispatch) => $dispatch->accepted_at->diffInMinutes($dispatch->arrived_at));
                $completionTimes = $dispatches
                    ->filter(fn(Dispatch $dispatch) => $dispatch->arrived_at && $dispatch->completed_at)
                    ->map(fn(Dispatch $dispatch) => $dispatch->arrived_at->diffInMinutes($dispatch->completed_at));
                $completionRate = $totalDispatches > 0 ? round(($completedDispatches / $totalDispatches) * 100, 2) : 0;
                $incidentsHandled = $driver->incidentReports->count();

                $driver->total_dispatches = $totalDispatches;
                $driver->completed_dispatches = $completedDispatches;
                $driver->average_response_time = $responseTimes->count() > 0 ? round($responseTimes->avg(), 2) : 0;
                $driver->average_arrival_time = $arrivalTimes->count() > 0 ? round($arrivalTimes->avg(), 2) : 0;
                $driver->average_completion_time = $completionTimes->count() > 0 ? round($completionTimes->avg(), 2) : 0;
                $driver->completion_rate = $completionRate;
                $driver->incident_count = $incidentsHandled;
                $driver->incidents_handled = $incidentsHandled;
                $driver->acceptance_rate = $completionRate;

                return $driver;
            })
            ->sortByDesc('completed_dispatches')
            ->values();
    }

    protected function buildMonthlyChart($drivers): array
    {
        $labels = [];
        $series = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $series[] = (int) Dispatch::query()
                ->whereHas('driver')
                ->whereMonth('created_at', substr($month, 5, 2))
                ->whereYear('created_at', substr($month, 0, 4))
                ->where('status', Dispatch::STATUS_COMPLETED)
                ->count();
        }

        return ['labels' => $labels, 'series' => $series];
    }
}
