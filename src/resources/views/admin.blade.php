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

                @if ($role == $admin_role)
                <li role='presentation' class='admin-nav-item'>
                    <a href='#admin'><span class="glyphicon glyphicon-user" aria-hidden="true"></span>Admin</a>
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

                <!-- <div role="tabpanel" class="tab-pane" id="links">
                    <h3>Links</h3>
                    @include('snippets.link_table', [
                        'table_id' => 'user_links_table'
                    ])
                </div> -->


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

            </div>
        </div>
    </div>

    <!-- <div class="angular-modals">
        <edit-long-link-modal ng-repeat="modal in modals.editLongLink" link-ending="modal.linkEnding"
            old-long-link="modal.oldLongLink" clean-modals="cleanModals"></edit-long-link-modal>
        <edit-user-api-info-modal ng-repeat="modal in modals.editUserApiInfo" user-id="modal.userId"
            api-quota="modal.apiQuota" api-active="modal.apiActive" api-key="modal.apiKey"
            generate-new-api-key="generateNewAPIKey" clean-modals="cleanModals"></edit-user-api-info>
    </div> -->

    <div id="editModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Link</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
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

</div>


@endsection

@section('js')
{{-- Include modal templates --}}
@include('snippets.modals')

{{-- Include extra JS --}}
<script src='/js/datatables.min.js'></script>
<script src='/js/api.js'></script>
<script>
    polr.directive('editLongLinkModal', function() {
        return {
            scope: {
                oldLongLink: '=',
                linkEnding: '=',
                cleanModals: '='
            },
            templateUrl: '/directives/editLongLinkModal.html',
            transclude: true,
            controller: function($scope, $element, $timeout) {
                $scope.init = function() {
                    // Destroy directive and clean modal on close
                    $element.find('.modal').on("hidden.bs.modal", function() {
                        $scope.$destroy();
                        $scope.cleanModals('editLongLink');
                    });
                }

                $scope.saveChanges = function() {
                    // Save long URL changes
                    apiCall('admin/edit_link_long_url', {
                        'link_ending': $scope.linkEnding,
                        'new_long_url': $element.find('input').val()
                    }, function(data) {
                        toastr.success('The link was updated.', 'Success')
                    }, function(err) {
                        toastr.error('The new URL format is not valid.', 'Error');
                    });
                };

                $scope.init();
            }
        };
    });

    polr.directive('editUserApiInfoModal', function() {
        return {
            scope: {
                userId: '=',
                apiActive: '=',
                apiKey: '=',
                apiQuota: '=',
                generateNewApiKey: '=',
                cleanModals: '='
            },
            templateUrl: '/directives/editUserApiInfoModal.html',
            transclude: true,
            controller: function($scope, $element, $timeout) {
                $scope.init = function() {
                    // Destroy directive and clean modal on close
                    $element.find('.modal').on("hidden.bs.modal", function() {
                        $scope.$destroy();
                        $scope.cleanModals('editUserApiInfo');
                    });

                    $scope.apiActive = res_value_to_text($scope.apiActive);
                }

                // Toggle API access status
                $scope.toggleAPIStatus = function() {
                    apiCall('admin/toggle_api_active', {
                        'user_id': $scope.userId,
                    }, function(new_status) {
                        $scope.apiActive = res_value_to_text(new_status);
                        $scope.$digest();
                    });
                };

                // Generate new API key for user_id
                $scope.parentGenerateNewAPIKey = function($event) {
                    $scope.generateNewApiKey($event, $scope.userId, false);
                };

                // Update user API quotas
                $scope.updateAPIQuota = function() {
                    apiCall('admin/edit_api_quota', {
                        'user_id': $scope.userId,
                        'new_quota': parseInt($scope.apiQuota)
                    }, function(next_action) {
                        toastr.success("Quota successfully changed.", "Success");
                    });
                };

                $scope.init();
            }
        };
    });

    polr.controller('AdminCtrl', function($scope, $compile, $timeout) {
        /* Initialize $scope variables */
        $scope.state = {
            showNewUserWell: false
        };
        $scope.datatables = {};
        $scope.modals = {
            editLongLink: [],
            editUserApiInfo: []
        };
        $scope.newUserParams = {
            username: '',
            userPassword: '',
            userEmail: '',
            userRole: ''
        };

        $scope.syncHash = function() {
            var url = document.location.toString();
            if (url.match('#')) {
                $('.admin-nav a[href=#' + url.split('#')[1] + ']').tab('show');
            }
        };

        $scope.cleanModals = function(modalType) {
            $timeout(function() {
                $scope.modals[modalType].shift();
            });

            $scope.reloadLinkTables();
        };

        // Initialise Datatables elements
        $scope.initTables = function() {
            var datatables_config = {
                'autoWidth': false,
                'processing': true,
                'serverSide': true,

                'drawCallback': function() {
                    // Compile Angular bindings on each draw
                    $compile($(this))($scope);
                }
            };

            if ($('#admin_users_table').length) {
                $scope.datatables['admin_users_table'] = $('#admin_users_table').DataTable($.extend({
                    "ajax": BASE_API_PATH + 'admin/get_admin_users',

                    "columns": [{
                            className: 'wrap-text',
                            data: 'username',
                            name: 'username'
                        },
                        {
                            className: 'wrap-text',
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'toggle_active',
                            name: 'toggle_active',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'change_role',
                            name: 'change_role',
                            orderable: false,
                            searchable: false
                        },
                        // {
                        //     data: 'delete',
                        //     name: 'delete',
                        //     orderable: false,
                        //     searchable: false
                        // }
                    ]
                }, datatables_config));
            }
            if ($('#admin_links_table').length) {
                $scope.datatables['admin_links_table'] = $('#admin_links_table').DataTable($.extend({
                    "ajax": BASE_API_PATH + 'admin/get_admin_links',

                    "columns": [{
                            className: 'wrap-text',
                            data: 'short_url',
                            name: 'short_url'
                        },
                        {
                            className: 'wrap-text',
                            data: 'long_url',
                            name: 'long_url'
                        },
                        {
                            data: 'clicks',
                            name: 'clicks'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            searchable: false
                        },
                        {
                            data: 'qr_code',
                            name: 'qr_code',
                            searchable: false
                        },
                        {
                            data: 'username',
                            orderable: false,
                            name: 'username',
                            searchable: false
                        },
                        {
                            data: 'edit',
                            name: 'edit',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'disable',
                            name: 'disable',
                            orderable: false,
                            searchable: false
                        },
                        // {
                        //     data: 'delete',
                        //     name: 'delete',
                        //     orderable: false,
                        //     searchable: false
                        // }

                    ]
                }, datatables_config));
            }


        };

        $scope.reloadLinkTables = function() {
            // Reload DataTables for affected tables
            if ('admin_links_table' in $scope.datatables) {
                $scope.datatables['admin_links_table'].ajax.reload(null, false);
            }

            $scope.datatables['user_links_table'].ajax.reload(null, false);
        };

        $scope.reloadUserTables = function() {
            $scope.datatables['admin_users_table'].ajax.reload(null, false);
        };

        /*
            User Management
        */
        $scope.toggleUserActiveStatus = function($event, user_id) {
            var el = $($event.target);

            apiCall('admin/toggle_user_active', {
                'user_id': user_id,
            }, function(new_status) {
                var text = (new_status == 1) ? 'Active' : 'Inactive';
                el.text(text);
                if (el.hasClass('btn-success')) {
                    el.removeClass('btn-success').addClass('btn-danger');
                } else {
                    el.removeClass('btn-danger').addClass('btn-success');
                }
            });
        }


        $scope.checkNewUserFields = function() {
            var response = true;

            $('.new-user-fields input').each(function() {
                if ($(this).val().trim() == '' || response == false) {
                    response = false;
                }
            });

            return response;
        }


        $scope.changeUserRole = function(role, user_id) {
            apiCall('admin/change_user_role', {
                'user_id': user_id,
                'role': role,
            }, function(result) {
                toastr.success("User role successfully changed.", "Success");
            });
        };

        /*
            Link Management
        

        // Delete link
        /*
        $scope.deleteLink = function($event, link_ending) {
            var el = $($event.target);

            apiCall('admin/delete_link', {
                'link_ending': link_ending,
            }, function(new_status) {
                toastr.success('Link successfully deleted.', 'Success');
                $scope.reloadLinkTables();
            });
        };
        */

        // Disable and enable links
        $scope.toggleLink = function($event, link_ending) {
            var el = $($event.target);
            var curr_action = el.text();

            apiCall('admin/toggle_link', {
                'link_ending': link_ending,
            }, function(next_action) {
                toastr.success(curr_action + " was successful.", "Success");
                if (next_action == 'Disable') {
                    el.removeClass('btn-success');
                    el.addClass('btn-danger');
                } else {
                    el.removeClass('btn-danger');
                    el.addClass('btn-success');
                }

                el.text(next_action);
            });
        };



        /*
            Initialisation
        */

        // Initialise AdminCtrl
        $scope.init = function() {
            var modal_source = $("#modal-template").html();

            $('.admin-nav a').click(function(e) {
                e.preventDefault();
                $(this).tab('show');
                console.log(document.location.toString());
            });
            $scope.syncHash();

            $(window).on('hashchange', function() {
                $scope.syncHash();
            });

            $("a[href^=#]").on("click", function(e) {
                history.pushState({}, '', this.href);
            });

            $scope.initTables();
        };

        $scope.init();


        //nusantara
        $(document).on('click', '.edit-link', function() {
            var shortUrl = $(this).data('short');
            var longUrl = $(this).data('long');
            console.log(shortUrl);
            $('#edit_short_url').val(shortUrl);
            $('#edit_long_url').val(longUrl);

            $('#editModal').modal('show');
        });

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
                        location.reload();
                        // $('#links-table').DataTable().ajax.reload(); // Reload DataTable
                    } else {
                        alert('Error updating link.');
                    }
                },
                error: function(xhr) {
                    alert('Failed to update link: ' + xhr.responseText);
                }
            });
        });

    });
</script>
@endsection