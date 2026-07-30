@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">
        Driver Performance Analytics
    </h2>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Badge ID</th>
                <th>Driver Name</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            @foreach($drivers as $driver)

            <tr>
                <td>{{ $driver->badge_id }}</td>
                <td>{{ $driver->user->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($driver->status ?? 'Active') }}</td>
            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection