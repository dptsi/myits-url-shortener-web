@extends('layouts.base')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div>
    <div class="row">
        <div class='col-md-10'>
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
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    $('#links_table').DataTable({
        processing: true,
        // serverSide: false, // Jika API tidak mendukung paginasi otomatis
        ajax: {
            url: "https://shortener.its.ac.id/links/datatable",
            dataType: "json",
            error: function(xhr, error, thrown) {
                console.log("Error:", xhr.responseText);
                alert("Terjadi kesalahan saat mengambil data!");
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'long_url', name: 'long_url' },
            { data: 'short_url', name: 'short_url' },
            { data: 'created_at', name: 'created_at' },
            { data: 'qr_code', name: 'qr_code', orderable: false, searchable: false }
        ]
    });
});
</script>

@endsection
