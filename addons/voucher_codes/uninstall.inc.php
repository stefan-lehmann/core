<?php
/**
 * This file is part of CONTEJO ADDON - VOUCHER CODES
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_voucher_codes
 * @version   	SVN: $Id: uninstall.inc.php 1054 2010-11-17 13:59:09Z s_lehmann $
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

$mypage = "voucher_codes";

if (cjoInstall::installDump(dirname(__FILE__).'/uninstall.sql')) {
    $CJO['ADDON']['install'][$mypage] = 0;
}