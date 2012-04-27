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

/**
 * Adds slashes to the elements of an array recursively.
 * @param array $theArray
 */
function addSlashesOnArray(&$theArray) {

	if (is_array($theArray)){
	    foreach($theArray as $Akey => $AVal){
			if (is_array($AVal)){
				addSlashesOnArray($AVal);
			}
			else{
				$theArray[$Akey] = addslashes($AVal);
			}
		}
		reset($theArray);
	}
}

if ((function_exists("get_magic_quotes_gpc") && !get_magic_quotes_gpc()) ||
    (ini_get('magic_quotes_sybase') == '' &&
    (strtolower(ini_get('magic_quotes_sybase')) == "off"))){

    if (is_array($_GET)){
    	addSlashesOnArray($_GET);
    	foreach($_GET as $Akey => $AVal){
    	    $$Akey = $AVal;
    	}
    }

    if (is_array($_POST)){
    	addSlashesOnArray($_POST);
        foreach($_POST as $Akey => $AVal){
    	    $$Akey = $AVal;
    	}
    }

    if (is_array($_REQUEST)){
	    addSlashesOnArray($_REQUEST);
            foreach($_REQUEST as $Akey => $AVal){
    	    $$Akey = $AVal;
    	}
	}
}