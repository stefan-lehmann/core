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

class cjoFormField {

    public $id;
    public $name;
    public $value;
    public $attributes;
    public $colattributes;
    public $needFullColumn;
    public $activateSave;
    public $note;
    public $border_top;    
    public $value_separator;
    public $default_value;
    public $format_type;
    public $format;
    public $validators;
    public $cjosection;

    public function cjoFormField($name, $label, $attributes = array (), $id = '') {
        $this->name = $name;
        $this->label = $label;
        $this->attributes = $attributes;
        $this->id = $id;
        $this->colattributes = array('class' => 'a22-col1');

        $this->validators = array ();
        $this->transformators = array ();
        $this->needFullColumn(false);
        $this->activateSave(true);
        $this->setValueSeparator("|");
        $this->setDefault(false);
        $this->enableBorderTop();
        $this->setFormat();
    }

    public function getName() {
        return $this->name;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function setDefault($value) {
        $this->default_value = $value;
    }

    public function getDefault() {
        return $this->default_value;
    }

    public function setFormat($format_type='',$format=''){
        $this->format_type = $format_type;
        $this->format = $format;
    }

    public function setNote($text, $attributes = '', $tags = 'span') {
        $this->note = array('text'=> $text, 'attributes' => $attributes, 'tags' => $tags);
    }

    public function getNote() {

    	$text = $this->note['text'];
        $attributes = $this->note['attributes'];
        $tags = $this->note['tags'];

        if($text != '' || $attributes != ''){

         	if (strpos($attributes, 'class') === false) {
            	$attributes .= ' class="note"';
            }
            else {
           		$attributes = str_replace('class="', 'class="note ', $attributes);
            }
        	return sprintf ('<%s %s>%s</%s>', $tags, $attributes, $text, $tags);
        }
    }

    public function setHelp($text) {

        $text = '<img src="./img/silk_icons/help.png" alt="?" class="cjo_dialog_help_img" /><div class="cjo_dialog_help_text">'.$text.'</div>';

        $this->setNote($text, 'class="cjo_dialog_help"', 'div');
    }

    public function stripslashes($value) {
        if (is_array($value)) {
            for ($i = 0; $i < count($value); $i++) {
                $value[$i] = stripslashes($value[$i]);
            }
        } else {
            $value = stripslashes($value);
        }

        return $value;
    }

    /**
     * Setzt den Trenner für die Werte
     * @access public
     */
    public function setValueSeparator($value_separator) {
        cjo_valid_type($value_separator, 'string', __FILE__, __LINE__);

        $this->value_separator = $value_separator;
    }
    /**
     * Schalter, um die Speicherfunktion dieses Feldes zu aktivieren/deaktvieren.
     * In der Grundeinstellung zeigt das readOnlyField die Werte nur an,
     * speichert diese beim save() aber nicht in die DB.
     *
     * @param $activate_save boolean true/false Speicherfunktion aktiviert/deaktiviert
     */
    public function activateSave($activateSave) {
        cjo_valid_type($activateSave, array (
            'boolean'
        ), __FILE__, __LINE__);

        $this->activateSave = $activateSave;
    }

    public function _getInsertValue() {
        // Werte auf den Insert vorbereiten
        // Aktuell nur den Wert zurückgeben, da die Werte via magic_quotes escaped werden!
        $value = $this->getValue(false);


        if (is_array($value)) {

            if (!empty ($value)) {
                $value = implode($this->value_separator, $value);
            } else {
                $value = '';
            }
        }
        return $value;
    }

    public function getInsertValue() {
        if ($this->activateSave === true) {
            return $this->_getInsertValue();
        }

        // null zurückgeben, damit der Wert nicht im SQL auftaucht
        return null;
    }

    public function getValue($format = true) {

        $value = '';
        
    	if ($this->getSection() != '') {
	    	$section = $this->getSection();
	        $dataset = $section->_getDataSet();
	    	$form    = $section->getForm();


            $name = str_replace ("]", '', $this->getName()); 
            $name = explode("[",$name); 
            
            if (cjo_post('cjo_form_name','string') == $form->getName())
                $dataset = $_POST;

            $temp = $dataset[array_shift($name)];
            if (!empty($name)) {
            foreach($name as $key)
                if (!isset($temp[$key])) {
                    $temp = ''; 
                    break;
                }
                if ($key != '') 
                $temp = $temp[$key];
            }
            
	        if (cjo_post('cjo_form_name','string') == $form->getName() && empty($this->attributes['disabled'])) {
		        if ($this->getName() && !empty($temp)) {
		            return $this->stripslashes($temp);
		        }
	        }
	        else {
		        if ($value == '' && $this->value != '') {
		            $value = $this->value;
		        }
	            if ($value == '' && isset ($temp)) {
		            $value = $temp;
		        }
		        if($value == '' && $this->getDefault()){
		             $value = $this->getDefault();
		        }
	        }
    	}
    	else {
    		$value = $this->value;
    	}
    	
                
        return $value = ($this->format_type != '' && $format === true) ? cjoFormatter :: format($value, $this->format_type, $this->format) : $value;
    }

    public function & getSection() {
        return $this->cjosection;
    }

    public function getId() {

        $id = $this->id;

        if ($id == '' && $this->getSection() != '') {

			if ($this->getName() == '') return false;

        	$section = & $this->getSection();
            $form = & $section->getForm();
            $section_label = $section->parseLabel($section->getLabel());
			$name = str_replace(array('[',']'),'',$this->getName());

            $id = strtolower($form->getName().'_'.$section_label.'_'.$name);
        }
        return $id;
    }

    public function addColAttribute($tag_name, $tag_value, $overwrite = true) {
        
        if ($overwrite === false && array_key_exists($tag_name, $this->colattributes)) {
            return;
        }
        if ($overwrite == 'join') {
        	$this->colattributes[$tag_name] 
        	   = !empty($this->colattributes[$tag_name]) 
        	   ? $this->colattributes[$tag_name] .' '.$tag_value 
        	   : $tag_value;
        	return;
        }
        $this->colattributes[$tag_name] = $tag_value;
    }

    public function getColAttributes() {
        $s = '';
        $attributes = $this->colattributes;

        // Attribute zu String umwandeln
        if (is_array($attributes)) {
            foreach ($attributes as $attr_name => $attr_value) {
                $s .= ' '.$attr_name.'="'.$attr_value.'"';
            }
        }
        return $s;
    }

    public function addAttribute($tag_name, $tag_value, $overwrite = true) {
        if ($overwrite === false && array_key_exists($tag_name, $this->attributes)) {
            return;
        }
        $this->attributes[$tag_name] = $tag_value;
    }

    public function _getAttributes() {
        return $this->attributes;
    }

    public function getAttributes() {

        global $SMARTY_VALIDATE;
        
        $s = '';
        $attributes = $this->_getAttributes();

        if($this->getSection() != ''){

	        $section = & $this->getSection();
	        $form = & $section->getForm();

	        $isInvalid = false;
	        if (cjo_post('cjo_form_name','string') == $form->getName()) {
	            // falls das Feld nicht gültig ist, CSS Klasse "invalid" zuweisen
	            
	            $fields = $SMARTY_VALIDATE[$form->getName()]['validators'];

	            if (is_array($fields)) {
	            
    	            foreach($fields as $field) {
    	                if ($field['field'] == $this->getName()) {
    	                    if (empty($field['valid'])) {
    	                        $isInvalid = true;
    
                                if (empty ($attributes['class'])) {
                                    $attributes['class'] = 'invalid';
                                } else {
                                    $attributes['class'] = $attributes['class'].' invalid';
                                }  
    	                    }
    	                }
    	            }
	            }
	        }

	        // Falls das Feld valide ist, Pflichtfelder markieren
	        if (!$isInvalid && $this->hasValidator()) {
	            if (empty ($attributes['class'])) {
	                $attributes['class'] = 'required';
	            } else {
	                $attributes['class'] = $attributes['class'].' required';
	            }
	        }
        }
        // Attribute zu String umwandeln
        if (is_array($attributes)) {
            foreach ($attributes as $attr_name => $attr_value) {
                $s .= ' '.$attr_name.'="'.$attr_value.'"';
            }
        }
        return $s;
    }

    public static function isValid($field) {
        return is_object($field) && is_a($field, 'cjoformfield');
    }

    public function needFullColumn($needFullColumn = null) {
        if ($needFullColumn !== null) {
            $this->needFullColumn = $needFullColumn;
        }
        return $this->needFullColumn;
    }

    public function registerValidators() {
        $section = & $this->getSection();
        $form = & $section->getForm();
        $validators = & $this->getValidators();


        for ($i = 0; $i < count($validators); $i++) {
            $validator = & $validators[$i];

            if (($pos = strpos($validator['criteria'], ':')) === false) {
                // Validierung ohne Parameter

                cjoValidateEngine :: register_validator($this->_getValidatorId($validator['criteria']), $this->getName(), $validator['criteria'], $validator['empty'], $validator['halt'], $validator['transform'], $form->getName());
            } else {
                // validierung mit Parametern
                cjoValidateEngine :: register_validator($this->_getValidatorId($validator['criteria']), $this->getName().substr($validator['criteria'], $pos), substr($validator['criteria'], 0, $pos), $validator['empty'], $validator['halt'], $validator['transform'], $form->getName());
            }
        }
    }

    public function activateValidators() {
        $section = $this->getSection();
        $form = $section->getForm();
        $formValidator = $form->getValidator();

        $validators = $this->getValidators();
        
        

        for ($i = 0; $i < count($validators); $i++) {
            $validator = & $validators[$i];
            //      var_dump('validation_errors_'.$form->getName().'_'.$section->getTableName().'::'.$section->getLabel());
            $params = array (
                'id' => $this->_getValidatorId($validator['criteria']), 
                'message' => $validator['message'], 
                'form' => $form->getName(), 
                'append' => 'validation_errors_'.$form->getName().'_'.$section->getTableName().'::'.$section->getLabel(), 
                'halt' => $validator['halt']);
            // validierung starten

            smarty_function_validate($params, $formValidator);
        }
    }

    public function _getValidatorId($criteria) {
        $section = $this->getSection();
        return $section->getLabel().'->'.$this->getName().'['.$criteria.']';
    }

    public function & getValidators() {
        return $this->validators;
    }

    /**
     * Fügt einen Validator hinzu
     *
     * @param $empty
     * "empty" determines if the field is allowed to be empty or not. If
     * allowed, the validation will be skipped when the field is empty.
     * Note this is ignored with the "notEmpty" criteria.
     *
     * @param $halt
     * If the validator fails, "halt" determines if any remaining validators for
     * this form will be processed. If "halt" is yes, validation will stop at this point.
     *
     * @param $transform
     * "transform" is used to apply a transformation to a form value prior to
     * validation. For instance, you may want to trim off extra whitespace from
     * the form value before validating.
     */
    public function addValidator($criteria, $message, $empty = false, $halt = false, $transform = null) {
        $this->validators[] = array (
            'criteria' => $criteria,
            'message' => $message,
            'empty' => $empty,
            'halt' => $halt,
            'transform' => $transform
        );
    }
    
    public function enableBorderTop() {
        $this->border_top = true;
    }
    
    public function disableBorderTop() {
        $this->border_top = false;
    }
    
    public function hasValidator() {
    	$validators = $this->getValidators();
    	if (count($validators) > 0){
    	   	foreach ($validators as $validator){
	    		if (strpos($validator['criteria'],'notEmpty') !== false) return true;
	    	}
    	}
        return false;
    }

    public function get() {
        return '';
    }

    public function show() {
        echo $this->get();
    }

    public function toString() {
        $section_str = 'null';
        $section = & $this->getSection();
        if ($section !== null) {
            $section_str = $section->toString();
        }
        return 'cjoFormField: type: "'.get_class($this).'", name: "'.$this->getName().'", label: "'.$this->getLabel().'", section: "{'.$section_str.'}"';
    }
}

// Field Klassen einbinden

// 3rd Party Klassen
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/validate/SmartyValidate.class.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/validate/internals/core.assemble_plugin_filepath.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/validate/plugins/function.validate.php';

// Validierung
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/class.cjo_validator.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/validate/cjo_ValidateEngine.inc.class.php';

// Field-Basis-Klassen
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/class.cjo_formMultiValueField.inc.php';

// Allgemeine Field-Klassen
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.textField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.textAreaField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.selectField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.buttonField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.saveField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.hiddenField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.readOnlyField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.readOnlyTextField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.foreignField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.popupButtonField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.checkboxField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.radioField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.fieldsetField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.passwordField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.colorpickerField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.datepickerField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/field.simpleButtonField.inc.php';

// CONTEJO Field-Klassen
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/cjo/field.cjoSaveField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/cjo/field.cjoLinkButtonField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/cjo/field.cjoMediaButtonField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/cjo/field.cjoMediaListField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/cjo/field.cjoWYMeditorField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/fields/cjo/field.cjoMediaCategoryButtonField.inc.php';

