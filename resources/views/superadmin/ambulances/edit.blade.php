<h1>Edit Ambulance</h1>

<form method="POST"
      action="{{ route('ambulances.update',$ambulance->id) }}">
    @csrf
    @method('PUT')

    <input type="text"
           name="plate_number"
           value="{{ $ambulance->plate_number }}">
    <br><br>

    <input type="text"
           name="vehicle_name"
           value="{{ $ambulance->vehicle_name }}">
    <br><br>

    <input type="text"
           name="vehicle_type"
           value="{{ $ambulance->vehicle_type }}">
    <br><br>

    <select name="status">
        <option value="available">Available</option>
        <option value="busy">Busy</option>
        <option value="maintenance">Maintenance</option>
    </select>

    <br><br>

    <button type="submit">
        Update Ambulance
    </button>
</form>