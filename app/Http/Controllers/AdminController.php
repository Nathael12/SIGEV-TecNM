<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'dashboardDate' => Carbon::now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y'),
        ]);
    }
}
