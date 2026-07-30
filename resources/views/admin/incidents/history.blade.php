@extends('layouts.admin')

@section('content')

<h2 class="mb-4">

    Incident & Response History

</h2>

<div class="card shadow">

    <div class="table-responsive">

        <table class="table">

            <thead>

                <tr>

                    <th>Incident</th>

                    <th>Priority</th>

                    <th>Driver</th>

                    <th>Ambulance</th>

                    <th>Status</th>

                    <th>Completed</th>

                </tr>

            </thead>

            <tbody>

                @foreach($history as $incident)

                <tr>

                    <td>

                        {{ $incident->incident_number }}

                    </td>

                    <td>

                        {{ $incident->priority }}

                    </td>

                    <td>

                        {{ $incident->driver?->user?->name }}

                    </td>

                    <td>

                        {{ $incident->ambulance?->plate_number }}

                    </td>

                    <td>

                        <span class="badge bg-success">

                            Completed

                        </span>

                    </td>

                    <td>

                        {{ $incident->updated_at->format('M d, Y h:i A') }}

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection