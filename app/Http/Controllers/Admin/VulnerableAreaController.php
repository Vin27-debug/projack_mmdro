<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VulnerableArea;
use Illuminate\Http\Request;

class VulnerableAreaController extends Controller
{
    public function index(Request $request)
    {
        $areas = VulnerableArea::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = trim($request->input('search'));
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%")
                        ->orWhere('area_type', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('level'), fn ($query) => $query->where('vulnerability_level', $request->input('level')))
            ->latest()
            ->get();

        return view('admin.vulnerable-areas.index', compact('areas'));
    }

    public function create()
    {
        return view('admin.vulnerable-areas.create', ['area' => new VulnerableArea()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        VulnerableArea::create($data);

        return redirect()->route('admin.vulnerable-areas.index')->with('success', 'Vulnerable area/household record added successfully.');
    }

    public function edit(VulnerableArea $vulnerableArea)
    {
        return view('admin.vulnerable-areas.create', ['area' => $vulnerableArea]);
    }

    public function update(Request $request, VulnerableArea $vulnerableArea)
    {
        $vulnerableArea->update($this->validateData($request));

        return redirect()->route('admin.vulnerable-areas.index')->with('success', 'Vulnerable area/household record updated successfully.');
    }

    public function deactivate(VulnerableArea $vulnerableArea)
    {
        $vulnerableArea->update(['status' => 'inactive']);

        return back()->with('success', 'Vulnerable area record marked inactive.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'area_type' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'household_count' => ['required', 'integer', 'min:0'],
            'population_count' => ['required', 'integer', 'min:0'],
            'vulnerability_level' => ['required', 'in:Low,Medium,High,Critical'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
