@if ($message = Session::get('erorr'))

<div class="alert alert-danger fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <b>Error !</b> {{ $message }}
</div>
@endif