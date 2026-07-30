@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <h2 class="mb-4">
        Audit Logs
    </h2>

    <div class="card">

        <div class="card-body">

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($logs as $log)

                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->module }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->created_at }}</td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="6" class="text-center">
                            No Audit Logs Found
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

            {{ $logs->links() }}

        </div>

    </div>

</div>

@endsection