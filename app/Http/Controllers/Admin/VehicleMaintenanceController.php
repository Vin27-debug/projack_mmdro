<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Notification;
use App\Models\VehicleMaintenance;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class VehicleMaintenanceController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_vehicles' => Ambulance::count(),
            'active_vehicles' => Ambulance::where(function ($query): void {
                $query->where('vehicle_status', 'active')
                    ->orWhere('status', 'on_duty');
            })->count(),
            'maintenance_vehicles' => Ambulance::where(function ($query): void {
                $query->where('vehicle_status', 'maintenance')
                    ->orWhere('status', 'maintenance');
            })->count(),
            'available_vehicles' => Ambulance::where(function ($query): void {
                $query->where('vehicle_status', 'available')
                    ->orWhere('status', 'available');
            })->count(),
        ];

        $maintenances = VehicleMaintenance::with('ambulance')
            ->latest()
            ->paginate(10);

        return view('admin.maintenance.index', compact('maintenances', 'stats'));
    }

    public function create(): View
    {
        $ambulances = Ambulance::orderBy('vehicle_name')->get();
        $vehicleStatuses = ['available', 'active', 'maintenance', 'out_of_service'];

        return view('admin.maintenance.create', compact('ambulances', 'vehicleStatuses'))->with('vehicleMaintenance', null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ambulance_id' => ['required', 'exists:ambulances,id'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'scheduled_date' => ['required', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'vehicle_status' => ['nullable', 'in:available,active,maintenance,out_of_service'],
        ]);

        $maintenance = VehicleMaintenance::create([
            'ambulance_id' => $data['ambulance_id'],
            'maintenance_type' => $data['maintenance_type'],
            'scheduled_date' => $data['scheduled_date'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        $normalizedVehicleStatus = $data['vehicle_status'] ?? ($data['status'] === 'completed' ? 'available' : 'maintenance');
        $maintenance->ambulance?->update([
            'status' => match ($normalizedVehicleStatus) {
                'active' => 'on_duty',
                'out_of_service' => 'maintenance',
                default => $normalizedVehicleStatus,
            },
        ]);

        Notification::create([
            'title' => 'Vehicle Maintenance',
            'message' => 'A vehicle has been scheduled for maintenance.',
            'type' => 'maintenance',
        ]);

        AuditService::log(
            'Schedule Maintenance',
            'Vehicle',
            'Vehicle sent to maintenance'
        );

        return Redirect::route('admin.maintenance.index')
            ->with('success', 'Maintenance record created successfully.');
    }

    public function edit(VehicleMaintenance $vehicleMaintenance): View
    {
        $ambulances = Ambulance::orderBy('vehicle_name')->get();
        $vehicleStatuses = ['available', 'active', 'maintenance', 'out_of_service'];

        return view('admin.maintenance.edit', compact('vehicleMaintenance', 'ambulances', 'vehicleStatuses'));
    }

    public function update(Request $request, VehicleMaintenance $vehicleMaintenance): RedirectResponse
    {
        $data = $request->validate([
            'ambulance_id' => ['required', 'exists:ambulances,id'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'scheduled_date' => ['required', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'vehicle_status' => ['nullable', 'in:available,active,maintenance,out_of_service'],
        ]);

        $vehicleMaintenance->update([
            'ambulance_id' => $data['ambulance_id'],
            'maintenance_type' => $data['maintenance_type'],
            'scheduled_date' => $data['scheduled_date'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        $normalizedVehicleStatus = $data['vehicle_status'] ?? ($data['status'] === 'completed' ? 'available' : 'maintenance');
        $vehicleMaintenance->ambulance?->update([
            'status' => match ($normalizedVehicleStatus) {
                'active' => 'on_duty',
                'out_of_service' => 'maintenance',
                default => $normalizedVehicleStatus,
            },
        ]);

        return Redirect::route('admin.maintenance.index')
            ->with('success', 'Maintenance record updated successfully.');
    }

    public function destroy(VehicleMaintenance $vehicleMaintenance): RedirectResponse
    {
        $vehicleMaintenance->delete();

        return Redirect::route('admin.maintenance.index')
            ->with('success', 'Maintenance record deleted successfully.');
    }

    public function complete(VehicleMaintenance $vehicleMaintenance): RedirectResponse
    {
        $vehicleMaintenance->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        $vehicleMaintenance->ambulance?->update([
            'status' => 'available',
        ]);

        return back()->with('success', 'Maintenance completed.');
    }
}
