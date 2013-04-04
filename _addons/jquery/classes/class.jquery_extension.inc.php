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

class cjojQueryExtension {

    private static $mypage  = "jquery";
    private static $jq_path = "/jquery/jquery/";

    public static function insertjQuery($params) {

        global $CJO;

    	$content = $params['subject'];
    	$path    = $CJO['ADDON_CONFIG_PATH'].self::$jq_path;
    	$suffix  = !empty($CJO['ADDON']['settings'][self::$mypage]['GZIP']) ? '.gz' : '';

    	if (strpos($content, '<html') === false) return $content;

    	$jquery = str_replace('.'.$CJO['ADDON_PATH'],
    						  $CJO['ADDON_CONFIG_PATH'],
    						  $CJO['ADDON']['settings'][self::$mypage]['VERSION']);

    	$js = "\r\n".'<script type="text/javascript">/* <![CDATA[ */ '.
    	      'var ARTICLE_ID = "'.$CJO['ARTICLE_ID'].'"; '.
              'var CLANG = ["'.$CJO['CUR_CLANG'].'", "'.$CJO['CLANG_ISO'][$CJO['CUR_CLANG']].'", "'.$CJO['CLANG'][$CJO['CUR_CLANG']].'"]; '. 	
              'var MEDIAFOLDER = "'.$CJO['MEDIAFOLDER'].'"; '.
              'var FRONTPAGE_PATH = "'.$CJO['FRONTPAGE_PATH'].'" ; '.
              'var JQUERY_ADDON_PATH = "'.$CJO['ADDON']['settings'][self::$mypage]['JQ_INCL'].'";'.
              ' /* ]]> */</script>';

    	$css = '';
    	    
	    if ($CJO['ADDON']['settings'][self::$mypage]['JS_FILES']) {
	        $filename = $path.$CJO['ADDON']['settings'][self::$mypage]['COMBINED_NAME'].'.js';
    	    if (!file_exists($filename)) cjojQuery::combineJsFiles();
    	    $js .= "\r\n".'<script type="text/javascript" src="'.$filename.$suffix.'"></script>';
	    }
	    
	    if ($CJO['ADDON']['settings'][self::$mypage]['CSS_FILES']) {
	        $filename = $path.$CJO['ADDON']['settings'][self::$mypage]['COMBINED_NAME'].'.css';  
    	    if (!file_exists($filename)) cjojQuery::combineCssFiles();
    	    $css = "\r\n".'<link type="text/css" rel="stylesheet" href="'.$filename.$suffix.'" />';
	    }

    	$content = preg_replace('/<head[^>]*>/i', '$0'.$js . $css."\r\n", $content, 1);

    	return $content;
    }
}

if ($CJO['CONTEJO']) return false;

cjoExtension::registerExtension('OUTPUT_FILTER', 'cjojQueryExtension::insertjQuery');
