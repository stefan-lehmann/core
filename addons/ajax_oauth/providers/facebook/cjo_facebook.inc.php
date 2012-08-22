<?php

require_once 'facebook.inc.php';

/**
 * Extends the BaseFacebook class with the intent of using
 * PHP sessions to store user ids and access tokens.
 */
class cjoFacebook {
    /**
     * Identical to the parent constructor, except that
     * we start a PHP session to store the user ID and
     * access token if during the course of execution
     * we discover them.
     *
     * @param Array $config the application configuration.
     * @see BaseFacebook::__construct in facebook.php
     */

    private $provider;

    public function __construct() {

        global $CJO, $mypage;
        
        include_once $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/facebook/settings.inc.php';
        
        $this->provider = new Facebook(array('appId' => $APPID, 'secret' => $SECRET));

        //Destroy facebook user session when user clicks log out
        if (cjo_get('logout', 'bool')) {
            $this->provider->destroySession();
        }
    }
    
    public function isValidUser(){
        
        $user = $this->provider->getUser();
        cjo_Debug($user); die();
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $me = $this->provider->api('/me');
                //user
                $uid = $this->provider->getUser();
                cjo_Debug($me);
            } catch (FacebookApiException $e) {
                //echo error_log($e);
                $user = null;
            }
        }
    }
    
    public static function connect() {
        
        
        
    }
}
