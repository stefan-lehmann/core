<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
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

$mypage = 'community';

// --- DYN

$CJO['ADDON']['settings'][$mypage]['SETUP'] = "true";

$CJO['ADDON']['settings'][$mypage]['GL_SUBJECT'] = "";
$CJO['ADDON']['settings'][$mypage]['TEMPLATE'] = "1";
$CJO['ADDON']['settings'][$mypage]['SEND_TYPE'] = "html";
$CJO['ADDON']['settings'][$mypage]['DEFAULTLETTER'] = "1";
$CJO['ADDON']['settings'][$mypage]['TEXT'] = "";
$CJO['ADDON']['settings'][$mypage]['DEFAULT_GROUPS'] = "1";
$CJO['ADDON']['settings'][$mypage]['ATONCE'] = "100";

$CJO['ADDON']['settings'][$mypage]['TEST_GENDER'] = "";
$CJO['ADDON']['settings'][$mypage]['TEST_FIRSTNAME'] = "";
$CJO['ADDON']['settings'][$mypage]['TEST_NAME'] = "";
$CJO['ADDON']['settings'][$mypage]['TEST_EMAIL'] = "";

$CJO['ADDON']['settings'][$mypage]['NL_SIGNIN'] = "";
$CJO['ADDON']['settings'][$mypage]['NL_SIGNOUT'] = "";
$CJO['ADDON']['settings'][$mypage]['NL_CONFIRM'] = "";
$CJO['ADDON']['settings'][$mypage]['LOGIN_FORM'] = "";
$CJO['ADDON']['settings'][$mypage]['REGISTER_USER'] = "";
$CJO['ADDON']['settings'][$mypage]['ACTIVATE_USER'] = "";
$CJO['ADDON']['settings'][$mypage]['MANAGE_ACCOUNT'] = "";
$CJO['ADDON']['settings'][$mypage]['SEND_PASSWORD'] = "";
$CJO['ADDON']['settings'][$mypage]['LOGOUT'] = "";
$CJO['ADDON']['settings'][$mypage]['SAFE_DOWNLOAD'] = "";

$CJO['ADDON']['settings'][$mypage]['BOUNCE'] = "1";
$CJO['ADDON']['settings'][$mypage]['BOUNCE_MAIL_ACCOUNT'] = "1";

// --- /DYN

$CJO['ADDON']['settings'][$mypage]['MAXLOGINS'] = "5";