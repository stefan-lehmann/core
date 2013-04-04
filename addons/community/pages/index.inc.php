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

$mypage  = 'community';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');
$group_id  = cjo_request('group_id', 'int', 0);


cjoSubPages::addPages(array(
                        array('user', 
                              'title' => cjoAddon::translate(10,'subtitle_user'), 
                              'params' => array('page'=>'community', 'subpage' => 'user','group_id'=>$group_id)),
                        array('groups', 
                              'title' => cjoAddon::translate(10,'subtitle_groups'), 
                              'params' => array('page'=>'community', 'subpage' => 'groups','group_id'=>$group_id)),
                        array('groupletter', 
                              'title' => cjoAddon::translate(10,'subtitle_groupletter')),
                        array('archiv', 
                              'title' => cjoAddon::translate(10,'subtitle_archiv')),
                        array('imexport', 
                              'title' => cjoAddon::translate(10,'import_export')),
                        array('settings', 
                              'title' => cjoAddon::translate(10,'subtitle_settings'))
                      ));

if (cjoProp::get('LOGIN_ENABLED'))  
cjoSubPages::addPage(array('types', 
                           'rights' => array('specials[types]'), 
                           'params' => array('page'=>'tools', 'subpage' => 'types')));

require_once cjoSubPages::getPagePath();

if ($subpage == 'show') return false;

cjoSelectLang::get();
