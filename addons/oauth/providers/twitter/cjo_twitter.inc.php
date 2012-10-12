<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  oauth
 * @version     2.7.2
 *
 * @author      Stefan Lehmann <sl@contejo.com> inspired by Saran Chamling's (saaraan@gmail.com) Facebook Ajax Connect
 * @copyright   Copyright (c) 2008-2012 CONTEJO. All rights reserved. 
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *  CONTEJO is free software. This version may have been modified pursuant to the
 *  GNU General Public License, and as distributed it includes or is derivative
 *  of works licensed under the GNU General Public License or other free or open
 *  source software licenses. See _copyright.txt for copyright notices and
 *  details.
 * @filesource
 */


require_once 'twitteroauth.php';

class cjoTwitter extends cjoOAuthProvider {

    public function __construct() {

        global $CJO;
        
        $this->name = 'twitter';
        $this->getSettings();
        $this->provider = new TwitterOAuth($this->settings->key, $this->settings->secret);

        if (cjo_get('logout', 'bool')) $this->provider->destroySession();
    }
    
    protected function getUserData() {
        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $this->provider = new TwitterOAuth($this->settings->key, $this->settings->secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        
        /* Request access tokens from twitter */
        $access_token = $this->provider->getAccessToken($_REQUEST['oauth_verifier']);
        
        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $_SESSION['access_token'] = $access_token;
            
        $me =  $this->provider->get('account/verify_credentials');

        $user = array(
            'provider'  => $this->name,
            'id'        => $me->id,
            'username'  => $me->screen_name,
            'name'      => empty($m->name) ? $me->screen_name : $m->name,
            'link'      => 'https://twitter.com/'.$me->screen_name,
            'image'     => str_replace('_normal.','_bigger.', $me->profile_image_url)
        );
        
        $this->finishConnect($user);
    }

    protected function isConnected() {
        return cjo_get('oauth_verifier','bool');
    }
         
    protected function redirectToPublisher() {
        $this->setCookie();
        $request_token = $this->provider->getRequestToken($this->getCurrentUrl().'&oauth_redirect=1');
        $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $redirect_uri = $this->provider->getAuthorizeURL($token);
        cjoAssistance::redirect($redirect_uri);
    }
}
