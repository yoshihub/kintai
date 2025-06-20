<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAttendanceListController extends Controller
{
    public function index()
    {
        return view('admin.attendance.list.index');
    }
}
