<!DOCTYPE html>
<html>

<head>
    <title>Submit Incident Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

    <div class="container mt-4">

        <h2>
            Incident Report
        </h2>

        <form method="POST"
            action="{{ route('driver.report.store',$incident) }}">

            @csrf

            <div class="mb-3">
                <label>Summary</label>

                <textarea
                    name="summary"
                    class="form-control"
                    required></textarea>
            </div>

            <div class="mb-3">
                <label>Actions Taken</label>

                <textarea
                    name="actions_taken"
                    class="form-control"
                    required></textarea>
            </div>

            <div class="mb-3">
                <label>Casualties</label>

                <input
                    type="number"
                    name="casualties"
                    class="form-control"
                    value="0">
            </div>

            <div class="mb-3">
                <label>Remarks</label>

                <textarea
                    name="remarks"
                    class="form-control"></textarea>
            </div>

            <button
                class="btn btn-success">
                Submit Report
            </button>

        </form>

    </div>

</body>

</html>