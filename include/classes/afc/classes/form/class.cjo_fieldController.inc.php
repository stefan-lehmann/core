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

define('CONTROLLER_INSERT_MODE', 1);
define('CONTROLLER_UPDATE_MODE', 2);

class cjoFieldController extends cjoFieldContainer {

    // Tabellen Name
    public $tablename;
    // Where Bedingungen
    public $where_params;
    // Wenn mehrere Sections auf die gleiche Tabelle zeigen, diese als einen Datensatz behandeln
    public $join_equal_sections;
    // Aktueller Datensatz
    public $dataset;
    // Aktueller Modus (Insert/Update)
    public $mode;
    // Validierungsmeldungen
    public $errors;
    // Referenz zu CjoForm
    public $cjoform;

    /**
     * Klassenkonstruktor
     * @param string Name der Tabelle, auf den dieser Abschnitt gemappt werden soll
     * @param array Array von Where Parametern, die genau einen Datensatz der Tabelle beschreiben
     */
    public function cjoFieldController($tablename, $where_params) {
        
        cjoProp::isValidType($tablename, 'string', __FILE__, __LINE__);
  
        $this->tablename = $tablename;
        $this->setWhere($where_params);

        $this->mode         = null;
        $this->prev_dataset = null;
        $this->dataset      = null;
        $this->errors       = null;

        // Parentkonstruktor aufrufen
        $this->cjoFieldContainer();
    }

    public function setWhere($where_params) {
        
        cjoProp::isValidType($where_params, 'array', __FILE__, __LINE__);

        $this->where_params = $where_params;
    }

    public function getWhere() {
        return $this->where_params;
    }

    public function getTableName() {
        return $this->tablename;
    }

    public function & getForm() {
        return $this->cjoform;
    }

    public function _getMode() {
        
        if ($this->getTableName() == '') {
            $this->dataset = $_POST;
            return $this->mode;  
        }

        if ($this->mode === null) {
            // Wenn der Select 0 Zeilen liefert => Insert
            // Wenn der Select 1 Zeile liefert => Update
            // Wenn der Select > 1 Zeilen liefert => Where Clause passt nicht
            $form = & $this->getForm();

            $sql = & $form->sql;
            $sql->setTable($this->getTableName());
            $sql->setWhere($this->getWhere());  
            $sql->setLimit(1);             
            $sql->Select('*'); 

            switch ($sql->getRows()) {
                case 0 :
                    $this->mode = CONTROLLER_INSERT_MODE;
                    $this->dataset = array ();
                    break;
                case 1 :
                    $this->mode = CONTROLLER_UPDATE_MODE;
                    $result = $sql->getArray();
                    $this->dataset = $this->_transformDataSet($result[0]);
                    break;
                default :
                    throw new cjoException('Given WHERE-parameters affect more than one row!');
                    return;
            }

        }
        return $this->mode;
    }

    public function _getDataSet() {
        if ($this->dataset === null) {
            // Der Datensatz wird in _getMode() bestimmt
            $this->_getMode();
        }
        return $this->dataset;
    }

    public function _transformDataSet($results) {

        $dataset = array();

        if (!is_array($results)) return $dataset;

        foreach($results as $key => $value) {
            $values = @unserialize($value);

            if (is_array($values)) {
                foreach($values as $subkey => $subvalue) {
                    $dataset[$key.'['.$subkey.']'] = $subvalue;
                }
            }
            else{
                $dataset[$key] = $value;
            }
        }
        return $dataset;
    }

    public static function isValid($section) {
        return is_object($section) && is_a($section, 'cjofieldcontroller');
    }

    public function addField(& $field, & $section = false) {
        $section = & $this;
        parent :: addField($field, $section);
    }

    public function addFields(& $fields) {
        if (!is_array($fields)) return false;
        foreach ($fields as $key => $field) {
            $this->addField($fields[$key]);
        }
    }

    public function getErrors($revalidate = false) {
        if ($this->errors === null || $revalidate) {
            $form = & $this->getForm();
            $validator = & $form->getValidator();

            $var_identifier = 'validation_errors_' . $form->getName() . '_' . $this->getTableName() . '::' . $this->getLabel();
            $errors = $validator->get_template_vars($var_identifier);

            $this->errors = $errors === null ? array () : $errors;
        }

        return $this->errors;
    }

    public function numErrors() {
        return count($this->getErrors());
    }

    public function registerValidators() {
        $fields = & $this->getFields();

        for ($i = 0; $i < count($fields); $i++) {
            $fields[$i]->registerValidators();
        }
    }

    public function activateValidators() {
        $fields = & $this->getFields();

        for ($i = 0; $i < count($fields); $i++) {
            $fields[$i]->activateValidators();
        }
    }

    public function delete() {
        
        $form = & $this->getForm();
        $sql = & $form->sql;
        $sql->setTable($this->getTableName());
        $sql->setWhere($this->getWhere());  
        $sql->setLimit(1);             
        return $sql->Delete(); 
    }

    public function save() {

        $preocessed = 0;
        $posted = array();
 
        // Set values
        $fields = & $this->getFields();

        if ($this->numFields() == 0) return false;

        for ($i = 0; $i < $this->numFields(); $i++) {
            
            if ($fields[$i]->getName() == '' ||
                $fields[$i]->activateSave != true ||
                $fields[$i]->getInsertValue() === null ||
                isset($posted[$fields[$i]->getName()]))  continue;            
            
            if (strpos($fields[$i]->getName(), '[') !== false) {
                $key = array();
                $n = $fields[$i]->getName();

                $key[1] = substr($n, 0, strpos($n, '['));
                $key[2] = substr($n, strpos($n, '['));
                $key[2] = stripslashes(str_replace('"', "'", $key[2]));
                $key[2] = stripslashes(str_replace("[", "['", $key[2]));
                $key[2] = stripslashes(str_replace("]", "']", $key[2]));

                if ($key[1] == '' || $key[2] == '') continue;

                $call = "$"."posted['".$key[1]."']".$key[2]." = $"."_POST['".$key[1]."']".$key[2].";";
                eval($call);
            }
            else {
                $posted[$fields[$i]->getName()] = $fields[$i]->getInsertValue();
            }
        }    
        if (count($posted) == 0) return false;
        
        
        if ($this->type == 'sql') {
                    
            $form = & $this->getForm();
            $sql = & $form->sql;  
            $sql->setTable($this->getTableName());  
            $table_fields = cjoSql::getFieldNames($this->getTableName());
            
            foreach($posted as $key=>$value) {
            
                if ($sql->hasSetValue($key) ||
                    !in_array($key, $table_fields)) continue;
                
                if ($value == 'setNewId') {
                    $sql->setNewId($key);
                    continue;
                }
                if (is_array($value)) {
                    $sql->setValue($key, cjo_json_encode_utf8($value));
                }
                else {
                    $sql->setValue($key, $value);
                }
            }
            
            switch ($this->_getMode()) {
                case CONTROLLER_INSERT_MODE :
                    if (in_array('createdate', $table_fields) &&
                        in_array('createuser', $table_fields)) {
                        $sql->addGlobalCreateFields();
                    }
                    $sql->Insert();
                    $form->last_insert_id = $sql->getLastId();
                    break;
    
                case CONTROLLER_UPDATE_MODE :
                    if (in_array('updatedate', $table_fields) &&
                        in_array('updateuser', $table_fields)) {
                        $sql->addGlobalUpdateFields();
                    }
    
                    $sql->setWhere($this->getWhere()); 
                    $sql->setLimit(1); 
                    $sql->Update();
                    break;
    
                default :
                    throw new cjoException('Unexpected value "' . $mode . '" for $mode !');
                    return;
            }
            
            unset($this->dataset);
            unset($this->mode);
            $this->_getDataSet(); // Dataset neu laden
    
            return $sql->getError();
        } 
        elseif($this->type == 'array') {
            $this->dataset = $posted;
        }
        elseif(cjoAddon::isActivated($this->type)) {

            $data = array_merge(cjoAddon::getProperty('settings', $this->type), $posted);
            cjoAddon::setProperty('settings', $data, $this->type);
            return !cjoAddon::saveParameterFile($this->type, $data, cjoPath::addonAssets($this->type,$this->file));
        }
    }

    public function toString() {
        return 'cjoFieldController: tablename: "' . $this->getTableName() . '", label: "' . $this->getLabel() . '", felder: "' . $this->numFields() . '"';
    }
}
