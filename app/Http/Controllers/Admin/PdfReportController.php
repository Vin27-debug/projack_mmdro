<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Incident;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncidentExport;
use App\Exports\DispatchExport;
use App\Exports\DriverPerformanceExport;
use App\Exports\VehicleUtilizationExport;

class PdfReportController extends Controller
{

    public function index()
    {
        return $this->downloadReport();
    }
    
    public function incidentReport()
    {
        $incidents = Incident::with([
            'driver',
            'report'
        ])->latest()->get();

        $pdf = Pdf::loadView(
            'admin.pdf.incident-report',
            compact('incidents')
        );

        return $pdf->stream('incident-report.pdf');
    }

    public function viewReport()
    {
        $incidents = Incident::with([
            'driver',
            'report'
        ])->latest()->get();

        $pdf = Pdf::loadView(
            'admin.pdf.incident-report',
            compact('incidents')
        );

        return $pdf->stream('incident-report.pdf');
    }

    public function downloadReport()
    {
        $incidents = Incident::with([
            'driver',
            'report'
        ])->latest()->get();

        $pdf = Pdf::loadView(
            'admin.pdf.incident-report',
            compact('incidents')
        );

        return $pdf->download('incident-report.pdf');
    }

    public function exportIncidentExcel()
    {
        return Excel::download(
            new IncidentExport,
            'incident-report.xlsx'
        );
    }

    public function exportDispatchExcel()
    {
        return Excel::download(
            new DispatchExport,
            'dispatch-report.xlsx'
        );
    }

    public function exportDriverExcel()
    {
        return Excel::download(
            new DriverPerformanceExport,
            'driver-performance.xlsx'
        );
    }

    public function exportVehicleUtilizationExcel()
    {
        return Excel::download(
            new VehicleUtilizationExport,
            'vehicle-utilization.xlsx'
        );
    }

    public function exportDriverPerformancePdf()
    {
        $drivers = app(\App\Http\Controllers\Admin\DriverPerformanceController::class)->index()->getData()['drivers'];

        $pdf = Pdf::loadView(
            'admin.reports.pdf.driver-performance',
            compact('drivers')
        );

        return $pdf->download('driver-performance.pdf');
    }

    public function exportVehicleUtilizationPdf()
    {
        $vehicles = app(\App\Http\Controllers\Admin\VehicleUtilizationController::class)->index()->getData()['vehicles'];

        $pdf = Pdf::loadView(
            'admin.reports.pdf.vehicle-utilization',
            compact('vehicles')
        );

        return $pdf->download('vehicle-utilization.pdf');
    }
}
