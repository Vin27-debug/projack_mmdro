@extends('layouts.driver')

@section('content')

<div class="container">

    <h2>My Assignment</h2>

    @if($dispatch)

    <div class="card">
        <div class="card-body">

            <h4>
                Incident:
                {{ $dispatch->incident->incident_number }}
            </h4>

            <p>
                Location:
                {{ $dispatch->incident->location }}
            </p>

            <p>
                Status:
                {{ $dispatch->status }}
            </p>

            <a
                href="{{ route('driver.navigation') }}"
                class="btn btn-primary">
                Open Navigation
            </a>

        </div>
    </div>

    @else

    <div class="alert alert-info">
        No assignment available.
    </div>

    @endif

</div>

@endsection