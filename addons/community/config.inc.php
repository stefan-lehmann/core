<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
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

$mypage = 'community';

$I18N_10 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');

$CJO['ADDON']['addon_id'][$mypage] 	    = '10';
$CJO['ADDON']['page'][$mypage] 		    = $mypage;
$CJO['ADDON']['name'][$mypage] 		    = $I18N_10->msg($mypage);
$CJO['ADDON']['perm'][$mypage] 		    = 'community[]';
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann 2010';
$CJO['ADDON']['version'][$mypage] 	    = '0.4';
$CJO['ADDON']['compat'][$mypage] 	    = '2.2';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/community';

if (!defined('TBL_COMMUNITY_USER')) {
    define('TBL_COMMUNITY_USER', $CJO['TABLE_PREFIX'].'10_community_user');
}
if (!defined('TBL_COMMUNITY_GROUPS')) {
    define('TBL_COMMUNITY_GROUPS', $CJO['TABLE_PREFIX'].'10_community_groups');
}
if (!defined('TBL_COMMUNITY_UG')) {
    define('TBL_COMMUNITY_UG', $CJO['TABLE_PREFIX'].'10_community_ug');
}
if (!defined('TBL_COMMUNITY_ARCHIV')) {
    define('TBL_COMMUNITY_ARCHIV', $CJO['TABLE_PREFIX'].'10_community_archiv');
}
if (!defined('TBL_COMMUNITY_PREPARED')) {
    define('TBL_COMMUNITY_PREPARED', $CJO['TABLE_PREFIX'].'10_community_prepared');
}
if (!defined('TBL_COMMUNITY_BOUNCE')) {
    define('TBL_COMMUNITY_BOUNCE', $CJO['TABLE_PREFIX'].'10_community_bounce');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;

$CJO['ADDON']['settings'][$mypage]['SETTINGS']   = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/settings.inc.php';
$CJO['ADDON']['settings'][$mypage]['CLANG_CONF'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/'.$clang.'.clang.inc.php';
$CJO['ADDON']['settings'][$mypage]['BLANK_IMG']  = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/blank.gif';

include_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
include_once $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];

if ($CJO['ADDON']['settings'][$mypage]['SETUP'] == 'true') {

    if (!$CJO['CONTEJO']) {
        $CJO['ADDON']['status'][$mypage] = 0;
        return;
    }
    if ($subpage != 'settings') {
        $url = cjoAssistance::createBEUrl(array('page' => $mypage, 'subpage'=>'settings'));
        cjoMessage::addWarning($I18N_10->msg('msg_err_configure_settings', $url));
    }
}

require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_groupletter.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_extension.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_groups.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_user_groups.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_user.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_imexport.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_template.inc.php';
include_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cm_bounce.inc.php'; 

if ($CJO['ADDON']['settings'][$mypage]['LOGOUT'] == $article_id) {
	$CJO['LOGOUT'] = true;
}

if (!$CJO['ADDON']['settings'][$mypage]['BOUNCE']) {
    cjoCommunityBounce::updateUserTable();
}
   
