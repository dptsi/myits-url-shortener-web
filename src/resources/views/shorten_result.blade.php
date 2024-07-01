@extends('layouts.base')

@section('css')
<link rel='stylesheet' href='/css/shorten_result.css' />
@endsection

@section('content')
<h3><strong>Shortened URL</strong></h3>
<div class="input-group">
    <input type='text' class='result-box form-control' value='{{$short_url}}' id='short_url' />
    <div class='input-group-addon' id='clipboard-copy' data-clipboard-target='#short_url' data-toggle='tooltip' data-placement='bottom' data-title='Link Copied!'>
        <i class='fa fa-clipboard' aria-hidden='true' title='Copy to clipboard'></i>
    </div>
</div>
<a id="generate-qr-code" class='btn btn-primary'>Generate QR Code</a>
<a href='{{route('index')}}' class='btn btn-info'>Shorten another</a>

<div class="qr-code-container">
</div>

@endsection


@section('js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-loading-overlay/2.1.7/loadingoverlay.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-loading-overlay/2.1.7/loadingoverlay.min.js"></script>
<script src='/js/qrcode.min.js'></script>
<script src='/js/clipboard.min.js'></script>
<script src='/js/shorten_result.js'></script>

@endsection