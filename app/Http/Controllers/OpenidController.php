<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CryptoHelper;
use App\Helpers\UserHelper;
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
            
            // Set necessary information in session
            $_SESSION['id_token'] = $oidc->getIdToken();
            $_SESSION['user_info'] = $oidc->requestUserInfo();
            $_SESSION['access_token'] = $oidc->getAccessToken();
        }
        catch (OpenIDConnectClientException $e) {
            echo $e->getMessage();
        }

        $this->makeUserLoggedIn($request);
        return redirect()->route('index');
    }

    /**
     * 
     */
    public function makeUserLoggedIn(Request $request) {
        $userInfo = $_SESSION['user_info'];
        $username = $userInfo->name;
        $user_id  = $userInfo->sub;
        if(is_null($userInfo->email)){
            if(is_null($userInfo->alternate_email)){
                $email = 'dummy@mail.com';
            }
            else{
                $email = $userInfo->alternate_email;
            }
        }
        else{
            $email = $userInfo->email;
        }
        $role = ''; // default role is empty string (user)

        $request->session()->put('username', $username);
        $request->session()->put('role', $role);
        $request->session()->put('user_id', $user_id);
        $request->session()->put('email', $email);
        
        // if the user logged in for the first time
        // then insert the user to the database
        if ( !UserHelper::isUserExist($user_id, $username) ) {
            UserHelper::registerUser($request);
        }
        
        // if the user have logged
        else{
            UserHelper::loginUser($user_id, $username, $email);
        }
    }

    /**
     * 
     */
    public function performLogout(Request $request) {
        try {
            session_start();
            $redirect = env('OIDC_POST_LOGOUT_URI');

            if ( isset($_SESSION['id_token']) ) {
                $accessToken = $_SESSION['id_token'];
                
                // Forget the session
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
            echo $e->getMessage();
        }
    }
}