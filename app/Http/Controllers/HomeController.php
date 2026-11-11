<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show Pre-School Homepage with Vision, Mission, Programs & Quick Links.
     */
    public function index(): View
    {
        $school = SchoolSetting::get();

        return view('welcome', compact('school'));
    }

    /**
     * Dedicated About Us, Vision & Mission page.
     */
    public function about(): View
    {
        $school = SchoolSetting::get();

        return view('public.about', compact('school'));
    }
}
