@extends('layouts.base')

@section('css')
<link rel='stylesheet' href='/css/shorten_result.css' />
@endsection

@section('content')
<h3><strong>QRCODE</strong></h3>
<div class="row mt-5 text-center">
    {!! $qrcode !!}
</div>
@endsection


