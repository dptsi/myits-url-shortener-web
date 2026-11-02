$(function() {

    // Pendekkan hanya bisa diklik jika URL valid dan akhiran tersedia
    var endingAvailable = false;

    // User boleh mengetik tanpa protokol; https:// ditambahkan otomatis
    function normalizeUrl(value) {
        value = $.trim(value);
        if (value !== '' && !/^[a-z][a-z0-9+.-]*:\/\//i.test(value)) {
            value = 'https://' + value.replace(/^\/+/, '');
        }
        return value;
    }

    function isValidUrl(value) {
        var url;
        try {
            url = new URL(normalizeUrl(value));
        } catch (e) {
            return false;
        }
        return (url.protocol === 'http:' || url.protocol === 'https:')
            && /^[^.]+(\.[^.]+)+$/.test(url.hostname);
    }

    function updateShortenButton() {
        $('#shorten').prop('disabled', !(endingAvailable && isValidUrl($('#link-url').val())));
    }

    // Pesan galat berada di luar pembungkus input, jadi ditampilkan lewat kelas
    function updateLinkUrlField() {
        var value = $('#link-url').val();
        var invalid = value !== '' && !isValidUrl(value);
        $('#link-url').toggleClass('is-invalid', invalid);
        $('#link-url-feedback').toggleClass('d-block', invalid);
        $('#clear-link-url').toggleClass('d-none', value === '');
    }

    $('#link-url').on('input', function() {
        updateLinkUrlField();
        updateShortenButton();
    });

    $('#clear-link-url').click(function() {
        $('#link-url').val('').trigger('input').focus();
    });

    $('#link-url').on('blur', function() {
        if (isValidUrl($(this).val())) {
            $(this).val(normalizeUrl($(this).val()));
        }
    });

    // Ketersediaan akhiran dicek otomatis setelah user berhenti mengetik
    var checkTimer = null;
    var checkSeq = 0;

    function setAvailabilityStatus(iconClass, icon, text) {
        $('#link-availability-status').html(
            '<p class="tx-color-01 mg-b-0 mg-t-10"><span class="ms-rounded ms-fill tx-18 ' + iconClass + ' align-middle" translate="no">' + icon + '</span> ' + text + '</p>'
        );
    }

    function resetAvailabilityStatus() {
        $('#link-availability-status').html('<p class="tx-color-03 mg-b-0 mg-t-10">Buat tautan yang mudah diingat</p>');
    }

    function checkAvailability() {
        var custom_link = $.trim($('.custom-url-field').val());
        // Respons dari pengecekan lama diabaikan
        var seq = ++checkSeq;
        if (custom_link === '') {
            resetAvailabilityStatus();
            return;
        }
        setAvailabilityStatus('tx-color-03', 'progress_activity', 'Memeriksa...');
        $.ajax({
            url: "/api/v2/link_avail_check",
            type: "POST",
            data: {
                link_ending: custom_link
            },
            dataType: "html"
        }).done(function(msg) {
            if (seq !== checkSeq) return;
            if (msg == 'unavailable') {
                setAvailabilityStatus('tx-danger', 'cancel', 'Tautan telah terpakai. Coba alternatif lain.');
            } else if (msg == 'available') {
                setAvailabilityStatus('tx-success', 'check_circle', 'Tautan tersedia. Silakan tekan tombol Buat.');
                endingAvailable = true;
                updateShortenButton();
            } else if (msg == 'invalid') {
                setAvailabilityStatus('tx-warning', 'warning', 'Akhiran tidak valid (gunakan huruf, angka, - atau _)');
            } else {
                setAvailabilityStatus('tx-danger', 'error', 'Terjadi kesalahan, coba lagi');
            }
        }).fail(function() {
            if (seq !== checkSeq) return;
            setAvailabilityStatus('tx-danger', 'error', 'Terjadi kesalahan, coba lagi');
        });
    }

    $('.custom-url-field').on('input', function() {
        endingAvailable = false;
        updateShortenButton();
        clearTimeout(checkTimer);
        checkTimer = setTimeout(checkAvailability, 400);
    });

    var qrSeq = 0;

    function showQrCode(shortUrl) {
        var slug = new URL(shortUrl).pathname.replace(/^\//, '');
        var seq = ++qrSeq;
        $('#short-url-qr-wrap, #download-short-url-qr').addClass('d-none');
        // QR tidak tersedia: tautan tetap bisa dipakai, bagian QR saja yang disembunyikan
        $.get('/generate-qrbase64/' + encodeURIComponent(slug)).done(function(base64) {
            if (seq !== qrSeq || !base64) return;
            var src = 'data:image/png;base64,' + base64;
            $('#short-url-qr').attr({ src: src, alt: 'Kode QR ' + shortUrl });
            $('#download-short-url-qr').attr({ href: src, download: 'qr-' + slug.replace(/\//g, '-') + '.png' });
            $('#short-url-qr-wrap, #download-short-url-qr').removeClass('d-none');
        });
    }

    // Hasil ditampilkan di bawah form, dan form langsung siap untuk tautan berikutnya
    function submitShorten() {
        var $button = $('#shorten');
        $button.prop('disabled', true);
        $('#link-url').val(normalizeUrl($('#link-url').val()));
        $.ajax({
            url: $('#shorten-form').attr('action'),
            type: 'POST',
            data: $('#shorten-form').serialize(),
            dataType: 'json'
        }).done(function(res) {
            $('#short-url-text').text(res.short_url);
            $('#copy-short-url').attr('data-clipboard-text', res.short_url);
            showQrCode(res.short_url);
            $('#shorten-result').removeClass('d-none');
            $('#shorten-result')[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            $('#shorten-form')[0].reset();
            updateLinkUrlField();
            clearTimeout(checkTimer);
            checkSeq++;
            resetAvailabilityStatus();
            endingAvailable = false;
        }).fail(function(jqXHR) {
            var res = jqXHR.responseJSON || {};
            var message = res.message || 'Terjadi kesalahan, coba lagi';
            if (res.errors) {
                message = Object.values(res.errors)[0][0];
            }
            toastr['error'](message, 'Error');
        }).always(updateShortenButton);
    }

    $('#confirm-shorten').click(function() {
        $('#modalSetujuiKetentuan').modal('hide');
        submitShorten();
    });

    var copyTimer = null;
    var clipboard = new Clipboard('#copy-short-url');
    clipboard.on('success', function(e) {
        e.clearSelection();
        var $button = $('#copy-short-url');
        $button.find('.ms-rounded').text('check');
        $button.find('.copy-label').text('Tersalin');
        clearTimeout(copyTimer);
        copyTimer = setTimeout(function() {
            $button.find('.ms-rounded').text('content_copy');
            $button.find('.copy-label').text('Salin Tautan');
        }, 1500);
    });
    clipboard.on('error', function() {
        toastr['error']('Tautan gagal disalin, salin secara manual', 'Error');
    });

    min = 1;
    max = 2;
    var i = Math.floor(Math.random() * (max - min + 1)) + min;
    changeTips(i);
    var tipstimer = setInterval(function() {
        changeTips(i);
        i++;
    }, 8000);

    function setTip(tip) {
        $("#tips").html(tip);
    }

    function changeTips(tcase) {
        switch (tcase) {
            case 1:
                setTip('Create an account to keep track of your links');
                break;
            case 2:
                setTip('Did you know you can change the URL ending by clicking on "Link Options"?');
                i = 1;
                break;
        }
    }
});

$(function() {
    // Setup drop down menu
    $('.dropdown-toggle').dropdown();

    // Fix input element click problem
    $('.dropdown input, .dropdown label').click(function(e) {
        e.stopPropagation();
    });
    $('.btn-toggle').click(function() {
        $(this).find('.btn').toggleClass('active');

        if ($(this).find('.btn-primary').length > 0) {
            $(this).find('.btn').toggleClass('btn-primary');
        }
        if ($(this).find('.btn-danger').length > 0) {
            $(this).find('.btn').toggleClass('btn-danger');
        }
        if ($(this).find('.btn-success').length > 0) {
            $(this).find('.btn').toggleClass('btn-success');
        }
        if ($(this).find('.btn-info').length > 0) {
            $(this).find('.btn').toggleClass('btn-info');
        }

        $(this).find('.btn').toggleClass('btn-default');

    });
});
