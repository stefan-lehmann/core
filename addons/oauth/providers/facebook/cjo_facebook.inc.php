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


require_once 'facebook.php';

class cjoFacebook extends cjoOAuthProvider {

    public function __construct() {

        global $CJO;

        $this->name = 'facebook';
        $this->getSettings();
        $this->provider = new Facebook( array('appId' => $this->settings->key, 'secret' => $this->settings->secret));

        if (cjo_get('logout', 'bool'))
            $this->provider->destroySession();
    }

    protected function getUserData() {

        $me = $this->provider->api('/me');
        $user = array('provider'   => $this->name, 
                     'id'          => $me['id'], 
                     'firstname'   => $me['first_name'], 
                     'lastname'    => $me['last_name'], 
                     'username'    => $me['username'], 
                     'name'        => $me['name'], 
                     'link'        => $me['link'], 
                     'image'       => 'https://graph.facebook.com/'.$me['id'].'/picture?type=normal');
                     
        $this->finishConnect($user);
    }

    protected function isConnected() {
        return !cjo_get('error','bool') && cjo_get('code','bool');
    }

    protected function redirectToPublisher() {
        $this->setCookie();
        $_SERVER['REQUEST_URI'] .= '&oauth_redirect=1';
        $parameter = array('response_type' => 'code');
        if (self::isAjax()) $parameter['display'] = 'popup';
        if (isset($this->settings->scope)) $parameter['scope'] = $this->settings->scope;
        $redirect_uri = $this->provider->getLoginUrl($parameter);
        cjoAssistance::redirect($redirect_uri);
    }
}
