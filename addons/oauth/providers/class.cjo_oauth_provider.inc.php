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

abstract class cjoOAuthProvider {

    protected $name = false;
    protected static $mypage = 'oauth';
    protected $settings;
    protected $provider;

    public function __construct() {
    }
    
    public static function initProviders() {
        
        global $CJO;
        
        foreach(self::getProviders() as $provider){
            
            include_once $CJO['ADDON_PATH'].'/'.self::$mypage.'/providers/'.$provider.'/cjo_'.$provider.'.inc.php';
        
            $class_name = 'cjo'.$provider;
            if (cjo_get(self::generateGetKey($provider),'string') == $provider) {
                $OAuth[$provider] = new $class_name;
                $OAuth[$provider]->connect();
            }
        }
        cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoOAuthProvider::replaceVars'); 
    }
    
    public static function replaceVars($params) {
        
        global $CJO;

        $content = $params['subject'];

        foreach(self::getProviders() as $provider){
            $key = 'OAUTH_LINK['.$provider.']';
            if (strpos($content,$key) !== false) {
                $content = str_replace($key, self::generateLink($provider), $content);
            }
        }
        if ($CJO['ADDON']['settings'][self::$mypage]['ajax']) {
            require_once $CJO['INCLUDE_PATH']."/classes/afc/functions/function_cjo_common.inc.php";
            $content = cjo_insertJS($content, $CJO['ADDON']['settings'][self::$mypage]['oauth_js']);
        }
        return $content;
    }

    protected function getSettings() {
        global $CJO;
        $json = file_get_contents($CJO['ADDON_CONFIG_PATH'] . '/' . self::$mypage . '/' . $this->name . '/settings.json');
        $this->settings = json_decode($json);
    }

    public function connect() {

        if (!cjo_get('oauth_redirect','bool')) {
            cjo_set_session('oauth_page', cjo_server('HTTP_REFERER','string'));
            $this->redirectToPublisher();
        } 
        else if ($this->isConnected()) {
            $this->getUserData();
        }
        else {
            $this->finishConnect();
        }   
    }

    protected function finishConnect($user=NULL) {
        
        global $CJO;
        
        cjo_set_session('oauth_user', $user);
        
        if ($CJO['ADDON']['settings'][self::$mypage]['ajax']) {
            cjo_set_session('oauth_page', NULL);
            $this->setCookie($user);
            $this->closePopUpWindow();
        }
        else {
            $this->backToPreviusPage();
        }
    }

    protected function closePopUpWindow() {
        
        $content  = '<!DOCTYPE><html><head>'."\r\n";
        $content .= '<title>OAuth</title>'."\r\n";
        $content .= '<script type="text/javascript">'."\r\n";
        $content .= 'self.close();'."\r\n";
        $content .= '</script>'."\r\n";
        $content .= '</head><body></body></html>'."\r\n";   

        cjoClientCache::sendContent($content);
    }
    
    protected function backToPreviusPage() {
        
        global $CJO;
        
        $redirect_uri = cjo_session('oauth_page','string');
        if ($redirect_uri && preg_match('/^https*:\/\//i', $redirect_uri)) {
            cjo_set_session('oauth_page', NULL);
            cjoAssistance::redirect($redirect_uri); 
        } 
        else {
            cjoAssistance::redirectFE($CJO['START_ARTICLE_ID']); 
        }
    }

    protected function setCookie($data=NULL) {
        $duration = time()-60;
        if (!empty($data)) {
            $data = json_encode($data);
            $duration = time()+86400;
        }
        setcookie('cjo_oauth', $data, $duration);
    }

    private function generateLink($provider) {
        return  '<a class="cjo_oauth '.$provider.'" href="./?'.self::generateGetKey($provider).'='.$provider.'">'.$provider.'</a>';
    }
    
    protected static function generateGetKey($provider) {
        global $CJO;
        return md5('cjo_outh_connect'.$CJO['INSTNAME'].$provider.session_id());
    }
    
    protected function getHttpHost() {
        if ($this->trustForwarded && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return $_SERVER['HTTP_X_FORWARDED_HOST'];
        }
        return $_SERVER['HTTP_HOST'];
    }

    protected function getHttpProtocol() {
        if ($this->trustForwarded && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                return 'https';
            }
            return 'http';
        }
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
            return 'https';
        }
        return 'http';
    }

    private static function getProviders(){
        global $CJO;
        return cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['providers']);
    }

    /**
     * Get the base domain used for the cookie.
     */
    protected function getBaseDomain() {
        // The base domain is stored in the metadata cookie if not we fallback
        // to the current hostname
        $metadata = $this->getMetadataCookie();
        if (array_key_exists('base_domain', $metadata) && !empty($metadata['base_domain'])) {
            return trim($metadata['base_domain'], '.');
        }
        return $this->getHttpHost();
    }

    protected function getCurrentUrl() {
        $protocol = $this->getHttpProtocol() . '://';
        $host = $this->getHttpHost();
        $currentUrl = $protocol . $host . $_SERVER['REQUEST_URI'];
        $parts = parse_url($currentUrl);

        $query = '';
        if (!empty($parts['query'])) {
            // drop known fb params
            $params = explode('&', $parts['query']);
            $retained_params = array();
            foreach ($params as $param) {
                $retained_params[] = $param;
            }

            if (!empty($retained_params)) {
                $query = '?' . implode($retained_params, '&');
            }
        }

        // use port if non default
        $port = isset($parts['port']) && (($protocol === 'http://' && $parts['port'] !== 80) || ($protocol === 'https://' && $parts['port'] !== 443)) ? ':' . $parts['port'] : '';

        // rebuild
        return $protocol . $parts['host'] . $port . $parts['path'] . $query;
    }

    /**
     * Returns true if and only if the key or key/value pair should
     * be retained as part of the query string.  This amounts to
     * a brute-force search of the very small list of Facebook-specific
     * params that should be stripped out.
     *
     * @param string $param A key or key/value pair within a URL's query (e.g.
     *                     'foo=a', 'foo=', or 'foo'.
     *
     * @return boolean
     */
    protected function shouldRetainParam($param) {
        if (!isset(self::$DROP_QUERY_PARAMS)) return true;
        foreach (self::$DROP_QUERY_PARAMS as $drop_query_param) {
            if (strpos($param, $drop_query_param . '=') === 0) {
                return false;
            }
        }

        return true;
    }
}
