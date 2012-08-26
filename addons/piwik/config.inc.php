<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  piwik
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

$mypage = "piwik"; // only for this file

$I18N_25 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '25';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_25->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'piwik[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage] 	    = '1';
$CJO['ADDON']['compat'][$mypage] 	    = '2.3';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/'.$mypage;
$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php"; // settings file

if ($CJO['ADDON']['status'][$mypage] != 1) return;

require_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.piwik_extension.inc.php';

if (!$CJO['CONTEJO'] && strpos(cjo_server('REQUEST_URI', 'string'), '/cjo_piwik/') !== false) {
    if (strpos(cjo_server('REQUEST_URI', 'string'), $CJO['ADDON']['settings'][$mypage]['EMAIL_CAMPAIGN_FILENAME']) !== false) {
        cjoPiwikExtension::redirectEmailPixelTracking();
    } else {
        cjoPiwikExtension::setSessionTrackRequest(); 
    }
}