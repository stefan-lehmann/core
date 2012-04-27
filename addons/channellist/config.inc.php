<?php
/**
 * This file is part of CONTEJO ADDON - CHANNEL LIST
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_channel_list
 * @subpackage 	config
 * @version   	SVN: $Id: config.inc.php 1037 2010-11-17 13:47:55Z s_lehmann $
 *
 * @author 		Stefan Lehmann <sl@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */
$mypage = "channellist"; // only for this file

$I18N_23 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '23';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_23->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'channellist[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann 2010';
$CJO['ADDON']['version'][$mypage] 	    = '1';
$CJO['ADDON']['compat'][$mypage] 	    = '2.1.3';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/channel_list';

if (!defined('TBL_TV_CHANNELS')) {
    define('TBL_TV_CHANNELS', $CJO['TABLE_PREFIX'].'23_tv_channels');
}
if (!defined('TBL_RADIO_CHANNELS')) {
    define('TBL_RADIO_CHANNELS', $CJO['TABLE_PREFIX'].'23_radio_channels');
}

if (!defined('TBL_CHANNELPACKAGES')) {
    define('TBL_CHANNELPACKAGES', $CJO['TABLE_PREFIX'].'23_packages');
}
if (!defined('TBL_FILM_LIST')) {
    define('TBL_FILM_LIST', $CJO['TABLE_PREFIX'].'23_filmlist');
}

$CJO['ADDON']['settings'][$mypage]['settings'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php"; // settings file

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON']['settings'][$mypage]['settings'];
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.channellist.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.filmlist.inc.php";

cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoChannelList::replaceVars');
cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoFilmList::replaceVars');