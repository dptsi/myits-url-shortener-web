
var original_link;

function select_text() {
    $('.result-box').focus().select();
}

$('.result-box').click(select_text);
$('.result-box').change(function () {
    $(this).val(original_link);
});


$('#generate-qr-code').click(function () {
    var container = $('.qr-code-container');
    var base_url = "https://its.id/";
    
    // Menghapus base_url dari original_link
    var short_link = original_link.replace(base_url, '');

    container.empty();
    $.LoadingOverlay("show");
    var url = "https://shortener.its.ac.id/generate-qrbase64/" + encodeURIComponent(short_link);
    $.ajax({
        url: url,
        method: 'GET',
        success: function (response) {
            // Asumsikan server mengembalikan string base64
            var base64Image = response;
            console.log('QR Code:', base64Image);

            // Buat elemen <img> dan atur atribut 'src' dengan data base64
            var imgElement = document.createElement('img');
            imgElement.src = 'data:image/png;base64,' + base64Image;
            imgElement.alt = "QR Code";

            container.append(imgElement);
            container.find('img').attr('alt', original_link);
            container.show();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error:', textStatus, errorThrown);
        }
        ,
        complete: function() {
            // Sembunyikan loading setelah permintaan selesai (berhasil atau gagal)
            $.LoadingOverlay("hide");
        }
    });

});

var clipboard = new Clipboard('#clipboard-copy');
clipboard.on('success', function (e) {
    e.clearSelection();
    $('#clipboard-copy').tooltip('show');
});

$('#clipboard-copy').on('blur', function () {
    $(this).tooltip('destroy');
}).on('mouseleave', function () {
    $(this).tooltip('destroy');
});

$(function () {
    original_link = $('.result-box').val();
    select_text();
});
