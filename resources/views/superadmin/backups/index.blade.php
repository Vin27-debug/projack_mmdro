@extends('layouts.superadmin')

@section('content')

<h2>Database Backups</h2>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<form method="POST"
    action="{{ route('backups.create') }}">
    @csrf

    <button
        class="btn btn-primary">
        Backup Now
    </button>
</form>

<hr>

<h4 class="mt-4">Backup History</h4>
<table class="table table-sm">
    <thead>
        <tr>
            <th>Type</th>
            <th>Filename</th>
            <th>Status</th>
            <th>Message</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->type }}</td>
            <td>{{ $log->filename }}</td>
            <td>{{ $log->status }}</td>
            <td>{{ $log->message }}</td>
            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr>

<table class="table">

    <thead>
        <tr>
            <th>Backup File</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

        @foreach($files as $file)

        <tr>

            <td>
                {{ basename($file) }}
            </td>

            <td>

                <a
                    href="{{ route('backups.download', basename($file)) }}"
                    class="btn btn-success btn-sm">
                    Download
                </a>

                <form
                    method="POST"
                    action="{{ route('backups.restore') }}"
                    style="display:inline;">

                    @csrf

                    <input
                        type="hidden"
                        name="backup_file"
                        value="{{ basename($file) }}">

                    <button
                        class="btn btn-warning btn-sm">
                        Restore
                    </button>

                </form>

            </td>

        </tr>

        @endforeach

    </tbody>

</table>

@endsection