<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MuniResQ Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])
</head>

<body>

    <div class="container-fluid">

        <div class="row">

            <!-- SIDEBAR -->

            <div class="col-md-2 bg-dark text-white min-vh-100 p-3">

                <h3>MuniResQ</h3>

                <hr>

                <div class="list-group">

                    <a href="{{ route('admin.dashboard') }}"
                        class="list-group-item list-group-item-action">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.dispatches.index') }}"
                        class="list-group-item list-group-item-action">
                        Dispatches
                    </a>

                    <a href="{{ route('admin.gps.monitoring') }}"
                        class="list-group-item list-group-item-action">
                        GPS Monitoring
                    </a>

                    <a href="{{ route('admin.incidents.index') }}"
                        class="list-group-item list-group-item-action">
                        Nearest Vehicle
                    </a>

                    <a href="{{ route('admin.maintenance.index') }}"
                        class="list-group-item list-group-item-action">
                        Vehicle Maintenance
                    </a>

                    <a href="{{ route('admin.reports.index') }}"
                        class="list-group-item list-group-item-action">
                        Incident Reports
                    </a>

                    <a href="{{ route('admin.reports.driver-performance') }}"
                        class="list-group-item list-group-item-action">
                        Driver Performance
                    </a>

                    <a href="{{ route('admin.reports.response-time') }}"
                        class="list-group-item list-group-item-action">
                        Response Time
                    </a>

                    <a href="{{ route('admin.reports.vehicle-utilization') }}"
                        class="list-group-item list-group-item-action">
                        Vehicle Utilization
                    </a>

                    <a href="{{ route('admin.reports.pdf.view') }}"
                        class="list-group-item list-group-item-action">
                        PDF Reports
                    </a>

                </div>

            </div>

            <!-- CONTENT -->

            <div class="col-md-10">

                <div class="p-4">

                    @yield('content')

                </div>

            </div>

        </div>

    </div>

</body>

</html>