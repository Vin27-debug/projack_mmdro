@extends('layouts.app')

@section('content')
<h1>Driver Dashboard</h1>

<p>Total Assigned Incidents: {{ $incidents->count() }}</p>

@foreach($incidents as $incident)
<div>
    {{ $incident->incident_number }} -
    {{ $incident->location }} -
    {{ $incident->status }}
</div>
@endforeach

@endsection