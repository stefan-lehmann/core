<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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

$mypage = 'comments'; // only for this file

$I18N_7 = new i18n($CJO['LANG'], $CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // create lang obj for this addon

$CJO['ADDON']['addon_id'][$mypage]      = '7';
$CJO['ADDON']['page'][$mypage]          = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage]          = $I18N_7->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage]          = 'comments[]';
$CJO['ADDON']['author'][$mypage]        = 'Stefan Lehmann 2011';
$CJO['ADDON']['version'][$mypage]       = '1.1';
$CJO['ADDON']['compat'][$mypage]        = '2.2.3';
$CJO['ADDON']['support'][$mypage]       = 'http://contejo.com/addons/comments';

if (!defined('TBL_COMMENTS')) {
    define('TBL_COMMENTS', $CJO['TABLE_PREFIX'].'7_comments');
}
if (!defined('TBL_COMMENTS_B8')) {
    define('TBL_COMMENTS_B8', $CJO['TABLE_PREFIX'].'7_comments_b8');
}
if (!defined('TBL_COMMENTS_CONFIG')) {
    define('TBL_COMMENTS_CONFIG', $CJO['TABLE_PREFIX'].'7_comments_config');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;

$CJO['ADDON']['settings'][$mypage]['form_articles'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/form_articles.inc.php';
$CJO['ADDON']['settings'][$mypage]['comments_js']   = '../'.$CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/comments.js';
$CJO['ADDON']['settings'][$mypage]['html_template'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/list.default.'.$CJO['SETTINGS']['TMPL_FILE_TYPE'];
$CJO['ADDON']['settings'][$mypage]['b8_path'] 		= $CJO['ADDON_PATH'].'/'.$mypage.'/b8-0.4.4';
$CJO['ADDON']['settings'][$mypage]['b8']            = $CJO['ADDON']['settings'][$mypage]['b8_path'].'/b8.php';

require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.co_comments.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.co_config.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.co_template.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.co_extension.inc.php';

include_once $CJO['ADDON']['settings'][$mypage]['form_articles'];

// ERWEITERTE EINSTELLUNGEN ///////////////////////////////////////////////////////////////////////////////////////////

if (!$CJO['CONTEJO']) {
 	cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoCommentsExtension::replaceVars');
}
else {
	cjoExtension::registerExtension('CJO_FORM_COMMENTS_SETTINGS_SAVE', 'cjoGenerate::generateAll');
	cjoExtension::registerExtension('CLANG_ADDED', 'cjoCommentsConfig::copyConfig');
	cjoExtension::registerExtension('CLANG_DELETED', 'cjoCommentsConfig::deleteConfigByLang');
}
