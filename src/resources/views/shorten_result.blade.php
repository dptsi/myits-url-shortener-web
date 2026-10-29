@extends('layouts.base')

@section('page_title', 'Tautan Berhasil Dibuat')

@section('content')
<div class="card bd-0">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mg-b-20">
            <span class="ms-rounded ms-fill tx-success tx-24">check_circle</span>
            <h5 class="tx-figtree tx-medium mg-b-0">Tautan pendek Anda siap dipakai</h5>
        </div>

        <label for="short_url" class="tx-medium">Tautan pendek</label>
        <div class="input-group mg-b-20">
            <input type='text' class='result-box form-control tx-medium' value='{{$short_url}}' id='short_url' readonly />
            <div class="input-group-append">
                <button type="button" class='btn btn-outline-primary d-inline-flex align-items-center gap-1' id='clipboard-copy' data-clipboard-target='#short_url' data-toggle='tooltip' data-placement='bottom' data-trigger='manual' title='Tautan disalin!'>
                    <span class="ms-rounded tx-18">content_copy</span> Salin
                </button>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" id="generate-qr-code" class='btn btn-primary d-inline-flex align-items-center gap-1'>
                <span class="ms-rounded tx-18">qr_code_2</span> Tampilkan QR Code
            </button>
            <a href='{{route('index')}}' class='btn btn-outline-secondary d-inline-flex align-items-center gap-1'>
                <span class="ms-rounded tx-18">add_link</span> Buat tautan lain
            </a>
            <a href='{{ url('links') }}' class='btn btn-outline-secondary d-inline-flex align-items-center gap-1'>
                <span class="ms-rounded tx-18">link</span> Tautan saya
            </a>
        </div>

        <div class="qr-code-container tx-center mg-t-25" style="display: none;">
        </div>
    </div>
</div>
@endsection


@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-loading-overlay/2.1.7/loadingoverlay.min.js"></script>
<script src='/js/qrcode.min.js'></script>
<script src='/js/clipboard.min.js'></script>
<script src='/js/shorten_result.js'></script>
@endsection
