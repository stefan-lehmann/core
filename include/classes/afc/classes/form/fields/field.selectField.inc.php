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

class selectField extends cjoFormMultiValueField {

    public $multiple;
    public $option_disabled;

    public function selectField($name, $label, $attributes = array (), $id = '') {
        $this->cjoFormMultiValueField($name, $label, $attributes, $id);
        $this->multiple = false;
        $this->option_disabled = array ();
    }

    /**
     * Fügt eine Option hinzu
     * @param $label Label der Option
     * @param $value Wert der Option
     * @access public
     */
    public function addOption($label, $value = '') {
        $this->addValue($label, $value);
    }

    /**
     * Fügt ein Array von Optionen hinzu
     * @param $options Array von Optionen
     * @access public
     */
    public function addOptions($options) {
        if (!empty($options)) $this->addValues($options);
    }

    public function disableOption($option) {
        $this->option_disabled[] = $option;
    }

    /**
     * Fügt Optionen via SQL-Query hinzu
     * @param $query SQL-Query, der ein 2 spaltiges Resultset beschreibt
     * @access public
     */
    public function addSqlOptions($query) {
        $this->addSqlValues($query);
    }

    /**
     * Gibt alle Optionen als Array zurück
     * @access public
     */
    public function getOptions() {
        return $this->getValues();
    }

    /**
     * Aktiviert/Deaktiviert, dass mehrere Optionen zugleich gewählt werden können
     * @param $multiple true => aktivieren / false => deaktivieren
     */
    public function setMultiple($multiple = true) {
        $this->multiple = $multiple;
    }

    /**
     * Gibt den HTML Content zurück
     */
    public function get() {

        $options = '';
        $name = $this->getName();
        $value = $this->getValue();
        $multiple_note = '';
        $attribute = '';

        foreach ($this->getOptions() as $opt) {

            if (in_array($opt[0], $this->option_disabled)) {
                $attribute = 'disabled="disabled" style="background:#ddd"';
            } else {
                $attribute = (in_array($opt[1], $value)) ? ' selected="selected" style="background-color:#ffe097"' : '';
            }
            $options .= sprintf('<option value="%s"%s>%s</option>', $opt[1], $attribute, $opt[0]);
        }

        if ($this->multiple) {
            $name .= '[]';
            $this->addAttribute('multiple', 'multiple');
            $this->addAttribute('size', '8', false);

            $multiple_note = '<span class="multiple_note">' . cjoI18N::translate('ctrl') . '</span>';
        } else {
            $this->addAttribute('size', '3', false);
        }

        return sprintf('<select name="%s" id="%s" tabindex="%s"%s>%s</select>%s ' . $multiple_note, $name, $this->getId(), cjo_a22_nextTabindex(), $this->getAttributes(), $options, $this->getNote());
    }
}