<?php
namespace FacebookEvents;

require_once __DIR__ . '/vendor/autoload.php'; 

use Facebook\Facebook;
use MapasCulturais\App;

/**
 * Undocumented class
 * 
 * @property-read \Facebook\Facebook $fb
 */
class Plugin extends \MapasCulturais\Plugin {
    
    /**
     * Instance of Facebook
     *
     * @var \Facebook\Facebook 
     */
    protected $_fb;

    public function _init() {
        $app = App::i();

        $fb = new Facebook([
            'app_id' => $this->config['app_id'],
            'app_secret' => $this->config['app_secret'],
            'default_graph_version' => 'v2.10',
        ]);
            
        $this->_fb = $fb;

        $app->hook('mapasculturais.run:before', function() use($fb){
            // define o access token padrão para uso nas requisições do plugin
            if($this->auth->isUserAuthenticated() && $this->user->facebookAccessToken){
                // @TODO validar token?
                $fb->setDefaultAccessToken($this->user->facebookAccessToken);
            }
        });
    }

    public function register() {
        $app = App::i();
        // register metadata, taxonomies

        $this->registerUserMetadata('facebookAccessToken', [
            'label' => 'Facebook Access Token',
            'private' => true
        ]);

        $app->registerController('facebook-events', "FacebookEvents\\Controller");
    }

    /**
     * Retorna o objeto Facebook
     *
     * @return \Facebook\Facebok;
     */
    public function getFb(){
        return $this->_fb;
    }
}