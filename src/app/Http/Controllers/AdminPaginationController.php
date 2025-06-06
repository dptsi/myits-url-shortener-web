<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

use App\Models\Link;
use App\Models\User;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Response;

class AdminPaginationController extends Controller
{
    /**
     * Process AJAX Datatables pagination queries from the admin panel.
     *
     * @return Response
     */

    /* Cell rendering functions */

    public function renderLongUrlCell($link)
    {
        return '<a target="_blank" title="' . e($link->long_url) . '" href="' . $link->long_url . '">' . str_limit($link->long_url, 50) . '</a>
            <a class="btn btn-primary btn-xs edit-long-link-btn" ng-click="editLongLink(\'' . $link->short_url . '\', \'' . $link->long_url . '\')"><i class="fa fa-edit edit-link-icon"></i></a>';
    }

    public function renderClicksCell($link)
    {
        if (env('SETTING_ADV_ANALYTICS')) {
            return $link->clicks . ' <a target="_blank" class="stats-icon" href="/admin/stats/' . e($link->short_url) . '">
                <i class="fa fa-area-chart" aria-hidden="true"></i>
            </a>';
        } else {
            return $link->clicks;
        }
    }

    public function renderDeleteUserCell($user)
    {
        // Add "Delete" action button
        $btn_class = '';
        if (session('username') === $user->username) {
            $btn_class = 'disabled';
        }
        return '';
        return '<a ng-click="deleteUser($event, \'' . $user->id . '\')" class="btn btn-sm btn-danger ' . $btn_class . ' delete-button-custom">
            Delete
        </a>';
    }

    public function renderDeleteLinkCell($link)
    {
        // Add "Delete" action button
        return '<a ng-click="deleteLink($event, \'' . e($link->short_url)  . '\')"
            class="btn btn-sm btn-default delete-link delete-button-custom">
            Delete
        </a>';
    }

    public function renderAdminApiActionCell($user)
    {
        return '';
        // Add "API Info" action button
        return '<a class="activate-api-modal btn btn-sm btn-info"
            ng-click="openAPIModal($event, \'' . e($user->username) . '\', \'' . $user->api_key . '\', \'' . $user->api_active . '\', \'' . e($user->api_quota) . '\', \'' . $user->id . '\')">
            API info
        </a>';
    }

    public function renderToggleUserActiveCell($user)
    {
        // Add user account active state toggle buttons
        $btn_class = '';
        if (session('username') === $user->username) {
            $btn_class = ' disabled';
        }

        if ($user->active) {
            $active_text = 'Active';
            $btn_color_class = ' btn-success';
        } else {
            $active_text = 'Inactive';
            $btn_color_class = ' btn-danger';
        }

        return '<a class="btn btn-sm status-display' . $btn_color_class . $btn_class . '" ng-click="toggleUserActiveStatus($event, ' . $user->id . ')">' . $active_text . '</a>';
    }

    public function renderChangeUserRoleCell($user)
    {
        $role = '-';
        if ($user->role != null) {
            $role = $user->role;
        }
        $select_role = '<select ng-init="changeUserRole.u' . $user->id . ' = \'' . e($user->role) . '\'"
            ng-model="changeUserRole.u' . $user->id . '" ng-change="changeUserRole(changeUserRole.u' . $user->id . ', ' . $user->id . ')"
            class="form-control"';

        if (session('username') === $user->username) {
            // Do not allow user to change own role
            $select_role .= ' disabled';
        }
        $select_role .= '>';

        foreach (UserHelper::$USER_ROLES as $role_text => $role_val) {
            // Iterate over each available role and output option
            $select_role .= '<option value="' . e($role_val) . '"';

            if ($user->role === $role_val) {
                $select_role .= ' selected';
            }

            $select_role .= '>' . e($role_text) . '</option>';
        }

        $select_role .= '</select>';
        return $role;
        // return $select_role;
    }

    public function renderToggleLinkActiveCell($link)
    {
        // Add "Disable/Enable" action buttons
        $btn_class = 'btn-danger';
        $btn_text = 'Disable';

        if ($link->is_disabled) {
            $btn_class = 'btn-success';
            $btn_text = 'Enable';
        }

        return '<a ng-click="toggleLink($event, \'' . e($link->short_url) . '\')" class="btn btn-sm ' . $btn_class . '">
            ' . $btn_text . '
        </a>';
    }

    public function formatDateTime($link)
    {
        return date('d M Y, H:i', strtotime($link->created_at));
    }

    /* DataTables bindings */

    public function paginateAdminUsers(Request $request)
    {
        self::ensureAdmin();
        $user_id  = session('user_id');
        $check = DB::table('users')->where('id',$user_id)->select('role')->first();  
        if($check->role != 'admin'){
            return ("You are not authorized to access this page");
        }

        $admin_users = User::select(['username', 'email', 'created_at', 'active', 'api_key', 'api_active', 'api_quota', 'role', 'id']);
        return Datatables::of($admin_users)
            ->addColumn('toggle_active', [$this, 'renderToggleUserActiveCell'])
            ->addColumn('change_role', [$this, 'renderChangeUserRoleCell'])
            ->addColumn('delete', [$this, 'renderDeleteUserCell'])
            ->editColumn('created_at', [$this, 'formatDateTime'])
            ->escapeColumns(['username', 'email'])
            ->make(true);
    }

    public function paginateAdminLinks(Request $request)
    {
        self::ensureAdmin();
        $user_id  = session('user_id');
        $check = DB::table('users')->where('id',$user_id)->select('role')->first();  
        if($check->role != 'admin'){
            return ("You are not authorized to access this page");
        }

        $admin_links = DB::table('links')
            ->leftjoin('users', 'users.id', '=', 'links.user_id')
            ->select([
                'links.short_url',
                'links.long_url',
                'links.clicks',
                'links.created_at',
                'links.base64',
                'users.username',
                'links.is_disabled'
            ]);
        return Datatables::of($admin_links)
            ->addColumn('disable', [$this, 'renderToggleLinkActiveCell'])
            // ->addColumn('delete', [$this, 'renderDeleteLinkCell'])
            ->addColumn('edit', function ($row) {
                return '<button class="btn btn-sm btn-primary edit-link" data-short="'.$row->short_url.'" data-long="'.$row->long_url.'">Edit</button>';
            })
            ->editColumn('clicks', [$this, 'renderClicksCell'])
            ->editColumn('created_at', [$this, 'formatDateTime'])
            ->addColumn('qr_code', [$this, 'renderQrCode'])
            ->escapeColumns(['short_url'])
            ->make(true);
    }

    public function paginateUserLinks(Request $request)
    {
        self::ensureLoggedIn();

        $user_id  = session('user_id');
        $user_links = Link::where('user_id', $user_id)
            ->select(['id', 'short_url', 'long_url', 'base64', 'clicks', 'created_at']);

        return Datatables::of($user_links)
            ->editColumn('created_at', [$this, 'formatDateTime'])
            ->addColumn('qr_code', [$this, 'renderQrCode'])
            ->addColumn('edit', function ($row) {
                return '<button class="btn btn-sm btn-primary edit-link" data-short="'.$row->short_url.'" data-long="'.$row->long_url.'">Edit</button>
                <button class="btn btn-sm btn-danger delete-link" data-short_url="'.$row->short_url.'">Delete</button>
                ';
            })
            ->escapeColumns(['short_url'])
            ->make(true);
    }

    //nusantara
    // public function renderQrCode($link)
    // {
    //     if ($link->base64 != null) {
    //             return ' <img src="data:image/png;base64,' . $link->base64 . '" alt="QR Code">';
    //     }
    //     return '<img src="https://api.qrserver.com/v1/create-qr-code/?data=https://its.id/' . $link->short_url . '&amp;size=100x100" alt="" title="" />';
    // }

    public function renderQrCode($link)
{
    if ($link->base64 != null) {
        // Menampilkan QR Code dari base64
        $qrCode = '<img src="data:image/png;base64,' . $link->base64 . '" alt="QR Code">';
        // Link download versi HD menggunakan base64
        $downloadLink = '<a href="data:image/png;base64,' . $link->base64 . '" download="qr_code_hd.png">Download</a>';
        return $qrCode . '<br>' . $downloadLink;
    }

    // URL untuk QR Code biasa
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=https://its.id/' . $link->short_url . '&size=100x100';
    // URL untuk QR Code HD
    $qrUrlHd = 'https://api.qrserver.com/v1/create-qr-code/?data=https://its.id/' . $link->short_url . '&size=500x500';

    // Menampilkan QR Code dan link download HD
    return '<img src="' . $qrUrl . '" alt="" title="" /><br><a href="' . $qrUrlHd . '" download="qr_code_hd.png">Download</a>';
}

}
