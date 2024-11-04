@extends('layouts.base')

@section('css')
    <link rel='stylesheet' href='css/index.css' />
@endsection

@section('content')
    <h3 class='title'><strong>Type your URL here</strong></h3>

    {{-- <form method='POST' action='/shorten' role='form'>
        <input type='url' autocomplete='off' class='form-control long-link-input' placeholder='http://' name='link-url' />
        <p>Customize link</p>
        <div class="form-inline">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">
                        <strong>{{ env('APP_ADDRESS') }}/@if (session('role_group') == 'Mahasiswa')
                                m/
                            @endif
                        </strong>
                    </div>
                    <input type='text' placeholder="your custom URL" autocomplete="off"
                        class='form-control custom-url-field' name='custom-ending' />
                </div>
            </div>
            <a href='#' class='btn btn-success check-btn' id='check-link-availability'>Check Availability</a>
        </div>
        <div>
            <div id='link-availability-status'></div>
        </div>

        <input type='submit' class='btn btn-primary' id='shorten' value='Shorten' />
        <input type="hidden" name='_token' value='{{ csrf_token() }}' />
    </form> --}}

    <form id='shorten-form' method='POST' action='/shorten' role='form'>
        <input type='url' autocomplete='off' class='form-control long-link-input' placeholder='https://' name='link-url' />
        <p>Customize link</p>
        <div class="form-inline">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">
                        <strong>{{ env('APP_ADDRESS') }}/@if (session('role_group') == 'Mahasiswa')
                                m/
                            @endif
                        </strong>
                    </div>
                    <input type='text' placeholder="your custom URL" autocomplete="off"
                        class='form-control custom-url-field' name='custom-ending' />
                </div>
            </div>
            <a href='#' class='btn btn-success check-btn' id='check-link-availability'>Check Availability</a>
        </div>
        <div>
            <div id='link-availability-status'></div>
        </div>

        <button type='button' class='btn btn-primary' id='shorten'>Shorten</button>
        <input type="hidden" name='_token' value='{{ csrf_token() }}' />
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('shorten').disabled = true;

        document.getElementById('check-link-availability').addEventListener('click', function() {
            // console.log("ok");
            setTimeout(function() {
                document.getElementById('shorten').disabled = false;
            }, 1000); // Simulate async check with 1 second delay
        });
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
                    var form = document.getElementById('shorten-form');
                    var formData = new FormData(form);
                    form.submit();
                }
            });
        });
    </script>



    {{-- <div class="container" style="text-align: left; margin-top: 30px;">
        <div class="alert alert-danger" role="alert">
            <h5><strong>Catatan Penting:</strong> pemendek tautan ini dilarang digunakan untuk mengarahkan ke konten yang
                melanggar hukum atau
                tidak sesuai dengan norma dan etika, termasuk namun tidak terbatas pada:</h5>
            <ul>
                <li><b>Konten negatif</b> yang memicu kebencian, diskriminasi, atau provokasi.</li>
                <li><b>Konten pornografi</b> atau materi tidak senonoh.</li>
                <li><b>Konten yang melanggar hak cipta</b> seperti situs atau materi pembajakan.</li>
                <li><b>Perjudian online</b> atau aktivitas yang berkaitan dengan taruhan ilegal.</li>
                <li><b>Penipuan</b> atau situs yang mengandung scam, phishing, dan aktivitas penipuan lainnya.</li>
                <li><b>Konten kekerasan</b> atau yang mengandung gambar, video, atau tulisan yang tidak pantas dan
                    menyinggung.
                </li>
                <li><b>Konten berbahaya</b> seperti virus, malware, atau software berbahaya lainnya.</li>
            </ul>
            <hr>
            <h5>Penggunaan layanan ini untuk tujuan-tujuan tersebut akan mengakibatkan pemblokiran link dan pengguna yang
                terkait. <b>Pelanggaran serius akan dilaporkan kepada pihak yang berwajib</b> sesuai dengan peraturan
                perundang-undangan yang berlaku.</h5>
        </div>
    </div> --}}
@endsection

@section('js')
    <script src='js/index.js'></script>
@endsection
