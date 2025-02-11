@extends('layouts.base')

@section('css')
<!-- <link rel='stylesheet' href='/css/admin.css'> -->
<!-- <link rel='stylesheet' href='/css/datatables.min.css'> -->
@endsection

@section('content')
<div ng-controller="AdminCtrl" class="ng-root">
    <div class="row row-no-gutters">
        <div class='col-md-2'>
            <ul class='nav nav-pills nav-stacked admin-nav' role='tablist'>
                <li role='presentation' aria-controls="home" class='admin-nav-item active'>
                    <a href='#home'><span class="glyphicon glyphicon-home" aria-hidden="true"></span>Home</a>
                </li>
                <li role='presentation' aria-controls="links" class='admin-nav-item'>
                    <a href='#links'><span class="glyphicon glyphicon-link" aria-hidden="true"></span>Links</a>
                </li>
            </ul>
        </div>
        <div class='col-md-10'>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="home">
                    <div class="page-header">
                        <h1>Welcome {{ session('username') }}!</h1>
                    </div>
                    <p>Use the links on the left hand side to navigate.</p>
                </div>

                <div role="tabpanel" class="tab-pane" id="links">
                    <h3>Links</h3>
                    <table id="links_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>URL</th>
                                <th>Shortened URL</th>
                                <th>Created At</th>
                                <th>Actions</th>
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
{{-- Include extra JS --}}
<script src='/js/datatables.min.js'></script>
<script src='/js/api.js'></script>
<script src='/js/AdminCtrl.js'></script>
<script>
$(document).ready(function() {
    $('#links_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('links/datatable') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'url', name: 'url' },
            { data: 'shortened_url', name: 'shortened_url' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>
@endsection
