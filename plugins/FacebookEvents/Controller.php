<?php
namespace FacebookEvents;

use Facebook\Facebook;
use MapasCulturais\App;

/**
 * @property-read \Facebook\Facebook $fb
 * @property-read \FacebookEvents\Plugin $plugin
 */
class Controller extends \MapasCulturais\Controller{
    /**
     * Retorna a instância do plugin
     *
     * @return \FacebookEvents\Plugin
     */
    public function getPlugin(){
        $plugin = App::i()->plugins['FacebookEvents'];

        return $plugin;
    }

    /**
     * Retorna objeto Facebook
     *
     * @return \Facebook\Facebook
     */
    public function getFb(){
        $plugin = $this->plugin;

        return $plugin->fb;
    }

    public function GET_panel(){
        $this->requireAuthentication();
        
        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = ['email', 'user_likes', 'user_events', 'user_friends']; 
        $login_url = $helper->getLoginUrl($this->createUrl('setAccessToken'), $permissions);
        
        
        
        $this->render('panel', ['login_url' => $login_url, 'fb' => $this->fb]);
    }
    
    
    public function GET_setAccessToken(){
        $this->requireAuthentication();
        
        $app = App::i();
        $plugin = $this->plugin;
        $fb = $this->fb;
        
        
        $helper = $fb->getRedirectLoginHelper();
        
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        
        
        // @TODO fazer de forma mapística
        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }        
        
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();
        
        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($plugin->config['app_id']);
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();
        
        if (! $accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
                exit;
            }
        }
        
        if (isset($accessToken)) {
            // Logged in!
            $app->disableAccessControl();
            $app->user->facebookAccessToken = (string) $accessToken;
            $app->user->save(true);
            $app->enableAccessControl();
            
            $app->redirect($this->createUrl('panel'));
        }
    }
}