@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('content')
    @include('components.navbar')

    <br>
    <div class="container">
        <form action="" method="get">
            <div class="input-group">
                <select name="sort" class="form-control">
                    <option value="balance_amt_desc">Balance Amount (Descending)</option>
                    <option value="balance_amt_asc">Balance Amount (Ascending)</option>
                </select>
                <div class="input-group-btn">
                    <button type="submit" class="btn btn-primary">Go!</button>
                </div>
            </div>
        </form>
        @if(Session::has('status'))
            <div class="alert alert-success">{{Session::get('status')}}</div>
        @endif
        <form method="post" action="/deactivate" id="customers">
            {{ csrf_field() }}
            <div class="text-left">
                <button type="button" class="btn btn-primary" id="checkAll">Select All</button>
                <button type="submit" class="btn btn-danger">Deactivate</button>
            </div>
            <div class="text-right">
                <a href="/reloadScraper" class="btn btn-warning">Reload Records</a>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Subscriber name</th>
                        <th>Subscriber Code</th>
                        <th>Phone Number</th>
                        <th>Black List Status</th>
                        <th>Subscriber Status</th>
                        <th>SetTopBox Status</th>
                        <th>STB Number</th>
                        <th>Balance Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td><input type="checkbox" name="customer[]" value="{{$customer->customers_id}}"></td>
                            <td>{{$customer->subscriber_name}}</td>
                            <td>{{$customer->subscriber_code}}</td>
                            <td>{{$customer->phone_number}}</td>
                            <td>{{$customer->blackListStatus}}</td>
                            <td>{{$customer->subscriberStatus}}</td>
                            <td>{{$customer->setTopBoxStatus}}</td>
                            <td>{{$customer->stbNumber}}</td>
                            <td>{{$customer->balance_amt}}</td>
                        </tr>
                    @endforeach
                <tbody>
            </table>
        </form>
        {{$customers->appends(\Input::except('page'))->render()}}
    </div>

@endsection

@section('scripts')
var checkedAll = false;
$("#checkAll").click(function(){
    if (checkedAll === false){
        $('input[type="checkbox"]').prop("checked", true);
        checkedAll = true
    }
    else{
        $('input[type="checkbox"]').prop("checked", false);
        checkedAll = false
    }
});
@endsection 