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

$CJO['ADDON']['settings'][$mypage]['MAIL_ACCOUNT'] = "1";
$CJO['ADDON']['settings'][$mypage]['SUBJECT'] = "Benutzer-Registrierung";
$CJO['ADDON']['settings'][$mypage]['GENDER_TYPES'] = "m=Herr|w=Frau";
$CJO['ADDON']['settings'][$mypage]['VERIFY_REGISTRATION'] = "1";
$CJO['ADDON']['settings'][$mypage]['ACTIVATION_MSG'] = "Sehr geehrte/r %title% %name%,

Ihr Benutzerkonto für die Webseite XXX wurde freigeschaltet.

Mit freundlichen Grüßen


";
$CJO['ADDON']['settings'][$mypage]['SEND_PASSWORD_MSG'] = "Sehr geehrte/r %title% %name%,

Ihr Benutzerkonto für die Webseite XXX wurde angelegt.

Ihr zentraler Benutzername lautet: %username%

Ihr zentrales, temporäres Passwort lautet: %password%

Aus Sicherheitsgründen bitten wir das Passwort nach dem erstmaligen Login zu ändern.


Mit freundlichen Grüßen

";
// --- /DYN
