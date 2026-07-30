<h1>Approved Drivers</h1>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Badge</th>
        <th>Name</th>
        <th>Email</th>
        <th>License</th>
    </tr>

    @foreach($drivers as $driver)

    <tr>
        <td>{{ $driver->id }}</td>
        <td>{{ $driver->badge_id }}</td>
        <td>{{ $driver->user->name }}</td>
        <td>{{ $driver->user->email }}</td>
        <td>{{ $driver->license_number }}</td>
    </tr>

    @endforeach

</table>