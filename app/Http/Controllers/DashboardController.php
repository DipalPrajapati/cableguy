<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class DashboardController extends Controller
{
    public function index(){
        if (Auth::check()){
            $customer = DB::table('customers')->paginate(10);
            return view('pages.dashboard',['customers'=>$customer]);
        }
        else{
            return redirect()->route('getLogin');
        }
    }
}
