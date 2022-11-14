@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')


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


@if($datas)
<div class="panel panel-inverse" data-sortable-id="ui-buttons-1">
    <!-- begin panel-heading -->
    <div class="panel-heading bg-pink ui-sortable-handle">


    </div>
    <!-- end panel-heading -->
    <!-- begin panel-body -->
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped m-b-0">


                <tbody>
                    <tr>
                        <td class="col-2"><b>Nomor SPK </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->ft_number}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Layanan </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->sp_code}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Tipe </b></td>
                        <td class="text-center">:</td>
                        <td>{{ spkType($datas->ft_status)}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Status </b></td>
                        <td class="text-center">:</td>
                        <td>{{ spkVal($datas->ft_status)}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Keterangan </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->ft_desc}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Lampiran </b></td>
                        <td class="text-center">:</td>
                        <td>- <br></td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Kelengkapan Kerja </b></td>
                        <td class="text-center">:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Rencana Pengerjaan </b></td>
                        <td class="text-center">:</td>
                        <td>{{\Carbon\Carbon::parse($datas->ft_plan)->isoFormat('D MMMM Y');}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Pelaksana 1 </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->ft_executor1}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Pelaksana 2 </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->ft_executor2}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Koordinator </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->ft_coordinator}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Diterima </b></td>
                        <td class="text-center">:</td>
                       

                        <td>{{\Carbon\Carbon::parse($datas->ft_recived)->isoFormat('D MMMM Y H:m')}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Update </b></td>
                        <td class="text-center">:</td>
                       
                        <td>{{\Carbon\Carbon::parse($datas->ft_updated)->isoFormat('D MMMM Y H:m')}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Update Oleh </b></td>
                        <td class="text-center">:</td>
                        <td>{{ $datas->emp_name}}</td>
                    </tr>
                    <tr>
                        <td class="col-2"><b>Selesai </b></td>
                        <td class="text-center">:</td>
                        <td>{{\Carbon\Carbon::parse($datas->ft_solved)->isoFormat('D MMMM Y H:m')}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- end hljs-wrapper -->
</div>
@else
<div class="alert alert-warning fade show m-b-10">
    <span class="close" data-dismiss="alert">×</span>
    <b>Maaf </b>, Data tidak ditemukan
</div>
@endif
<!-- end panel -->
<div class="modal" tabindex="-1" role="dialog" id="modal-qris">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="/assets/img/svg/QRIS_logo.svg.png" alt="" srcset="" class="img-fluid w-50 ">
                <div id="qr-container" class="text-center"></div>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="message_modal" tabindex="-1" role="dialog" aria-labelledby="message_modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="message_modalLabel">Kirim Porfoma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('send-invoice-store')}}" method="POST" id="form_send_message">
                    @csrf
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Nomor Porfoma :</label>
                        <input type="text" class="form-control" id="" name="form_inv" value="{{ $datas['inv_number'] }}">
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Nomor Telfon :</label>
                        <input type="text" class="form-control" id="" name="form_phone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="form_send_message" class="btn btn-pink">Kirim</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

</script>
@endpush