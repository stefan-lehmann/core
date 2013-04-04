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
class cjoPageToolsActions extends cjoPage {


    public function setEdit() {
         
        $this->form->setEditMode($this->oid);
    
        $this->fields['name'] = new textField('name', cjoI18N::translate("label_name"));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_name_notEmpty"));
    
        $this->fields['action'] = new codeField('action', cjoI18N::translate("label_input"));
    
        $this->fields['prepost'] = new selectField('prepost', "PRE/POST");
        $this->fields['prepost']->addOption("PRE",0);
        $this->fields['prepost']->addOption("POST",1);
        $this->fields['prepost']->addAttribute('size', '1');
        $this->fields['prepost']->addAttribute('style', 'width:120px');
    
        $this->fields['sadd_hidden'] = new hiddenField('sadd');
        $this->fields['sadd_hidden']->setValue('0');
        $this->fields['sadd'] = new checkboxField('sadd', 'Status',  array('style' => 'width: auto;'));
        $this->fields['sadd']->addBox('ADD ('.cjoI18N::translate("label_action_add").')', '1');
    
        $this->fields['sedit_hidden'] = new hiddenField('sedit');
        $this->fields['sedit_hidden']->setValue('0');
        $this->fields['sedit'] = new checkboxField('sedit', '&nbsp;',  array('style' => 'width: auto;'));
        $this->fields['sedit']->addBox('EDIT ('.cjoI18N::translate("label_action_edit").')', '1');
    
        $this->fields['sdelete_hidden'] = new hiddenField('sdelete');
        $this->fields['sdelete_hidden']->setValue('0');
        $this->fields['sdelete'] = new checkboxField('sdelete', '&nbsp;',  array('style' => 'width: auto;'));
        $this->fields['sdelete']->addBox('DELETE ('.cjoI18N::translate("label_action_delete").')', '1');
    
        $this->AddSection(TBL_ACTIONS, cjoI18N::translate("label_".$this->function."_action"), array ('id' => $this->oid));
    }
    
    public static function onFormSaveorUpdate($params) {
        
        $oid   = cjo_get('oid','int'); 
       
        $posted = array();
        $posted['id'] = ($added) ? $params['form']->last_insert_id : $oid;        
        $posted['name'] = cjo_post('name','string');
        
        self::setSaveExtention($posted);
    }

    protected function getDefault() {
    
        $this->list = new cjoList("SELECT id, sadd, sedit, sdelete, CONCAT(name,IF(prepost=1,' [POST]',' [PRE]')) AS name FROM ".TBL_ACTIONS,
                                  "name", '', 100);
    
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/lightning.png" alt="" />',
                                         cjoUrl::createBELink(
                                         			  '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                       array('function' => 'add', 'oid' => ''),
                                                       $this->list->getGlobalParams(),
                                                      'title="'.cjoI18N::translate("button_add").'"'));
    
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
    
        $this->cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
        $this->cols['id']->setHeadAttributes('class="icon"');
        $this->cols['id']->setBodyAttributes('class="icon"');
    
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_name").' ');
    
        $this->cols['sadd'] = new staticColumn('sadd', cjoI18N::translate("label_action_add"));
        $this->cols['sadd']->addCondition('sadd', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
        $this->cols['sadd']->addCondition('sadd', 0, '&nbsp;');
    
        $this->cols['sedit'] = new staticColumn('sedit', cjoI18N::translate("label_action_edit"));
        $this->cols['sedit']->addCondition('sedit', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
        $this->cols['sedit']->addCondition('sedit', 0, '&nbsp;');
    
        $this->cols['sdelete'] = new staticColumn('sdelete', cjoI18N::translate("label_action_delete"));
        $this->cols['sdelete']->addCondition('sdelete', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
        $this->cols['sdelete']->addCondition('sdelete', 0, '&nbsp;');
    
        $this->cols['edit'] = new editColumn();
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams());
    
        $this->list->addColumns($this->cols);
        $this->list->show(false);
    }

    public static function onListDelete($id) {

        $sql = new cjoSql();
        $qry = "SELECT DISTINCT 
                    m.id AS id, 
                    m.name AS name,
                    (SELECT a.name FROM ".TBL_ACTIONS." a WHERE a.id = ma.action_id) AS action
                FROM ".TBL_MODULES_ACTIONS." ma 
                LEFT JOIN ".TBL_MODULES." m 
                ON ma.module_id = m.id 
                WHERE ma.action_id='".$id."'";

        $result = $sql->getArray($qry);

        if (!empty($result)) {

            $temp = array();
            foreach ($result as $var){
                $temp[] = cjoUrl::createBELink(
                                   $var['name'],
                                   array('page' => 'tools', 'subpage' => 'module', 'function' => 'edit', 'mode' => 'actions', 'oid' => $var['id']));
            }
            cjoMessage::addError(cjoI18N::translate("msg_action_cannot_be_deleted",$result[0]['action']).'<br/>'.implode(' | ',$temp));
        }
        if (!cjoMessage::hasErrors()) {
            $sql->flush();
            $result = $sql->getArray("SELECT * FROM ".TBL_ACTIONS." WHERE id='".$id."'");
            $sql->flush();
            if ($sql->statusQuery("DELETE FROM ".TBL_ACTIONS." WHERE id='".$id."'",
                                  cjoI18N::translate("msg_action_deleted"))) {
                cjoExtension::registerExtensionPoint('ACTION_DELETED', $result[0]); 
            }
        }
    }
}