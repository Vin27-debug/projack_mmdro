@extends('layouts.driver')

@section('content')
<style>
    .driver-report-page {
        color: #eef4ff;
    }

    .driver-report-page .report-card {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 1rem;
        background: rgba(7, 18, 38, .94);
        box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, .15);
    }

    .driver-report-page .report-section-title {
        color: #fff;
        font-size: 1.125rem;
        font-weight: 650;
    }

    .driver-report-page .form-label {
        color: #e4edff;
        font-size: 1rem;
        font-weight: 600;
    }

    .driver-report-page .form-control {
        min-height: 44px;
        border-color: rgba(148, 163, 184, .38);
        background: #0d1f3a;
        color: #eef4ff;
        font-size: 1rem;
    }

    .driver-report-page textarea.form-control {
        min-height: 112px;
    }

    .driver-report-page .form-control:focus {
        border-color: #4b8bff;
        background: #0a1730;
        color: #fff;
        box-shadow: 0 0 0 .22rem rgba(59, 130, 246, .24);
    }

    .driver-report-page .form-control[readonly] {
        color: #c6d4eb;
        background: rgba(13, 31, 58, .72);
    }

    .driver-report-page .report-help {
        color: #b7c5dc;
        font-size: .875rem;
        line-height: 1.5;
    }

    .attachment-dropzone {
        display: flex;
        min-height: 230px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        border: 1px dashed rgba(126, 166, 231, .72);
        border-radius: .85rem;
        background: rgba(20, 45, 82, .52);
        text-align: center;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .attachment-dropzone.is-dragover,
    .attachment-dropzone:focus-within {
        border-color: #6ba1ff;
        background: rgba(27, 69, 126, .55);
        box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .18);
    }

    .attachment-dropzone-icon {
        display: grid;
        width: 3.25rem;
        height: 3.25rem;
        margin-bottom: .85rem;
        place-items: center;
        border: 1px solid rgba(143, 180, 238, .3);
        border-radius: 50%;
        background: rgba(56, 109, 190, .2);
        color: #a9caff;
        font-size: 1.45rem;
    }

    .attachment-dropzone-title {
        margin: 0 0 .35rem;
        color: #f5f8ff;
        font-size: 1rem;
        font-weight: 600;
    }

    .attachment-dropzone .btn-link {
        padding: 0;
        color: #8fbbff;
        font-weight: 650;
        text-decoration: underline;
        text-underline-offset: .18em;
    }

    .attachment-dropzone .btn-link:focus-visible,
    .attachment-remove:focus-visible,
    .driver-report-page .btn:focus-visible {
        outline: 3px solid #a9caff;
        outline-offset: 3px;
        box-shadow: none;
    }

    .attachment-list {
        display: grid;
        gap: .65rem;
        margin: 1rem 0 0;
        padding: 0;
        list-style: none;
    }

    .attachment-item {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: .75rem;
        padding: .65rem;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: .65rem;
        background: rgba(13, 31, 58, .8);
    }

    .attachment-preview {
        display: grid;
        width: 3rem;
        height: 3rem;
        flex: 0 0 3rem;
        overflow: hidden;
        place-items: center;
        border-radius: .45rem;
        background: rgba(70, 111, 174, .2);
        color: #a9caff;
        font-size: 1.25rem;
    }

    .attachment-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .attachment-file-info {
        min-width: 0;
        flex: 1;
    }

    .attachment-file-name {
        overflow: hidden;
        color: #f2f6ff;
        font-size: .9375rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .attachment-file-size {
        color: #b7c5dc;
        font-size: .875rem;
    }

    .attachment-remove {
        display: grid;
        width: 2.35rem;
        height: 2.35rem;
        flex: 0 0 2.35rem;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: .5rem;
        background: transparent;
        color: #e5edfb;
    }

    .attachment-remove:hover {
        border-color: rgba(255, 125, 136, .55);
        background: rgba(220, 53, 69, .15);
        color: #ffb6bd;
    }

    .report-actions {
        border-top: 1px solid rgba(255, 255, 255, .1);
    }

    @media (max-width: 575.98px) {
        .driver-report-page .card-body {
            padding: 1rem !important;
        }

        .attachment-dropzone {
            min-height: 200px;
            padding: 1rem;
        }
    }
</style>

<div class="container-fluid driver-report-page">
    <header class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase mb-1" style="color:#91b8f5;font-size:.875rem;letter-spacing:.12em;font-weight:700">Response documentation</p>
            <h1 class="h2 fw-bold mb-1">Incident Report</h1>
            <p class="mb-0" style="color:#c1cee2;font-size:1rem">Record your response and attach official incident evidence.</p>
        </div>
        @if($incident)
            <span class="badge rounded-pill text-bg-primary px-3 py-2">{{ $incident->incident_number }}</span>
        @endif
    </header>

    <div class="report-card">
        <div class="card-body p-4 p-xl-5">
            @if(!$incident)
                <div class="text-center py-5" role="status">
                    <i class="bi bi-exclamation-circle fs-1 mb-3 d-block" aria-hidden="true"></i>
                    <h2 class="h5 fw-semibold">No incident available</h2>
                    <p class="mb-4" style="color:#c1cee2">There is no completed incident ready for reporting at the moment.</p>
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
                </div>
            @else
                <form id="driverIncidentReportForm" method="POST"
                    action="{{ route('driver.report.store', $incident) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4 align-items-start">
                        <section class="col-12 col-lg-7" aria-labelledby="response-details-heading">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-clipboard2-pulse text-info" aria-hidden="true"></i>
                                <h2 id="response-details-heading" class="report-section-title mb-0">Response details</h2>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="incident_number" class="form-label">Incident Number</label>
                                    <input id="incident_number" type="text" class="form-control" value="{{ $incident->incident_number }}" readonly>
                                </div>
                                <div class="col-sm-6">
                                    <label for="incident_type" class="form-label">Incident Type</label>
                                    <input id="incident_type" type="text" class="form-control" value="{{ $incident->incident_type }}" readonly>
                                </div>
                                <div class="col-12">
                                    <label for="incident_location" class="form-label">Location</label>
                                    <input id="incident_location" type="text" class="form-control" value="{{ $incident->location }}" readonly>
                                </div>
                                <div class="col-12">
                                    <label for="summary" class="form-label">Summary of Response</label>
                                    <textarea id="summary" name="summary" rows="3" class="form-control">{{ old('summary') }}</textarea>
                                    @error('summary')<div class="text-danger mt-1" role="alert">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="actions_taken" class="form-label">Actions Taken</label>
                                    <textarea id="actions_taken" name="actions_taken" rows="3" class="form-control">{{ old('actions_taken') }}</textarea>
                                    @error('actions_taken')<div class="text-danger mt-1" role="alert">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="casualties" class="form-label">Casualties</label>
                                    <input id="casualties" type="text" name="casualties" value="{{ old('casualties') }}" class="form-control">
                                    @error('casualties')<div class="text-danger mt-1" role="alert">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <input id="remarks" type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                                    @error('remarks')<div class="text-danger mt-1" role="alert">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </section>

                        <aside class="col-12 col-lg-5" aria-labelledby="attachments-heading">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-paperclip text-info" aria-hidden="true"></i>
                                <h2 id="attachments-heading" class="report-section-title mb-0">Photos / Documents</h2>
                            </div>

                            <input id="attachments" class="visually-hidden" type="file" name="attachments[]" multiple
                                accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx"
                                aria-describedby="attachment-help attachment-selection-status">
                            <div id="attachmentDropzone" class="attachment-dropzone" role="group" aria-labelledby="attachments-heading">
                                <div class="attachment-dropzone-icon"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i></div>
                                <p class="attachment-dropzone-title">
                                    <button id="chooseAttachments" type="button" class="btn btn-link">Click to upload</button>
                                    <span>or drag and drop</span>
                                </p>
                                <p id="attachment-help" class="report-help mb-0">JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX</p>
                                <p class="report-help mb-0">Up to 10 files · 10 MB each maximum</p>
                            </div>
                            @error('attachments')<div class="text-danger mt-2" role="alert">{{ $message }}</div>@enderror
                            @foreach($errors->get('attachments.*') as $attachmentErrors)
                                @foreach($attachmentErrors as $message)
                                    <div class="text-danger mt-2" role="alert">{{ $message }}</div>
                                @endforeach
                            @endforeach
                            <div id="attachmentClientErrors" class="text-danger mt-2" role="alert" aria-live="assertive"></div>
                            <p id="attachment-selection-status" class="report-help mt-3 mb-2" aria-live="polite">No files selected.</p>
                            <ul id="attachmentList" class="attachment-list" aria-label="Selected attachments"></ul>
                        </aside>
                    </div>

                    <div class="report-actions d-flex flex-wrap justify-content-end gap-2 mt-4 pt-4">
                        <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-light px-4">Cancel</a>
                        <button id="submitIncidentReport" type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1" aria-hidden="true"></i>Submit Report
                        </button>
                    </div>
                    <p id="reportSubmissionStatus" class="report-help text-end mt-2 mb-0" role="status" aria-live="polite"></p>
                </form>
            @endif
        </div>
    </div>
</div>

@if($incident)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('driverIncidentReportForm');
        const input = document.getElementById('attachments');
        const chooser = document.getElementById('chooseAttachments');
        const dropzone = document.getElementById('attachmentDropzone');
        const list = document.getElementById('attachmentList');
        const selectionStatus = document.getElementById('attachment-selection-status');
        const clientErrors = document.getElementById('attachmentClientErrors');
        const submitButton = document.getElementById('submitIncidentReport');
        const submissionStatus = document.getElementById('reportSubmissionStatus');
        const maxFiles = 10;
        const maxFileSize = 10 * 1024 * 1024;
        const acceptedExtensions = new Set(['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);
        let selectedFiles = [];
        let previewUrls = [];

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function syncInputFiles() {
            const transfer = new DataTransfer();
            selectedFiles.forEach(function (file) { transfer.items.add(file); });
            input.files = transfer.files;
        }

        function renderFiles() {
            previewUrls.forEach(function (url) { URL.revokeObjectURL(url); });
            previewUrls = [];
            list.replaceChildren();

            if (selectedFiles.length === 0) {
                selectionStatus.textContent = 'No files selected.';
                return;
            }

            const totalSize = selectedFiles.reduce(function (sum, file) { return sum + file.size; }, 0);
            selectionStatus.textContent = selectedFiles.length + ' of ' + maxFiles + ' files selected · ' + formatFileSize(totalSize) + ' total';

            selectedFiles.forEach(function (file, index) {
                const item = document.createElement('li');
                item.className = 'attachment-item';

                const preview = document.createElement('span');
                preview.className = 'attachment-preview';
                preview.setAttribute('aria-hidden', 'true');
                if (file.type.startsWith('image/')) {
                    const image = document.createElement('img');
                    const url = URL.createObjectURL(file);
                    previewUrls.push(url);
                    image.src = url;
                    image.alt = '';
                    preview.appendChild(image);
                } else {
                    const icon = document.createElement('i');
                    icon.className = file.type === 'application/pdf' ? 'bi bi-file-earmark-pdf' : 'bi bi-file-earmark-text';
                    preview.appendChild(icon);
                }

                const info = document.createElement('span');
                info.className = 'attachment-file-info';
                const name = document.createElement('span');
                name.className = 'attachment-file-name d-block';
                name.textContent = file.name;
                const size = document.createElement('span');
                size.className = 'attachment-file-size';
                size.textContent = formatFileSize(file.size);
                info.append(name, size);

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'attachment-remove';
                remove.setAttribute('aria-label', 'Remove ' + file.name);
                remove.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
                remove.addEventListener('click', function () {
                    selectedFiles.splice(index, 1);
                    syncInputFiles();
                    renderFiles();
                    clientErrors.textContent = '';
                });

                item.append(preview, info, remove);
                list.appendChild(item);
            });
        }

        function addFiles(fileList) {
            const additions = Array.from(fileList || []);
            const accepted = [];
            const rejected = [];

            additions.forEach(function (file) {
                const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
                if (!acceptedExtensions.has(extension)) {
                    rejected.push(file.name + ': unsupported file type.');
                } else if (file.size > maxFileSize) {
                    rejected.push(file.name + ': larger than 10 MB.');
                } else if (selectedFiles.length + accepted.length >= maxFiles) {
                    rejected.push(file.name + ': the 10-file limit has been reached.');
                } else {
                    accepted.push(file);
                }
            });

            selectedFiles = selectedFiles.concat(accepted);
            syncInputFiles();
            renderFiles();
            clientErrors.textContent = rejected.join(' ');
        }

        chooser.addEventListener('click', function () { input.click(); });
        input.addEventListener('change', function () {
            const chosenFiles = Array.from(input.files || []);
            input.value = '';
            addFiles(chosenFiles);
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', function (event) { addFiles(event.dataTransfer.files); });

        form.addEventListener('submit', function () {
            submitButton.disabled = true;
            submissionStatus.textContent = 'Submitting report and selected attachments…';
        });

        window.addEventListener('beforeunload', function () {
            previewUrls.forEach(function (url) { URL.revokeObjectURL(url); });
        });
    });
</script>
@endif
@endsection
