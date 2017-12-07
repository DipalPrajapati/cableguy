<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SiteController extends Controller
{
    public function index(Request $request){
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        else{
            return redirect()->route('getLogin');
        }

    }
}
