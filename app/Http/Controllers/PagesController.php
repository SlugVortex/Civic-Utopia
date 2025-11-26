<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Display the Privacy Policy page.
     */
    public function privacy()
    {
        return view('content.pages.privacy');
    }

    /**
     * Display the Terms of Service page.
     */
    public function terms()
    {
        return view('content.pages.terms');
    }
}
