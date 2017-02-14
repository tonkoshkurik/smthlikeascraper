@extends('layouts.app')
{{--         "id" => 30
      "site_id" => 3
      "link" => "http://nstacommunities.org/blog/2017/02/04/students-teaching-science-to-younger-students/"
      "title" => "Students Teaching Science to Younger Students"
      "saved" => 94
      "created_at" => "2017-02-13 10:02:28"
      "updated_at" => "2017-02-13 14:19:29"  --}}
@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">Last fetched result</div>
        <div class="panel-body">
          <p>Scratch results from site: <b>{{ $site->site_to_fetch }} </b></p>
          {{--                    {{ dd(get_defined_vars()) }} --}}
          <table class="table">
            <thead>
             <tr>
               <td>Post</td>
               <td>Edit</td>
               <td>Fetched</td>
             </tr> 
           </thead>
           <tbody>
            @foreach ($fetch as $fetched)
              <tr>
               <td><a rel="noreferrer" href="{{ $fetched->link }}">{{ $fetched->title }}</a></td>
               <td>
                @if($fetched->saved !== 0)
                 <button class="btn btn-primary editpost" data-id="{{ $fetched->saved }}"  data-toggle="modal" data-target="#editPost">Edit post</button>
                @else
                  <span class="warning">Fetch error</span>
                @endif
               </td>
               <td>{{ $fetched->updated_at }}</td> 
              </tr>
            @endforeach 
           </tbody>
         </table>
         {{-- {!! html_entity_decode($RSS_DISPLAY) !!} --}}
       </div>
     </div>
   </div>
 </div>
</div>
<div id="editPost" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editPost">
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title"></h4>
    </div>
    <!-- .modal-body -->
    <div class="modal-body text-center">
      <form action="{{route('editpost')}}" id="editPostForm" method="POST">
        <div class="inner">
          <img src="{{URL::asset('img/preloader.svg')}}" alt="loading...">
        </div>
        {{csrf_field()}}
      </form>
    </div>
    <!-- /.modal-body -->
    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
   $(document).ready(function(){
        var $modal = $('#editPost');
        var post_id;
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $('.editpost').on('click', function(){
          var postID = $(this).data('id');
          $.ajax({
            type: 'POST',
            url: '{{ URL::to('/getpost') }}',
            data:  { 
              site_id: {{$site->id}},
              post_id: postID
            },
            success: function (data) {
              $modal.find('.modal-body .inner').html(data);
              tinymce.init({ selector:'textarea',
                  height: 500,
                  theme: 'modern',
                  plugins: [
                    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars code fullscreen',
                    'insertdatetime media nonbreaking save table contextmenu directionality',
                    'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc'
                  ],
                  toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                  toolbar2: 'print preview media | forecolor backcolor emoticons | codesample',
                  image_advtab: true,
               });
            }
          });
        });
        $modal.on('hidden.bs.modal', function () {
          $modal.find('.modal-body .inner').html('<img src="{{URL::asset('img/preloader.svg')}}" alt="loading...">');
        });
        var frm = $('#editPostForm');

        frm.submit(function (ev) {
            $.ajax({
                type: 'POST',
                url: '{{route('editpost')}}',
                data: frm.serialize(),
                success: function (data) {
                    $modal.find('.modal-body .inner').html(data);
                }
            });

            ev.preventDefault();
        });
   });
</script>
<script src="//cloud.tinymce.com/stable/tinymce.min.js"></script>
@endsection