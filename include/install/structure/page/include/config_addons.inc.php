<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.6.0
 *
 * @author      Stefan Lehmann <sl@contejo.com>
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
 
// ----------------- addons
if (isset($CJO['ADDON']['status'])) {
	unset($CJO['ADDON']['status']);
}

if (isset($CJO['ADDON']['menu'])) {
	unset($CJO['ADDON']['menu']);
}

// ----------------- DONT EDIT BELOW THIS
// --- DYN

$CJO['ADDON']['install']['developer'] = 1;
$CJO['ADDON']['status']['developer'] = 1;
$CJO['ADDON']['menu']['developer'] = "0";

$CJO['ADDON']['install']['html5video'] = 0;
$CJO['ADDON']['status']['html5video'] = 0;
$CJO['ADDON']['menu']['html5video'] = "0";

$CJO['ADDON']['install']['image_processor'] = 0;
$CJO['ADDON']['status']['image_processor'] = 0;
$CJO['ADDON']['menu']['image_processor'] = "0";

$CJO['ADDON']['install']['import_export'] = 0;
$CJO['ADDON']['status']['import_export'] = 0;
$CJO['ADDON']['menu']['import_export'] = "0";

$CJO['ADDON']['install']['log'] = 0;
$CJO['ADDON']['status']['log'] = 0;
$CJO['ADDON']['menu']['log'] = "0";

$CJO['ADDON']['install']['opf_lang'] = 0;
$CJO['ADDON']['status']['opf_lang'] = 0;
$CJO['ADDON']['menu']['opf_lang'] = 0;

$CJO['ADDON']['install']['phpmailer'] = 1;
$CJO['ADDON']['status']['phpmailer'] = 1;
$CJO['ADDON']['menu']['phpmailer'] = "addons";

$CJO['ADDON']['install']['wymeditor'] = 0;
$CJO['ADDON']['status']['wymeditor'] = 0;
$CJO['ADDON']['menu']['wymeditor'] = "0";

// --- /DYN
// ----------------- /DONT EDIT BELOW THIS

foreach($CJO['ADDON']['status'] as $addonname=>$status){

	$addon_path = $CJO['ADDON_PATH'].'/'.$addonname.'/config.inc.php';
	if (!file_exists($addon_path)) continue;
	require_once $addon_path;
    if (!$status || !isset($CJO['ADDON']['perm'][$addonname])) continue;

	foreach(cjoAssistance::toArray($CJO['ADDON']['perm'][$addonname]) as $key => $perm){
		if(empty($perm)) continue;
		$title = (is_numeric($key)) ? $CJO['ADDON']['name'][$addonname] : $key;
		$CJO['PERMADDON'][$title] = $perm;
	}
}

unset($mypage);

// ----- all addons configs included
cjoExtension::registerExtensionPoint('ADDONS_INCLUDED');
