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

$mypage  = 'event_calendar';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');
$group_id  = cjo_request('group_id', 'int', 0);

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('events', 'title' => $I18N_16->msg('subtitle_events')));
$subpages->addPage( array('attributes', 'title' => $I18N_16->msg('subtitle_attributes')));
$subpages->addPage( array('imexport', 'title' => $I18N_16->msg('subtitle_import_export')));
$subpages->addPage( array('settings'));


require_once $CJO['INCLUDE_PATH'].'/layout/top.php';
require_once $subpages->getPage();
require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';

$CJO['SEL_LANG']->get();