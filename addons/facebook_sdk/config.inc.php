<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  facebook_sdk
 * @version     2.7.2
 *
 * @author      Stefan Lehmann <sl@contejo.com> based on Markus Lorch's (http://www.it-kult.de) REDAXO AddOn
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

$mypage = 'facebook_sdk'; // only for this file

$CJO['ADDON']['addon_id'][$mypage]      = '32';
$CJO['ADDON']['page'][$mypage]          = '$mypage'; // pagename/foldername
$CJO['ADDON']['name'][$mypage]          = 'Facebook SDK';  // name
$CJO['ADDON']['perm'][$mypage]          = 'facebook_sdk[]'; // permission
$CJO['ADDON']['author'][$mypage]        = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage]       = '1.0';
$CJO['ADDON']['compat'][$mypage]        = '2.7.2';
$CJO['ADDON']['support'][$mypage]       = 'http://contejo.com/addons/'.$mypage;
$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php"; // settings file

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/facebook.inc.php";

if (!$CJO['CONTEJO']) {
    $facebook = new Facebook();
}