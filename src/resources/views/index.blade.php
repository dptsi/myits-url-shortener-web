@extends('layouts.base')

@section('page_title', 'Buat Tautan')

@section('content')
    <div class="card bd-0">
        <div class="card-body pd-lg-30">
            <h4 class="tx-figtree tx-bold mg-b-15">Masukkan URL Anda di sini</h4>

            <form id='shorten-form' method='POST' action='/shorten' role='form'>
                <div class="long-link-input">
                    <input type='url' id="link-url" autocomplete='off' class='form-control form-control-lg' placeholder='https://' name='link-url' />
                    <div class="invalid-feedback">URL tidak valid. Gunakan alamat lengkap, contoh: https://www.its.ac.id</div>
                </div>

                <p class="mg-b-10">Kustomisasi tautan</p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="input-group custom-url-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text tx-bold">{{ env('APP_ADDRESS') }}/@if (session('role_group') == 'Mahasiswa')m/@endif</span>
                        </div>
                        <input type='text' id="custom-ending" placeholder="akhiran tautan Anda" autocomplete="off"
                            class='form-control custom-url-field' name='custom-ending' />
                    </div>
                    <button type="button" class='btn btn-success tx-bold' id='check-link-availability'>Cek Ketersediaan</button>
                </div>
                <div id='link-availability-status' class="tx-13 mg-t-10 mg-b-20" style="min-height: 20px;"></div>

                <button type='button' class='btn btn-primary tx-bold' id='shorten'>Pendekkan</button>
                <input type="hidden" name='_token' value='{{ csrf_token() }}' />
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/swal-mids.js"></script>
    <script src='/js/index.js'></script>
    <script>
        // Tombol Pendekkan diaktifkan oleh index.js hanya jika akhiran tersedia
        document.getElementById('shorten').disabled = true;
        document.getElementById('shorten').addEventListener('click', function() {
            Swal.fire({
                width: window.innerWidth <= 768 ? '100%' : '50%',
                title: 'Peringatan',
                html: `
            <h5 style="text-align: justify;"><strong>Catatan Penting:</strong> pemendek tautan ini dilarang digunakan untuk mengarahkan ke konten yang melanggar hukum atau
                tidak sesuai dengan norma dan etika, termasuk namun tidak terbatas pada:</h5>
            <ul style="text-align: justify;">
                <li><b>Konten negatif</b> yang memicu kebencian, diskriminasi, atau provokasi.</li>
                <li><b>Konten pornografi</b> atau materi tidak senonoh.</li>
                <li><b>Konten yang melanggar hak cipta</b> seperti situs atau materi pembajakan.</li>
                <li><b>Perjudian online</b> atau aktivitas yang berkaitan dengan taruhan ilegal.</li>
                <li><b>Penipuan</b> atau situs yang mengandung scam, phishing, dan aktivitas penipuan lainnya.</li>
                <li><b>Konten kekerasan</b> atau yang mengandung gambar, video, atau tulisan yang tidak pantas dan menyinggung.</li>
                <li><b>Konten berbahaya</b> seperti virus, malware, atau software berbahaya lainnya.</li>
            </ul>
            <hr style="text-align: justify;">
            <h5 style="text-align: justify;">Penggunaan layanan ini untuk tujuan-tujuan tersebut akan mengakibatkan pemblokiran link dan pengguna yang
                terkait. <b>Pelanggaran serius akan dilaporkan kepada pihak yang berwajib</b> sesuai dengan peraturan
                perundang-undangan yang berlaku.</h5>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, saya setuju',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('shorten-form').submit();
                }
            });
        });
    </script>
@endsection
