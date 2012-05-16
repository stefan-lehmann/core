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
 * Gibt eine Fehlermeldung aus
 *
 * @param string Meldungstext
 * @param string Datei
 * @param string Zeilennummer
 * @param integer Fehlermeldungstype
 * @access public
 */
function cjo_error($message, $file, $line, $type = E_USER_ERROR) {
    trigger_error(sprintf('%s <br/>in <b>%s</b> on line <b>%s</b><br/>', $message, $file, $line), $type);
}

/**
 * Gibt eine Addonspezifischen Fehlermeldung aus
 *
 * @param string Name des Addons
 * @param string Meldungstext
 * @param string Datei
 * @param string Zeilennummer
 * @param integer Fehlermeldungstype
 * @access public
 */
function cjo_addon_error($addon, $file, $line, $message, $type = E_USER_ERROR) {
    cjo_error('cjoAddon['.$addon.']: '.$message, $file, $line, $type);
}

/**
 * Gibt eine Fehlermeldung entsprechend der Datentyp validierung aus
 *
 * @param mixed zu prüfende Variable
 * @param string Datentype der Variable (ermitteln durch gettype)
 * @param string erwarteter Datentype
 * @param string Datei
 * @param string Zeilennummer
 * @access protected
 */
function _cjo_type_error($var, $type, $expected, $file, $line) {

    switch ($expected) {
        case 'class' :
            cjo_error('Unexpected class for Object "'.$var.'"!', $file, $line);
        case 'subclass' :
            cjo_error('Class "'.$var.'" is not a valid subclass!', $file, $line);
            // filesystem-types
        case 'method' :
            cjo_error('Method "'.$var.'" not exists!', $file, $line);
        case 'dir' :
            cjo_error('Folder "'.$var.'" not found!', $file, $line);
        case 'file' :
            cjo_error('File "'.$var.'" not found!', $file, $line);
        case 'resource' :
            cjo_error('Var "'.$var.'" is not a valid resource!', $file, $line);
        case 'upload' :
            cjo_error('File "'.$var.'" is no valid uploaded file!', $file, $line);
        case 'readable' :
            cjo_error('Destination "'.$var.'" not readable!', $file, $line);
        case 'writable' :
            cjo_error('Destination "'.$var.'" not writable!', $file, $line);
        case 'callable' :
            if (is_array($var))
            $var = implode('::', $var);
            cjo_error('Function or Class "'.$var.'" not callable!', $file, $line);

        default :
            cjo_error('Unexpected type "'.$type.'" for "$'.$var.'"! Expecting type "'.$expected.'"', $file, $line);
    }
}

/**
 * Prüft die übergebene Variable auf einen bestimmten Datentyp
 * und bricht bei einem Fehler mit einer Meldung das Script ab.
 *
 * <code>
 * // Prüfung der Variable $url auf den type String
 * cjo_valid_type($url, 'string', __FILE__, __LINE__);
 * </code>
 *
 * <code>
 * // Prüfung der Variable $param auf String ODER Array
 * cjo_valid_type($param, array ('string', 'array'), __FILE__, __LINE__);
 * </code>
 *
 * <code>
 * // Prüfung von $file, ob die Datei existiert UND ob die Datei beschreibbar ist
 * cjo_valid_type($file, array(array('file', 'readable')), __FILE__, __LINE__);
 * </code>
 *
 * @param mixed zu überprüfende Variable
 * @param mixed Kriterium das geprüft werden soll
 * @param string Datei
 * @param string Zeilennummer
 * @access public
 */
function cjo_valid_type($var, $expected, $file, $line) {
    if (!_cjo_valid_type($var, $expected, $file, $line)) {
        _cjo_type_error($var, gettype($var), $expected, $file, $line);
    }
}

/**
 * Prüft die Übergebene Variable auf einen bestimmten Datentyp.
 * Diese Funktion verknüpft die übergebenen Kriterien mit logischen UND oder ODER.
 *
 * @param mixed zu überprüfende Variable
 * @param mixed Kriterium das geprüft werden soll
 * @param string Datei
 * @param string Zeilennummer
 * @return boolean true wenn die Variable $var allen Kriterien des Types $type entspricht, sonst false
 * @access protected
 */
function _cjo_valid_type($var, $type, $file, $line) {
    if (is_array($type)) {
        foreach ($type as $_type) {
            if (is_array($_type)) {
                foreach ($_type as $__type) {
                    // AND Opperator
                    // if one of the checks is NOT correct, return false
                    if (!_cjo_check_vartype($var, $__type, $file, $line)) {
                        return false;
                    }
                }
            } else {
                // OR Opperator
                // if one of the checks is correct, return true
                if (_cjo_check_vartype($var, $_type, $file, $line)) {
                    return true;
                }
            }
        }
        return false;
    }
    elseif (is_string($type)) {
        if (_cjo_check_vartype($var, $type, $file, $line)) {
            return true;
        }
    } else {
        cjo_type_error('type', gettype($type), 'array|string', __FILE__, __LINE__);
    }
    return false;
}

/**
 * Prüft die übergebene Variable auf einen bestimmten Datentyp
 *
 * @param mixed zu überprüfende Variable
 * @param mixed Kriterium das geprüft werden soll
 * @param string Datei
 * @param string Zeilennummer
 * @return bool true wenn die Variable $var dem Type $type entspricht, sonst false
 * @access protected
 */
function _cjo_check_vartype($var, $type, $file, $line) {
    switch ($type) {
        // simple-vartypes
        case 'boolean' :
            return is_bool($var);
        case 'integer' :
            return is_int($var);
        case 'double' :
            return is_double($var);
        case 'float' :
            return is_float($var);
        case 'scalar' :
            return is_scalar($var);
        case 'numeric' :
            return is_numeric($var);
        case 'string' :
            return is_string($var);
        case 'array' :
            return is_array($var);
            // object-types
        case 'NULL' :
        case 'null' :
            return is_null($var);
        case 'object' :
            return is_object($var);
        case 'class' :
            cjo_valid_type($var, 'array', $file, $line);
            cjo_valid_type($var[0], 'object', $file, $line);
            cjo_valid_type($var[1], 'string', $file, $line);
            return is_a($var[0], $var[1]);
        case 'subclass' :
            cjo_valid_type($var, 'array', $file, $line);
            cjo_valid_type($var[0], 'object', $file, $line);
            cjo_valid_type($var[1], 'string', $file, $line);
            return is_subclass_of($var[0], $var[1]);
            // filesystem-types
        case 'method' :
            cjo_valid_type($var, 'array', $file, $line);
            cjo_valid_type($var[0], 'object', $file, $line);
            cjo_valid_type($var[1], 'string', $file, $line);
            return method_exists($var[0], $var[1]);
        case 'file' :
            return is_file($var);
        case 'dir' :
            return is_dir($var);
        case 'resource' :
            return is_resource($var);
        case 'upload' :
            return is_uploaded_file($var);
            // attributechecks
        case 'readable' :
            return is_readable($var);
        case 'writable' :
            return is_writable($var);
        case 'callable' :
            cjo_valid_type($var, array ('string','array'), $file, $line);
            return is_callable($var);
        default :
            return false;
    }
}

/**
 * Bindet eine CSS Datei via Extension Point in den Quelltext ein
 *
 * @param string Quelltext des Artikels
 * @param string|array Datei(en) die eingebunden werden sollen
 * @access private
 */
function cjo_insertCss($content, $files) {

    global $CJO;
    $styles = '';
    foreach (cjoAssistance::toArray($files) as $file) {
        if (empty($file) || !empty($CJO['AFC']['css'][$file])) continue;
        // CSS-Datei merken, damit jedes nur einmal eingebunden wird
        $CJO['AFC']['css'][$file] = true;
        $url = $CJO['BACKEND_PATH'].'/get_file.php?file='.rawurlencode($file);
        $styles .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />'."\n";
    }
    if ($content === false) {
        echo $styles;
        return;
    }
    return ($styles != '') ? preg_replace('/<\/head>/i', $styles.'</head>', $content, 1) : $content;
}

/**
 * Bindet eine JS Datei via Extension Point in den Quelltext ein
 *
 * @param string Quelltext des Artikels
 * @param string|array Datei(en) die eingebunden werden sollen
 * @access private
 */
function cjo_insertJS($content, $files) {

    global $CJO;
    $js = '';
    foreach (cjoAssistance::toArray($files) as $file) {
        if (empty($file) || ($CJO['AFC']['js'][$file])) continue;
        // JS-Datei merken, damit jedes nur einmal eingebunden wird
        $CJO['AFC']['js'][$file] = true;
        $url = $CJO['BACKEND_PATH'].'/get_file.php?file='.rawurlencode($file);
        $js .= '<script type="text/javascript" src="'.$url.'"></script>'."\n";
    }
    if ($content === false) {
        echo $js;
        return;
    }
    return ($js != '') ? str_replace('</head>', $js.'</head>', $content) : $content;
}

/**
 * Gibt die Standard-Parameter zurück, die man benötigt um die aktuelle Seite
 * wieder aufzurufen
 *
 * @return array Array von Parametern
 * @access protected
 */
function cjo_a22_getDefaultGlobalParams() {

    global $CJO, $mypage;

    $params = array();
    $params_in = array('page' => 'string', 'mypage' => 'string', 'subpage' => 'string',
                       'clang' => 'cjo-clang-id', 'function' => 'string', 'func' => 'string',
                       'oid' => 'int', 'order_col' => 'string', 'order_type' => 'string',
                       'search_key' => 'string', 'search_column' => 'string',
                       'stepping' => 'int', 'next' => 'int');

    if (!$CJO['CONTEJO']) {
        $params_in['article_id'] = 'cjo-article-id';
        $params_in['ctype'] = 'cjo-ctype-id';
    }

    foreach($params_in as $key=>$vartype) {

        $var = $key;
        if ($key == 'mypage') continue;
        if ($key == 'page' || $key == 'subpage') {
            global $$var;
            $$var = _cjo_cast_var($$var, $vartype, '', 'default');
        }
        if (!isset($$var) || (empty($$var) && $$var !== 0)) $$var = cjo_request($key, $vartype);
        if (!empty($$var) || $$var === 0) $params[$key] = $$var;
    }

    $params['page'] = (!empty($mypage)) ? $mypage : $page;

    return $params;
}

/**
 * Gibt den aktuellen Tabindex zurück
 * Der Tabindex ist eine stetig fortlaufende Zahl,
 * welche die Priorität der Tabulatorsprünge des Browsers regelt.
 *
 * @return integer aktueller Tabindex
 */
function cjo_a22_getTabindex() {
    global $CJO;
    if (empty ($CJO['tabindex'])) {
        $CJO['tabindex'] = 0;
    }
    return $CJO['tabindex'];
}

/**
 * Gibt den nächsten freien Tabindex zurück.
 * Der Tabindex ist eine stetig fortlaufende Zahl,
 * welche die Priorität der Tabulatorsprünge des Browsers regelt.
 *
 * @return integer nächster freier Tabindex
 */
function cjo_a22_nextTabindex() {
    global $CJO;
    if (empty ($CJO['tabindex'])) {
        $CJO['tabindex'] = 0;
    }
    return ++ $CJO['tabindex'];
}
