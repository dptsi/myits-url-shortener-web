<?php
namespace App\Http\Controllers;

use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use App\Helpers\CryptoHelper;
use App\Helpers\UserHelper;
use Faker\Provider\UserAgent;
use Its\Sso\OpenIDConnectClient;
use Its\Sso\OpenIDConnectClientException;

class OpenidController extends Controller
{
    /**
     * Perform login using My ITS SSO
     */
    public function performLogin(Request $request) {
        
        if ($this->isLoggedIn()) {
            return redirect()->route('index');
        }

        // Perform MyITS SSO login
        try {
            $oidc = new OpenIDConnectClient(
                env('OIDC_ENDPOINT'),
                env('OIDC_CLIENT_ID'),
                env('OIDC_CLIENT_SECRET')
            );

            $oidc->setRedirectURL( env('OIDC_REDIRECT_URL') );
            $oidc->addScope( env('OIDC_SCOPE') );

            // remove this if in production mode
            $oidc->setVerifyHost(false);
            $oidc->setVerifyPeer(false);

            //call the main function of myITS SSO login
            $oidc->authenticate();
            
            $oidc->getSessionState();

            if ($oidc->getVerifiedClaims('sid') != NULL) {
                $sid = $oidc->getVerifiedClaims('sid');

                if (session_status() !== PHP_SESSION_NONE) {
                    session_destroy();
                    session_id($sid);
                    session_start();
                }
                else {
                    session_id($sid);
                    session_start();
                }
            }
            else {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
            }
            
            $_SESSION['id_token']  = $oidc->getIdToken();
            $_SESSION['user_info'] = $oidc->requestUserInfo();
        }
        catch (OpenIDConnectClientException $e) {
            if (env('APP_DEBUG') == true)  {
                echo $e->getMessage();
            }
        }
        
        $this->makeUserLoggedIn();
        return redirect()->route('index');
    }

    /**
     * 
     */
    public function makeUserLoggedIn() {
        $userInfo = $_SESSION['user_info']; // object
        $username = $userInfo->name;    // string
        $role     = '';                 // string
        $group    = '';                 // string
        $ssoId    = $userInfo->sub;     // string
        $ssoRole  = $userInfo->role;    // array of object
        $ssoGroup = $userInfo->group;   // array of object
        
        // check for user email
        if ( is_null($userInfo->email) ) {
            if ( is_null($userInfo->alternate_email) ) {
                $email = 'dummy@mail.com';
            }
            else {
                $email = $userInfo->email;
            }
        }
        else {
            $email = $userInfo->email;
        }

        // try to find administrator role if exist
        foreach ($ssoRole as $eachRole) {
            $ssoAdminRoleID = UserHelper::$SSO_USER_ROLES_ID['admin'];
            //
            if ($eachRole->role_id == $ssoAdminRoleID) {
                $role = UserHelper::$USER_ROLES['admin'];
                $expiredTime = $eachRole->expired_at;
                break;
            }
        }

        // check the expired date of user role
        // if it is expires, then the user is not admin
        if ($role == UserHelper::$USER_ROLES['admin']) {
            // if there is an expiration date (not null), check whether it is expired
            if ( is_null($expiredTime) == false) {
                if ( AdminHelper::isDateExpired($expiredTime) )  {
                    $role = UserHelper::$USER_ROLES['default'];
                }
            }
        }

        // try to find the group of this user
        // store them to a list
        $obtainedGroup = [];
        foreach ($ssoGroup as $eachGroup) {
            array_push($obtainedGroup, $eachGroup->group_name);
        }
        // determine the group (student vs non-student e.g Dosen, Tendik, Pegawai etc.)
        $isStudent = in_array(UserHelper::$ROLE_GROUP['mahasiswa'], $obtainedGroup);
        if ($isStudent) {
            // obviously, if the user is a student, note the group as student
            $group = UserHelper::$ROLE_GROUP['mahasiswa'];
        }
        else {
            foreach ($obtainedGroup as $seeGroup) {
                // assumption: each user is only given two group_name
                // the first non-"everyone" group_name will be taken
                if ($seeGroup != UserHelper::$ROLE_GROUP['everyone']) {
                    $group = $seeGroup;
                    break;
                }
            }
        }

        // if the user is logged in for the first time
        // then insert to the DB
        if ( !UserHelper::isUserExist($ssoId, $username) ) {
            UserHelper::registerUser($ssoId, $username, $email, $role);
        }
        // else, the user have logged in. check the email and the role
        else {
            UserHelper::checkIdentity($ssoId, $username, $email, $role);
        }

        $role = UserHelper::getUserRole($ssoId);
        $user_id = UserHelper::getUserID($ssoId);

        // set the required sessions to be used
        // later throughout this application
        session([
            'username' => $username,
            'sso_id' => $ssoId,
            'email' => $email,
            'role' => $role,
            'user_id' => $user_id,
            'role_group' => $group
        ]);
    }

    /**
     * 
     */
    public function performLogout(Request $request) {
        try {
            if(!isset($_SESSION)) 
            { 
                session_start(); 
            }
            $redirect = env('OIDC_POST_LOGOUT_URI');

            if ( isset($_SESSION['id_token']) ) {
                $accessToken = $_SESSION['id_token'];
                
                // Forget the 'id_token' and 'user_info' session
                // ensuring the user is really logged out
                session_destroy();

                $oidc = new OpenIDConnectClient(
                    env('OIDC_ENDPOINT'),
                    env('OIDC_CLIENT_ID'),
                    env('OIDC_CLIENT_SECRET')
                );

                $oidc->setVerifyHost(false);
                $oidc->setVerifyPeer(false);
                $oidc->signOut($accessToken, $redirect);
            }
        }
        catch (OpenIDConnectClientException $e) {
            if (env('APP_DEBUG') == true)  {
                echo $e->getMessage();
            }
        }
    }
}
