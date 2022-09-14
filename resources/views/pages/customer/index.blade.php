@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<style>
    .pagination>li>a,
    .pagination>li>span {
        color: #b64260;
    }

    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        background-color: green;
        border-color: green;
    }

    .page-item.active .page-link {
        z-index: 1;
        color: #fff;
        background-color: #b64260;
        border-color: #b64260;
    }
</style>
@endpush
<!-- begin breadcrumb -->
<ol class="breadcrumb float-xl-right">
    <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
    <li class="breadcrumb-item"><a href="javascript:;">Page Options</a></li>
    <li class="breadcrumb-item active">Blank Page</li>
</ol>
<!-- end breadcrumb -->
<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">
     
    <div class="panel-body">
        <div class="row mb-2">
            <div class="col-md-3 w-25 ">
                <div class="input-group rounded">
                    <input type="search" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
                    <span class="input-group-text border-0 ml-1" id="search-addon">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
            <div class="col-md">
                <button type="button" class="btn btn-pink" data-toggle="collapse" href="#collapseFilter" aria-expanded="false"><i class="fa fa-filter">&nbsp;</i>Filter</button>
            </div>
        </div>
        <div class="collapse mb-2" id="collapseFilter">
            <div class="card card-body">
                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped m-b-0">
                <thead class="bg-pink text-white">
                    <tr>
                        <th class="text-center">Nomor Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Layanan</th>
                        <th>Nomor Telfon</th>
                        <th>Mulai Layanan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $value)
                    <tr>
                        <td class="text-center">
                            {{ $value->cust_number }}
                        </td>
                        <td>{{ $value->cust_name}}</td>
                        <td>{{ $value->sp_code}}</td>
                        <td>{{ $value->cust_phone}}</td>
                        <td>{{ \Carbon\Carbon::parse($value->cupkg_svc_begin)->isoFormat('dddd, D MMMM Y')}}</td>
                        
                       
                        <td> <a href="{{ route('customer-detail','?cust='.$value->cust_number) }}" class="btn btn-pink btn-circle  m-r-2"><i class="fa fa-search-plus"></i> </a></td>

                    </tr>
                    @empty
                    <tr>
                        <td class="text-center text-mute" colspan="4">Data tidak tersedia</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">

            {!! $datas->onEachSide(5)->links() !!}
        </div>
    </div>
</div>
<!-- end panel -->
@endsection