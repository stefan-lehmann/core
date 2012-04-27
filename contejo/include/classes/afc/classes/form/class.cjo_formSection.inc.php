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

class cjoFormSection extends cjofieldController {

    // Section-Label
    public $label;
    // Spaltenanzahl
    public $columns;
    // ID
    public $id;
    // Anker
    public $anchor;
    
    /**
     * Klassenkonstruktor
     */
    public function cjoFormSection($dataset, $label, $where_params = array(), $columns = 1) {
        cjo_valid_type($label, 'string', __FILE__, __LINE__);

        $this->label   = $label;
        $this->columns = $columns;
        $this->anchor  = '';

        if (!is_array($dataset)) {
            // Parentkonstruktor aufrufen
            $this->cjofieldController($dataset, $where_params);
        }
        else {
            // Parentkonstruktor aufrufen
            $this->cjoFieldContainer();
            $this->mode    = null;
            $this->dataset = $dataset;
            $this->errors  = null;
        }
    }
    /**
     * Setzt die Id des Abschnitts
     * @param string Name der Id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Gibt die Id der Section zurück
     * @return string Name der Id
     */
    public function getId() {
        if($this->id != ''){
            if(strpos($this->id, 'id=') !== false){
                return ' '.$this->id;
            } else {
                return ' id ="'.$this->id.'"';
            }
        }
    }
    /**
     * Setzt einen Anker am beginn des Abschnitts
     * @param string Name des Ankers
     */
    public function setAnchor($anchor) {
        $this->anchor = $anchor;
    }

    /**
     * Gibt den Namen des Ankers zurück
     * @return string Name des Ankers
     */
    public function getAnchor() {
        return $this->anchor;
    }

    /**
     * Setzt die Überschrift des Formularabschnitts
     * @param string Überschrift des Abschnitts
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Gibt die Überschrift des Formularabschnitts
     * @return string Überschrift des Abschnitts
     */
    public function getLabel() {
        return $this->label;
    }

    public static function isValid($section) {
        return is_object($section) && is_a($section, 'cjoformsection');
    }

    public function numColumns(){
        return count($this->columns);
    }

    public function getColumnsWidth($num){
        return $this->columns[$num];
    }

    public function parseLabel($label) {
        $label = str_replace(' - ', '-', $label);
        $label = str_replace(' ', '-', $label);
        $label = str_replace('.', '-', $label);
        $label = str_replace('Ä', 'Ae', $label);
        $label = str_replace('Ö', 'Oe', $label);
        $label = str_replace('Ü', 'Ue', $label);
        $label = str_replace('ä', 'ae', $label);
        $label = str_replace('ö', 'oe', $label);
        $label = str_replace('ü', 'ue', $label);
        $label = str_replace('ß', 'ss', $label);
        $label = preg_replace("/[^a-zA-Z\-0-9]/", "", $label);
        return $label;
    }

    public function get() {
        $s = '';
        $s .= '    <!-- cjoSection start -->' . "\r\n";
        $s .= '    <div class="a22-cjoform-section"'.$this->getId().'>' . "\r\n";

        // Validierungsfehler
        $errors = $this->getErrors();
        $numErrors = $this->numErrors();

        if ($numErrors > 0) {
            foreach ($errors as $error) {
                if (!empty ($error))
                $this->cjoform->setMessage($error, FORM_ERROR_MSG);
            }
        }

        // Abschnittsanker
        $anchor = $this->getAnchor();
        if ($anchor != '') {
            $s .= '      <a name="' . $anchor . '"></a>' . "\r\n";
        }
        // $s .= '      <fieldset>' . "\r\n";

        // Abschnittsüberschirft
        $label = $this->getLabel();
        if ($label != '') {
            $s .= '        <div class="legend">' . $label . '</div>' . "\r\n";
        }

        $s .= '      <div class="a22-container clearfix">' . "\r\n";
        $s .= 		cjoExtension::registerExtensionPoint('CJO_FORM_' . strtoupper($this->cjoform->getName()) . '_SECTION_START');
        // Hidden fields
        $fields = $this->getFields();
        $numFields = $this->numFields();


        for ($t = 0; $t < $numFields; $t++) {
            if (is_a($fields[$t], 'hiddenfield')) {
                $s .= '        ' . $fields[$t]->get() . "\r\n";
                unset ($fields[$t]);
            }
        }

        // Enstandene Lücken zwischen den Indizes löschen
        $fields = array_resort_keys($fields);
        $numFields = count($fields);

        // Daten aufbereiten
        $numCols = $this->numColumns();

        $i = 1;
        for ($t = 0; $t < $numFields; $t++) {
            $field = & $fields[$t];

            $field_label = $field->getLabel();
            $field_value = $field->get();

            if ($field_label != '') {
                if ($field->hasValidator()) {
                    $required = ' class="required"';
                    $marker = ' *';
                } else {
                    $required = '';
                    $marker = '';
                }

                $for = ($field->getId() != '') ? ' for="' . $field->getId() . '"' : '';

                $field_label = '<label' . $for . '' . $required . '>' . $field_label . '' . $marker . '</label>';
            }

            $fieldStr = $field_label . $field_value;

            if ($i <= 1 && $field->border_top != false) $s .= '<div class="hr"></div>';

            if ($field->needFullColumn() || $numCols <= 1) {
                $i = 1;
                $s .= $this->getFullColumn($fieldStr,$field);
                continue;
            }

            $s .= $this->getMultiColumn($fieldStr, $i, $field);

            $i++;
            if ($i > $numCols) {
                $i = 1;
            }
        }
        $s .= '      	</div>' . "\r\n";
        // $s .= '      </fieldset>' . "\r\n";
        $s .= '    </div>' . "\r\n";
        $s .= '    <!-- cjoSection end -->' . "\r\n";

        return $s;
    }

    public function getMultiColumn($colValue, $colIndex, $field){

        $field->addColAttribute('class', 'multicols', 'join');
        $field->addColAttribute('style', 'width: '.$this->getColumnsWidth($colIndex-1).'; ', false);
        $attributes = str_replace('a22-col1','a22-col'.$colIndex, $field->getColAttributes());

        $class = ($field->border_top == false) ? ' no_border_top' : '';
        
        $colStr  = '        <div '.$attributes.'>'. "\r\n";
        $colStr .= '        	<div class="field clearfix'.$class.'">' . $colValue.'</div>'. "\r\n";
        $colStr .= '        </div>' . "\r\n";

        return $colStr;
    }

    public function getFullColumn($colValue, $field){

        $attributes = $field->getColAttributes();
        
        $class = ($field->border_top == false) ? ' no_border_top' : '';

        $colStr  = '        <div '.$attributes.'>'. "\r\n";
        $colStr .= '        	<div class="field clearfix'.$class.'">' . $colValue.'</div>'. "\r\n";
        $colStr .= '        </div>'. "\r\n";

        return $colStr;
    }

    public function show() {
        echo $this->get();
    }

    public function toString() {
        return 'cjoFormSection: name: "' . $this->getTableName() . '", label: "' . $this->getLabel() . '", felder: "' . $this->numFields() . '"';
    }
}
