<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GuardianController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function index(): View
    {
        return view('guardian.dashboard');
    }
}
