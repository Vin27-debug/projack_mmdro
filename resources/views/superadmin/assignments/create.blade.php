<h1>Assign Driver to Ambulance</h1>

<form method="POST"
    action="{{ route('assignments.store') }}">

    @csrf

    <p>Driver</p>

    <select name="driver_id">

        @foreach($drivers as $driver)

        <option value="{{ $driver->id }}">
            {{ $driver->badge_id }}
        </option>

        @endforeach

    </select>

    <br><br>

    <p>Ambulance</p>

    <select name="ambulance_id">

        @foreach($ambulances as $ambulance)

        <option value="{{ $ambulance->id }}">
            {{ $ambulance->plate_number }}
            -
            {{ $ambulance->vehicle_name }}
        </option>

        @endforeach

    </select>

    <br><br>

    <button type="submit">
        Assign
    </button>

</form>