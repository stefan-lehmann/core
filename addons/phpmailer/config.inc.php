<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  phpmailer
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


$mypage = "phpmailer";

$I18N_20 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // create lang obj for this addon

$CJO['ADDON']['addon_id'][$mypage] 	    = '20';
$CJO['ADDON']['page'][$mypage] 		    = $mypage;
$CJO['ADDON']['name'][$mypage] 		    = $I18N_20->msg($mypage);
$CJO['ADDON']['perm'][$mypage] 		    = 'phpmailer[]';
$CJO['ADDON']['author'][$mypage] 		= 'Stefan Lehmann 2009';
$CJO['ADDON']['version'][$mypage] 	    = '0.2';
$CJO['ADDON']['compat'][$mypage] 	    = '2.2';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/phpmailer';

if (!defined('TBL_20_MAIL_SETTINGS')) {
    define('TBL_20_MAIL_SETTINGS', $CJO['TABLE_PREFIX'].'20_mail_settings');
}
if (!defined('TBL_20_MAIL_ARCHIV')) {
    define('TBL_20_MAIL_ARCHIV', $CJO['TABLE_PREFIX'].'20_mail_archiv');
}

include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.phpmailer.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.cjo_phpmailer.inc.php";



?>