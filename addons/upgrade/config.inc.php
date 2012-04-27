<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  upgrade
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

$mypage = 'upgrade'; // only for this file

$I18N_29 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '28';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_29->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'upgrade[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage] 	    = '1.0';
$CJO['ADDON']['compat'][$mypage] 	    = '2.5.1';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/'.$mypage;
