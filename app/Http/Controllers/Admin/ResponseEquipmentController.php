<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponseEquipment;
use Illuminate\Http\Request;

class ResponseEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipment = ResponseEquipment::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = trim($request->input('search'));
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%")
                        ->orWhere('serial_number', 'like', "%{$term}%")
                        ->orWhere('storage_location', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->get();

        return view('admin.response-equipment.index', compact('equipment'));
    }

    public function create()
    {
        return view('admin.response-equipment.create', ['equipment' => new ResponseEquipment()]);
    }

    public function store(Request $request)
    {
        ResponseEquipment::create($this->validateData($request));

        return redirect()->route('admin.response-equipment.index')->with('success', 'Response equipment added successfully.');
    }

    public function edit(ResponseEquipment $responseEquipment)
    {
        return view('admin.response-equipment.create', ['equipment' => $responseEquipment]);
    }

    public function update(Request $request, ResponseEquipment $responseEquipment)
    {
        $responseEquipment->update($this->validateData($request));

        return redirect()->route('admin.response-equipment.index')->with('success', 'Response equipment updated successfully.');
    }

    public function deactivate(ResponseEquipment $responseEquipment)
    {
        $responseEquipment->update(['status' => 'inactive']);

        return back()->with('success', 'Equipment marked inactive.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'in:New,Good,Fair,Needs Repair,Unserviceable'],
            'status' => ['required', 'in:available,in_use,maintenance,inactive'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
