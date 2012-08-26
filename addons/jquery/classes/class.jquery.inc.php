<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  jquery
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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

class cjojQuery {

    private static $mypage  = "jquery";
    private static $jq_path = "/jquery/jquery/";
    
    public static function copyjQueryFiles() {

        global $CJO, $I18N_11;
        
        $jq_incl_path  = $CJO['ADDON_CONFIG_PATH'].self::$jq_path;
        $files = array();
    
    	$jq_version = cjo_post('VERSION', 'string');
        $jq_plugins = cjo_post('PLUGINS','array');
    	
        if (file_exists($jq_incl_path) && !cjoAssistance::deleteDir($jq_incl_path,true)) {
    		cjoMessage::addError($I18N_11->msg("err_create_plugin_dir", $jq_incl_path));
    	}
    
    	if (!@mkdir($jq_incl_path, $CJO['FILEPERM'])) {
    		cjoMessage::addError($I18N_11->msg("err_create_plugin_dir", $jq_incl_path));
    	}
    
    	if (!file_exists($jq_version)) {
    		cjoMessage::addError($I18N_11->msg("err_jquery_version_is_missing", $jq_version));
    		return false;
    	}
    
    	$dest = str_replace($CJO['ADDON_PATH'], $CJO['ADDON_CONFIG_PATH'], $jq_version);
    
    	if (!@copy($jq_version, $dest)) {
    		cjoMessage::addError($I18N_11->msg("err_copy_version", $jq_version, $dest));
    	}
    	else {
    	    @chmod($dest, $CJO['FILEPERM']);
    	    $files['js'][] = str_replace($jq_incl_path,'', $dest);
    	}
    	
    	foreach($jq_plugins as $key=>$jq_plugin) {
    
    		if (cjoMessage::hasErrors()) break;
    		if (!file_exists($jq_plugin)) continue;

    	    if (is_dir($jq_plugin)) {
                if ($dh = opendir($jq_plugin)) {
                    while (($file = readdir($dh)) !== false) {
                        if (substr($file, 0, 1) != '_' && end(explode('.', $file)) == 'js') {
                            $files['js'][] = $file;
                        }
                        if (substr($file, 0, 1) != '_' && end(explode('.', $file)) == 'css') {
                            $files['css'][] = $file;
                        }
                    }
                    closedir($dh);
                }
            }
    		
    		$dest = str_replace($CJO['ADDON_PATH'], $CJO['ADDON_CONFIG_PATH'], $jq_plugin);
    
    		if (!cjoAssistance::copyDir($jq_plugin, $jq_incl_path)) {
    			cjoMessage::addError($I18N_11->msg("err_copy_plugin_dir", $jq_plugin, $dest));
    		}
    	}
    	
    	$CJO['ADDON']['settings'][self::$mypage]['JS_FILES'] = $files['js'];
    	$CJO['ADDON']['settings'][self::$mypage]['CSS_FILES'] = $files['css'];    
    	$_POST['JS_FILES'] = $files['js'];
    	$_POST['CSS_FILES'] = $files['css'];     	

    	cjoGenerate::updateSettingsFile($CJO['ADDON']['settings'][self::$mypage]['SETTINGS']);

    	self::combineJsFiles($files['js']);
    	self::combineCssFiles($files['css']);
    }
    
    public static function combineJsFiles($files = NULL) {
    	
    	global $CJO;
        
    	include $CJO['ADDON']['settings'][self::$mypage]['SETTINGS'];
    	
    	$content  = '';
        $path     = $CJO['ADDON_CONFIG_PATH'].self::$jq_path;
    	$filename = $path.$CJO['ADDON']['settings'][self::$mypage]['COMBINED_NAME'].'.js';
    	    	
    	if ($files == NULL)    	
    	    $files = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['JS_FILES']);	

    	foreach($files as $file) {
    	    if (!cjoAssistance::isReadable($path.$file)) continue;
    	    $content .= "\r\n\r\n /********************\r\n  *\r\n  *".$path.$file."\r\n  *\r\n  *********************/\r\n\r\n ";
    	    $content .= file_get_contents($path.$file);
    	}
        file_put_contents($filename, $content); 
	    @chmod($filename, $CJO['FILEPERM']);          
        file_put_contents($filename.'.gz', gzencode($content,9));   
	    @chmod($filename.'.gz', $CJO['FILEPERM']);        
   }

    public static function combineCssFiles($files = NULL) {
    	
    	global $CJO;
        
    	include $CJO['ADDON']['settings'][self::$mypage]['SETTINGS'];
    	
    	$content  = '';
        $path     = $CJO['ADDON_CONFIG_PATH'].self::$jq_path;    	
    	$filename = $path.$CJO['ADDON']['settings'][self::$mypage]['COMBINED_NAME'].'.css';

    	if ($files == NULL)
    	    $files    = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['CSS_FILES']);	

    	foreach($files as $file) {
    	    
    	    if (!cjoAssistance::isReadable($path.$file)) continue;
    	    
    	    $content .= "\r\n\r\n /*    ".$path.$file."    */\r\n ";
    	    $content .= file_get_contents($path.$file);
    	}
    	
        file_put_contents($filename, $content); 
	    @chmod($filename, $CJO['FILEPERM']);          
        file_put_contents($filename.'.gz', gzencode($content,9));   
	    @chmod($filename.'.gz', $CJO['FILEPERM']);          
   }  
}
