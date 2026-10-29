@extends('layouts.base')

@section('page_title', 'QR Code')

@section('content')
<div class="card bd-0">
    <div class="card-body tx-center">
        {!! $qrcode !!}
    </div>
</div>
@endsection
