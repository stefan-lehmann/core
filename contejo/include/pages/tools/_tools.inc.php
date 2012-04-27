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
 * @version     2.6.0
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

$mypage        = $cur_page['page'];
$oid           = cjo_request('oid', 'int');
$function      = cjo_request('function', 'string');
$mode          = cjo_request('mode', 'string');

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('templates',
					'rights' => array('tools[templates]'),
					'important' => true));

$subpages->addPage( array('modules',
					'rights' => array('tools[modules]'),
					'important' => true));

$subpages->addPage( array('actions',
					'rights' => array('tools[actions]'),
					'important' => true));

$subpages->addPage( array('ctypes',
					'rights' => array('tools[ctypes]'),
					'important' => true));

$subpages->addPage( array('catgroups', 'rights' => array('tools[catgroups]'),
					'important' => true));

$subpages->addPage( array('langs', 'rights' => array('tools[langs]'),
					'important' => true));

if ($CJO['LOGIN_ENABLED'])
    $subpages->addPage(array('types',
					'rights' => array('tools[types]'),
					'important' => true));

require_once $subpages->getPage();

/**
 * Do not delete translate values for i18n collection!
 * [translate: title_templates]
 * [translate: title_modules]
 * [translate: title_actions]
 * [translate: title_ctypes]
 * [translate: title_catgroups]
 * [translate: title_langs]
 * [translate: title_types]
 */
