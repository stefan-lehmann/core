<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  search
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
$mypage = 'search'; // only for this file

$I18N_13 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');

$CJO['ADDON']['addon_id'][$mypage] 	= '13';
$CJO['ADDON']['page'][$mypage] 		= $mypage;
$CJO['ADDON']['name'][$mypage] 		= $I18N_13->msg($mypage);   // name
$CJO['ADDON']['perm'][$mypage] 		= 'search[]';
$CJO['ADDON']['author'][$mypage] 	= 'Stefan Lehmann 2010';
$CJO['ADDON']['version'][$mypage] 	= '0.2';
$CJO['ADDON']['compat'][$mypage] 	= '2.2';
$CJO['ADDON']['support'][$mypage] 	= 'http://contejo.com/addons/search';

$CJO['ADDON']['settings'][$mypage]['settings'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage."/settings.inc.php";

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON_PATH']."/".$mypage.'/classes/class.cjo_search.inc.php';
include_once $CJO['ADDON']['settings'][$mypage]['settings'];

if ($CJO['ADDON']['settings'][$mypage]['SETUP'] == 'true') {

    if (!$CJO['CONTEJO']) {
        $CJO['ADDON']['status'][$mypage] = 0;
        return;
    }
    if ($page != $mypage && $subpage != $mypage) {
        $url = cjoAssistance::createBEUrl(array('page' => $mypage, 'subpage'=>'settings'));
        cjoMessage::addWarning($I18N_13->msg('msg_err_configure_settings', $url));
    }
}

cjoExtension::registerExtension('SQL_IMPORTED', 'cjoSearch::addFulltextIndex');

if (!$CJO['CONTEJO']) {
	cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSearch::replaceVars');
}
