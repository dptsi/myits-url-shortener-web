<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

Route::namespace('App\Http\Controllers')->group(function () {

/* GET endpoints */

Route::get('/', ['as' => 'index', 'uses' => 'IndexController@showIndexPage']);
Route::get('/logout', ['as' => 'logout', 'uses' => 'UserController@performLogoutUser']);
Route::get('/logout_oidc', ['as' => 'logout_oidc', 'uses' => 'OpenidController@performLogout']);
Route::get('/login', ['as' => 'login', 'uses' => 'OpenidController@performLogin']);
Route::get('/generate-qrbase64', ['as' => 'generate-qrbase64', 'uses' => 'GenerateController@index']);
Route::get('/generate-qrbase64/{short_url}', ['as' => 'generate-qrbase64-show', 'uses' => 'GenerateController@show']);
Route::get('/generate-qr', ['as' => 'generate-qr', 'uses' => 'GenerateController@generate']);
Route::get('/shorten_result/{short_url}', ['as' => 'shorten_result', 'uses' => 'LinkController@showShortenResult']);

Route::get('/admin', ['as' => 'admin', 'uses' => 'AdminController@displayAdminPage']);
Route::get('/links', ['uses' => 'LinkController@index']);
Route::get('/links/datatable', ['as' => 'api_get_user_links', 'uses' => 'AdminPaginationController@paginateUserLinks']);
Route::post('/links/edit_url', ['as' => 'api_edit_link_long_url', 'uses' => 'AjaxController@editLinkLongUrl']);
Route::post('links/delete', ['as' => 'api_delete_link', 'uses' => 'AjaxController@deleteLink']);

// Route::get('/links/datatable', ['uses' => 'LinkController@getDatatable']);
// Route::get('/setup/finish', ['as' => 'setup_finish', 'uses' => 'SetupController@finishSetup']);
Route::get('/{short_url}', ['uses' => 'LinkController@performRedirect']);
Route::get('/m/{short_url}', ['uses' => 'LinkController@performRedirect']);
Route::get('/{short_url}/{secret_key}', ['uses' => 'LinkController@performRedirect']);
Route::get('/admin/stats/{short_url}', ['uses' => 'StatsController@displayStats']);
Route::post('/shorten', ['as' => 'pshorten', 'uses' => 'LinkController@performShorten']);

Route::group(['prefix' => '/api/v2', 'middleware' => 'admin'], function () {
    /* API internal endpoints */
    // Route::post('admin/toggle_api_active', ['as' => 'api_toggle_api_active', 'uses' => 'AjaxController@toggleAPIActive']);
    // Route::post('admin/generate_new_api_key', ['as' => 'api_generate_new_api_key', 'uses' => 'AjaxController@generateNewAPIKey']);
    Route::post('admin/toggle_user_active', ['as' => 'api_toggle_user_active', 'uses' => 'AjaxController@toggleUserActive']);
    Route::post('admin/change_user_role', ['as' => 'api_change_user_role', 'uses' => 'AjaxController@changeUserRole']);
    // Route::post('admin/add_new_user', ['as' => 'api_add_new_user', 'uses' => 'AjaxController@addNewUser']);
    // Route::post('admin/delete_user', ['as' => 'api_delete_user', 'uses' => 'AjaxController@deleteUser']);
    // Route::post('admin/toggle_link', ['as' => 'api_toggle_link', 'uses' => 'AjaxController@toggleLink']);
    // Route::post('admin/delete_link', ['as' => 'api_delete_link', 'uses' => 'AjaxController@deleteLink']);

    Route::get('admin/get_admin_users', ['as' => 'api_get_admin_users', 'uses' => 'AdminPaginationController@paginateAdminUsers']);
    Route::get('admin/get_admin_links', ['as' => 'api_get_admin_links', 'uses' => 'AdminPaginationController@paginateAdminLinks']);

});

Route::group(['prefix' => '/api/v2', 'middleware' => 'api.auth'], function () {
    // Route::post('admin/edit_link_long_url', ['as' => 'api_edit_link_long_url', 'uses' => 'AjaxController@editLinkLongUrl']);
    Route::post('link_avail_check', ['as' => 'api_link_check', 'uses' => 'AjaxController@checkLinkAvailability']);
});
Route::group(['prefix' => '/api/v2', 'namespace' => 'Api', 'middleware' => 'api.auth'], function () {
    /* API shorten endpoints */
    // Route::post('action/shorten', ['as' => 'api_shorten_url', 'uses' => 'ApiLinkController@shortenLink']);
    Route::get('action/shorten', ['as' => 'api_shorten_url', 'uses' => 'ApiLinkController@shortenLink']);

    /* API lookup endpoints */
    // Route::post('action/lookup', ['as' => 'api_lookup_url', 'uses' => 'ApiLinkController@lookupLink']);
    Route::get('action/lookup', ['as' => 'api_lookup_url', 'uses' => 'ApiLinkController@lookupLink']);

    /* API data endpoints */
    Route::get('data/link', ['as' => 'api_link_analytics', 'uses' => 'ApiAnalyticsController@lookupLinkStats']);
    // Route::post('data/link', ['as' => 'api_link_analytics', 'uses' => 'ApiAnalyticsController@lookupLinkStats']);
});

});
