@extends('layouts.base')

@section('css')
<link rel="stylesheet" href="/css/datatables.min.css">
<style>
    .links-card {
        background: #ffffff;
        border: 1px solid #dde3ea;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        padding: 28px 28px 20px;
        margin-top: 8px;
    }

    .links-card h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1a2a3a;
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 2px solid #e8ecf0;
    }

    /* Kolom URL panjang — fixed width, truncate */
    #links_table td.col-long-url,
    #links_table th.col-long-url {
        max-width: 260px;
        min-width: 160px;
        width: 260px;
    }

    #links_table td.col-long-url {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 260px;
    }

    /* Kolom lain */
    #links_table td.col-short-url {
        white-space: nowrap;
    }

    #links_table td.col-actions {
        white-space: nowrap;
        width: 140px;
    }

    #links_table td.col-qr {
        text-align: center;
        width: 120px;
    }

    /* DataTable overrides agar menyatu dengan card */
    #links_table.dataTable thead th {
        background-color: #f4f7fa;
        color: #445566;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 2px solid #dde3ea;
    }

    #links_table.dataTable tbody tr:hover {
        background-color: #f0f5ff;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 4px;
        border: 1px solid #cdd5de;
        padding: 4px 8px;
    }

    div.dataTables_wrapper div.dataTables_length select {
        border-radius: 4px;
        border: 1px solid #cdd5de;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="links-card">
            <h3><i class="fa fa-link" aria-hidden="true"></i>&nbsp; My Links</h3>
            <div class="table-responsive">
                <table id="links_table" class="table table-bordered table-striped mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:50px">ID</th>
                            <th class="col-long-url">URL</th>
                            <th class="col-short-url">Shortened URL</th>
                            <th style="width:130px">Created At</th>
                            <th class="col-qr">QR Code</th>
                            <th class="col-actions">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Link</h4>
            </div>
            <div class="modal-body">
                <form id="editLinkForm">
                    <input type="hidden" id="edit_short_url">
                    <div class="form-group">
                        <label for="edit_long_url">Long URL</label>
                        <input type="url" id="edit_long_url" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
@include('snippets.modals')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src='/js/datatables.min.js'></script>

<script>
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
                alert("Terjadi kesalahan saat mengambil data!");
            }
        },
        columns: [
            { data: 'id',         name: 'id',         width: '50px' },
            { data: 'long_url',   name: 'long_url',   className: 'col-long-url',
              render: function(data) {
                  // Tampilkan teks terpotong, full URL ada di tooltip
                  var escaped = $('<div>').text(data).html();
                  return '<span title="' + escaped + '">' + escaped + '</span>';
              }
            },
            { data: 'short_url',  name: 'short_url',  className: 'col-short-url' },
            { data: 'created_at', name: 'created_at',  searchable: false, width: '130px' },
            { data: 'qr_code',    name: 'qr_code',    className: 'col-qr',      orderable: false, searchable: false },
            { data: 'edit',       name: 'edit',       className: 'col-actions', orderable: false, searchable: false }
        ]
    });

    // Klik tombol edit
    $(document).on('click', '.edit-link', function() {
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
                    table.ajax.reload();
                } else {
                    alert('Error updating link.');
                }
            },
            error: function(xhr) {
                alert('Failed to update link: ' + xhr.responseText);
            }
        });
    });

    // Tombol delete
    $(document).on('click', '.delete-link', function() {
        var shortUrl = $(this).data('short_url');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/links/delete/',
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
