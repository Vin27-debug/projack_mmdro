<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Services\ReportsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ReportsCenterController extends Controller
{
    public function __construct(protected ReportsService $reportsService) {}

    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);

        $summary = $this->reportsService->getSummary($filters);
        $driverPerformance = $this->reportsService->getDriverPerformance($filters);
        $vehicleUtilization = $this->reportsService->getVehicleUtilization($filters);
        $responseTimeMetrics = $this->reportsService->getResponseTimeMetrics($filters);
        $monthlyTrends = $this->reportsService->getMonthlyIncidentTrends($filters);

        $incidents = Incident::query()
            ->with(['driver.user', 'ambulance'])
            ->when($filters['start_date'] ?? null, function ($query, $date): void {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($filters['end_date'] ?? null, function ($query, $date): void {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->get();

        return view('admin.reports-center', compact(
            'filters',
            'summary',
            'driverPerformance',
            'vehicleUtilization',
            'responseTimeMetrics',
            'monthlyTrends',
            'incidents'
        ));
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $summary = $this->reportsService->getSummary($filters);
        $driverPerformance = $this->reportsService->getDriverPerformance($filters);
        $vehicleUtilization = $this->reportsService->getVehicleUtilization($filters);
        $responseTimeMetrics = $this->reportsService->getResponseTimeMetrics($filters);
        $incidents = Incident::query()
            ->with(['driver.user', 'ambulance'])
            ->when($filters['start_date'] ?? null, function ($query, $date): void {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($filters['end_date'] ?? null, function ($query, $date): void {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.reports-center', compact(
            'filters',
            'summary',
            'driverPerformance',
            'vehicleUtilization',
            'responseTimeMetrics',
            'incidents'
        ));

        return $pdf->download('muniresq-reports-center.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $incidents = Incident::query()
            ->with(['driver.user', 'ambulance'])
            ->when($filters['start_date'] ?? null, function ($query, $date): void {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($filters['end_date'] ?? null, function ($query, $date): void {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->get();

        $rows = $incidents->map(function (Incident $incident): array {
            return [
                $incident->incident_number,
                $incident->reporter_name,
                $incident->incident_type,
                $incident->location,
                $incident->status,
                optional($incident->created_at)->format('Y-m-d H:i:s'),
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
                    'Incident Number',
                    'Reporter',
                    'Incident Type',
                    'Location',
                    'Status',
                    'Created At',
                ];
            }
        }, 'muniresq-reports-center.xlsx');
    }

    protected function normalizeFilters(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
    }
}
