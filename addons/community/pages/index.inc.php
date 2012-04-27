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
 * @version     2.6.0
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

$mypage  = 'community';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');
$group_id  = cjo_request('group_id', 'int', 0);

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('user', 'title' => $I18N_10->msg('subtitle_user'), 'query_str' => 'page=community&subpage=user&clang='.$clang.'&group_id='.$group_id));
$subpages->addPage( array('groups', 'title' => $I18N_10->msg('subtitle_groups'), 'query_str' => 'page=community&subpage=groups&clang='.$clang.'&group_id='.$group_id));
$subpages->addPage( array('groupletter', 'title' => $I18N_10->msg('subtitle_groupletter')));
$subpages->addPage( array('archiv', 'title' => $I18N_10->msg('subtitle_archiv')));
$subpages->addPage( array('imexport', 'title' => $I18N_10->msg('import_export')));
$subpages->addPage( array('settings', 'title' => $I18N_10->msg('subtitle_settings')));

if ($CJO['LOGIN_ENABLED'])
    $subpages->addPage( array('types',
						'rights' => array('specials[types]'),
						'query_str' => 'page=tools&subpage=types'));

require_once $CJO['INCLUDE_PATH'].'/layout/top.php';
require_once $subpages->getPage();
require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';

if ($subpage == 'show') return false;

$CJO['SEL_LANG']->get();
