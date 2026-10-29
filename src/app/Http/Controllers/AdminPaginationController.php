<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables as Datatables;

use App\Models\Link;
use App\Models\User;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

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
        return '<a target="_blank" title="' . e($link->long_url) . '" href="' . $link->long_url . '">' . Str::limit($link->long_url, 50) . '</a>
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
            $active_text = 'Aktif';
            $btn_color_class = ' btn-outline-success';
        } else {
            $active_text = 'Nonaktif';
            $btn_color_class = ' btn-outline-danger';
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
        // Item menu "Nonaktifkan/Aktifkan" pada dropdown aksi
        $item_class = 'tx-danger';
        $item_icon = 'block';
        $item_text = 'Nonaktifkan';

        if ($link->is_disabled) {
            $item_class = 'tx-success';
            $item_icon = 'check_circle';
            $item_text = 'Aktifkan';
        }

        return '<a href="#" ng-click="toggleLink($event, \'' . e($link->short_url) . '\')" class="dropdown-item d-flex align-items-center gap-2 ' . $item_class . '">'
            . '<span class="ms-rounded tx-18">' . $item_icon . '</span> <span class="action-label">' . $item_text . '</span></a>';
    }

    // Tombol titik tiga (more_vert) berisi item-item dropdown aksi
    public function renderActionMenu(array $items)
    {
        return '<div class="dropdown">'
            . '<button type="button" class="btn btn-icon btn-sm rounded-circle bd-0 d-flex align-items-center" data-toggle="dropdown" data-boundary="window" aria-label="Aksi"><span class="ms-rounded tx-20">more_vert</span></button>'
            . '<div class="dropdown-menu dropdown-menu-right">' . implode('', $items) . '</div>'
            . '</div>';
    }

    public function renderEditLinkItem($link)
    {
        return '<a href="#" class="dropdown-item d-flex align-items-center gap-2 edit-link" data-short="'.e($link->short_url).'" data-long="'.e($link->long_url).'"><span class="ms-rounded tx-18">edit</span> Ubah</a>';
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

        // Filter peran: 'admin' atau 'default' (pengguna biasa, role kosong/NULL)
        $role_filter = $request->input('role');
        if ($role_filter === 'admin') {
            $admin_users->where('role', UserHelper::$USER_ROLES['admin']);
        } elseif ($role_filter === 'default') {
            $admin_users->where(function ($query) {
                $query->whereNull('role')->orWhere('role', UserHelper::$USER_ROLES['default']);
            });
        }
        return Datatables::of($admin_users)
            ->addColumn('toggle_active', $this->renderToggleUserActiveCell(...))
            ->addColumn('change_role', $this->renderChangeUserRoleCell(...))
            ->addColumn('delete', $this->renderDeleteUserCell(...))
            ->editColumn('created_at', $this->formatDateTime(...))
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
            // ->addColumn('delete', $this->renderDeleteLinkCell(...))
            ->addColumn('action', function ($row) {
                return $this->renderActionMenu([
                    $this->renderEditLinkItem($row),
                    $this->renderToggleLinkActiveCell($row),
                ]);
            })
            ->editColumn('clicks', $this->renderClicksCell(...))
            ->editColumn('created_at', $this->formatDateTime(...))
            ->addColumn('qr_code', $this->renderQrCode(...))
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
            ->editColumn('created_at', $this->formatDateTime(...))
            ->addColumn('qr_code', $this->renderQrCode(...))
            ->addColumn('edit', function ($row) {
                return $this->renderActionMenu([
                    $this->renderEditLinkItem($row),
                    '<a href="#" class="dropdown-item d-flex align-items-center gap-2 tx-danger delete-link" data-short_url="'.e($row->short_url).'"><span class="ms-rounded tx-18">delete</span> Hapus</a>',
                ]);
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
    $shortUrl = $link->short_url;
    $modalId = 'qrModal_' . preg_replace('/[^a-zA-Z0-9]/', '_', $shortUrl);

    if ($link->base64 != null) {
        $qrDataUri = 'data:image/png;base64,' . $link->base64;
        // Thumbnail kecil -- klik buka modal HD
        $qrCode = '<img src="' . $qrDataUri . '" alt="QR Code" class="qr-thumb" '
                . 'style="width:72px;height:72px;cursor:pointer" '
                . 'data-toggle="modal" data-target="#' . $modalId . '">';
        $downloadLink = '<a href="' . $qrDataUri . '" download="qr_code_hd.png" class="qr-download tx-13">Unduh</a>';

        // Modal untuk tampilan HD
        $modal = '<div id="' . $modalId . '" class="modal fade effect-scale" tabindex="-1" role="dialog">'
               . '<div class="modal-dialog modal-sm" role="document">'
               . '<div class="modal-content bd-0">'
               . '<div class="modal-header bd-0 d-flex align-items-center pd-b-0">'
               . '<h5 class="modal-title">QR Code — ' . e($shortUrl) . '</h5>'
               . '<button type="button" class="btn btn-icon btn-sm rounded-circle d-flex align-items-center bd-0" data-dismiss="modal"><span class="ms-rounded tx-22" aria-label="close">close</span></button></div>'
               . '<div class="modal-body text-center">'
               . '<img src="' . $qrDataUri . '" alt="QR Code HD" style="max-width:100%">'
               . '</div>'
               . '<div class="modal-footer">'
               . '<button type="button" class="btn" data-dismiss="modal">Tutup</button>'
               . '<a href="' . $qrDataUri . '" download="qr_code_hd.png" class="btn btn-primary">Unduh HD</a>'
               . '</div>'
               . '</div></div></div>';

        return $qrCode . '<br>' . $downloadLink . $modal;
    }

    // URL untuk QR Code biasa
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=https://its.id/' . $link->short_url . '&size=72x72';
    // URL untuk QR Code HD
    $qrUrlHd = 'https://api.qrserver.com/v1/create-qr-code/?data=https://its.id/' . $link->short_url . '&size=500x500';

    // Menampilkan QR Code dan link download HD
    return '<img src="' . $qrUrl . '" alt="" title="" /><br><a href="' . $qrUrlHd . '" download="qr_code_hd.png" class="tx-13">Unduh</a>';
}

}
