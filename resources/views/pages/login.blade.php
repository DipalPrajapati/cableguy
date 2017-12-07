@extends('layouts.master')

@section('title')
Login to continue
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <br>
                @if(Session::has('status'))
                    <div class="alert alert-danger">{{Session::get('status')}}</div>
                @endif
                <h3>Login to continue</h3>
                <form method="post" action="/login">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>Username: </label>
                        <input class="form-control" type="text" name="username"> 
                    </div>
                    <div class="form-group">
                        <label>Password: </label>
                        <input class="form-control" type="password" name="password"> 
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
@endsection