<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  developer
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

$mypage = "developer";

$CJO['ADDON']['addon_id'][$mypage] 	= '2';
$CJO['ADDON']['page'][$mypage] 		= $mypage;
$CJO['ADDON']['name'][$mypage] 		= 'Developer';
$CJO['ADDON']['perm'][$mypage] 		= 'developer[]';
$CJO['ADDON']['author'][$mypage] 	= 'Stefan Lehmann 2009';
$CJO['ADDON']['version'][$mypage] 	= '0.3';
$CJO['ADDON']['compat'][$mypage] 	= '2.2';
$CJO['ADDON']['support'][$mypage] 	= 'http://contejo.com/addons/developer';

$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/settings.inc.php';
$CJO['ADDON']['settings'][$mypage]['STATUS'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/status.inc.php';
if ($CJO['SETUP'] === true) return;
if ($CJO['ADDON']['status'][$mypage] != 1) return;

// Include Funtions and Classes
require_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.live_edit.inc.php';
require_once $CJO['ADDON']['settings'][$mypage]['STATUS'];

// ERWEITERTE EINSTELLUNGEN ///////////////////////////////////////////////////////////////////////////////////////////

cjoExtension::registerExtension('SQL_IMPORTED', 'liveEdit::deleteLiveEdit');

// Check for Modules Updates in Files
if ($CJO['ADDON']['settings'][$mypage]['modules'] == 'true' &&
	!$CJO['SETUP'] && (!isset($function) || $function != 'import')) {

	$liveEdit = new liveEdit();
	$articles_overwrite = $page == 'addon' && $addonname == 'developer' && $activate=='1' ? true : false;
	$liveEdit->writeModuleFiles($articles_overwrite);

	if ($page != 'developer' && (!isset($action) || $action != 'update')) {
	    
		if (!$CJO['CONTEJO']) {
		    if (cjo_session('UID', 'bool', false, md5($CJO['INSTNAME']))) {
		        $CJO['ADDON']['settings'][$mypage]['regenerate'] = $liveEdit->syncModules();
			    cjoExtension::registerExtension('OUTPUT_FILTER', 'liveEdit::regenerateArticlesByJS');
		    }
		}
		else {
    	    $CJO['ADDON']['settings'][$mypage]['regenerate'] = $liveEdit->syncModules();
			$liveEdit = new liveEdit();
			$liveEdit->regenerateArticlesByModultypId($CJO['ADDON']['settings'][$mypage]['regenerate']);
		}
	}
}

// Check for Template Updates in Files
if ($CJO['ADDON']['settings'][$mypage]['templates'] == 'true' &&
	(!isset($function) || $function != 'import')) {

	$liveEdit = new liveEdit();
	$templates_overwrite = $page == 'addon' && $addonname == 'developer' && $activate == '1' ? true : false;
	$liveEdit->writeTemplateFiles($templates_overwrite);
	$liveEdit->syncTemplates();
}

// Developer-CSS asu geschütztem Verzeichnis einbinden
if ($CJO['CONTEJO'] && cjo_request('page', 'string') != 'setup') {
	cjoExtension::registerExtension('OUTPUT_FILTER', 'liveEdit::insertCss');
}

