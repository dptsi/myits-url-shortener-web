@extends('layouts.base')

@section('page_title', 'Statistik Tautan')

@section('css')
<link rel='stylesheet' href='/css/jquery-jvectormap.css'>
<link rel='stylesheet' href='/css/bootstrap-datetimepicker.min.css'>
@endsection

@section('content')
<div ng-controller="StatsCtrl" class="ng-root">
    <div class="card bd-0 mg-b-20">
        <div class="card-body">
            <div class="row">
                <div class="col-md-7 mg-b-15 mg-md-b-0">
                    <p class="mg-b-5 tx-color-03">Tautan pendek</p>
                    <p class="tx-medium">
                        <a target="_blank" href="{{ env('APP_PROTOCOL') }}/{{ env('APP_ADDRESS') }}/{{ $link->short_url }}">
                            {{ env('APP_ADDRESS') }}/{{ $link->short_url }}
                        </a>
                    </p>
                    <p class="mg-b-5 tx-color-03">URL tujuan</p>
                    <p class="tx-medium mg-b-0">
                        <a target="_blank" href="{{ $link->long_url }}">{{ Str::limit($link->long_url, 50) }}</a>
                    </p>
                </div>
                <div class="col-md-5">
                    <form action="" method="GET">
                        <div class="form-group">
                            <div class='input-group date' id='left-bound-picker'>
                                <input type="text" class="form-control" name="left_bound" placeholder="Dari">
                                <div class="input-group-append">
                                    <span class="input-group-text"><span class="ms-rounded tx-18">calendar_month</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class='input-group date' id='right-bound-picker'>
                                <input type="text" class="form-control" name="right_bound" placeholder="Sampai">
                                <div class="input-group-append">
                                    <span class="input-group-text"><span class="ms-rounded tx-18">calendar_month</span></span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Perbarui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-md-8 mg-b-20">
            <div class="card bd-0 ht-100p">
                <div class="card-body">
                    <h6 class="tx-figtree tx-medium">Kunjungan dari waktu ke waktu <span class="tx-color-03">(total: {{ $link->clicks }})</span></h6>
                    <canvas id="dayChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mg-b-20">
            <div class="card bd-0 ht-100p">
                <div class="card-body">
                    <h6 class="tx-figtree tx-medium">Sumber kunjungan</h6>
                    <canvas id="refererChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mg-b-20">
            <div class="card bd-0 ht-100p">
                <div class="card-body">
                    <h6 class="tx-figtree tx-medium">Peta</h6>
                    <div id="mapChart"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mg-b-20">
            <div class="card bd-0 ht-100p">
                <div class="card-body">
                    <h6 class="tx-figtree tx-medium">Referer</h6>
                    <table class="table table-hover" id="refererTable">
                        <thead>
                            <tr>
                                <th>Host</th>
                                <th>Klik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referer_stats as $referer)
                                <tr>
                                    <td>{{ $referer->label }}</td>
                                    <td>{{ $referer->clicks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
{{-- Load data --}}
<script>
// Load data
var dayData = JSON.parse('{!! json_encode($day_stats) !!}');
var refererData = JSON.parse('{!! json_encode($referer_stats) !!}');
var countryData = JSON.parse('{!! json_encode($country_stats) !!}');

// Load datepicker dates
var datePickerLeftBound = '{{ $left_bound }}';
var datePickerRightBound = '{{ $right_bound }}';
</script>

{{-- Include extra JS --}}
<script src='/js/lodash.min.js'></script>
<script src='/js/chart.bundle.min.js'></script>
<script src='/mids/lib/datatables.net/js/jquery.dataTables.min.js'></script>
<script src='/js/datatables-mids.js'></script>
<script src='/js/jquery-jvectormap.min.js'></script>
<script src='/js/jquery-jvectormap-world-mill.js'></script>
<script src='/js/moment.min.js'></script>
<script src='/js/bootstrap-datetimepicker.min.js'></script>
<script src='/js/StatsCtrl.js'></script>
@endsection
