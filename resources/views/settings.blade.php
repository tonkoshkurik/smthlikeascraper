@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    {{--<div class="panel-heading">Add new site</div>--}}
                    <div class="panel-body">
                        <div class="col-md-6">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <hr>
                        <form method="POST" action="{{url('/settings')}}">
                            <div class="form-group">
                                <label for="bulkapi">BulkAddURL API</label>
                                <input type="text" id="bulkapi" class="form-control" name="bulkapi" value="{{$bulkapi}}" placeholder="Bulkadd API">
                            </div>
                            <div class="form-group">
                                <label for="proxy_list">List of proxy</label>
                                <textarea type="text" id="proxy_list" class="form-control" name="proxy" placeholder="Proxy list">{{$proxy}}</textarea>
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