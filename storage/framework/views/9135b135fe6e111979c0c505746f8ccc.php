<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>MuniResQ Incident Report</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
        }

        th {
            background: #f2f2f2;
        }
    </style>

</head>

<body>

    <h1>MuniResQ Incident Reports</h1>

    <table>

        <thead>

            <tr>

                <th>Incident #</th>
                <th>Location</th>
                <th>Status</th>
                <th>Driver</th>
                <th>Summary</th>
                <th>Actions Taken</th>
                <th>Casualties</th>
                <th>Remarks</th>

            </tr>

        </thead>

        <tbody>

            <?php $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <tr>

                <td>
                    <?php echo e($incident->incident_number); ?>

                </td>

                <td>
                    <?php echo e($incident->location); ?>

                </td>

                <td>
                    <?php echo e(strtoupper($incident->status)); ?>

                </td>

                <td>
                    <?php echo e($incident->driver?->badge_id ?? 'N/A'); ?>

                </td>

                <td>
                    <?php echo e($incident->report?->summary ?? 'N/A'); ?>

                </td>

                <td>
                    <?php echo e($incident->report?->actions_taken ?? 'N/A'); ?>

                </td>

                <td>
                    <?php echo e($incident->report?->casualties ?? 'N/A'); ?>

                </td>

                <td>
                    <?php echo e($incident->report?->remarks ?? 'N/A'); ?>

                </td>

            </tr>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </tbody>

    </table>

</body>

</html><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/pdf/incident-report.blade.php ENDPATH**/ ?>