<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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

$mypage      = 'comments';
$oid         = cjo_request('oid', 'int', '');
$function    = cjo_request('function', 'string');
$article_id  = cjo_request('article_id', 'cjo-article-id', 0);
$clang       = cjo_request('clang', 'cjo-clang-id', 0);

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('comments', 'title' => $I18N_7->msg('subtitle_comments_list')));
$subpages->addPage( array('settings', 'title' => $I18N_7->msg('subtitle_settings')));
$subpages->addPage( array('manage_tokens', 'title' => $I18N_7->msg('subtitle_manage_tokens')));

require_once $CJO['INCLUDE_PATH'].'/layout/top.php';
require_once $subpages->getPage();
require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';

if ($subpage == 'show') return false;

$CJO['SEL_LANG']->get();