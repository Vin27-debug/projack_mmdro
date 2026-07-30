<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;

class DriverAssignmentController extends Controller
{
    public function index()
    {
        return view('driver.assignments');
    }
}
