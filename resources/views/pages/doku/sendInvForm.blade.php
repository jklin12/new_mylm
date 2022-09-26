@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
@endpush
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->

@include('includes.component.erorr-message')
@include('includes.component.success-message')


@if ($errors->any())
<div class="alert alert-danger fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="panel panel-inverse">
    <div class="panel-body">
        {{
            Aire::open()
            ->route('send-invoice-store')
            ->rules([
                        'form_inv' => 'required',
                    ])
            }}

        <div class="flex flex-col md:flex-row">

            {{ Aire::input('form_inv', 'Nomor Invoice')
                ->id('form_inv')
                ->autoComplete('off')
                ->groupClass('flex-1 mr-2 mb-2') }}

            {{ Aire::input('form_phone', 'Nomor Telfon')
                ->id('form_phone')
                ->autoComplete('off')
                ->groupClass('flex-1 mr-2 mb-2') }}

        </div>



        {{ Aire::submit('Kirim Invoice')->addClass('my-1 btn-pink'); }}

        {{ Aire::close() }}
    </div>
</div>

@endsection

@push('scripts')

@endpush