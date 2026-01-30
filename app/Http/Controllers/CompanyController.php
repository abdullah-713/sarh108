<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function webhook(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }

    public function success(Request $request)
    {
        return redirect()->route('dashboard')->with('success', 'Payment successful');
    }

    public function callback(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
