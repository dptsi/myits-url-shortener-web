@extends('layouts.base')

@section('css')
<link rel='stylesheet' href='/css/admin.css'>
<link rel='stylesheet' href='/css/datatables.min.css'>
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
<!-- 
                <li role='presentation' aria-controls="links" class='admin-nav-item'>
                    <a href='{{ url('links') }}'><span class="glyphicon glyphicon-link" aria-hidden="true"></span>Links</a>
                </li> -->

                @if ($role == $admin_role)
                <li role='presentation' class='admin-nav-item'>
                    <a href='#admin'><span class="glyphicon glyphicon-user" aria-hidden="true"></span>Admin</a>
                </li>
                @endif

                @if ($api_active == 1)
                <li role='presentation' class='admin-nav-item'>
                    <a href='#developer'><span class="glyphicon glyphicon-console" aria-hidden="true"></span>Developer</a>
                </li>
                @endif
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
                    @include('snippets.link_table', [
                        'table_id' => 'user_links_table'
                    ])
                </div>


                @if ($role == $admin_role)
                <div role="tabpanel" class="tab-pane" id="admin">
                    <h3>Links</h3>
                    @include('snippets.link_table', [
                        'table_id' => 'admin_links_table'
                    ])

                    <h3>Users</h3>
                    @include('snippets.user_table', [
                        'table_id' => 'admin_users_table'
                    ])

                </div>
                @endif

                @if ($api_active == 1)
                <div role="tabpanel" class="tab-pane" id="developer">
                    <h3>Developer</h3>

                    <p>API keys and documentation for developers.</p>
                    <p>
                        Documentation:
                        <a href='http://docs.polr.me/en/latest/developer-guide/api/'>http://docs.polr.me/en/latest/developer-guide/api/</a>
                    </p>

                    <h4>API Key: </h4>
                    <div class='row'>
                        <div class='col-md-8'>
                            <input class='form-control status-display' disabled type='text' value='{{$api_key}}'>
                        </div>
                        <div class='col-md-4'>
                            <a href='#' ng-click="generateNewAPIKey($event, '{{$user_id}}', true)" id='api-reset-key' class='btn btn-danger'>Reset</a>
                        </div>
                    </div>


                    <h4>API Quota: </h4>
                    <h2 class='api-quota'>
                        @if ($api_quota == -1)
                            unlimited
                        @else
                            <code>{{$api_quota}}</code>
                        @endif
                    </h2>
                    <span> requests per minute</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="angular-modals">
        <edit-long-link-modal ng-repeat="modal in modals.editLongLink" link-ending="modal.linkEnding"
            old-long-link="modal.oldLongLink" clean-modals="cleanModals"></edit-long-link-modal>
        <edit-user-api-info-modal ng-repeat="modal in modals.editUserApiInfo" user-id="modal.userId"
            api-quota="modal.apiQuota" api-active="modal.apiActive" api-key="modal.apiKey"
            generate-new-api-key="generateNewAPIKey" clean-modals="cleanModals"></edit-user-api-info>
    </div>
</div>


@endsection

@section('js')
{{-- Include modal templates --}}
@include('snippets.modals')

{{-- Include extra JS --}}
<script src='/js/datatables.min.js'></script>
<script src='/js/api.js'></script>
<script src='/js/AdminCtrl.js'></script>
@endsection
