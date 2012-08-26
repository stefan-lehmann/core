<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  voucher_codes
 * @version     2.7.x
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

global $CJO;
$mypage = "voucher_codes";

$I18N_17 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // create lang obj for this addon

$CJO['ADDON']['addon_id'][$mypage] 	    = '17';
$CJO['ADDON']['page'][$mypage] 		    = $mypage;
$CJO['ADDON']['name'][$mypage] 		    = $I18N_17->msg($mypage);
$CJO['ADDON']['perm'][$mypage] 		    = 'voucher_codes[]';
$CJO['ADDON']['autor'][$mypage] 		= 'Stefan Lehmann 2010';
$CJO['ADDON']['version'][$mypage] 	    = '0.3';
$CJO['ADDON']['compat'][$mypage]        = '2.2';
$CJO['ADDON']['supportpage'][$mypage] 	= 'http://contejo.com/addons/voucher_codes';

if (!defined('TBL_17_VOUCHER')) {
    define('TBL_17_VOUCHER', $CJO['TABLE_PREFIX'].'17_voucher');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;

// Include Funtions and Classes
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/functions/functions_vc_voucher.inc.php';
