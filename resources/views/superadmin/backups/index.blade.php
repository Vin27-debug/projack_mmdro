@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Data Protection</div>
        <h1 class="page-title">Backup &amp; Recovery</h1>
        <p class="page-subtitle mb-0">Private backups; restoring overwrites the current database.</p>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card admin-card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="text-white mb-1">Create a backup</h4>
            <small class="text-white-50">
                Database driver: {{ strtoupper($driver) }}
                @if (! $isSupported)
                    &mdash; unsupported configuration
                @endif
            </small>
        </div>
        <form method="POST" action="{{ route('backups.create') }}">@csrf <button class="btn btn-primary" @disabled(! $isSupported)>Backup Now</button></form>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm p-4 mb-4">
    <h4 class="text-white mb-3">Available backups</h4>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Filename</th><th>Created</th><th>Size</th><th>Actions</th></tr></thead><tbody>
        @forelse($files as $file)
        <tr><td>{{ $file->getFilename() }}</td><td>{{ \Carbon\Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i') }}</td><td>{{ number_format($file->getSize() / 1024, 1) }} KB</td><td class="text-nowrap"><a class="btn btn-success btn-sm" href="{{ route('backups.download', $file->getFilename()) }}">Download</a> <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#restore-{{ $loop->index }}" @disabled(! $isSupported)>Restore</button></td></tr>
        <div class="modal fade" id="restore-{{ $loop->index }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form method="POST" action="{{ route('backups.restore') }}" class="modal-content">@csrf <input type="hidden" name="backup_file" value="{{ $file->getFilename() }}"><div class="modal-header"><h5 class="modal-title">Restore this backup?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="text-danger">This overwrites the current database. A safety backup will be created first where supported.</p><div class="form-check"><input class="form-check-input" id="confirm-{{ $loop->index }}" type="checkbox" name="confirm_restore" value="1"><label class="form-check-label" for="confirm-{{ $loop->index }}">I understand that current data will be overwritten.</label></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning">Restore backup</button></div></form></div></div>
        @empty <tr><td colspan="4" class="text-center text-muted py-4">No backups are available.</td></tr>
        @endforelse
    </tbody></table></div>
</div>

<div class="card admin-card border-0 shadow-sm p-4"><h4 class="text-white mb-3">Backup activity</h4><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Type</th><th>Filename</th><th>Status</th><th>Message</th><th>When</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{ $log->type }}</td><td>{{ $log->filename }}</td><td>{{ ucfirst($log->status) }}</td><td>{{ $log->message }}</td><td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No backup activity has been recorded.</td></tr>@endforelse</tbody></table></div></div>
@endsection
