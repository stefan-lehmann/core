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

/**
 * cjoExtension class
 *
 * The cjoExtension class provides an interface for
 * the execution of callable functions at registered points
 * during the workflow.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoExtension {

    /**
     * Defines an extension point.
     *
     * @param string $extension name of the extension point
     * @param array $params parameter for the callback function
     * @param boolean $read_only
     * @return mixed
     * @access public
     */
    public static function registerExtensionPoint($extension, $params = array (), $read_only = false) {

    	global $CJO;

    	if (!is_array($params)) {
    	    $result = $params;
    		$params = array('subject' => $params);
    	}
    	
        if (class_exists('cjoLog')) cjoLog::writeLog($extension, $params);
    	
    	if (isset($CJO['EXTENSIONS'][$extension]) && is_array($CJO['EXTENSIONS'][$extension])) {

    		if ($read_only) {
    			foreach ($CJO['EXTENSIONS'][$extension] as $ext) {
    				self::callFunction($ext, $params);
    			}
    		} else {

    			foreach ($CJO['EXTENSIONS'][$extension] as $ext) {
    				$result = self::callFunction($ext, $params);
    				
    				// Rückgabewert nur auswerten wenn auch einer vorhanden ist
    				// damit $params['subject'] nicht verfälscht wird

    				if (!empty($result)) {
    					$params['subject'] = $result;
    				}
    			}
    		}
       	} 
    	
    	return (isset($params['subject'])) ? $params['subject'] : NULL;
    }

    /**
     * Defines a callback function, that will be executed
     * when the extension point present.
     *
     * @param string $extension name of the extension point
     * @param string $function name of the callback function
     * @return void
     * @access public
     */
    public static function registerExtension($extension, $function) {

        global $CJO;

    	if(!is_string($function)) return false;
    	$CJO['EXTENSIONS'][$extension][md5($function)] = $function;
    }

    /**
     * Verifies the existence of an extension point.
     * @param string $extension Name der Extension
     * @return boolean
     * @access public
     */
    public static function isExtensionRegistered($extension) {
    	global $CJO;
    	return !empty ($CJO['EXTENSIONS'][$extension]);
    }

    /**
     * Call of a class member or a static function.
     * @param string|array $function name of the callback function
     * @param array $params parameter of the callback function
     *
     * @example
     *   cjoExtension :: callFunction( 'myFunction', array( 'Param1' => 'ab', 'Param2' => 12))
     * @example
     *   cjoExtension :: callFunction( 'myObject::myMethod', array( 'Param1' => 'ab', 'Param2' => 12))
     * @example
     *   cjoExtension :: callFunction( array('myObject', 'myMethod'), array( 'Param1' => 'ab', 'Param2' => 12))
     * @example
     *   $myObject = new myObject();
     *   cjoExtension :: callFunction( array($myObject, 'myMethod'), array( 'Param1' => 'ab', 'Param2' => 12))
     *
     * @return mixed
     * @access public
     */
    public static function callFunction($function, $params) {

    	$func = '';

        
    	if (is_string($function) && strlen($function) > 0) {
    		// static class method
    		if (strpos($function, '::') !== false) {

                preg_match('/(\w+)(\s*::\s*)(\w+)/', $function, $_match);
                $_class_name = $_match[1];
                $_method_name = $_match[3];
                
    			self::checkCallable($func = array ($_class_name,$_method_name));
    		}
    		elseif (function_exists($function)) {
    			$func = $function;
    		}
    		else {
    			trigger_error('cjoExtension: Function "' . $function . '" not found!');
    		}
    	}
    	// object method call
    	elseif (is_array($function)) {

    		$_object = $function[0];
    		$_method_name = $function[1];

    		self::checkCallable($func = array ($_object, $_method_name));
    	} else {
    		trigger_error('cjoExtension: Using of an unexpected function var "' . $function . '"!');
    	}

    	return call_user_func($func, $params);
    }

    /**
     * Verifies if the defined callback function is callable.
     * @param string|array $function name of the callback function
     * @return boolean
     * @access public
     * @todo Test this function again.
     */
    public static function checkCallable($function) {

    	if (is_callable($function)) {
    		return true;
    	} else {
    		if (!is_array($function)) {
    			trigger_error('cjoExtension: Unexpected vartype for $function given! Expecting Array!', E_USER_ERROR);
    		}
    		$_object = $function[0];
    		$_method_name = $function[1];

    		if (!is_object($_object)) {
    			$_class_name = $_object;
    			if (!class_exists($_class_name)) {
    				trigger_error('cjoExtension: Class "' . $_class_name . '" not found!', E_USER_ERROR);
    			}
    		}
    		else {
    		    trigger_error('cjoExtension: No such method "' . $_method_name . '" in class "' . get_class($_object) . '"!', E_USER_ERROR);
    		}
    	}
    }
}