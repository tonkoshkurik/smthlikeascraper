@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Here is what we got from all sites</div>
                    <div class="panel-body">
                        <table class="table">
                            <tr><td>Our site</td><td>Site where we fetch data</td><td>Fetching data</td><td>Delete</td></tr>
                            @foreach ($result as $r)
                                <tr>
                                    {{ $r->site_to_fetch }}
                                </tr>
                            @endforeach

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
