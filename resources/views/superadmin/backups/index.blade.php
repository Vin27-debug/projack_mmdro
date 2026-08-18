@extends('layouts.superadmin')

@section('content')

<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Data Protection</div>
        <h1 class="page-title">Database Backups</h1>
        <p class="page-subtitle mb-0">Create backups and review recent restore history.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card admin-card border-0 shadow-sm p-4 mb-4">
    <form method="POST" action="{{ route('backups.create') }}">
        @csrf
        <button class="btn btn-primary">Backup Now</button>
    </form>
</div>

<div class="card admin-card border-0 shadow-sm p-4 mb-4">
    <h4 class="text-white mb-3">Backup History</h4>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
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