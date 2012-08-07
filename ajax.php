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

error_reporting(E_ALL ^ E_NOTICE);
// ----- caching start für output filter

ob_start();

// ----------------- MAGIC QUOTES CHECK
require_once "./include/functions/function.cjo_mquotes.inc.php";

// ----- CJO UNSET
unset($CJO);

$CJO['HTDOCS_PATH'] = '../';
$CJO['CONTEJO']     = true;
$cur_page           = array();

require_once "include/master.inc.php";

$function = cjo_request('function', 'string', '');

if (!empty($_GET)){
	$vars = $_GET;
}
else if (!empty($_POST)){
	$vars = $_POST;
}
else {
	return 0;
}
unset($vars['function']);

$values = '';
foreach ($vars as $var){

	$s = $values != '' ? ', ' : '';
	if(!is_numeric($var) && !is_bool($var)) $var = '\''.$var.'\'';
	$values .= $s.$var;
}

if ($function) {
    $call = '$data = '.$function.'('.$values.');';
    //cjo_debug($call);
    eval($call);
}

if (cjoMessage::hasErrors() ||
    cjoMessage::hasSuccesses() ||
    cjoMessage::hasWarnings()) {

    echo cjoMessage::outputMessages(false);
}
elseif (is_bool($data)){
	echo $data == false ? 0 : 1;
}
elseif (is_string($data)) {
	echo $data;
}