<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  event_calendar
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

$mypage = "event_calendar";
$error = '';
$CJO['ADDON']['install'][$mypage] = true;

require_once dirname(__FILE__).'/config.inc.php';

$source[] = $CJO['INCLUDE_PATH']."/addons/".$mypage."/setup/list.default.html";
$source[] = $CJO['INCLUDE_PATH']."/addons/".$mypage."/setup/filter.default.html";

$dest[] = $CJO['ADDON_PATH'].'/'.$mypage.'/list.default.'.$CJO['TMPL_FILE_TYPE'];
$dest[] = $CJO['ADDON_PATH'].'/'.$mypage.'.$mypage."/filter.default.'.$CJO['TMPL_FILE_TYPE'];

foreach ($source as $key=>$value){
	if (!@copy($source[$key], $dest[$key])){
		$error .= 'Unable to copy<br/>"'.$source[$key].'" to<br/>"'.$dest[$key].'"!<br/>';
	}
}

if ($error == '' && cjoInstall::installDump(dirname(__FILE__).'/install.sql')) {
    $CJO['ADDON']['install'][$mypage] = 1;
}

$CJO['ADDON']['installmsg'][$mypage] = $error;