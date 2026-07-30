@extends('layouts.admin')

@section('content')

<div class="container">

    <a href="{{ route('admin.reports.response-time') }}">
        Response Time Analytics
    </a>

    <div class="row">

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Average Response</h5>
                    <h2>{{ $averageResponseTime }} min</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Fastest</h5>
                    <h2>{{ $fastestResponse }} min</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Slowest</h5>
                    <h2>{{ $slowestResponse }} min</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Completed Responses</h5>
                    <h2>{{ $completedResponses }}</h2>
                </div>
            </div>
        </div>



    </div>

    <div class="card mt-4 shadow">

        <div class="card-header">
            Monthly Response Time Trend
        </div>

        <div class="card-body">

            <canvas id="responseChart" height="100"></canvas>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('responseChart');

    new Chart(ctx, {

        type: 'line',

        data: {

            labels: @json($labels),

            datasets: [{

                label: 'Average Response Time',

                data: @json($series),

                borderColor: '#dc3545',

                backgroundColor: 'rgba(220,53,69,.15)',

                borderWidth: 3,

                fill: true,

                tension: .4

            }]

        },

        options: {

            responsive: true,

            plugins: {

                legend: {

                    display: true

                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    title: {

                        display: true,

                        text: 'Minutes'

                    }

                }

            }

        }

    });
</script>

@endsection