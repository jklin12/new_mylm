@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
@push('css')
<link href="https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.css" rel="stylesheet">
<link href="/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .map-overlay {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #fff;
        margin-right: 20px;
        font-family: Arial, sans-serif;
        overflow: auto;
        border-radius: 3px;
    }

    #features {
        top: 0;
        height: 100px;
        margin-top: 20px;
        width: 250px;
    }

    #legend {
        padding: 10px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        line-height: 18px;
        height: 200px;
        margin-right: 50px;
        margin-bottom: 80px;
        width: 200px;
    }

    .legend-key {
        display: inline-block;
        border-radius: 20%;
        width: 10px;
        height: 10px;
        margin-right: 5px;
    }
</style>
@endpush

<!-- begin page-header -->
<h1 class="page-header">{{ $title}}<small>&nbsp;{{ $sub_title }}</small></h1>
<!-- end page-header -->
<!-- begin panel -->
<div class="panel panel-inverse">
    <div class="panel-body">
        <form action="" method="get" id="search-filter">
            <h5>Filter Pencarian</h5>
            <div class="row ">
                <div class="col-md-4">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Status Pelanggan</label>
                        <div class="col-md-9">
                            <select name="cupkg_status[]" id="filter_cupkg_status" class="multiple-select2 form-control" multiple="multiple">
                                <option value="">Select Status</option>
                                <option value="1">Registrasi</option>
                                <option value="2">Instalasi</option>
                                <option value="3">Setup</option>
                                <option value="4">Sistem Aktif</option>
                                <option value="5">Tidak Aktif</option>
                                <option value="6">Trial</option>
                                <option value="7">Sewa Khusus</option>
                                <option value="8">Blokir</option>
                                <option value="9">Eksklusif</option>
                                <option value="10">CSR</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Layanan</label>
                        <div class="col-md-9">
                            <select name="sp_code[]" id="filter_sp_code" class="multiple-select2 form-control" multiple="multiple">
                                <option></option>
                                <option value="Life Vu">Life Vu</option>
                                <option value="Life Vision - K">Life Vision - K</option>
                                <option value="Life Vision - T">Life Vision - T</option>
                                <option value="Big Pipe 2">Big Pipe 2 Core</option>
                                <option value="Life - VPN 4">Life - VPN 4</option>
                                <option value="Life - Corp 125">Life - Corp 125</option>
                                <option value="Life - Corp 150">Life - Corp 150</option>
                                <option value="Life - Corp 70">Life - Corp 70</option>
                                <option value="Life Style 200">Life Style 200</option>
                                <option value="Life - VPN 3">Life - VPN 3</option>
                                <option value="Life - VPN 6">Life - VPN 6</option>
                                <option value="Life - Corp 5">Life - Corp 5 </option>
                                <option value="Life - Corp 200">Life - Corp 200</option>
                                <option value="Life - VPN 1 ">Life - VPN 1 </option>
                                <option value="Life - Corp 80">Life - Corp 80</option>
                                <option value="Life - Corp 50">Life - Corp 50</option>
                                <option value="Life - Corp 30">Life - Corp 30</option>
                                <option value="Life - Corp 300">Life - Corp 300</option>
                                <option value="Life - Corp 10">Life - Corp 10</option>
                                <option value="Life - Corp 100">Life - Corp 100</option>
                                <option value="Life - Corp 60">Life - Corp 60</option>
                                <option value="Life - Corp 15">Life - Corp 15</option>
                                <option value="Life Style 10">Life Style 10</option>
                                <option value="Life Style 20">Life Style 20</option>
                                <option value="Life Style - Trusty ">Life Style - Trusty 30</option>
                                <option value="Life Style 50">Life Style 50</option>
                                <option value="Life Style 100">Life Style 100</option>
                                <option value="Life - Corp 20 ">Life - Corp 20</option>
                                <option value="EMPTY">EMPTY</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">POP</label>
                        <div class="col-md-9">
                            <select name="cust_pop" id="filter_cust_pop" class="multiple-select2 form-control" multiple="multiple">
                                <option value="">Select POP</option>
                                @foreach($arr_pop as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Kecamatan</label>
                        <div class="col-md-9">
                            <select name="cust_kecamatan" id="filter_kecamatan" class="multiple-select2 form-control" multiple="multiple">
                                <option value="">Select Kecamatan</option>
                                @foreach($kecamatan as $key => $value)
                                <option value="{{$value->area_name}}">{{$value->area_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group row m-b-15">
                        <label class="col-form-label col-md-3">Kelurahan</label>
                        <div class="col-md-9">
                            <select name="cust_kelurahan" id="filter_kelurahan" class="multiple-select2 form-control" multiple="multiple">
                                <option value="">Select Kelurahan</option>
                                @foreach($Kelurahan as $key => $value)
                                <option value="{{$value->area_name}}">{{$value->area_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


            </div>

            <div class="mb-3 text-right">
                <button type="submit" class="btn btn-pink"><i class="fa fa-search"></i> Cari</button>
            </div>
        </form>
        <div id='map' style='height: 500px;'></div>
    </div>
</div>

<div class="map-overlay d-none d-xl-block" id="legend">
    <div>
        <span class="legend-key" style="background-color: #727cb6;"></span>
        <span>Registrasi</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #8753de;"></span>
        <span>Instalasi</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #49b6d6;"></span>
        <span>Setup</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #32a932;"></span>
        <span>Sistem Aktif</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #000000;"></span>
        <span>Tidak Aktif</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #348fe2;"></span>
        <span>Sewa Khusus</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #ffd900;"></span>
        <span>Blokir</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #00acac;"></span>
        <span>Ekslusif</span>
    </div>
    <div>
        <span class="legend-key" style="background-color: #00acac;"></span>
        <span>CSR</span>
    </div>

</div>
<!-- end panel -->
@endsection

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.js"></script>
<script src="../assets/plugins/select2/dist/js/select2.min.js"></script>
<script>
    $(".multiple-select2").select2();
    mapboxgl.accessToken = 'pk.eyJ1IjoiZmFyaXNhaXp5IiwiYSI6ImNrd29tdWF3aDA0ZDAycXVzMWp0b2w4cWQifQ.tja8kdSB4_zpO5rOgGyYrQ';
    const map = new mapboxgl.Map({
        container: 'map',
        // Choose from Mapbox's core styles, or make your own style with Mapbox Studio
        style: 'mapbox://styles/mapbox/streets-v11',
        center: [110.370529, -7.797068],
        zoom: 12
    });

    map.on('load', () => {
        map.loadImage(
            'https://docs.mapbox.com/mapbox-gl-js/assets/custom_marker.png',
            (error, image) => {
                if (error) throw error;
                map.addImage('custom-marker', image, {
                    sdf: true
                });
                // Add a GeoJSON source with 2 points
                map.addSource('places', {
                    // This GeoJSON contains features that include an "icon"
                    // property. The value of the "icon" property corresponds
                    // to an image in the Mapbox Streets style's sprite.
                    'type': 'geojson',
                    'data': {
                        'type': 'FeatureCollection',
                        'features': <?php echo $datas ?>
                    }
                });
                // Add a layer showing the places.
                map.addLayer({
                    'id': 'places',
                    'type': 'symbol',
                    'source': 'places',
                    'layout': {
                        'icon-image': 'custom-marker',
                        'icon-size': 0.25,
                        'icon-allow-overlap': true
                    },
                    "paint": {
                        "icon-color": [
                            'match', // Use the 'match' expression: https://docs.mapbox.com/mapbox-gl-js/style-spec/#expressions-match
                            ['get', 'status'], // Use the result 'STORE_TYPE' property
                            'Registrasi',
                            '#727cb6',
                            'Instalasi',
                            '#8753de',
                            'Setup',
                            '#49b6d6',
                            'Sistem Aktif',
                            '#32a932',
                            'Tidak Aktif',
                            '#000000',
                            'Trial',
                            '#ffd900',
                            'Sewa Khusus',
                            '#348fe2',
                            'Blokir',
                            '#ffd900',
                            'Ekslusif',
                            '#00acac',
                            'CSR',
                            '#00acac',
                            '#FF0000' // any other store type
                        ]


                    }
                });

                // When a click event occurs on a feature in the places layer, open a popup at the
                // location of the feature, with description HTML from its properties.
                map.on('click', 'places', (e) => {
                    // Copy coordinates array.
                    const coordinates = e.features[0].geometry.coordinates.slice();
                    const description = e.features[0].properties.description;

                    // Ensure that if the map is zoomed out such that multiple
                    // copies of the feature are visible, the popup appears
                    // over the copy being pointed to.
                    while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                        coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                    }

                    new mapboxgl.Popup()
                        .setLngLat(coordinates)
                        .setHTML(description)
                        .addTo(map);
                });

                // Change the cursor to a pointer when the mouse is over the places layer.
                map.on('mouseenter', 'places', () => {
                    map.getCanvas().style.cursor = 'pointer';
                });

                // Change it back to a pointer when it leaves.
                map.on('mouseleave', 'places', () => {
                    map.getCanvas().style.cursor = '';
                });

            })
    });
</script>
@endpush