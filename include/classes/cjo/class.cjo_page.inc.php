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

class cjoPage {
    
    protected $form;  
    protected $list;    
    protected $subpage;  
    protected $section;
    protected $oid; 
    protected $function = false;
    protected $mode     = false;
    protected $fields   = array();
    protected $cols     = array();
    protected $params   = array(); 
    protected $default_buttons = true;    
    protected $list_delete     = false;

    function __construct() {
        
        $this->getParams();

        if (method_exists($this, 'set'.$this->function)) {
            $this->initForm();  
            $this->{'set'.$this->function}();
            if ($this->getForm()) return;
        }
        $this->getDefault();
    }
    
    protected function initForm() {

        $this->form = new cjoForm(); 
        
        $class = get_called_class();
        
        $this->form->onIsValid($class.'::onFormIsValid');  
        $this->form->onIsInvalid($class.'::onFormIsInvalid');  
        $this->form->onCancel($class.'::onFormCancel');  
        $this->form->onDelete($class.'::onFormDelete'); 
        $this->form->onSave($class.'::onFormSave');  
        $this->form->onUpdate($class.'::onFormUpdate'); 
        $this->form->onSaveorUpdate($class.'::onFormSaveorUpdate');  
        
        $this->setMode(); 
        $this->setMessage();          
    }
    
    
    protected function setAdd() {
        $this->oid = '';
        $this->setEdit();
    }
   
    protected function setEdit() { }
    
    protected function getDefault() {
        $this->initForm(); 
        $this->setEdit();
        $this->getForm();
    }
        
    protected function getForm() {
        
        if (empty($this->section)) return false;
        $this->section->addFields($this->fields);
        $this->form->addSection($this->section);
        return $this->form->show($this->default_buttons);
    }  

    protected function AddSection($data, $label='', $where = array(), $columns = 1) {
        $this->section = new cjoFormSection($data, $label, $where, $columns);
    }
    
    protected function addCreateFields() {
            
        $this->fields['createdate_hidden'] = new hiddenField('createdate');
        $this->fields['createdate_hidden']->setValue(time());
        
        $this->fields['createuser_hidden'] = new hiddenField('createuser');
        $this->fields['createuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));
    }

    protected function addUpdateFields() {
            
        if (self::isAddMode()) {
            $this->addCreateFields();
            return;    
        }    
            
        $this->fields['updatedate_hidden'] = new hiddenField('updatedate');
        $this->fields['updatedate_hidden']->setValue(time());

        $this->fields['updateuser_hidden'] = new hiddenField('updateuser');
        $this->fields['updateuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));

        $this->fields['headline1'] = new headlineField(cjoI18N::translate("label_info"), true);

        $this->fields['updatedate'] = new readOnlyField('updatedate', cjoI18N::translate('label_updatedate'), array(), 'label_updatedate');
        $this->fields['updatedate']->setFormat('strftime',cjoI18N::translate('dateformat_sort'));
        $this->fields['updatedate']->setDefault('--');        
        $this->fields['updatedate']->needFullColumn(true);

        $this->fields['updateuser'] = new readOnlyField('updateuser', cjoI18N::translate('label_updateuser'), array(), 'label_updateuser');
        $this->fields['updateuser']->setDefault('--');        
        $this->fields['updateuser']->needFullColumn(true);

        $this->fields['createdate'] = new readOnlyField('createdate', cjoI18N::translate('label_createdate'), array(), 'label_createdate');
        $this->fields['createdate']->setFormat('strftime',cjoI18N::translate('dateformat_sort'));
        $this->fields['createdate']->setDefault('--');        
        $this->fields['createdate']->needFullColumn(true);

        $this->fields['createuser'] = new readOnlyField('createuser', cjoI18N::translate('label_createuser'), array(), 'label_createuser');
        $this->fields['createuser']->setDefault('--');        
        $this->fields['createuser']->needFullColumn(true);
    }

    protected function getParams() {
        
        if (cjo_request('cjoform_cancel_button', 'bool')) return false;
        
        $this->oid                = cjo_get('oid', 'int', '');
        $this->function           = cjo_get('function', 'string');
        $this->mode               = cjo_get('mode', 'string');
        $this->params             = $_GET;

        if ($this->function != 'add' && 
            $this->function != 'edit' && 
            $this->function != 'delete') {
            $this->function = false;
        }
    }
    
    protected static function isAddMode() {
        return cjo_get('function', 'string') == 'add';
    } 
    
    protected function setMode($edit_mode=NULL){
        if ($edit_mode === NULL) $edit_mode = $this->oid != '';
        $this->form->setEditMode((bool) $edit_mode);
    }  
    
    protected function setMessage($message=NULL) {
        if ($message === NULL) {
            $message = 'msg_'.cjoProp::getSubpage();
            $message .= self::isAddMode() ? '_added' : '_updated';
        }
        $this->form->setSuccessMessage(strtolower((string) $message));
    }
    
    protected static function setSaveExtention($params=NULL, $extension=NULL) {
        if ($params === NULL) {
            $params = $_POST;
        }
        if ($extension === NULL) {
            $extension = strtoupper(cjoProp::getSubpage());
            $extension .= self::isAddMode() ? '_ADDED' : '_UPDATED';
        }        
        cjoExtension::registerExtensionPoint($extension, $params);
    }
     
    public static function onFormIsValid($params)      {}      
    public static function onFormIsInvalid($params)    {}
    public static function onFormCancel($params)       {} 
    public static function onFormDelete($params)       {} 
    public static function onFormSave($params)         {} 
    public static function onFormUpdate($params)       {} 
    public static function onFormSaveorUpdate($params) {}  
    
    
    protected function getDeleteColParams($params=array('id'=>'%id%')) {
        if (isset($this->list_delete) && is_callable($this->list_delete)) {
            $function = $this->list_delete;
        } else {
            $function = get_called_class().'::onListDelete';
        }
        return array_merge(array('function' => $function), $params);
    }    
    
    public static function onListDelete($id) {}    
}
