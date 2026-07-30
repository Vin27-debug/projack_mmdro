<h1>Driver Assignments</h1>

<a href="{{ route('assignments.create') }}">
    New Assignment
</a>

<br><br>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Driver</th>
        <th>Ambulance</th>
        <th>Status</th>
    </tr>

    @foreach($assignments as $assignment)

    <tr>

        <td>{{ $assignment->id }}</td>

        <td>
            {{ $assignment->driver->badge_id }}
            -
            {{ $assignment->driver->user->name }}
        </td>

        <td>
            {{ $assignment->ambulance->plate_number }}
            -
            {{ $assignment->ambulance->vehicle_name }}
        </td>

        <td>
            {{ $assignment->status }}
        </td>

    </tr>

    @endforeach

</table>