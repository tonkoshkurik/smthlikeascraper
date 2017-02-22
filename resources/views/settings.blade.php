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
                        <form method="POST" action="/settings">
                            <div class="form-group">
                                <input type="text" class="form-control" name="bulkapi" placeholder="Bulkadd API">
                            </div>
                            <div class="form-group">
                                <textarea type="text" class="form-control" name="proxy" placeholder="Proxy list"></textarea>
                                <p>Separated by newline</p>
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