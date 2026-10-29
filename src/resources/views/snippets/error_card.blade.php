<div class="card bd-0">
    <div class="card-body pd-y-40 d-flex flex-column align-items-center tx-center">
        <span class="ms-rounded ms-fill tx-60 {{ $color ?? 'tx-primary' }} mg-b-10">{{ $icon }}</span>
        <h1 class="tx-figtree tx-semibold mg-b-5">{{ $code }}</h1>
        <p class="tx-color-03 mg-b-25">{{ $message }}</p>
        <a href="{{ route('index') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <span class="ms-rounded tx-18">arrow_back</span> Kembali ke beranda
        </a>
    </div>
</div>
