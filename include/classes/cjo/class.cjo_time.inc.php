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
 * cjoTime class
 *
 * The cjoTime class provides script execution time measurement.
 * @package 	contejo
 * @subpackage 	core
 */

class cjoTime {

    function __construct(){
        global $CJO;
        $CJO['SCRIPT_START_TIME'] = $this->getCurrentTime();
    }

    public static function showScriptTime($render = false, $pre = '') {

        global $CJO;
        $time = intval((self::getCurrentTime() - $CJO['SCRIPT_START_TIME']) * 1000) / 1000;

        if (!$render) return $time;

        echo $pre.'<br/> '.$time.' sec. <br/><br/>';
    }

    public static function getCurrentTime() {
        $time = explode(" ", microtime());
        return ($time[0] + $time[1]);
    }

    public static function avoidTimeout($message, $time = 8, $redirect = false) {

        $max_ext_time = ini_get('max_execution_time');

        if (empty($max_ext_time)) $max_ext_time = (int) get_cfg_var('max_execution_time');
        if (empty($max_ext_time)) $max_ext_time = 30;
                    
    	$left_time = $max_ext_time - cjoTime::showScriptTime();

    	if ($left_time < $time) {

    		while(@ob_end_clean());

    		if ($redirect || strpos($_SERVER['SCRIPT_NAME'], 'connectmedia.php') !== false) {
    			header('Location: '.$_SERVER['REQUEST_URI']);
    			exit();
    		}
    		
            echo '<!DOCTYPE html>'."\r\n";
            echo '<html>'."\r\n";
            echo '<head>'."\r\n";
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>'."\r\n";
            echo '</head>'."\r\n";
            echo '<body>'."\r\n";      
    		echo '<pre>'.$message.'</pre>';
    		echo '<script type="text/javascript"> location.reload(); </script>'."\r\n";
            echo '</body></html>'."\r\n";    		
    		exit();
    	}
    }
}

new cjoTime();
