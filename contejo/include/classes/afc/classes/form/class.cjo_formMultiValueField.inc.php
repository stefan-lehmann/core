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

class cjoFormMultiValueField extends cjoFormField {

    public $values;
    public $value_separator;

    public function cjoFormMultiValueField($name, $label, $tags = array (), $id = '', $value_separator = '|') {
        $this->cjoFormField($name, $label, $tags, $id);
        $this->setValueSeparator($value_separator);
        $this->values = array ();
    }

    /**
     * Fügt dem Feld einen neuen Wert hinzu
     * @param $label Label des Wertes
     * @param $value Wert des Wertes
     * @access protected
     */
    public function addValue($label, $value) {
        cjo_valid_type($label, array ('string','scalar'), __FILE__, __LINE__);
        cjo_valid_type($value, array ('string','scalar'), __FILE__, __LINE__);

        $this->values[] = array ($label,$value);
    }

    /**
     * Fügt dem Feld eine Array von Werten hinzu
     * @param $values Array von Werten
     * @access protected
     */
    public function addValues($values) {
        cjo_valid_type($values, 'array', __FILE__, __LINE__);

        $value = array_shift($values);
        $mode = '';
        if (isset ($value[0]) && isset ($value[1])) {
            $mode = 'Numeric';
        }
        elseif (isset ($value['label']) && isset ($value['value'])) {
            $mode = 'Assoc';
        }
        elseif (is_scalar($value)) {
            $mode = 'Scalar';
        } else {
            cjoForm :: triggerError('Unexpected Array-Structure for Array $values. Expected Keys are "0" and "1" or "label" and "value"!');
        }

        if ($mode == 'Numeric') {
            // Add first Option
            $this->addValue($value[0], $value[1]);

            // Add remaing Options
            foreach ($values as $value) {
                $this->addValue($value[0], $value[1]);
            }
        }
        elseif ($mode == 'Assoc') {
            // Add first Option
            $this->addValue($value['label'], $value['value']);

            // Add remaing Options
            foreach ($values as $value) {
                $this->addValue($value['label'], $value['value']);
            }
        }
        elseif ($mode == 'Scalar') {
            // Add first Option
            $this->addValue($value, $value);

            // Add remaing Options
            foreach ($values as $value) {
                $this->addValue($value, $value);
            }
        }
    }

    /**
     * Fügt dem Feld neue Werte via SQL-Query hinzu.
     * Dieser Query muss ein 2 Spaltiges Resultset beschreiben.
     *
     * @param $query SQL-Query
     * @access protected
     */
    public function addSqlValues($query) {
        $sql = new cjoSql();
        //      $sql->debugsql = true;

        $result = $sql->getArray($query, PDO::FETCH_NUM);

        if (is_array($result) && count($result) >= 1) {
            $value = array_shift($result);

            if (count($value) > 2) {
                cjoForm :: triggerError('Query "'.$query.'" affects more than 2 columns!');
            }

            if (count($value) == 2) {
                // Add first Option
                $this->addValue($value[0], $value[1]);
                foreach ($result as $value) {
                    // Add remaing Options
                    $this->addValue($value[0], $value[1]);
                }
            }
            elseif (count($value) == 1) {
                // Add first Option
                $this->addValue($value[0], $value[0]);
                foreach ($result as $value) {
                    // Add remaing Options
                    $this->addValue($value[0], $value[0]);
                }
            }
        }
    }

    /**
     * Entfernt einen Wert des Feld
     * @param $value Wert des Wertes
     * @access protected
     */
    public function delValue($value) {
        cjo_valid_type($value, 'string', __FILE__, __LINE__);

        if ($this->hasValue($value)) {
            unset ($this->values[$value]);
        }
    }

    /**
     * Prüft, ob ein Wert schon vorhanden ist
     * @param $value Wert des Wertes
     * @access protected
     */
    public function hasValue($value) {
        cjo_valid_type($value, 'string', __FILE__, __LINE__);

        return array_key_exists($value, $this->getValues());
    }

    /**
     * Gibt alle Werte des Feldes zurück
     * @access protected
     */
    public function getValues() {
        return $this->values;
    }

    /*
     * Prepariert den InsertValue um das Array als String in die DB zu speichern
     * @access protected
     */
    public function getInsertValue() {
        $value = parent :: getInsertValue();
        if (is_array($value)) {
            $value = implode($this->value_separator, $value);
        }
        return $value;
    }

    /*
     * Prepariert den Value um den String aus der DB als Array zurückzugeben
     * @access protected
     */
    public function getValue($format = false) {
        $value = parent :: getValue($format);
        if (!is_array($value)) {
            $value = explode($this->value_separator, $value);
        }
        $value = array_diff($value, array (''));
        return $value;
    }
}
