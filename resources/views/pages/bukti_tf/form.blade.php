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
            ->route('bukti_tf.store')
            ->encType('multipart/form-data')
            ->rules($required)

            }}        

        <div class="flex flex-col md:flex-row">
            @forelse($form as $kform => $vform)
            @if($vform['form_type'] == 'text')
            {{ Aire::input($kform, $vform['label'])
                ->id($kform)
                ->autoComplete('off')
                ->groupClass('flex-1 mr-2 mb-2') }}

            @elseif($vform['form_type'] == 'area')
            
            {{ Aire::textArea($kform, $vform['label'])
                ->id($kform)
                ->groupClass('flex-1 mr-2 mb-2')
                ->rows(3)
                ->cols(40); }}

            @elseif($vform['form_type'] == 'date')

            {{ Aire::date($kform, $vform['label'])
                ->id($kform)
                ->groupClass('flex-1 mr-2 mb-2')
                ->helpText('Browser-native date picker (ymmv)');
            }}

            @elseif($vform['form_type'] == 'file')

            {{ Aire::file($kform, $vform['label'])
                ->id($kform)
                ->groupClass('flex-1 mr-2 mb-2');
            }}

            @endif

            @empty
            <div class="alert alert-danger fade show m-b-10">
                <span class="close" data-dismiss="alert">×</span>
                <b>Error !</b> Form tidak ditemukan
            </div>
            @endforelse


        </div>



        {{ Aire::submit('Simpan')->addClass('my-1 btn-pink'); }}

        {{ Aire::close() }}
    </div>
</div>

@endsection

@push('scripts')

@endpush