<h1>Add Ambulance</h1>

@if($errors->any())
<ul>
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
</ul>
@endif

<form method="POST"
    action="{{ route('ambulances.store') }}">

    @csrf

    <p>
        Plate Number
    </p>

    <input
        type="text"
        name="plate_number"
        placeholder="ABC-1234">

    <br><br>

    <p>
        Vehicle Name
    </p>

    <input
        type="text"
        name="vehicle_name"
        placeholder="Toyota HiAce">

    <br><br>

    <p>
        Vehicle Type
    </p>

    <select name="vehicle_type">

        <option value="ambulance">
            Ambulance
        </option>

        <option value="rescue_van">
            Rescue Van
        </option>

        <option value="fire_truck">
            Fire Truck
        </option>

    </select>

    <br><br>

    <button type="submit">
        Save Ambulance
    </button>

</form>