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

    $('#link-url').on('input', function() {
        var value = $(this).val();
        $(this).toggleClass('is-invalid', value !== '' && !isValidUrl(value));
        updateShortenButton();
    });

    $('#link-url').on('blur', function() {
        if (isValidUrl($(this).val())) {
            $(this).val(normalizeUrl($(this).val()));
        }
    });

    // Ketersediaan akhiran dicek otomatis setelah user berhenti mengetik
    var checkTimer = null;
    var checkSeq = 0;

    function setAvailabilityStatus(cls, icon, text) {
        $('#link-availability-status').html('<span class="' + cls + ' d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">' + icon + '</span> ' + text + '</span>');
    }

    function checkAvailability() {
        var custom_link = $.trim($('.custom-url-field').val());
        // Respons dari pengecekan lama diabaikan
        var seq = ++checkSeq;
        if (custom_link === '') {
            $('#link-availability-status').empty();
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
                setAvailabilityStatus('tx-danger', 'block', 'Akhiran sudah dipakai');
            } else if (msg == 'available') {
                setAvailabilityStatus('tx-success', 'check_circle', 'Akhiran tersedia');
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

    $('.custom-url-field').on('input change', function() {
        endingAvailable = false;
        updateShortenButton();
        clearTimeout(checkTimer);
        checkTimer = setTimeout(checkAvailability, 400);
    });

    // Hasil pemendekan ditampilkan di bawah form tanpa pindah halaman
    window.submitShorten = function() {
        var $button = $('#shorten');
        $button.prop('disabled', true);
        $('#link-url').val(normalizeUrl($('#link-url').val()));
        $.ajax({
            url: $('#shorten-form').attr('action'),
            type: 'POST',
            data: $('#shorten-form').serialize(),
            dataType: 'json'
        }).done(function(res) {
            original_link = res.short_url;
            $('#short_url').val(res.short_url);
            $('.qr-code-container').empty().hide();
            $('#shorten-result').show();
            $('#shorten-result')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            select_text();
            // Akhiran sudah terpakai oleh tautan ini
            endingAvailable = false;
            checkSeq++;
            $('#link-availability-status').empty();
        }).fail(function(jqXHR) {
            var res = jqXHR.responseJSON || {};
            var message = res.message || 'Terjadi kesalahan, coba lagi';
            if (res.errors) {
                message = Object.values(res.errors)[0][0];
            }
            toastr['error'](message, 'Error');
        }).always(updateShortenButton);
    };

    $('#shorten-another').click(function() {
        $('#shorten-form')[0].reset();
        $('#link-url').removeClass('is-invalid');
        clearTimeout(checkTimer);
        checkSeq++;
        $('#link-availability-status').empty();
        endingAvailable = false;
        updateShortenButton();
        $('#shorten-result').hide();
        $('#link-url').focus();
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
