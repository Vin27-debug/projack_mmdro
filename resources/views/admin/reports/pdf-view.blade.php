@extends('layouts.admin')

@section('content')
<section class="container-fluid px-0" aria-labelledby="pdf-preview-title">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h1 id="pdf-preview-title" class="h3 mb-1">PDF Reports</h1>
            <p class="text-white-50 mb-0">Preview the incident report or download a copy.</p>
        </div>
        <a class="btn btn-outline-light" href="{{ route('admin.reports.pdf') }}">
            <i class="bi bi-download me-1" aria-hidden="true"></i> Download PDF
        </a>
    </div>

    <div class="bg-dark border border-secondary rounded overflow-hidden" style="height: min(78vh, 960px); min-height: 480px;">
        <iframe
            title="Incident report PDF preview"
            src="data:application/pdf;base64,{{ $pdfData }}"
            style="width: 100%; height: 100%; border: 0;"
        ></iframe>
    </div>
</section>
@endsection
