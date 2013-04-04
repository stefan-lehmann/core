<?php

/**
 * Utility class to generate absolute paths
 *
 * @author gharlan
 *
 * @package redaxo5
 */
class cjoUrl {
    
    static protected $base, $backend;
    
    static public function init($htdocs, $backend) {
        self::$base = $htdocs;
        self::$backend = 'core';
    }
    
    /**
     * Returns a base path
     */
    static public function base($file = '') {
        return str_replace(array('/', '\\'), '/', self::$base.$file);
    }

    /**
     * Returns the path to the frontend
     */
    static public function frontend($file = '') {
        return self::base($file);
    }

    /**
     * Returns the path to the backend
     */
    static public function backend($file = '') {
        return self::base(self::$backend.'/'.$file);
    }

    /**
     * Returns the path to the media-folder
     */
    static public function media($file = '') {
        return self::base(cjoProp::get('MEDIAFOLDER').'/'.$file);
    }
    
    /**
     * Returns the path to the media-folder
     */
    static public function mediaCache($file = '') {
        return self::cache(self::media(cjoAddon::getParameter('cachedir', 'image_processor')).$file);
    }

    /**
     * Returns the path to the cache folder of the core
     */
    static public function cache($file = '') {
        return self::base('cache/'.$file);
    }
    
    /**
     * Returns the path to the page folder 
     */
    static public function page($file = '') {
        return self::base(cjoProp::get('FRONTPAGE_PATH').'/'.$file);
    }
    
    /**
     * Returns the base path to the folder of the given addon
     */
    static public function addon($addon, $file = '') {
        return self::base(cjoProp::get('ADDON_PATH').'/'.$addon.'/'.$file);
    }
    
    /**
     * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @see assets()
     */
    static public function addonAssets($addon, $file = '') {
        return self::base(cjoProp::get('ADDON_CONFIG_PATH').'/'.$addon.'/'.$file);
    }
    
    /**
     * Returns the path to the individual upload folder of the user
     */
    static public function uploads($file = '') {
        
        if (!cjoProp::isBackend() || !cjoProp::getUser()) return false;
        
        $path = self::base(cjoProp::get('UPLOADFOLDER').'/'.cjo_url_friendly_string(cjoProp::getUser()->getValue('login')));

        if (is_dir($path) || mkdir($path, cjoProp::getDirPerm())) return $file != '' ? $path.'/'.$file : $path;
    }
    
    /**
     * Converts a relative path to an absolute
     *
     * @param string $relPath The relative path
     *
     * @return string Absolute path
     */
    static public function absoluteUrl($relPath) {
        $relPath = cjoPath::absolute($relPath);
        $relPath = self::setServerPath().$relPath;
        return $relPath;
    }

    /**
     * Generates an url friendly name.
     * @param string $name
     * @return string
     * @access public
     */
    public static function parseArticleName($name) {
        return cjo_url_friendly_string($name);
    }

    /**
     * Returns an url for linking to an article.
     * The generated url respects the setting for mod_rewrite
     * support!
     *
     * If you pass an associative array for $params,
     * then these parameters will be attached to the URL.
     *
     * @param int $article_id
     * @param int|boolean $clang
     * @param string|array $query_params parameters for query string
     * @param string $hash
     * @return string
     *
     * @example
     *
     *      $url = cjoUrl::getUrl(10, 0, array("order" => "123", "name" => "horst"),'hash');
     *      - OR -
     *      $url = $article->getUrl(10, 0, "order=123&name=horst",'hash');
     *
     *      RETURNS:
     *          ./index.php?article_id=10&clang=0&order=123&name=horst#hash
     *
     *      or if mod_rewrite support is activated:
     *
     *          ./The_Article_Name.10.0.html?order=123&name=horst#hash
     *
     * @access public
     */
    public static function getUrl($article_id = 0, $clang = false, $query_params = '', $hash = '') {
        
        $params = self::setServerUri();
        $params['path'] = self::setServerPath(); 
        $redirect = false;

        if (!empty($hash) && $hash != '#') {
            $params['hash'] = (strpos($hash,'#') === false) ? '#'.$hash : $hash;
        }       
        
        if ($article_id == null) {
            $params['path'] .= cjoProp::isBackend() ? '' : cjoProp::get('BACKEND_PATH').'/';
            $params['path'] .= 'index.php';
            $url = $params['path'];
        }
        else {

            if (!empty($query_params) && !is_array($query_params)) {
                $query_params = str_replace(array('?','&amp;'), array('','&'), $query_params);
                parse_str($query_params, $query_params);
            }
            
            $query_params['clang']      = ($clang === false || !cjoProp::getClangName($clang)) 
                                        ? cjoProp::getClang() : $clang;
        
            $query_params['article_id'] = (strlen($article_id) == 0 || $article_id == 0) 
                                        ? cjo_request('article_id','cjo-article-id', cjoProp::get('START_ARTICLE_ID'))
                                        : $article_id;

            $article = OOArticle::getArticleById($query_params['article_id'], $query_params['clang']);
            if (!OOArticle::isValid($article)) return false;
                
            if (cjoProp::get('MODREWRITE|LINK_REDIRECT') && 
                is_numeric($article->getRedirect()) &&
                $article->getRedirect() > 0) {
                    
                $redirect = OOArticle::getArticleById($article->getRedirect(), $clang);
                if (OOArticle::isValid($redirect)) {
                    $article = & $redirect;
                    $query_params['article_id'] = $article->getId();
                }
            }
            $params['name'] = cjo_url_friendly_string($article->getName());
            
            if (empty($params['name'])) $params['name'] = 'article';
            
            $params['deeplink'] = cjoProp::get('MODREWRITE|DEEPLINK_FORMAT') ? cjoProp::get('MODREWRITE|DEEPLINK_FORMAT') : '%name%.%article_id%.%clang%.html';
            
            $params['query'] = $query_params;
            
            $temp = cjoExtension::registerExtensionPoint('GENERATE_URL', $params);
            
            if (!empty($temp) && is_array($temp)) $params = $temp;
    
            if (!empty($params['hash'])) {
                $hash = $params['hash'];
                unset($params['hash']);
            } else {
                $hash = '';
            }
    
            if (cjoProp::get('MODREWRITE|ENABLED')) {
                $replace = array();
                $replace['%name%']        = $params['name'];
                $replace['%article_id%']  = $params['query']['article_id'];
                $replace['%clang%']       = $params['query']['clang'];
                $replace['%clang_iso%']   = cjoProp::getClangIso($params['query']['clang']);  
                $replace['%clang_name%']  = cjoProp::getClangName($params['query']['clang']); ; 
                $replace['%clang_sname%'] = substr($replace['%clang_name%'], 0, 2);  
                
                $params['path'] .= str_replace(array_keys($replace),$replace, $params['deeplink']);
            }
            else {
                $params['path'] .= 'index.php';  
            }
            
            if (isset($params['deeplink']))            unset($params['deeplink']);
            if (isset($params['name']))                unset($params['name']);
            if (isset($params['query']['article_id'])) unset($params['query']['article_id']);
            if (isset($params['query']['clang']))      unset($params['query']['clang']);
    
            if ($redirect === false &&
                cjoProp::get('MODREWRITE|LINK_REDIRECT') && 
                preg_match('/\D/',$article->getRedirect())) {
                $url = $article->getRedirect();
            }
            else {
                $params['query'] = is_array($params['query']) && !empty($params['query']) ? http_build_query($params['query']) : null;
                $url = http_build_url('',$params,HTTP_URL_STRIP_AUTH | HTTP_URL_JOIN_PATH | HTTP_URL_JOIN_QUERY | HTTP_URL_STRIP_FRAGMENT).$hash;
            }
            $url = preg_replace('/(?<!:)\/{2,}/','/', $url);
        }
        return cjoExtension::registerExtensionPoint('GENERATED_URL', array('subject' => $url));
    }

    /**
     * Generates the rewrite url.
     * @param array $params
     * @return string
     * @access public
     */
    public static function setRewriteUrl($params){

        if ($params['params'] != '') {
            $params['params'] = preg_replace('/&(?!amp;)|&amp;/', '?', $params['params'], 1);
        } 
        
        $params['hash'] = trim($params['hash']);
        
        if (!empty($params['hash']) && substr($params['hash'],0,1) != '#'){
            $params['hash'] = '#'.$params['hash'];
        }       
        
        $url  = self::setServerUri(false,false);
        $url .= self::setServerPath();
        $url  = preg_replace('/core\/$/', '', $url);
        $url .= cjo_url_friendly_string($params['name']).'.'.$params['id'].'.'.$params['clang'].'.html';
        $url .= $params['params'];
        $url .= !empty($params['hash']) ? $params['hash'] : '';

        return preg_replace('/(?<!:)\/{2,}/', '/', $url, -1);  
    }
    
    /**
     * Returns the current Server Uri
     * @param bool $forward
     * @return string
     * @access public
     */
    public static function setServerUri($forward = true, $return_array = true){
        
        $output = array();
        
        $output['scheme'] = (cjo_server('HTTPS','bool') ||
                            ($forward && cjo_server('HTTP_X_FORWARDED_PROTO','string') == 'https')) 
                          ? 'https' : 'http';

        $output['host'] = $forward && cjo_server('HTTP_X_FORWARDED_SERVER','bool') 
                        ? cjo_server('HTTP_X_FORWARDED_SERVER','string') 
                        : cjo_server('HTTP_HOST','string');         
        
        if ($return_array) {
            return $output;
        }

        return http_build_url('',$output,HTTP_URL_STRIP_AUTH | HTTP_URL_JOIN_PATH | HTTP_URL_JOIN_QUERY | HTTP_URL_STRIP_FRAGMENT);
    }
    
    /**
     * Returns the current Path
     * @return string
     * @access public
     */
    public static function setServerPath() {

        $path = cjoAssistance::toArray(pathinfo(cjo_server('PHP_SELF','string'),PATHINFO_DIRNAME),'/');
        $length = count($path)-1;
        
        if ($path[$length] == str_replace(cjoProp::get('HTDOCS_PATH'), '', cjoProp::get('BACKEND_PATH'))) {
            $temp = array_pop($path);
        }
        return '/'.implode('/', $path).'/';
    } 

    
    public static function redirectBE($params = array()) {

        if (is_array($params)) {
            $url = self::createBEUrl($params);
        }
        return self::redirect($url);
    }

    /**
     * Generates a link.
     * @param string $link_text
     * @param array $local_params
     * @param array $global_params
     * @param string $link_tags
     * @return string
     * @access public
     */
    public static function createBELink($link_text, $local_params = array (), $global_params = array (), $link_tags = '') {

        if (count($local_params) == 0 || $link_text == '')
            return $link_text;

        if (!empty($link_tags))
            $link_tags = ' '.$link_tags;

        return sprintf('<a href="%s"%s>%s</a>', self::createBEUrl($local_params, $global_params), $link_tags, $link_text);
    }

    /**
     * Generates an url.
     * @param array $local_params
     * @param array $global_params
     * @param string $ampersand
     * @return string
     * @access public
     */
    public static function createBEUrl($local_params = array (), $global_params = array (), $ampersand = '&') {
        return self::createCjoUrl('index.php', $local_params, $global_params, $ampersand);
    }
    
    /**
     * Generates a link.
     * @param string $text
     * @param array $params
     * @param string $tags
     * @return string
     * @access public
     */
    public static function createAjaxLink($text, $params = array (), $tags = '') {

        if (count($params) == 0 || 
            trim($text) == '' || 
            empty($params['function']))
            return $text;

        if (!empty($tags))
            $tags = ' '.$tags;
            
        if (strpos($tags,'class="') === false) {
            $tags .= ' class="cjo_ajax"';
        }
        else {
            $tags = str_replace('class="', 'class=" cjo_ajax ', $tags);
        }

        return sprintf('<a href="%s"%s>%s</a>', self::createAjaxUrl($params), $tags, $text);
    }
        
    /**
     * Generates an url.
     * @param array $params
     * @param string $ampersand
     * @return string
     * @access public
     */
    public static function createAjaxUrl($params = array (), $ampersand = '&') {
        
        if (!$params['function']) return false;
        
        cjoProp::isValidType($params['function'], 'callable');
                
        $function = $params['function'];
        unset($params['function']);

        return self::createCjoUrl('ajax.php', $params, array('function'=>$function), $ampersand);
    }    

    /**
     * Generates an url.
     * @param array $local_params
     * @param array $global_params
     * @param string $ampersand
     * @return string
     * @access public
     */
    public static function createCjoUrl($file, $local_params = array (), $global_params = array (), $ampersand = '&') {

        if (count($local_params) == 0)  return $value;
        if (count($global_params) == 0) $global_params = cjoUrl::getDefaultGlobalParams();
        
        $_params = array_merge($global_params, $local_params);

        if (isset($local_params['oid']) && $local_params['oid']===false){
            unset($_params['oid']);
        }
        if (isset($local_params['function']) && $local_params['function']===false){
            unset($_params['function']);
        }        

        $query_string = '?';
        $hash = '';

        foreach ($_params as $_name => $_value) {
            if ($_value == '' && $_value != '0')
                continue;
            if ($_name != '#') {
                if (strpos($query_string, $ampersand.$_name.'=') === false) {
                    $query_string .= (strpos($query_string, '?') === false) ? '?' : $ampersand;
                    $query_string .= $_name.'='.urlencode($_value);
                } else {
                    $query_string = preg_replace('/('.$_name.')=(.*?(?=['.$ampersand.'|#|\s]))/', '$1='.urlencode($_value), $query_string);
                }
            } else {
                $hash = $_name.$_value;
            }
        }

        $query_string = str_replace(array('?'.$ampersand, ' '), array('?', ''), $query_string);

        if (!empty($tags))
            $tags = ' '. $tags;

        return $file.$query_string.$hash;
    }

    public static function redirectFE($article_id = 0, $clang = false, $params = '', $hash_string = '') {
        return self::redirect(cjoUrl::getUrl($article_id, $clang, $params, $hash_string));
    }

    public static function redirect($url) {
        if (empty($url))
            return false;
        // Alle OBs schließen
        while (ob_get_level() > 0) { ob_end_clean();
        };

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.str_replace('&amp;', '&', $url));
        exit();
    }

    public static function redirectAchor($params = false) {

        if (!is_array($params)) {
            parse_str(cjo_server('QUERY_STRING', 'string'), $params);
        }
        if (empty($params['cjo_anchor']))
            return false;

        $anchor = $params['cjo_anchor'];
        $params['cjo_anchor'] = null;

        cjoUrl::redirectFE(cjoProp::getArticleId(), cjoProp::getClang(), $params, $anchor);
    }
    
    /**
     * Gibt die Standard-Parameter zurück, die man benötigt um die aktuelle Seite
     * wieder aufzurufen
     *
     * @return array Array von Parametern
     * @access protected
     */
    public static function getDefaultGlobalParams() {

        $params = array();
        $params_in = array('page' => 'string', 'mypage' => 'string', 'subpage' => 'string',
                           'clang' => 'cjo-clang-id', 'function' => 'string', 'func' => 'string',
                           'oid' => 'int', 'order_col' => 'string', 'order_type' => 'string',
                           'search_key' => 'string', 'search_column' => 'string',
                           'stepping' => 'int', 'next' => 'int', 'mode' => 'string');
    
        if (!cjoProp::isBackend()) {
            $params_in['article_id'] = 'cjo-article-id';
            $params_in['ctype'] = 'cjo-ctype-id';
        }
    
        foreach($params_in as $key=>$vartype) {
            $var = $key;
            if (!isset($$var) || (empty($$var) && $$var !== 0)) $$var = cjo_request($key, $vartype);
            if (!empty($$var) || $$var === 0) $params[$key] = $$var;
        }
    
        $params['page'] = (cjoProp::getPage()) ? cjoProp::getPage() : $params['page'];
        $params['subpage'] = (cjoProp::getSubpage()) ? cjoProp::getSubpage() : $params['subpage'];
        return $params;
    }
}
