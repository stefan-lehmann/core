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
 * cjoProcess class
 *
 * The cjoProcess class contains processing methods.
 *
 * @package 	contejo
 * @subpackage 	core
 */

class cjoProcess {
    
     static private $initialized = false;  
                                            
     public static function init() {
         
        if (self::$initialized) return false;

        cjoExtension::registerExtensionPoint('CJO_PROCESS_STARTING'); 
        self::cjoMagicQuotes();
        self::unregisterGlobals();

        if (cjoProp::isSetup() && !cjoProp::isBackend()) {
            header('Location: core/index.php');
            exit();
        } 

        if (cjo_get('process_image', 'bool')) {
            cjoGenerate::processImage();
        }
        
        if (cjo_get('cjo_anchor', 'bool')) {
            cjoUrl::redirectAchor();
        }

        self::getAdjustPath();  
        self::setFavicon();
        self::setCurrentClangId();
        self::setCurrentArticleId();

        $temp = array();
        foreach(cjoProp::get('VARIABLES') as $key => $value) {
            $temp[$key] = new $value;
        }
        cjoProp::set('VARIABLES',$temp);
        
        cjoExtension::registerExtension('OUTPUT_FILTER','cjoI18N::searchAndTranslate');    
        cjoExtension::registerExtensionPoint('CJO_PROCESS_STARTED'); 
        
        self::$initialized = true;
    }      
                                  
    private static function unregisterGlobals() {

        if (!ini_get('register_globals') ) return;
    
        if (isset($_REQUEST['GLOBALS']) ) die('GLOBALS overwrite attempt detected');
    
        // Variables that shouldn't be unset
        $noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');
    
        $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
        foreach ( $input as $k => $v )
            if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
                $GLOBALS[$k] = NULL;
                unset($GLOBALS[$k]);
            }
    }      
    
    public static function setCurrentClangId() {
        cjoProp::set('CUR_CLANG', cjo_request('clang', 'cjo-clang-id', cjoProp::get('START_CLANG_ID')));
    }
    
    private static function setCurrentArticleId() {

        if (cjoProp::isBackend()) {
            cjoProp::set('ARTICLE_ID', cjo_request('article_id', 'cjo-article-id'));
            return;
        }  
                   
        if (!cjo_request('article_id', 'bool')) {
            
            cjoExtension::registerExtensionPoint('GET_ARTICLE_ID');   
            if (cjoProp::getArticleId()) {
                cjoProp::set('ARTICLE_ID', cjoProp::get('START_ARTICLE_ID'));
            }   
            else {
                return;
            }
        }
        else {
            cjoProp::set('ARTICLE_ID', cjo_request('article_id','cjo-article-id', cjoProp::get('NOTFOUND_ARTICLE_ID')));
        } 
        
        if (!cjo_request('article_id','cjo-article-id') || 
            !cjoProp::getClangName(cjo_request('clang','cjo-clang-id', false))) {
            cjoUrl::redirectFE(cjoProp::getArticleId(), cjo_request('clang','cjo-clang-id', false));
        }
    }       
                       
    public static function getAdjustPath() {
        
        $adjust_path = '';
        
        if (cjoProp::isBackend()) {
            cjoProp::set('ADJUST_PATH', $adjust_path);
            return;
        } 
    
        $script_uri  = cjo_server('SCRIPT_NAME','string');
        $request_uri = cjo_server('REQUEST_URI','string');
        $uri_info    = pathinfo($request_uri);
        $script_info = pathinfo($script_uri);
        
        $script_path = $script_info['dirname'];
        $uri_path    = (empty($uri_info['extension']) || substr($request_uri,-1) == '/') 
                     ? $_SERVER['REQUEST_URI'] 
                     : $uri_info['dirname'];
         
        $script_path = preg_replace('/\/$/','',$script_path);
        $uri_path = preg_replace('/\/$/','',$uri_path);
         
        if (!empty($script_path) && strpos($uri_path, $script_path) === false) {
            cjoProp::set('ADJUST_PATH', $adjust_path);
            return;
        }
        
        cjoProp::set('VIRTUAL_PATH', str_replace($script_path,'',$uri_path));
        
        $offset  = count(cjoAssistance::toArray(cjoProp::get('VIRTUAL_PATH'),'/'));
    
        if ($offset < 1) {
            cjoProp::set('ADJUST_PATH', $adjust_path);
            return;
        } 
        
        for ($i=0;$i < $offset;$i++) {
            $adjust_path .= '../';
        }

        cjoProp::set('ADJUST_PATH', $adjust_path);
    }
                              
    private static function setFavicon(){

        if (!cjoProp::isBackend() &&
            cjo_server('HTTP_HOST','string') == 'localhost' &&
            !in_array(cjo_server('HTTP_HOST','string'), cjoProp::get('LOCALHOST'))) {
            cjoProp::set('FAVICON', 'favicon.ico');
            return;
        }
        cjoProp::set('FAVICON', 'favicon_local.ico');
        return;
    }      
    
    private static function cjoMagicQuotes() {
        
        global $_GET, $_POST, $_REQUEST;
  
        if ((function_exists("get_magic_quotes_gpc") && !get_magic_quotes_gpc()) ||
            (ini_get('magic_quotes_sybase') == '' &&
            (strtolower(ini_get('magic_quotes_sybase')) == "off"))){
            
        
            if (is_array($_GET)){
                self::addSlashesOnArray($_GET);
                foreach($_GET as $Akey => $AVal){
                    $$Akey = $AVal;
                }
            }
        
            if (is_array($_POST)){
                self::addSlashesOnArray($_POST);
                foreach($_POST as $Akey => $AVal){
                    $$Akey = $AVal;
                }
            }
        
            if (is_array($_REQUEST)){
                self::addSlashesOnArray($_REQUEST);
                    foreach($_REQUEST as $Akey => $AVal){
                    $$Akey = $AVal;
                }
            }
        }            
    }    
    
    
    private static function addSlashesOnArray(&$theArray) {
    
        if (is_array($theArray)){
            foreach($theArray as $Akey => $AVal){
                if (is_array($AVal)){
                    self::addSlashesOnArray($AVal);
                }
                else{
                    $theArray[$Akey] = addslashes($AVal);
                }
            }
            reset($theArray);
        }
    }  
}