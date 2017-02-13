@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <div class="panel panel-default">
        <div class="panel-heading">Dashboard</div>
        <div class="panel-body">
          {{--<h1>Hi there!</h1>--}}
          <a href="/scrape/all">Mannual scrape</a>
          <table class="table">
            <tr><td>Our site</td><td>Site where we fetch data</td><td>Fetching data</td><td>Delete</td></tr>
            @foreach ($sites as $site)
            <tr>
             <td><a href="{{ $site->site }}">{{ $site->site }}</a></td>
             <td><a href="{{ route('siteDelete', $site->site_to_fetch) }}">{{ $site->site_to_fetch }}</a></td>
             <td><a href="/sites/connect/{{ $site->id }}" class="btn btn-default">Connect to WP</a></td>
             <td><a href="{{ route('showFetchResult', $site->id) }}" class="btn btn-primary">Check result</a></td>
             <td><a href="/sites/delete/{{ $site->id }}" class="btn btn-warning">Delete</a></td>   
           </tr>
           @endforeach
         </table>
         <hr>
         <a href="{{ route('addsite') }}" class="btn btn-primary">Add new site</a>
       </div>
     </div>
   </div>
 </div>
</div>
@endsection
