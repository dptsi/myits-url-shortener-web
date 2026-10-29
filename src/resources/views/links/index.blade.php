@extends('layouts.base')

@section('page_title', 'Tautan Saya')

@section('page_actions')
<a href="{{ route('index') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
    <span class="ms-rounded tx-18">add</span> Buat Tautan
</a>
@endsection

@section('content')
<div class="card bd-0">
    <div class="card-body">
        <table id="links_table" class="table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th class="col-long-url">URL Tujuan</th>
                    <th class="col-short-url">Tautan Pendek</th>
                    <th>Dibuat</th>
                    <th class="col-qr">QR Code</th>
                    <th class="col-actions">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@include('snippets.edit_link_modal')
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/swal-mids.js"></script>
<script src='/mids/lib/datatables.net/js/jquery.dataTables.min.js'></script>
<script src='/js/datatables-mids.js'></script>
<script src='/js/clipboard.min.js'></script>

<script>
@php
    $shortBase = rtrim(env('APP_PROTOCOL') . env('APP_ADDRESS'), '/');
    if (!preg_match('#^https?://#', $shortBase)) {
        $shortBase = 'https://' . $shortBase;
    }
@endphp
var SHORT_BASE = @json($shortBase);

$(document).ready(function() {
    var table = $('#links_table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: "/links/datatable",
            dataType: "json",
            error: function(xhr) {
                console.log("Error:", xhr.responseText);
                toastr.error("Terjadi kesalahan saat mengambil data.", "Error");
            }
        },
        columns: [
            { data: 'id',         name: 'id' },
            { data: 'long_url',   name: 'long_url',   className: 'col-long-url',
              render: function(data) {
                  // Tampilkan teks terpotong, full URL ada di tooltip
                  var escaped = $('<div>').text(data).html();
                  return '<span title="' + escaped + '">' + escaped + '</span>';
              }
            },
            { data: 'short_url',  name: 'short_url',  className: 'col-short-url',
              render: function(data, type) {
                  if (type !== 'display') return data;
                  // Tautan bisa diklik, label tanpa protokol: its.id/akhiran
                  var href  = $('<div>').text(SHORT_BASE + '/' + data).html();
                  var label = $('<div>').text(SHORT_BASE.replace(/^https?:\/\//, '') + '/' + data).html();
                  return '<div class="d-inline-flex align-items-center gap-1">'
                       + '<a href="' + href + '" target="_blank" rel="noopener" class="tx-medium">' + label + '</a>'
                       + '<button type="button" class="btn btn-icon btn-sm bd-0 d-inline-flex align-items-center copy-short-url" data-clipboard-text="' + href + '" title="Salin tautan">'
                       + '<span class="ms-rounded tx-18">content_copy</span></button>'
                       + '</div>';
              }
            },
            { data: 'created_at', name: 'created_at',  searchable: false },
            { data: 'qr_code',    name: 'qr_code',    className: 'col-qr',      orderable: false, searchable: false },
            { data: 'edit',       name: 'edit',       className: 'col-actions', orderable: false, searchable: false }
        ]
    });

    // Tombol salin di kolom tautan pendek
    var clipboard = new Clipboard('.copy-short-url');
    clipboard.on('success', function(e) {
        e.clearSelection();
        toastr.success('Tautan disalin.', 'Berhasil');
    });

    // Klik tombol edit
    $(document).on('click', '.edit-link', function(e) {
        e.preventDefault();
        var shortUrl = $(this).data('short');
        var longUrl  = $(this).data('long');
        $('#edit_short_url').val(shortUrl);
        $('#edit_long_url').val(longUrl);
        $('#editModal').modal('show');
    });

    // Form submit edit
    $('#editLinkForm').submit(function(e) {
        e.preventDefault();
        var shortUrl   = $('#edit_short_url').val();
        var newLongUrl = $('#edit_long_url').val();

        $.ajax({
            url: '/links/edit_url',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                link_ending: shortUrl,
                new_long_url: newLongUrl
            },
            success: function(response) {
                if (response === "OK") {
                    $('#editModal').modal('hide');
                    toastr.success('Tautan berhasil diperbarui.', 'Berhasil');
                    table.ajax.reload(null, false);
                } else {
                    toastr.error('Gagal memperbarui tautan.', 'Error');
                }
            },
            error: function(xhr) {
                toastr.error('Gagal memperbarui tautan: ' + xhr.responseText, 'Error');
            }
        });
    });

    // Tombol delete
    $(document).on('click', '.delete-link', function(e) {
        e.preventDefault();
        var shortUrl = $(this).data('short_url');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,

            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/links/delete',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        link_ending: shortUrl
                    },
                    success: function() {
                        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                        table.ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
