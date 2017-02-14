@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">Dashboard</div>
        <div class="panel-body">
          {{--<h1>Hi there!</h1>--}}
          {{-- <a href="/scrape/all">Mannual scrape</a> --}}
          <table class="table">
            <tr class="no-border-top"><td>Our site</td><td>Site where we fetch data</td><td>Fetching data</td><td>Delete</td></tr>
            @foreach ($sites as $site)
            <tr>
             <td><a href="{{ $site->site }}" rel="noreferrer">{{ $site->site }}</a></td>
             <td><a href="{{ $site->site_to_fetch }}" rel="noreferrer">{{ $site->site_to_fetch }}</a></td>
             <td><a href="{{ route('showFetchResult', $site->id) }}" class="btn btn-primary">Check result</a></td>
             <td><a href="{{ route('siteDelete', $site->id) }}" class="btn btn-warning">Delete</a></td>   
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
