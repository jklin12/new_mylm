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
<div id="accordion" class="accordion">
    <!-- begin card -->
    @forelse($detail_data as $key => $value)
    <div class="card ">
        <div class="card-header bg-pink text-white pointer-cursor d-flex align-items-center" data-toggle="collapse" data-target="#collapse-{{$key}}" aria-expanded="true">
            <i class="fa fa-circle fa-fw text-warning mr-2 f-s-8"></i> {{ $value['title']}}
        </div>
        <div id="collapse-{{$key}}" class="collapse show" data-parent="#accordion" style="">
            <table class="table table-striped m-b-0">
                <tbody>
                    @foreach($value['data'] as $keys => $values)
                    <tr>
                        <td class="col-1">
                            {{ $values[0] }}
                        </td>
                        <td class="text-center">:</td>
                        <td>
                            @if($keys == 'inv_status' || $keys == 'result_msg' || $keys == 'cust_status')
                            <span class="label label-{{$values[1][1]}}">{{$values[1][0]}}</span>
                            
                            @else
                            {{ $values[1] }}
                            @endif
                        </td>
                       
                    </tr>  
                    @endforeach     
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-warning fade show m-b-10">
        <span class="close" data-dismiss="alert">×</span>
        <b>Maaf </b>, Data tidak ditemukan
    </div>
    @endforelse


</div>
<!-- end panel -->
@endsection

@push('scripts')
<script>

</script>
@endpush