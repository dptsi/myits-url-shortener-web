$(function() {

    // Pendekkan hanya bisa diklik jika URL valid dan akhiran sudah dicek tersedia
    var endingAvailable = false;

    function isValidUrl(value) {
        var url;
        try {
            url = new URL($.trim(value));
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

    // Akhiran yang diubah harus dicek ulang sebelum bisa dipendekkan
    $('.custom-url-field').on('input', function() {
        endingAvailable = false;
        updateShortenButton();
        $('#link-availability-status').empty();
    });

    $('#check-link-availability').click(function() {
        var custom_link = $('.custom-url-field').val();
        endingAvailable = false;
        updateShortenButton();
        var request = $.ajax({
            url: "/api/v2/link_avail_check",
            type: "POST",
            data: {
                link_ending: custom_link
            },
            dataType: "html"
        });
        $('#link-availability-status').html('<span class="tx-color-03 d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">progress_activity</span> Memeriksa...</span>');
        request.done(function(msg) {
            if (msg == 'unavailable') {
                $('#link-availability-status').html('<span class="tx-danger d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">block</span> Akhiran sudah dipakai</span>');
            } else if (msg == 'available') {
                $('#link-availability-status').html('<span class="tx-success d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">check_circle</span> Akhiran tersedia</span>');
                endingAvailable = true;
                updateShortenButton();
            } else if (msg == 'invalid') {
                $('#link-availability-status').html('<span class="tx-warning d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">warning</span> Akhiran tidak valid (gunakan huruf, angka, - atau _)</span>');
            } else {
                $('#link-availability-status').html('<span class="tx-danger d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">error</span> Terjadi kesalahan, coba lagi</span>');
            }
        });

        request.fail(function(jqXHR, textStatus) {
            $('#link-availability-status').html('<span class="tx-danger d-inline-flex align-items-center gap-1"><span class="ms-rounded tx-16">error</span> Terjadi kesalahan, coba lagi</span>');
        });
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
