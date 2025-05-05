@extends('layouts.base')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> -->
@endsection

@section('content')
<div>
    <div class="row">
        <div class="col-md-10">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="links">
                    <h3>Links</h3>
                    <table id="links_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>URL</th>
                                <th>Shortened URL</th>
                                <th>Created At</th>
                                <th>QR Code</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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
{{-- Include extra JS --}}
<script src='/js/datatables.min.js'></script>

<script>
$(document).ready(function() {
    var table = $('#links_table').DataTable({
        processing: true,
        ajax: {
            url: "/links/datatable",
            dataType: "json",
            error: function(xhr) {
                console.log("Error:", xhr.responseText);
                alert("Terjadi kesalahan saat mengambil data!");
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'long_url', name: 'long_url' },
            { data: 'short_url', name: 'short_url' },
            { data: 'created_at', name: 'created_at' },
            { data: 'qr_code', name: 'qr_code', orderable: false, searchable: false },
            { data: 'edit', name: 'edit', orderable: false, searchable: false }
        ]
    });

    // Klik tombol edit
    $(document).on('click', '.edit-link', function() {
        var shortUrl = $(this).data('short');
        var longUrl = $(this).data('long');

        $('#edit_short_url').val(shortUrl);
        $('#edit_long_url').val(longUrl);

        $('#editModal').modal('show'); // Bootstrap 3
    });

    // Form submit
    $('#editLinkForm').submit(function(e) {
        e.preventDefault();

        var shortUrl = $('#edit_short_url').val();
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
                    table.ajax.reload(); // Reload DataTable tanpa refresh halaman
                } else {
                    alert('Error updating link.');
                }
            },
            error: function(xhr) {
                alert('Failed to update link: ' + xhr.responseText);
            }
        });
    });

    $(document).on('click', '.delete-link', function () {
    let shortUrl = $(this).data('short_url');
    console.log(shortUrl);
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/links/delete/',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    link_ending: shortUrl,
                },
                success: function(response) {
                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                    $('#links_table').DataTable().ajax.reload(); // reload datatable
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
