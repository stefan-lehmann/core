<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@contejo.com>
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

/**
 * cjoMedia class
 *
 * The cjoSubpages class includes the main functionality
 * to handle media in the backend.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoRewrite {

    /**
     * Generates an url friendly name.
     * @param string $name
     * @return string
     * @access public
     */
    public static function parseArticleName($name) {
        $name = str_replace(array(' ', ' -- ',' - ','.'), '-', trim($name));
        $name = html_entity_decode($name);
        $name = cjo_specialchars($name);     
        $name = preg_replace("/[^a-zA-Z\-0-9]/", "", $name);
        $name = preg_replace('/-{1,}/', '-', $name);   
        return $name;
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
     * 		$url = cjoRewrite::getUrl(10, 0, array("order" => "123", "name" => "horst"),'hash');
     * 		- OR -
     * 		$url = $article->getUrl(10, 0, "order=123&name=horst",'hash');
     *
     * 		RETURNS:
     *   		./index.php?article_id=10&clang=0&order=123&name=horst#hash
     *
     * 		or if mod_rewrite support is activated:
     *
     *   		./The_Article_Name.10.0.html?order=123&name=horst#hash
     *
     * @access public
     */
    public static function getUrl($article_id = 0, $clang = false, $query_params = array(), $hash = '') {

    	global $CJO;
        
    	$params         = self::setServerUri();
    	$params['path'] = self::setServerPath();
    	$redirect       = false;
   	
    	if (!empty($hash) && $hash != '#') {
    	    $params['hash'] = (strpos($hash,'#') === false) ? '#'.$hash : $hash;
    	}  	 	
    	
    	if ($article_id == null) {
    	    $params['path'] .= $CJO['CONTEJO'] ? '' : 'core/';
    	    $params['path'] .= 'index.php';
    	}
    	else {

    	    if (!empty($query_params) && !is_array($query_params)) {
        	    $query_params = str_replace(array('?','&amp;'), array('','&'), $query_params);
        	    parse_str($query_params, $query_params);
        	}
        	
        	$query_params['clang']      = ($clang === false || !isset($CJO['CLANG'][$clang])) 
        	                            ? $CJO['CUR_CLANG'] : $clang;
    	
        	$query_params['article_id'] = (strlen($article_id) == 0 || $article_id == 0) 
        	                            ? cjo_get('article_id','cjo-article-id', $CJO['START_ARTICLE_ID'])
        	                            : $article_id;

    		$article = OOArticle :: getArticleById($query_params['article_id'], $query_params['clang']);
    		if (!OOArticle::isValid($article)) return false;
    		    
            if (!empty($CJO['MODREWRITE']['LINK_REDIRECT']) && 
                is_numeric($article->getRedirect()) &&
                $article->getRedirect() > 0) {
                    
    	        $redirect = OOArticle::getArticleById($article->getRedirect(), $clang);
    	        if (OOArticle::isValid($redirect)) {
    	            $article = & $redirect;
    	            $query_params['article_id'] = $article->getId();
    	        }
    	    }
    		$params['name'] = cjoRewrite::parseArticleName($article->getName());
    		
        	if (empty($params['name'])) $params['name'] = 'article';
            
            $params['deeplink'] = !empty($CJO['MODREWRITE']['DEEPLINK_FORMAT']) ? $CJO['MODREWRITE']['DEEPLINK_FORMAT'] : '%name%.%article_id%.%clang%.html';
            
        	$params['query'] = $query_params;
            
        	$temp = cjoExtension::registerExtensionPoint('GENERATE_URL', $params);
        	
        	if (!empty($temp) && is_array($temp)) $params = $temp;
    
        	if (!empty($params['hash'])) {
        	    $hash = $params['hash'];
        	    unset($params['hash']);
        	} else {
        	    $hash = '';
        	}
    
        	if ($CJO['MODREWRITE']['ENABLED']) {
        	    $replace = array();
        	    $replace['%name%']        = $params['name'];
        	    $replace['%article_id%']  = $params['query']['article_id'];
        	    $replace['%clang%']       = $params['query']['clang'];
                $replace['%clang_iso%']   = $CJO['CLANG_ISO'][$params['query']['clang']];  
                $replace['%clang_name%']  = $CJO['CLANG'][$params['query']['clang']]; 
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
        }

    	if ($redirect === false &&
    	    !empty($CJO['MODREWRITE']['LINK_REDIRECT']) &&
    	    OOArticle::isValid($article) && 
            preg_match('/\D/',$article->getRedirect())) {
            $url = $article->getRedirect();
        }
        else {
            $params['query'] = is_array($params['query']) && !empty($params['query']) ? http_build_query($params['query']) : null;
            $url = http_build_url('',$params,HTTP_URL_STRIP_AUTH | HTTP_URL_JOIN_PATH | HTTP_URL_JOIN_QUERY | HTTP_URL_STRIP_FRAGMENT).$hash;
        }
        $url = preg_replace('/(?<!:)\/{2,}/','/', $url);
        
        return cjoExtension::registerExtensionPoint('GENERATED_URL', array('subject' => $url));
    }

    /**
     * Generates the rewrite url.
     * @param array $params
     * @return string
     * @access public
     */
    public static function setRewriteUrl($params){

        global $CJO;

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
        $url .= cjoRewrite::parseArticleName($params['name']).'.'.$params['id'].'.'.$params['clang'].'.html';
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

        global $CJO;
        
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
        global $CJO;
        $path = pathinfo(cjo_server('PHP_SELF','string'),PATHINFO_DIRNAME);
        $path = str_replace('\\','/',$path);
        $path = cjoAssistance::toArray($path,'/');
        $length = count($path)-1;
        
        if ($path[$length] == str_replace($CJO['HTDOCS_PATH'], '', $CJO['BACKEND_PATH'])) {
            $temp = array_pop($path);
        }
        
        return preg_replace('!\/{1,}!', '/', '/'.implode('/', $path).'/');
    } 
}
