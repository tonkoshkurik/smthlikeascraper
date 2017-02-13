@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Dashboard</div>
                    <div class="panel-body">
                        <p>Scratch results from site: <b>{{ $site->site_to_fetch }} </b></p>
{{--                    {{ dd(get_defined_vars()) }} --}}
                        {!! html_entity_decode($RSS_DISPLAY) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
