<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = Incident::with(['driver.user', 'ambulance', 'attachments'])
            ->where(function ($query): void {
                $query->whereIn('status', [Incident::STATUS_COMPLETED, Incident::STATUS_CLOSED])
                    ->orWhereNotNull('archived_at');
            })
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = trim($request->input('search'));
                $query->where(function ($q) use ($term): void {
                    $q->where('incident_number', 'like', "%{$term}%")
                        ->orWhere('reporter_name', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('start_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('start_date')))
            ->when($request->filled('end_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('end_date')))
            ->latest()
            ->get();

        return view('admin.incidents.history', compact('history'));
    }
}
