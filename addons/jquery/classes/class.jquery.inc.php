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

    private static $addon  = "jquery";
    
    public static function copyjQueryFiles($params=array()) {
        
        $path  = cjoUrl::addonAssets(self::$addon, cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/');
        $files = array('js' => array(), 'css' => array());

    	$jq_version = cjo_post('VERSION', 'string');
        $jq_plugins = cjo_post('PLUGINS','array');
    	
        if (file_exists($path) && !cjoAssistance::deleteDir($path,true)) {
    		cjoMessage::addError(cjoAddon::translate(11,"err_create_plugin_dir", $path));
    	}
    
    	if (!@mkdir($path, cjoProp::getDirPerm())) {
    		cjoMessage::addError(cjoAddon::translate(11,"err_create_plugin_dir", $path));
    	}
    
    	if (!file_exists($jq_version)) {
    		cjoMessage::addError(cjoAddon::translate(11,"err_jquery_version_is_missing", $jq_version));
    		return false;
    	}
        
        $dest = str_replace(cjoUrl::addon(self::$addon,cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/'), 
                            cjoUrl::addonAssets(self::$addon,cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/'),
                            $jq_version);
    
    	if (!@copy($jq_version, $dest)) {
    		cjoMessage::addError(cjoAddon::translate(11,"err_copy_version", $jq_version, $dest));
    	}
    	else {
    	    @chmod($dest, cjoProp::getFilePerm());
    	    $files['js'][] = str_replace($path,'', $dest);
    	}

    	foreach($jq_plugins as $key=>$jq_plugin) {
    
    		if (cjoMessage::hasErrors()) break;
    		if (!file_exists($jq_plugin)) continue;

    	    if (is_dir($jq_plugin)) {
                if ($dh = opendir($jq_plugin)) {
                    while (($file = readdir($dh)) !== false) {
                        $ext = pathinfo($file,PATHINFO_EXTENSION);
                        if (substr($file, 0, 1) != '_' && $ext == 'js') {
                            $files['js'][] = $file;
                        }
                        if (substr($file, 0, 1) != '_' && $ext == 'css') {
                            $files['css'][] = $file;
                        }
                    }
                    closedir($dh);
                }
            }
    		
            $dest = str_replace(cjoUrl::addon(self::$addon,cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/'), 
                                cjoUrl::addonAssets(self::$addon,cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/'), 
                                $jq_plugin);
    
    		if (!cjoFile::copyDir($jq_plugin, $path)) {
    			cjoMessage::addError(cjoAddon::translate(11,"err_copy_plugin_dir", $jq_plugin, $dest));
    		}
    	}

    	cjoAddon::setParameter('JS_FILES', implode('|',$files['js']), self::$addon);
        cjoAddon::setParameter('CSS_FILES', implode('|',$files['css']), self::$addon);       

    	self::combineJsFiles($files['js']);
    	self::combineCssFiles($files['css']);
    }
    
    public static function combineJsFiles($files = NULL) {
    	
    	$content  = '';
        $path     = cjoUrl::addonAssets(self::$addon, cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/');    
        $filename = $path.'/'.cjoAddon::getParameter('COMBINED_NAME', self::$addon).'.js';
    	    	
    	if ($files == NULL)    	
    	    $files = cjoAssistance::toArray(cjoAddon::getParameter('JS_FILES',self::$addon));	

    	foreach($files as $file) {
    	    if (!cjoFile::isReadable($path.$file)) continue;
    	    $content .= "\r\n\r\n /********************\r\n  *\r\n  *".$path.$file."\r\n  *\r\n  *********************/\r\n\r\n ";
    	    $content .= file_get_contents($path.$file);
    	}
        file_put_contents($filename, $content); 
	    @chmod($filename, cjoProp::getFilePerm());          
        file_put_contents($filename.'.gz', gzencode($content,9));   
	    @chmod($filename.'.gz', cjoProp::getFilePerm());        
   }

    public static function combineCssFiles($files = NULL) {
    	
    	$content  = '';
        $path     = cjoUrl::addonAssets(self::$addon, cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/');   	
    	$filename = $path.'/'.cjoAddon::getParameter('COMBINED_NAME', self::$addon).'.css';

    	if ($files == NULL)
            $files = cjoAssistance::toArray(cjoAddon::getParameter('CSS_FILES',self::$addon));   ;	

    	foreach($files as $file) {
    	    
    	    if (!cjoFile::isReadable($path.$file)) continue;
    	    
    	    $content .= "\r\n\r\n /*    ".$path.$file."    */\r\n ";
    	    $content .= file_get_contents($path.$file);
    	}
    	
        file_put_contents($filename, $content); 
	    @chmod($filename, cjoProp::getFilePerm());          
        file_put_contents($filename.'.gz', gzencode($content,9));   
	    @chmod($filename.'.gz', cjoProp::getFilePerm());          
   }  
}
