@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
        
            <div class="panel panel-default">
                <div class="panel-heading">Config</div>

                <div class="panel-body">
                    <form>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                      </div>
                      <button type="submit" class="btn btn-default">Submit</button>
                    </form>

                </div>
            </div>
                   
        </div>
    </div>
</div>
@endsection
