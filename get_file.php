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

error_reporting(E_ALL ^ E_NOTICE);
// ----- caching start für output filter

ob_start();
unset($CJO);

$CJO['HTDOCS_PATH'] = '../';
$CJO['CONTEJO']     = false;
$CJO['NOFUNCTIONS'] = true;
$cur_page           = array();

require_once "include/master.inc.php";

$filename  = rawurldecode(cjo_get('file', 'string', $filename, false));

if (parse_url($filename,PHP_URL_SCHEME)) return false;

$extension = substr(strrchr($filename, "."), 1);

if ($extension == 'css' || $extension == 'js' || $extension == 'sql' || $extension == 'gz') {
    cjoClientCache::sendFile($filename, false, 'backend');
}