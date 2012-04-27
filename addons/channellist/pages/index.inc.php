<?php
/**
 * This file is part of CONTEJO ADDON - CHANNEL LIST
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_channel_list
 * @subpackage 	pages
 * @version   	SVN: $Id: index.inc.php 1084 2010-11-24 12:37:42Z s_lehmann $
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

$mypage    = 'channellist';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('tv', 'title' => $I18N_23->msg('tv_channels')));
$subpages->addPage( array('radio', 'title' => $I18N_23->msg('radio_channels')));
$subpages->addPage( array('packages', 'title' => $I18N_23->msg('packages')));
$subpages->addPage( array('settings', 'title' => $I18N_23->msg('settings')));

require_once $CJO['INCLUDE_PATH'].'/layout/top.php';
require_once $subpages->getPage();
require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';