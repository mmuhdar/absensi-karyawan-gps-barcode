<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\EmployeeSchedule;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jadwal');
    }
}
