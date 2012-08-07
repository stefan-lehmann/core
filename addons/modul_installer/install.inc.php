<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  modul_installer
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

require_once dirname(__FILE__).'/config.inc.php';

global $I18N_22;

$mypage = 'modul_installer';

$install = new cjoInstall($mypage);

if ($install->installResource()) {
    if (!cjoMessage::hasErrors())
        cjoMessage::addSuccess($I18N_22->msg('msg_modules_installed'));
}

$CJO['ADDON']['installmsg'][$mypage] = $I18N_22->msg('msg_not_installable');
$CJO['ADDON']['install'][$mypage] = 0;
