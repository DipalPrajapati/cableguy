<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request){
        if (Auth::check()){
            $sort = $request->input('sort');
            if (!$sort == null){
                if ($sort == "balance_amt_desc"){
                    $customer = DB::table('customers')->orderBy('balance_amt','DESC')->paginate(10);
                }
                elseif ($sort == "balance_amt_asc"){
                    $customer = DB::table('customers')->orderBy('balance_amt','ASC')->paginate(10);
                }
            }
            else{
                $customer = DB::table('customers')->paginate(10);
            }
            return view('pages.dashboard',['customers'=>$customer]);
        }
        else{
            return redirect()->route('getLogin');
        }
    }
}
