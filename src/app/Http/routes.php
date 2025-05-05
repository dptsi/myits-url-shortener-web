<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

/* GET endpoints */

$app->get('/', ['as' => 'index', 'uses' => 'IndexController@showIndexPage']);
$app->get('/logout', ['as' => 'logout', 'uses' => 'UserController@performLogoutUser']);
$app->get('/logout_oidc', ['as' => 'logout_oidc', 'uses' => 'OpenidController@performLogout']);
$app->get('/login', ['as' => 'login', 'uses' => 'OpenidController@performLogin']);
$app->get('/generate-qrbase64', ['as' => 'generate-qrbase64', 'uses' => 'GenerateController@index']);
$app->get('/generate-qrbase64/{short_url}', ['as' => 'generate-qrbase64-show', 'uses' => 'GenerateController@show']);
$app->get('/shorten_result/{short_url}', ['as' => 'shorten_result', 'uses' => 'LinkController@showShortenResult']);

$app->get('/admin', ['as' => 'admin', 'uses' => 'AdminController@displayAdminPage']);
$app->get('/links', ['uses' => 'LinkController@index']);
$app->get('/links/datatable', ['as' => 'api_get_user_links', 'uses' => 'AdminPaginationController@paginateUserLinks']);
$app->post('/links/edit_url', ['as' => 'api_edit_link_long_url', 'uses' => 'AjaxController@editLinkLongUrl']);
$app->post('links/delete', ['as' => 'api_delete_link', 'uses' => 'AjaxController@deleteLink']);

// $app->get('/links/datatable', ['uses' => 'LinkController@getDatatable']);
// $app->get('/setup/finish', ['as' => 'setup_finish', 'uses' => 'SetupController@finishSetup']);
$app->get('/{short_url}', ['uses' => 'LinkController@performRedirect']);
$app->get('/m/{short_url}', ['uses' => 'LinkController@performRedirect']);
$app->get('/{short_url}/{secret_key}', ['uses' => 'LinkController@performRedirect']);
$app->get('/admin/stats/{short_url}', ['uses' => 'StatsController@displayStats']);
$app->post('/shorten', ['as' => 'pshorten', 'uses' => 'LinkController@performShorten']);

$app->group(['prefix' => '/api/v2', 'namespace' => 'App\Http\Controllers', 'middleware' => 'admin'], function ($app) {
    /* API internal endpoints */
    // $app->post('admin/toggle_api_active', ['as' => 'api_toggle_api_active', 'uses' => 'AjaxController@toggleAPIActive']);
    // $app->post('admin/generate_new_api_key', ['as' => 'api_generate_new_api_key', 'uses' => 'AjaxController@generateNewAPIKey']);
    $app->post('admin/toggle_user_active', ['as' => 'api_toggle_user_active', 'uses' => 'AjaxController@toggleUserActive']);
    $app->post('admin/change_user_role', ['as' => 'api_change_user_role', 'uses' => 'AjaxController@changeUserRole']);
    // $app->post('admin/add_new_user', ['as' => 'api_add_new_user', 'uses' => 'AjaxController@addNewUser']);
    // $app->post('admin/delete_user', ['as' => 'api_delete_user', 'uses' => 'AjaxController@deleteUser']);
    // $app->post('admin/toggle_link', ['as' => 'api_toggle_link', 'uses' => 'AjaxController@toggleLink']);
    // $app->post('admin/delete_link', ['as' => 'api_delete_link', 'uses' => 'AjaxController@deleteLink']);

    $app->get('admin/get_admin_users', ['as' => 'api_get_admin_users', 'uses' => 'AdminPaginationController@paginateAdminUsers']);
    $app->get('admin/get_admin_links', ['as' => 'api_get_admin_links', 'uses' => 'AdminPaginationController@paginateAdminLinks']);

});

$app->group(['prefix' => '/api/v2', 'namespace' => 'App\Http\Controllers', 'middleware' => 'api'], function ($app) {
    // $app->post('admin/edit_link_long_url', ['as' => 'api_edit_link_long_url', 'uses' => 'AjaxController@editLinkLongUrl']);
    $app->post('link_avail_check', ['as' => 'api_link_check', 'uses' => 'AjaxController@checkLinkAvailability']);
});
$app->group(['prefix' => '/api/v2', 'namespace' => 'App\Http\Controllers\Api', 'middleware' => 'api'], function ($app) {
    /* API shorten endpoints */
    // $app->post('action/shorten', ['as' => 'api_shorten_url', 'uses' => 'ApiLinkController@shortenLink']);
    $app->get('action/shorten', ['as' => 'api_shorten_url', 'uses' => 'ApiLinkController@shortenLink']);

    /* API lookup endpoints */
    // $app->post('action/lookup', ['as' => 'api_lookup_url', 'uses' => 'ApiLinkController@lookupLink']);
    $app->get('action/lookup', ['as' => 'api_lookup_url', 'uses' => 'ApiLinkController@lookupLink']);

    /* API data endpoints */
    $app->get('data/link', ['as' => 'api_link_analytics', 'uses' => 'ApiAnalyticsController@lookupLinkStats']);
    // $app->post('data/link', ['as' => 'api_link_analytics', 'uses' => 'ApiAnalyticsController@lookupLinkStats']);
});
