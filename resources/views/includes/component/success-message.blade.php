@if ($message = Session::get('success'))

<div class="alert alert-success fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <b>Success !</b> {{ $message }}
</div>
@endif