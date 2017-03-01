@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Add new site</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <ul>
                            @foreach($errors->all() as $error)
                               <li>{{ $error }}</li> 
                            @endforeach              
                        </ul>
                    </div>
                <hr>
                    <form method="POST" action="/sites/create">
                        <div class="form-group">
                            <label>Our WP site</label>
                            <input type="text" class="form-control" name="site" placeholder="Our site">
                        </div>
                        <div class="form-group">
                            <label>Login for our site</label>
                            <input type="text" class="form-control" name="login" placeholder="Site login">
                        </div>
                        <div class="form-group">
                            <label>Site password</label>
                            <input type="text" class="form-control" name="password" placeholder="Site password">
                        </div>
                        <div class="form-group">
                        <label>Sites where we going to fetch data</label>
                            <textarea type="text" class="form-control" name="site_to_fetch" placeholder="Sites to fetch"></textarea>
                            <p>Separated by <a href="https://en.wikipedia.org/wiki/Newline">newline</a></p>
                        </div>
                        <div class="form-group">
                            <input type="submit" value="submit">
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
