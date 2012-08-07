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
                                            
     public static function start() {
                         
        global $CJO;        

        cjoExtension::registerExtensionPoint('CJO_PROCESS_STARTS'); 
           
        new cjoMessage();
        
        self::unregisterGlobals();
        
        if ($CJO['SETUP']) {
            header('Location: core/index.php');
            exit();
        } 

        if (cjo_get('process_image', 'bool')) {
            cjoGenerate::processImage();
        }
        
        if (cjo_get('cjo_anchor', 'bool')) {
            cjoAssistance::redirectAchor();
        }

        self::getAdjustPath();  
        self::setFavicon();
        self::getCurrentArticleId();
        
        require_once $CJO['INCLUDE_PATH'].'/classes/var/class.cjo_vars.inc.php';
        
        foreach($CJO['VARIABLES'] as $key => $value) {
            require_once $CJO['INCLUDE_PATH']."/classes/var/class.". strtolower(str_replace('cjoVar', 'cjo_var_', $value)).".inc.php";
            $CJO['VARIABLES'][$key] = new $value;
        }
        
        cjoExtension::registerExtension('OUTPUT_FILTER','i18n::searchAndTranslate');    
        cjoExtension::registerExtensionPoint('CJO_PROCESS_STARTED'); 

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
    
    public static function getCurrentClangId() {
        global $CJO;  
        $CJO['CUR_CLANG'] = cjo_request('clang', 'cjo-clang-id', $CJO['START_CLANG_ID']);
    }
    
    private static function getCurrentArticleId() {
        
        global $CJO;  
        
        if ($CJO['CONTEJO']) {
            $CJO['ARTICLE_ID'] = cjo_request('article_id', 'cjo-article-id');
            return;
        }  
           
        if (!cjo_request('article_id', 'bool')) {
            
            cjoExtension::registerExtensionPoint('GET_ARTICLE_ID');   
            if (empty($CJO['ARTICLE_ID'])) {
                $CJO['ARTICLE_ID'] = $CJO['START_ARTICLE_ID'];
            }   
            else {
                return;
            }
        }
        else {
            $CJO['ARTICLE_ID'] = cjo_request('article_id','cjo-article-id', $CJO['NOTFOUND_ARTICLE_ID']);
        } 
        
        if (!cjo_request('article_id','cjo-article-id') || 
            !$CJO['CLANG'][cjo_request('clang','cjo-clang-id', -1)]) {
            cjoAssistance::redirectFE($CJO['ARTICLE_ID'], cjo_request('clang','cjo-clang-id', false));
        }
    }       
                       
    public static function getAdjustPath() {
    
        global $CJO;
        
        $adjust_path = '';
        
        if ($CJO['CONTEJO']) {
            $CJO['ADJUST_PATH'] = $adjust_path;
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
            $CJO['ADJUST_PATH'] = $adjust_path;
            return;
        }
        
        $CJO['VIRTUAL_PATH'] = str_replace($script_path,'',$uri_path);
        $offset  = count(cjoAssistance::toArray($CJO['VIRTUAL_PATH'],'/'));
    
        if ($offset < 1) {
            $CJO['ADJUST_PATH'] = $adjust_path;
            return;
        } 
        
        for ($i=0;$i < $offset;$i++) {
            $adjust_path .= '../';
        }

        $CJO['ADJUST_PATH'] = $adjust_path;
    }
                              
    private static function setFavicon(){
        global $CJO;
        if (!$CJO['CONTEJO'] ||
            (cjo_server('HTTP_HOST','string') != 'localhost' &&
             cjo_server('HTTP_HOST','string') != $CJO['LOCALHOST'])) {
            $CJO['FAVICON'] = 'favicon.ico';
            return;
        }
        $CJO['FAVICON'] = 'favicon_local.ico';
        return;
    }      

    public static function setIndividualUploadFolder() {

        global $CJO;
        
        if (!$CJO['CONTEJO'] || empty($CJO['USER']) || !is_object($CJO['USER'])) return false;
        
        $login = cjoRewrite::parseArticleName($CJO['USER']->getValue('login'));
        $CJO['UPLOADFOLDER'] .= '/'.$login;
    
        if (!is_dir($CJO['UPLOADFOLDER'])) {
            mkdir($CJO['UPLOADFOLDER']);
            @chmod($CJO['UPLOADFOLDER'],$CJO['FILEPERM']);
        }
    }              
            
}