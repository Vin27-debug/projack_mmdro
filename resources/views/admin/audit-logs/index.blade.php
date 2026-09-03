@extends('layouts.admin')

@section('content')

<div class="audit-page">

    <div class="audit-header">
        <div>
            <h2>Audit Logs</h2>
            <p>Track system activities and administrative actions.</p>
        </div>

        <div class="audit-count">
            {{ $logs->total() }} Records
        </div>
    </div>


    <div class="audit-card">

        <div class="audit-card-header">
            <div>
                <h3>Activity History</h3>
                <span>Recent system actions</span>
            </div>

            <i class="bi bi-journal-text"></i>
        </div>


        <div class="audit-table-wrapper">

            <table class="audit-table">

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
                        <td>
                            <span class="audit-id">
                                #{{ $log->id }}
                            </span>
                        </td>

                        <td>
                            <span class="audit-action">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td>
                            <span class="audit-module">
                                {{ $log->module }}
                            </span>
                        </td>

                        <td class="audit-description">
                            {{ $log->description }}
                        </td>

                        <td>
                            <span class="audit-ip">
                                {{ $log->ip_address }}
                            </span>
                        </td>

                        <td>
                            <span class="audit-date">
                                {{ $log->created_at?->format('M d, Y h:i A') }}
                            </span>
                        </td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="6">
                            <div class="audit-empty">
                                <i class="bi bi-journal-x"></i>

                                <h4>No Audit Logs Found</h4>

                                <p>
                                    System activities will appear here once recorded.
                                </p>
                            </div>
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($logs->hasPages())

        <div class="audit-pagination">
            {{ $logs->links() }}
        </div>

        @endif

    </div>

</div>


<style>

.audit-page {
    width: 100%;
}


/* HEADER */

.audit-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.audit-header h2 {
    margin: 0;
    color: #fff;
    font-size: 1.6rem;
    font-weight: 700;
}

.audit-header p {
    margin: .35rem 0 0;
    color: rgba(255,255,255,.6);
    font-size: .9rem;
}

.audit-count {
    padding: .55rem .9rem;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: .7rem;
    background: rgba(255,255,255,.05);
    color: rgba(255,255,255,.8);
    font-size: .85rem;
}


/* CARD */

.audit-card {
    overflow: hidden;
    background: rgba(7,18,38,.88);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 1.25rem;
    box-shadow: 0 18px 40px rgba(0,0,0,.18);
}


/* CARD HEADER */

.audit-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

.audit-card-header h3 {
    margin: 0;
    color: #fff;
    font-size: 1rem;
    font-weight: 650;
}

.audit-card-header span {
    display: block;
    margin-top: .25rem;
    color: rgba(255,255,255,.5);
    font-size: .8rem;
}

.audit-card-header > i {
    color: #6c8cff;
    font-size: 1.35rem;
}


/* TABLE */

.audit-table-wrapper {
    overflow-x: auto;
}

.audit-table {
    width: 100%;
    border-collapse: collapse;
}

.audit-table th {
    padding: .9rem 1.2rem;
    background: rgba(255,255,255,.035);
    border-bottom: 1px solid rgba(255,255,255,.08);
    color: rgba(255,255,255,.55);
    font-size: .72rem;
    font-weight: 600;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: .06em;
    white-space: nowrap;
}

.audit-table td {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid rgba(255,255,255,.06);
    color: rgba(255,255,255,.82);
    font-size: .86rem;
    vertical-align: middle;
}

.audit-table tbody tr {
    transition: background .15s ease;
}

.audit-table tbody tr:hover {
    background: rgba(59,105,255,.055);
}

.audit-table tbody tr:last-child td {
    border-bottom: none;
}


/* TABLE CONTENT */

.audit-id {
    color: rgba(255,255,255,.45);
    font-size: .8rem;
}

.audit-action {
    display: inline-block;
    padding: .3rem .6rem;
    border-radius: .45rem;
    background: rgba(59,105,255,.12);
    color: #91a8ff;
    font-size: .76rem;
    font-weight: 600;
}

.audit-module {
    color: rgba(255,255,255,.7);
    font-weight: 500;
}

.audit-description {
    min-width: 220px;
    max-width: 420px;
    color: rgba(255,255,255,.65) !important;
}

.audit-ip {
    font-family: Consolas, monospace;
    color: rgba(255,255,255,.55);
    font-size: .78rem;
}

.audit-date {
    color: rgba(255,255,255,.55);
    font-size: .78rem;
    white-space: nowrap;
}


/* EMPTY */

.audit-empty {
    padding: 4rem 1rem;
    text-align: center;
}

.audit-empty i {
    display: block;
    margin-bottom: .8rem;
    color: rgba(255,255,255,.25);
    font-size: 2.5rem;
}

.audit-empty h4 {
    margin: 0 0 .4rem;
    color: rgba(255,255,255,.8);
    font-size: 1rem;
}

.audit-empty p {
    margin: 0;
    color: rgba(255,255,255,.45);
    font-size: .82rem;
}


/* PAGINATION */

.audit-pagination {
    padding: 1rem 1.2rem;
    border-top: 1px solid rgba(255,255,255,.06);
}


/* MOBILE */

@media (max-width: 768px) {

    .audit-header {
        align-items: flex-start;
        gap: 1rem;
        flex-direction: column;
    }

    .audit-table {
        min-width: 850px;
    }

}

</style>

@endsection