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

if (!class_exists('Smarty')) {
    // Create Smarty Env Dummy
    class Smarty { }

    $smartyDir = dirname(__FILE__);
    define('SMARTY_DIR', $smartyDir.DIRECTORY_SEPARATOR.'validate'.DIRECTORY_SEPARATOR);
    define('SMARTY_CORE_DIR', SMARTY_DIR.'internals'.DIRECTORY_SEPARATOR);
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR.'plugins'.DIRECTORY_SEPARATOR);
}

class cjoValidator extends Smarty {

    public $plugins_dir = SMARTY_PLUGINS_DIR;
    public $_tpl_vars;

    
    public function __construct() {
        cjoValidateEngine::prepare();
    }
    
    
    /**
     * assigns values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to assign
     */
    public function assign($tpl_var, $value = null) {


        if (is_array($tpl_var)) {
            foreach ($tpl_var as $key => $val) {
                if ($key != '') {
                    $this->_tpl_vars[$key] = $val;
                }
            }
        }
        else {
            if ($tpl_var != '') $this->_tpl_vars[$tpl_var] = $value;
        }
    }

    /**
     * appends values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to append
     */
    public function append($tpl_var, $value = null, $merge = false) {
        if (is_array($tpl_var)) {
            // $tpl_var is an array, ignore $value
            foreach ($tpl_var as $_key => $_val) {
                if ($_key != '') {
                    if (!@ is_array($this->_tpl_vars[$_key])) {
                        settype($this->_tpl_vars[$_key], 'array');
                    }
                    if ($merge && is_array($_val)) {
                        foreach ($_val as $_mkey => $_mval) {
                            $this->_tpl_vars[$_key][$_mkey] = $_mval;
                        }
                    }
                    else {
                        $this->_tpl_vars[$_key][] = $_val;
                    }
                }
            }
        }
        else {
            if ($tpl_var != '' && isset ($value)) {
                if (!@ is_array($this->_tpl_vars[$tpl_var])) {
                    settype($this->_tpl_vars[$tpl_var], 'array');
                }

                if ($merge && is_array($value)) {
                    foreach ($value as $_mkey => $_mval) {
                        $this->_tpl_vars[$tpl_var][$_mkey] = $_mval;
                    }
                }
                else {
                    $this->_tpl_vars[$tpl_var][] = $value;
                }
            }
        }
    }

    /**
     * get filepath of requested plugin
     *
     * @param string $type
     * @param string $name
     * @return string|false
     */
    public function _get_plugin_filepath($type, $name) {
        $_params = array ('type' => $type, 'name' => $name);
        require_once (SMARTY_CORE_DIR.'core.assemble_plugin_filepath.php');
        return smarty_core_assemble_plugin_filepath($_params, $this);
    }

    /**
     * Returns an array containing template variables
     *
     * @param string $name
     * @param string $type
     * @return array
     */
    public function get_template_vars($name = null) {
        if (!isset ($name)) {
            return $this->_tpl_vars;
        }
        if (isset ($this->_tpl_vars[$name])) {
            return $this->_tpl_vars[$name];
        }
    }

    /**
     * trigger Smarty error
     *
     * @param string $error_msg
     * @param integer $error_type
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING) {
        trigger_error("Smarty error: $error_msg", $error_type);
    }
}
