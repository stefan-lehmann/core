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

define('OPT_NONE', 0); // 0
define('OPT_SORT', 1); // 2^0
define('OPT_SEARCH', 2); // 2^1
define('OPT_FILTER', 4); // 2^2 NOT IN USE!
define('OPT_ALL', 7); // 2^3 - 1

/**
 * Basisklasse für Spalten innerhalb der Liste.
 */
class cjoListColumn extends cjoListComponent {

    // Spaltenüberschrift
    public $label;

    // Optionen zur Darstellung und zum Verhalten
    public $options;

    // conditionale Texte
    public $conditions;

    // Tags
    public $head_attributes;
    public $body_attributes;

    public function cjoListColumn($label = '', $options = OPT_ALL) {
        $this->params = array ();
        $this->conditions = array ();
        $this->head_attributes = '';
        $this->body_attributes = '';
        $this->setOptions($options);
        $this->setLabel($label);
    }

    /**
     * Überschreibt die vorhandenen Optionen mit der/den übergebenen Option/Optionen
     * @param $option OPT_* Konstante
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * Gibt alle Optionen zurück
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Fügt die Option hinzu
     * @param $option OPT_* Konstante
     */
    public function addOption($option) {
        if ($option == OPT_NONE) {
            $this->setOptions(OPT_NONE);
        } else {
            $this->setOptions($this->getOptions() | $option);
        }
    }

    /**
     * Entfernt die übergebene Option
     * @param $option OPT_* Konstante
     */
    public function delOption($option) {
        $this->setOptions($this->getOptions() ^ $option);
    }

    /**
     * Prüft ob die übergebene Option gesetzt ist
     * @param $option OPT_* Konstante
     */
    public function hasOption($option) {
        return ($this->getOptions() & $option) == $option;
    }

    /**
     * Setzt die Spaltenüberschrift
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Gibt die Spaltenüberschrift zurück
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Fügt der Spalte einen Wert hinzu, der von einer Spalte abhängig ist.
     *
     * @param $cond_column Name der Spalte die geprüft werden soll
     * @param $cond_value Wert, auf den geprüft werden soll
     * @param $text Text der ausgegeben werden soll
     * @param $params Link-Parameter die auf $text als Link gesetzt werden sollen
     */
    public function addCondition($cond_column, $cond_value, $text, $params = '', $tags='') {
        $this->conditions[] = array (
            $cond_column,
            $cond_value,
            $text,
            $params,
            $tags
        );
    }

    /**
     * @access public
     * @static
     */
    public static function isValid($column) {
        return is_object($column) && is_a($column, 'cjolistcolumn');
    }

    /**
     * Durchsucht die Parameter nach %VarName%
     * und ersetzt diese durch die entsprechenden Werte
     */
    public function parseParams($row) {
        return $this->parseArray($this->params, $row);
    }

    public function parseString($string, $row) {
        if (empty ($row) || empty ($string)) {
            return '';
        }

        foreach ($row as $_name => $_value) {
            $string = str_replace('%' . $_name . '%', $_value, $string);
        }
        return $string;
    }

    public function parseArray($array, $row) {
        $result = array ();
        if (empty ($row) || empty ($array)) {
            return $result;
        }

        foreach ($array as $_name => $_value) {
            // %VAR_NAME%
            // Wert beginnt und endet mit '%'
            if (substr($_value, 0, 1) == '%' && substr($_value, -1) == '%') {
                // Name der Variablen herausschneiden
                $var = substr($_value, 1, strlen($_value) - 2);

                // Name in der aktuellen Zeile suchen
                if (array_key_exists($var, $row)) {
                    // Und ersetzen
                    $result[$_name] = $row[$var];
                    continue;
                }
            }
            $result[$_name] = $_value;
        }
        return $result;
    }

    public function setHeadAttributes($attributes) {
        if ($attributes != '' && !startsWith($attributes, ' ')) {
            $attributes = ' ' . $attributes;
        }
        $this->head_attributes .= $attributes;
    }

    public function getHeadAttributes() {
        return $this->head_attributes;
    }

    public function setBodyAttributes($attributes) {
        if ($attributes != '' && !startsWith($attributes, ' ')) {
            $attributes = ' ' . $attributes;
        }
        $this->body_attributes .= $attributes;
    }

    public function getBodyAttributes() {
        return $this->body_attributes;
    }

    /**
     * Formatiert die Werte der aktuellen Spalte.
     * Dabei kann mit $row auf alle Werte in der aktuellen Zeile zugegriffen werden.
     */
    public function format($row) {

        for ($i = 0; $i < count($this->conditions); $i++) {
            $condition = & $this->conditions[$i];
            // $condition[0] Name der Spalte die geprüft werden soll
            // $condition[1] Wert, auf den geprüft werden soll
            // $condition[2] Text der ausgegeben werden soll
            // $condition[3] Link-Parameter die auf $text als Link gesetzt werden sollen

            if (!array_key_exists($condition[0], $row) && !empty($condition[1])) continue;

            if (is_array($condition[1])) {
                switch($condition[1][0]) {
                    case '>':   $check = ($row[$condition[0]] > $condition[1][1]) ? true : false; break;
                    case '<':   $check = ($row[$condition[0]] < $condition[1][1]) ? true : false; break;
                    case '>=':  $check = ($row[$condition[0]] >= $condition[1][1]) ? true : false; break;
                    case '<=':  $check = ($row[$condition[0]] <= $condition[1][1]) ? true : false; break;
                    case '===':	$check = ($row[$condition[0]] === $condition[1][1]) ? true : false; break;
                    case '!=':	$check = ($row[$condition[0]] != $condition[1][1]) ? true : false; break;
                    case '!==':	$check = ($row[$condition[0]] !== $condition[1][1]) ? true : false; break;
                    default:	$check = ($row[$condition[0]] == $condition[1][1]) ? true : false; break;
                }
            } else {
                $check = (isset($row[$condition[0]]) && $row[$condition[0]] == $condition[1]) ? true : false;
            }

            if ($check){
                if (isset($row[$this->name])) {
                    if ($this->format_type){
                        //$row[$condition[0]] = cjoFormatter :: format($row[$condition[0]], $this->format_type, $this->format);
                        $row[$this->name] = cjoFormatter :: format($row[$this->name], $this->format_type, $this->format);
                    }
                } else {
                    $row[$this->name] = '';
                }
                $out = sprintf($condition[2],$row[$this->name]);
                if (is_array($condition[3])) {
                  if ($this->format_type != '')
                    // Text mit den Parametern $condition[3] verlinken
                    return $this->link($out, $this->parseArray($condition[3], $row), $condition[4]);
                } else {
                    // Plain-Text
                    return $out;
                }
            }
        }
        return '';
    }
}

// Column Klassen einbinden
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/columns/column.resultColumn.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/columns/column.staticColumn.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/columns/column.countColumn.inc.php';
