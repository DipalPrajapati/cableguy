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
                    $customer = DB::table('customers')->orderBy('balance_amt','DESC')->paginate(50);
                }
                elseif ($sort == "balance_amt_asc"){
                    $customer = DB::table('customers')->orderBy('balance_amt','ASC')->paginate(50);
                }
            }
            else{
                $customer = DB::table('customers')->paginate(50);
            }
            return view('pages.dashboard',['customers'=>$customer]);
        }
        else{
            return redirect()->route('getLogin');
        }
    }

    public function search(Request $request){
        $q = $request->input('q');
        $customer = DB::table('customers')->where('stbNumber','like','%' . $q . '%')->paginate(50);
        return view('pages.search',['customers' => $customer]);
    }
}
