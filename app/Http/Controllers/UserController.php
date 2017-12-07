<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class UserController extends Controller
{
    public function getLogin(){
        if (!Auth::check()){
            return view('pages.login');
        }
        else{
            return view('pages.dashboard');
        }
    }

    public function postLogin(Request $request){
        $username = $request->input('username');
        $password = $request->input('password');

        if (Auth::attempt(['username' => $username,'password' => $password])){
            return redirect()->route('dashboard');
        }

        else{
            return redirect()->back()->with('status','Incorrect username or password');
        }

    }

    public function logout(){
        if (Auth::check()){
            Auth::logout();
            return redirect()->route('getLogin');
        }
    }
}
