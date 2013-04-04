<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
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

$CJO['FILE_CONFIG_DB'] 				= $CJO['FILE_CONFIG_PATH']."/config_databases.inc.php";
$CJO['FILE_CONFIG_ADDONS'] 			= $CJO['FILE_CONFIG_PATH']."/config_addons.inc.php";
$CJO['FILE_CONFIG_CTYPES'] 			= $CJO['FILE_CONFIG_PATH']."/config_ctypes.inc.php";
$CJO['FILE_CONFIG_LANGS'] 			= $CJO['FILE_CONFIG_PATH']."/config_clangs.inc.php";

$mypage  = $cur_page['page'];
$subpage = cjo_request('subpage', 'string');
$lang    = cjo_request('lang', 'string', 'de');

if (cjo_post('cjoform_back_button', 'string') == $I18N->msg('button_back')) {
	$subpage = cjo_request('prev_subpage', 'string');
}

if ($subpage == '')  $subpage = 'step1';

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage(array($subpage, 'title' => 'SETUP', 
						 'important' => true,
						  'query_str' => 'page=setup&subpage='.$subpage.'&lang='.$lang));

require_once $subpages->getPage();

/**
 * Do not delete translate values for cjoI18N collection!
 * [translate: title_setup]
 * [translate: label_setup_step2_title]
 * [translate: label_setup_step3_title]
 * [translate: label_setup_step4_title]
 * [translate: label_setup_step5_title]
 * [translate: label_setup_step6_title]
 * [translate: label_setup_step7_title]
 * [translate: label_setup_step8_title]
 */