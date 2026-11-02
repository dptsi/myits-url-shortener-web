@extends('layouts.base')

@section('page_title', 'Buat Tautan')

@section('content')
    <div class="card bd-0 mg-b-20">
        <div class="card-body">
            <h5 class="tx-figtree tx-medium mg-b-20">Buat Kustomisasi Tautan Anda</h5>

            <form id='shorten-form' method='POST' action='/shorten' role='form'>
                <div class="mg-t-20">
                    <label for="link-url">Tautan Asli</label>
                    <div class="pos-relative">
                        <input type='text' inputmode='url' id="link-url" autocomplete='off' class='form-control pd-r-45-f' placeholder='Contoh: its.ac.id/berita' name='link-url' />
                        <button type="button" id="clear-link-url" class="btn btn-icon btn-sm rounded-circle bd-0 bg-transparent pos-absolute d-none" style="top: 50%; right: 8px; transform: translateY(-50%);" aria-label="Hapus tautan asli" title="Hapus">
                            <span class="ms-rounded tx-20">close</span>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="link-url-feedback">URL tidak valid. Contoh: its.ac.id</div>
                </div>
                <div class="mg-t-20">
                    <label for="custom-ending">Kustomisasi Tautan</label>
                    <div class="row row-xs">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text tx-medium">{{ env('APP_PROTOCOL') }}{{ env('APP_ADDRESS') }}/@if (session('role_group') == 'Mahasiswa')m/@endif</span>
                                </div>
                                <input type='text' id="custom-ending" placeholder="Tulis akhiran tautan" autocomplete="off"
                                    class='form-control custom-url-field' name='custom-ending' />
                            </div>
                            <div id='link-availability-status' aria-live="polite">
                                <p class="tx-color-03 mg-b-0 mg-t-10">Buat tautan yang mudah diingat</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mg-t-20 mg-md-t-0">
                            <button type='button' class='btn btn-primary btn-block' id='shorten' data-toggle="modal" data-target="#modalSetujuiKetentuan" disabled>
                                Buat
                                <span class="ms-rounded tx-20">arrow_forward</span>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name='_token' value='{{ csrf_token() }}' />
            </form>
        </div>
    </div>

    <div class="card bd-0 d-none" id="shorten-result">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                <div class="flex-shrink-0 pd-10 d-none" id="short-url-qr-wrap">
                    <img id="short-url-qr" class="wd-150 ht-150" alt="" />
                </div>
                <div class="wd-100p" style="min-width: 0;">
                    <p class="tx-figtree tx-medium tx-center tx-sm-left mg-b-0">
                        <span class="ms-rounded ms-fill tx-20 tx-success align-middle">check_circle</span>
                        Tautan Berhasil Dibuat
                    </p>
                    <p class="tx-figtree tx-medium tx-20 tx-center tx-sm-left text-break mg-b-0" id="short-url-text"></p>
                    <div class="d-flex flex-column flex-sm-row gap-2 mg-t-20 flex-wrap">
                        <button type="button" id="copy-short-url" class="btn btn-sm btn-primary tx-figtree wd-100p wd-sm-auto">
                            <span class="ms-rounded tx-20 align-middle">content_copy</span>
                            <span class="copy-label">Salin Tautan</span>
                        </button>
                        <a id="download-short-url-qr" class="btn btn-sm tx-figtree wd-100p wd-sm-auto d-none">
                            <span class="ms-rounded tx-20 align-middle">download</span>
                            Unduh Kode QR
                        </a>
                        <a href="{{ url('links') }}" class="btn btn-sm tx-figtree wd-100p wd-sm-auto">
                            <span class="ms-rounded tx-20 align-middle">history</span>
                            Lihat Tautan Saya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <div class="modal fade effect-scale" id="modalSetujuiKetentuan" tabindex="-1" role="dialog" aria-labelledby="modalSetujuiKetentuanLabel" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content bd-0">
                <div class="modal-header bd-0 d-flex align-items-center pd-b-0">
                    <h5 class="tx-figtree tx-medium mg-b-0" id="modalSetujuiKetentuanLabel">
                        Konfirmasi Buat Tautan
                    </h5>
                    <button type="button" class="btn btn-icon btn-sm tx-figtree tx-medium rounded-circle d-flex align-items-center bd-0" data-dismiss="modal" aria-label="Tutup">
                        <span class="ms-rounded tx-22">close</span>
                    </button>
                </div>
                <div class="modal-body lh-7">
                    <div class="alert alert-warning rounded-20 d-flex justify-content-center align-items-center gap-2 bd-0" style="aspect-ratio: 3/1;">
                        <span class="ms-rounded ms-fill tx-warning tx-60 tx-md-70 tx-lg-80">add_link</span>
                        <span class="ms-rounded ms-fill tx-warning tx-60 tx-md-70 tx-lg-80">warning</span>
                        <span class="ms-rounded ms-fill tx-warning tx-60 tx-md-70 tx-lg-80">web</span>
                    </div>
                    <p>
                        <span class="tx-medium">Catatan Penting:</span> Pemendek tautan ini dilarang digunakan untuk mengarahkan ke konten yang melanggar hukum atau
                        tidak sesuai dengan norma dan etika, termasuk namun tidak terbatas pada:
                    </p>
                    <ul class="pd-l-20">
                        <li><span class="tx-medium">Konten negatif</span> yang memicu kebencian, diskriminasi, atau provokasi.</li>
                        <li><span class="tx-medium">Konten pornografi</span> atau materi tidak senonoh.</li>
                        <li><span class="tx-medium">Konten yang melanggar hak cipta</span> seperti situs atau materi pembajakan.</li>
                        <li><span class="tx-medium">Perjudian online</span> atau aktivitas yang berkaitan dengan taruhan ilegal.</li>
                        <li><span class="tx-medium">Penipuan</span> atau situs yang mengandung scam, phishing, dan aktivitas penipuan lainnya.</li>
                        <li><span class="tx-medium">Konten kekerasan</span> atau yang mengandung gambar, video, atau tulisan yang tidak pantas dan menyinggung.</li>
                        <li><span class="tx-medium">Konten berbahaya</span> seperti virus, malware, atau software berbahaya lainnya.</li>
                    </ul>
                    <p>Penggunaan layanan ini untuk tujuan-tujuan tersebut akan mengakibatkan pemblokiran link dan pengguna yang
                        terkait. <span class="tx-medium tx-danger">Pelanggaran serius akan dilaporkan kepada pihak yang berwajib</span> sesuai dengan peraturan
                        perundang-undangan yang berlaku.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn tx-figtree" data-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-primary tx-figtree" id="confirm-shorten">Ya, Saya Setuju</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src='/js/clipboard.min.js'></script>
    <script src='/js/index.js?v={{ filemtime(public_path('js/index.js')) }}'></script>
@endsection
