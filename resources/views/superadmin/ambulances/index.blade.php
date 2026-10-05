@extends('layouts.superadmin')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-danger">Vehicle Fleet</h2>
        <p class="text-muted mb-0">
            Manage ambulance availability, maintenance status, and fleet readiness.
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('superadmin.ambulances.index', ['archived' => $archived ? 0 : 1]) }}" class="btn btn-outline-secondary">
            {{ $archived ? 'Active Vehicles' : 'Archived Vehicles' }}
        </a>
        @unless($archived)
        <a href="{{ route('superadmin.ambulances.create') }}" class="btn btn-danger">Add Ambulance</a>
        @endunless
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Plate Number</th>
                        <th>Vehicle Name</th>
                        <th>Vehicle Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($ambulances as $ambulance)

                    <tr>
                        <td>#{{ $ambulance->id }}</td>

                        <td>
                            {{ $ambulance->plate_number }}
                        </td>

                        <td>
                            {{ $ambulance->vehicle_name }}
                        </td>

                        <td>
                            {{ $ambulance->vehicle_type }}
                        </td>

                        <td>
                            @if($ambulance->status == 'available')

                            <span class="badge bg-success">
                                Available
                            </span>

                            @elseif($ambulance->status == 'maintenance')

                            <span class="badge bg-warning text-dark">
                                Maintenance
                            </span>

                            @else

                            <span class="badge bg-secondary">
                                {{ ucfirst($ambulance->status) }}
                            </span>

                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-2">

                                @if($archived)
                                <form method="POST" action="{{ route('superadmin.ambulances.restore', $ambulance) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                                </form>
                                @else
                                <a
                                    href="{{ route('superadmin.ambulances.edit', $ambulance->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>

                                <form
                                    action="{{ route('superadmin.ambulances.archive', $ambulance->id) }}"
                                    method="POST">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Archive this ambulance? Historical records will be retained.')">
                                        Archive
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            {{ $archived ? 'No archived vehicles found.' : 'No vehicles found.' }}
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>
        </div>
    </div>
</div>

@endsection