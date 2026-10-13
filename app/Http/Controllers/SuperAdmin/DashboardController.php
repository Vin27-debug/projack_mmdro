<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

class DashboardController extends AdminDashboardController
{
    protected function dashboardView(): string
    {
        return 'superadmin.dashboard';
    }
}
