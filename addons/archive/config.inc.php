<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  archive
 * @version     2.6.0
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

$mypage = 'archive'; // only for this file

$I18N_28 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '28';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_28->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'archive[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage] 	    = '1.0';
$CJO['ADDON']['compat'][$mypage] 	    = '2.5.1';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/'.$mypage;


$CJO['ADDON']['settings'][$mypage]['SETTINGS_FILE'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php";

// required table
if (!defined('TBL_28_ARCHIVE_ARTICLES')) {
    define('TBL_28_ARCHIVE_ARTICLES', $CJO['TABLE_PREFIX'].'28_archive_articles');
}

if (!defined('TBL_28_ARCHIVE_ARTICLES_SLICE')) {
    define('TBL_28_ARCHIVE_ARTICLES_SLICE', $CJO['TABLE_PREFIX'].'28_archive_article_slice');
}

if (!defined('TBL_28_ARCHIVE_PATHS')) {
    define('TBL_28_ARCHIVE_PATHS', $CJO['TABLE_PREFIX'].'28_archive_paths');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;

require_once $CJO['ADDON']['settings'][$mypage]['SETTINGS_FILE'];
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.archive.inc.php';


if ($CJO['CONTEJO']) {
    cjoExtension::registerExtension('USER_LOGGED_IN', 'cjoArchive::archiveArticles');  
}