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

// Form Komponenten einbinden
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/class.cjo_fieldContainer.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/class.cjo_fieldController.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/class.cjo_formSection.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/form/class.cjo_formField.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/functions/function_cjo_form.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/class.cjo_formatter.inc.php';


define('FORM_INFO_MSG'   , 'success');
define('FORM_WARNING_MSG', 'warning');
define('FORM_ERROR_MSG'  , 'error');

/**
 * CJoForm Klasse
 */

class cjoForm extends cjoFieldContainer {

    // Name des Formulars
    public $name;
    // Status-Meldungen
    public $messages;
    // Url, auf die nach druck auf "Uebernehmen" umgeleitet wird
    public $redirect;
    // Zuletzt hinzugefügte Section
    public $section;
    // Alle Sections
    public $sections;
    // Validierungsmeldungen
    public $errors;
    // Validatoren
    public $validator;
    // Datenbankverbindung
    public $sql;
    // Debug-Flag
    public $debug;
    // Modus des Formulars true/false
    public $edit_mode;
    public $enctype;
    public $valid_master;
    public $validated;  

    public function cjoForm($name = '') {
        
        global $mypage, $subpage;
        
        $this->name         = !$name ? $mypage.'_'.$subpage.'_form' : $name;
        $this->messages     = array();
        $this->redirect     = array();
        $this->enctype      = '';
        $this->edit_mode    = true;

        $this->errors       = null;
        $this->validator    = new cjoValidator();
        $this->valid_master = true;                
        $this->validated    = false;        

        $this->sql          = new cjoSql();
        $this->debug        = & $this->sql->debugsql;
        
        cjoAssistance::resetAfcVars();
    }

    public function applyRedirect($action, $params) {
        $this->redirect[$action] = $params;
    }
    
    public function applyRedirectOnCancel($params) {
        $this->applyRedirect('cancel', $params);
    }
    
    public function applyRedirectOnSave($params) {
        $this->applyRedirect('save', $params);
    }
    
    public function applyRedirectOnUpdate($params) {
        $this->applyRedirect('update', $params);
    }    

    public function setEnctype($enctype = 'multipart/form-data') {
        if ($enctype != '') {
            $this->enctype = ' enctype="' . $enctype . '"';
        }
    }

    /**
     * Versetzt das Formular in den Editier-Modus.
     * Im Editier Modus, ist ein "ÜBERNEHMEN" und "SPEICHERN" Button vorhanden.
     * Diese sind sonst nicht eingeblendet.
     */
    public function setEditMode($edit_mode) {
        $this->edit_mode = (bool) $edit_mode;
    }

    public function isEditMode() {
        return $this->edit_mode;
    }

    public function isValid($form) {

        return is_object($form) && is_a($form, 'cjoform');
    }

    public function & getValidator() {
        return $this->validator;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function setMessage($message, $message_type) {
        $this->messages[] = array($message, $message_type);
    }

    public function resetMessages() {
        $this->messages = array ();
    }

    public function setMessages($message_array) {
        if (!is_array($message_array))
        return false;

        $this->resetMessages();

        foreach ($message_array as $message_type => $messages) {

            if (is_array($messages)) {
                foreach ($messages as $message) {
                    $this->setMessage($message, $message_type);
                }
            }
        }
    }

    public function getName() {
        return $this->name;
    }

    public function addField(& $field, &$section = false) {
        $section = $this->getSection();
        parent :: addField($field, $section);
    }

    public function addFields(& $fields) {
        if (!is_array($fields))
        return false;
        foreach ($fields as $key => $field) {
            $this->addField($fields[$key]);
        }
    }

    public function addSection(& $section) {
        if (!cjoFormSection :: isValid($section)) {
            cjoForm :: triggerError('Unexpected type "' . gettype($section) . '" for $section! Expecting type string or cjoFormSection-Object!');
        }

        $section->cjoform = & $this;
        $this->section = & $section;
        $this->sections[] = & $section;
    }

    public function addSections(& $sections) {
        if (!is_array($sections))
        return false;
        foreach ($sections as $key => $field) {
            $this->addSection($sections[$key]);
        }
    }

    public function & getSection() {
        return $this->section;
    }

    public function & getSections() {
        return $this->sections;
    }

    public function numSections() {
        return count($this->getSections());
    }

    public function triggerError($message, $message_type = E_USER_ERROR) {
        trigger_error('cjoForm: ' . $message, $message_type);
    }

    public function registerValidators() {
        // register our validators
        $sections = $this->getSections();
        for ($i = 0; $i < count($sections); $i++) {
            $sections[$i]->registerValidators();
        }
    }

    public function activateValidators() {
        $sections = $this->getSections();
        for ($i = 0; $i < count($sections); $i++) {
            $sections[$i]->activateValidators();
        }
    }

    public function delete() {
        $sections = $this->getSections();
        for ($i = 0; $i < count($sections); $i++) {
            $sections[$i]->delete();
        }
        // trigger extensions point
        cjoExtension::registerExtensionPoint('CJO_FORM_' . strtoupper($this->getName()) . '_DELETE', array (
            'form' => $this
        ));
    }
    
    public function save() {
        $sections = $this->getSections();
        $messages = array();
        for ($i = 0; $i < count($sections); $i++) {
            $sql_error = $sections[$i]->save();
            if ($sql_error) {
                $messages[md5($sql_error)] = $sql_error;
            }
        }
        // trigger extensions point
        if (empty($messages)) {
            cjoExtension::registerExtensionPoint('CJO_FORM_' . strtoupper($this->getName()) . '_SAVE', array (
                'form' => $this
            ));
        }

        return $messages;
    }

    public function _get($addDefaultFields = true) {
        
        global $I18N;

        if ($addDefaultFields) {
            $section = $this->getSection();
            $section->addField(new cjoSaveField());
        }

        if ($this->enctype == '') {
            $this->setEnctype();
        }

        $s = cjoExtension::registerExtensionPoint('CJO_FORM_'.strtoupper($this->getName()).'_BEFORE', array('subject' => '', 'form' => $this));
        
        
        $s .= '<!-- cjoForm start -->' . "\r\n";
        $s .= '<div class="a22-cjoform">' . "\r\n";
        $s .= '  <form action="index.php" id="'. $this->name.'" name="'. $this->name.'" method="post"' . $this->enctype . ' accept-charset="' . $I18N->msg("htmlcharset") . '">' . "\r\n";
        $s .= 		cjoExtension::registerExtensionPoint('CJO_FORM_' . strtoupper($this->getName()) . '_START', array ('form' => $this));
        $s .= '    <div class="a22-cjoform-hidden">' . "\r\n";
        $s .= '    	<input type="hidden" value="' . $this->name . '" name="cjo_form_name" />' . "\r\n";

        $def_params = cjo_a22_getDefaultGlobalParams();
        if (is_array($def_params)) {
            foreach ($def_params as $name => $value) {
                $field = new hiddenField($name);
                $field->setValue($value);
                $s .= '      ' . $field->get() . "\r\n";
            }
        }

        // Show Hidden fields
        $fields = $this->getFields();
        $numFields = $this->numFields();
        if (is_array($fields)) {
            foreach ($fields as $key => $field) {
                if (is_a($field, 'hiddenfield')) {
                    $s .= '      ' . $field->get() . "\r\n";
                }
            }
        }
        $s .= '    </div>' . "\r\n";

        // Show Sections
        $sections = $this->getSections();
        $s_sections = '';

        if (is_array($sections)){
            foreach ($sections as $key => $section_temp) {
                $section = & $section_temp;
                $s_sections .= $section->get();
            }
        }
        // Show Messages
        if (cjo_post('cjo_form_name','string') == $this->getName()) {
            $this->formatMessages();
        }

        $s .= $s_sections;
        $s .= 		cjoExtension::registerExtensionPoint('CJO_FORM_' . strtoupper($this->getName()) . '_END', array ('form' => $this));
        $s .= '  </form>' . "\r\n";
        $s .= '</div>' . "\r\n";
        $s .= '<!-- cjoForm end -->' . "\r\n";

        return cjoExtension::registerExtensionPoint('CJO_FORM_'.strtoupper($this->getName()).'_AFTER', array('subject' => $s, 'form' => $this));
    }


    public function validate() {
        
        global $SMARTY_VALIDATE;
        
        if ($this->validated) return $this->valid_master;
        
        cjoValidateEngine :: connect($this->getValidator());
        cjoValidateEngine :: register_form($this->getName(), true);
        $this->registerValidators();
        $this->activateValidators();
        
        if (cjo_post('cjo_form_name','string') != $this->getName()) {
            // validate after a POST 
            $this->valid_master = false;
        }
        else {
            $this->valid_master = cjoValidateEngine :: is_valid($_POST, $this->getName());
        }
        
        $this->validated = true;
        
        return $this->valid_master;
    }
    
    public function get($addDefaultFields = true) {

        global $CJO, $I18N, $SMARTY_VALIDATE;
        
        $this->validate();

        if (cjo_post('cjoform_cancel_button','bool')) {
            $this->redirectCancel();
            return false;
        }

        // Nur auf buttons reagieren, die von cjo_form sind
        if (cjo_post('cjoform_save_button','bool') ||
            cjo_post('cjoform_update_button','bool')) {
             
            if ($this->valid_master) {
                
                $messages = $this->save();

                if (empty($messages)) {
                    $this->setMessage($I18N->msg('msg_data_saved'), FORM_INFO_MSG);
                    
                    if (cjo_post('cjoform_save_button','bool')) {
                        // Speichern Button wurde gedrückt
                        $this->redirectSave();
                        return;
                    }
                    if (cjo_post('cjoform_update_button','bool')) {
                        $this->redirectUpdate();
                    }
                }
                else {
                    $this->setMessage($I18N->msg('msg_data_not_saved'), FORM_ERROR_MSG);
                    foreach($messages as $message){
                        $this->setMessage($message, FORM_ERROR_MSG);
                    }
                }
            }
            else {
                $this->setMessage($I18N->msg('msg_data_not_saved'), FORM_ERROR_MSG);
            }
        }
        return $this->_get($addDefaultFields);
    }

    public function show($addDefaultFields = true, $render = true) {
        if ($render){
            echo $this->get($addDefaultFields);
        } else {
            return $this->get($addDefaultFields);
        }
    }

    public function formatMessages() {

        $messages = $this->getMessages();

        if (!is_array($messages)) return false;

        foreach ($messages as $message){

            switch($message[1]){
                case FORM_ERROR_MSG   : cjoMessage::addError($message[0]); break;
                case FORM_INFO_MSG    : cjoMessage::addSuccess($message[0]); break;
                case FORM_WARNING_MSG : cjoMessage::addWarning($message[0]); break;
            }
        }
    }

    public function redirectForm($type) {
        
        $params = $this->redirect[$type];

        if (empty($params)) return false;
        
        if (is_string($params)) {
            if ($this->debug) exit ('<hr />Redirect to:' . $params);
            cjoAssistance::redirectBE($params);
        }
        
        if (is_array($params)) {
            if ($this->debug) exit ('<hr />Redirect to:' . cjoAssistance::createBEUrl($params));
            cjoAssistance::redirectBE($params);
        }
    }
    
    public function redirectCancel() {
        $this->redirectForm('cancel');
    }
    
    public function redirectSave() {
        $this->redirectForm('save');
    }
    
    public function redirectUpdate() {
        $this->redirectForm('update');
    }    
    /**
     * Durchsucht das Formular nach einem Feld
     * @param string Name des Feldes, wonach gesucht werden soll
     * @return object|null Bei erfolgreicher Suche wird ein cjoFormField-Objekt zurückgegeben, sonst null
     * @access public
     */
    public function searchField($name) {

        $result = parent :: searchField($name);
        if ($result !== null) {
            return $result;
        }

        $sections = $this->getSections();
        for ($i = 0; $this->numSections(); $i++) {
            $section = & $sections[$i];
            $result = $section->searchField($name);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    public function toString() {
        return 'cjoForm: name: "' . $this->getName() . '", edit_mode: "' . ($this->isEditMode() ? 'true' : 'false') . '", sections: "' . $this->numSections() . '"';
    }
}
