<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  import_export
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

$mypage  = 'import_export';

$function       = cjo_request('function', 'string');
$import_file    = cjo_request('import_file', 'string');
$export_file    = cjo_request('export_file', 'string');
$download       = cjo_request('download', 'string');
$export_include = cjo_request('export_include', 'array');

require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.cjo_import_export.inc.php';

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('import_export_db', 'title' => $I18N_3->msg('import_export_db')));
//$subpages->addPage( array('import_export_files', 'title' => $I18N_3->msg('import_export_files')));

require_once $CJO['INCLUDE_PATH'].'/layout/top.php';
require_once $subpages->getPage();
require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';