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

$mypage = "jquery"; // only for this file

$I18N_11 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '11';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_11->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'jquery[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann 2009';
$CJO['ADDON']['version'][$mypage] 	    = '0.4';
$CJO['ADDON']['compat'][$mypage] 	    = '2.2';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/jquery';

$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/settings.inc.php';
$CJO['ADDON']['settings'][$mypage]['JQ_SRC']   = $CJO['ADDON_PATH'].'/'.$mypage.'/jquery';
$CJO['ADDON']['settings'][$mypage]['JQ_INCL']  = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/jquery';

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.jquery.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.jquery_extension.inc.php";