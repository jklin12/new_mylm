@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
<link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/smartwizard@6/dist/css/smart_wizard_all.min.css" rel="stylesheet" type="text/css" />
@endpush

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
@if($step == 2)
<div class="note note-warning m-b-15">
    <div class="note-icon"><i class="fa fa-lightbulb"></i></div>
    <div class="note-content">
        <h4><b>Note !</b></h4>
        <p>
            - Untuk OLT C300( TMH_2 ) onu Index Perlu di cek Manual <br>
            - retail profile tcon 2 dan trafic 2 tidak perlu diisi <br>
            - Untuk JSS Profile tcon 1 512k tcon 2 50M dan trafic 1 512k trafi 2 50m
        </p>
    </div>
</div>
@endif
<!-- begin panel -->
<div class="panel panel-inverse">
    <div class="panel-body">
        <div id="smartwizard">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="#step-1">
                        <div class="num">1</div>
                        Add PPP Secret
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#step-2">
                        <span class="num">2</span>
                        PPP Secret Result
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#step-3">
                        <span class="num">3</span>
                        ONU Register
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="#step-4">
                        <span class="num">4</span>
                        Final Check
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
                    <h5> Mikrotik PPP Secret {{ $olt.'-'.$ip_olt }}</h5>
                    @if($step == 0)
                    <div class="row">
                        <div class="col">
                            <form action="{{ route('ppp-register') }}" method="post" id="ppp-register">
                                @csrf
                                <input type="hidden" name="olt" value="{{ $olt }}">
                                <input type="hidden" name="ip_olt" value="{{ $ip_olt }}">
                                <input type="hidden" name="interface" value="{{ $interface }}">
                                <input type="hidden" name="sn" value="{{ $sn }}">
                                <input type="hidden" name="type" value="{{ $type }}">
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="" name="name">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Password</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$sn}}" name="password" required>
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Service</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="pppoe" name="service">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Profile</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$profile_pppoe}}" name="profile">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Remote Address</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$new_ip}}" name="remote_address">
                                    </div>
                                </div>


                            </form>

                        </div>
                        <div class="col">
                            <h5>Ip Terdekat</h5>
                            @forelse($ip_terdekat as $key => $value)
                            <p class="ml-3">
                                {{ $value }}
                            </p>
                            @empty
                            <p>Data tidak ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-pink" form="ppp-register">Next</button>
                    </div>

                    @endif
                </div>
                <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                    @if($step == 1)
                    <table class="table table-striped" id="table-data">
                        <tbody>
                            @forelse($ppp_result[0] as $key => $value)
                            <tr>
                                <td class="col-2">{{$key}}</td>
                                <td>:</td>
                                <td>{{$value}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="col-2">
                                    <p>Data tidak ditemukan</p>
                                </td>

                            </tr>

                            @endif
                        </tbody>
                    </table>
                    <div class="text-right">
                        <a href="{{route('olt-register', ('2?olt=' . $olt . '&ip_olt=' . $ip_olt . '&interface=' . $interface . '&sn=' . $sn . '&type=' . $type.'&name='.$name)) }}" class="btn btn-pink">Next</a>
                    </div>
                    @endif
                </div>
                <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                    @if($step == 2)
                    <h5> OLT {{ $olt.'-'.$ip_olt }}</h5>
                    <div class="row">
                        <div class="col">
                            <form action="{{ route('onu-register') }}" method="post" id="onu-register">
                                @csrf
                                <input type="hidden" name="olt" value="{{ $olt }}">
                                <input type="hidden" name="ip_olt" value="{{ $ip_olt }}">

                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Jenis</label>
                                    <div class="col-md-9">
                                        <select class="form-control" name="jenis" id="jenis">
                                            <option value="1">Retail</option>
                                            <option value="2">JSS</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$name}}" name="name">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">SN ONT</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$sn}}" name="sn">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Type</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5 border border-danger" placeholder="" value="{{$type}}" name="type">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Interface</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$interface}}" name="interface">
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Onu Index Suggestion</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5 border border-danger" placeholder="" value="{{$onu_index}}" name="onu_index">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-3">Profile Tcon 1</label>
                                    <div class="col-md-9">
                                        <select class="default-select2 form-control border border-danger" id="tcon_profile_1" name="tcon_profile_1" required>
                                            <option value="">-- Pilih Profile --</option>
                                            @forelse($profile_tcon as $key => $value)
                                            <option value="{{$value[1]}}">{{$value[1]}}</option>
                                            @empty
                                            <option value="">Data tidak ditemukan</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-3">Profile Tcon 2</label>
                                    <div class="col-md-9">
                                        <select class="default-select2 form-control border border-danger" id="tcon_profile_2" name="tcon_profile_2">
                                            <option value="">-- Pilih Profile --</option>
                                            @forelse($profile_tcon as $key => $value)
                                            <option value="{{$value[1]}}">{{$value[1]}}</option>
                                            @empty
                                            <option value="">Data tidak ditemukan</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-3">Profile Trafic 1</label>
                                    <div class="col-md-9">
                                        <select class="default-select2 form-control border border-danger" id="trafic_profile_1" name="trafic_profile_1" required>
                                            <option value="">-- Pilih Profile --</option>
                                            @forelse($profile as $key => $value)
                                            <option value="{{$value[1]}}">{{$value[1]}}</option>
                                            @empty
                                            <option value="">Data tidak ditemukan</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-3">Profile Trafic 2</label>
                                    <div class="col-md-9">
                                        <select class="default-select2 form-control border border-danger" id="trafic_profile_2" name="trafic_profile_2" required>
                                            <option value="">-- Pilih Profile --</option>
                                            @forelse($profile as $key => $value)
                                            <option value="{{$value[1]}}">{{$value[1]}}</option>
                                            @empty
                                            <option value="">Data tidak ditemukan</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row m-b-15">
                                    <label class="col-form-label col-md-3">Vlan</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control m-b-5" placeholder="" value="{{$vlan}}" name="vlan">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col">
                            <h5># show run int {{ $interface }}</h5>
                            @forelse($onu_data as $key => $value)
                            <p class="ml-3">
                                @foreach($value as $values)
                                {{ $values }}
                                @endforeach
                            </p>
                            @empty
                            <p>Data tidak ditemukan</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-pink" form="onu-register">Next</button>
                    </div>

                    @endif
                </div>
                <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
                    @if($step == 3)
                    <h5> OLT {{ $olt.'-'.$ip_olt }}</h5>
                    <p>
                        @forelse($onu_result as $key => $value)
                        @foreach($value as $keys => $values)
                        {{ $values}} <br>
                        @endforeach
                        <hr>
                        <br>
                        @empty
                        @endforelse
                    </p>
                    @endif
                    <div class="text-right">
                        <a href="{{route('olt-uncfg') }}" class="btn btn-pink">Finish</a>
                    </div>
                </div>
            </div>

            <!-- Include optional progressbar HTML -->
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- end panel -->
@endsection

@push('scripts')
<script src="/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="/assets/plugins/select2/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/smartwizard@6/dist/js/jquery.smartWizard.min.js" type="text/javascript"></script>
<script>
    $(function() {
        $(function() {
            // SmartWizard initialize
            $('#smartwizard').smartWizard({
                selected: <?php echo $step ?>,
                // autoAdjustHeight: false,
                theme: 'arrows',
                toolbar: {
                    showNextButton: false, // show/hide a Next button
                    showPreviousButton: false, // show/hide a Previous button
                }
            });
        });
        $(".default-select2").select2();
        $("#jenis").change(function() {
            var id = $(this).val()

            if (id == 2) {
                $('#tcon_profile_1').select2().select2('val', 'up-512k')
                $('#tcon_profile_2').select2().select2('val', 'UP-LIFESTYLE-50M')

                $('#trafic_profile_1').select2().select2('val', '512k')
                $('#trafic_profile_2').select2().select2('val', 'LIFESTYLE-50M')
            }
        })
    })
</script>
@endpush