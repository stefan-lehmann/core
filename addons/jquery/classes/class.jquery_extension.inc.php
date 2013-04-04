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

    private static $addon  = "jquery";
    private static $jq_path = "/jquery/jquery/";

    public static function insertjQuery($params) {

    	$content = $params['subject'];
    	$path    = cjoUrl::addonAssets(self::$addon, cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/');
    	$suffix  = cjoAddon::getParameter('GZIP', self::$addon) ? '.gz' : '';

    	if (strpos($content, '<html') === false) return $content;

    	$jquery = str_replace(cjoUrl::addon(self::$addon,cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/'), 
                            cjoUrl::addonAssets(self::$addon,cjoAddon::getParameter('INCLUDE_PATH', self::$addon).'/'),
                            cjoAddon::getParameter('VERSION', self::$addon));

    	$js = "\r\n".'<script type="text/javascript">/* <![CDATA[ */ '.
    	      'var ARTICLE_ID = "'.cjoProp::getArticleId().'"; '.
              'var CLANG = ["'.cjoProp::getClang().'", "'.cjoProp::getClangIso().'", "'.cjoProp::getClangName().'"]; '. 	
              'var MEDIAFOLDER = "'.cjoUrl::media().'"; '.
              'var FRONTPAGE_PATH = "'.cjoUrl::frontend().'" ; '.
              'var JQUERY_ADDON_PATH = "'.cjoPath::addonAssets($addon, 'jquery').'";'.
              ' /* ]]> */</script>';

    	$css = '';
    	    
	    if (cjoAddon::getParameter('JS_FILES', self::$addon)) {
	        $filename = $path.cjoAddon::getParameter('COMBINED_NAME', self::$addon).'.js';
    	    if (!file_exists($filename)) cjojQuery::combineJsFiles();
    	    $js .= "\r\n".'<script type="text/javascript" src="'.$filename.$suffix.'"></script>';
	    }
	    
	    if ($CJO['ADDON']['settings'][self::$addon]['CSS_FILES']) {
            $filename = $path.cjoAddon::getParameter('COMBINED_NAME', self::$addon).'.css';
    	    if (!file_exists($filename)) cjojQuery::combineCssFiles();
    	    $css = "\r\n".'<link type="text/css" rel="stylesheet" href="'.$filename.$suffix.'" />';
	    }

    	$content = preg_replace('/<head[^>]*>/i', '$0'.$js . $css."\r\n", $content, 1);

    	return $content;
    }
}

if (cjoProp::isBackend()) return false;

cjoExtension::registerExtension('OUTPUT_FILTER', 'cjojQueryExtension::insertjQuery');
