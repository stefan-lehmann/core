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

if ($function == 'delete'){

    if($oid != ''){
        $sql = new cjoSql();
        $qry = "SELECT DISTINCT 
                	m.id AS id, 
                	m.name AS name 
                FROM ".TBL_MODULES_ACTIONS." a 
                LEFT JOIN ".TBL_MODULES." m 
                ON a.module_id = m.id 
                WHERE a.action_id='".$oid."'";

        $result = $sql->getArray($qry);

        if (!empty($result)) {

            $temp = array();
            foreach ($result as $var){
                $temp[] = cjoUrl::createBELink(
                                   $var['name'],
                                   array('page' => 'tools', 'subpage' => 'modules', 'function' => 'edit', 'oid' => $var['id']));
            }
            cjoMessage::addError(cjoI18N::translate("msg_action_cannot_be_deleted",$result[0]['name']).'<br/>'.implode(' | ',$temp));
        }
        if (!cjoMessage::hasErrors()) {
            $sql->flush();
            $result = $sql->getArray("SELECT * FROM ".TBL_ACTIONS." WHERE id='".$oid."'");
            $sql->flush();
            if ($sql->statusQuery("DELETE FROM ".TBL_ACTIONS." WHERE id='".$oid."'",
                                  cjoI18N::translate("msg_action_deleted"))) {
                cjoExtension::registerExtensionPoint('ACTION_DELETED', $result[0]); 
            }
        }
    }
    unset($function);
}

if ($function == "add" || $function == "edit" ) {
    
    //Form
    $form = new cjoForm();
    $form->setEditMode($oid);
    //$form->debug = true;

    //Fields
    $fields['name'] = new textField('name', cjoI18N::translate("label_name"));
    $fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_name_notEmpty"));

    $fields['action'] = new codeField('action', cjoI18N::translate("label_input"));

    $fields['prepost'] = new selectField('prepost', "PRE/POST");
    $fields['prepost']->addOption("PRE",0);
    $fields['prepost']->addOption("POST",1);
    $fields['prepost']->addAttribute('size', '1');
    $fields['prepost']->addAttribute('style', 'width:120px');

    $fields['sadd_hidden'] = new hiddenField('sadd');
    $fields['sadd_hidden']->setValue('0');
    $fields['sadd'] = new checkboxField('sadd', 'Status',  array('style' => 'width: auto;'));
    $fields['sadd']->addBox('ADD ('.cjoI18N::translate("label_action_add").')', '1');

    $fields['sedit_hidden'] = new hiddenField('sedit');
    $fields['sedit_hidden']->setValue('0');
    $fields['sedit'] = new checkboxField('sedit', '&nbsp;',  array('style' => 'width: auto;'));
    $fields['sedit']->addBox('EDIT ('.cjoI18N::translate("label_action_edit").')', '1');

    $fields['sdelete_hidden'] = new hiddenField('sdelete');
    $fields['sdelete_hidden']->setValue('0');
    $fields['sdelete'] = new checkboxField('sdelete', '&nbsp;',  array('style' => 'width: auto;'));
    $fields['sdelete']->addBox('DELETE ('.cjoI18N::translate("label_action_delete").')', '1');

	if ($function == 'add') $oid = '';

    /**
     * Do not delete translate values for cjoI18N collection!
     * [translate: label_add_action]
     * [translate: label_edit_action]
     */
    $section = new cjoFormSection(TBL_ACTIONS, cjoI18N::translate("label_".$function."_action"), array ('id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();    
    
    if ($form->validate() || $add_no_user) {

        $posted = array();
        $posted['id'] = ($function == "add") ? $form->last_insert_id : $oid;        
        $posted['name'] = cjo_post('name','string');
        
        if ($function == 'add') {
            cjoExtension::registerExtensionPoint('ACTION_ADDED', $posted); 
        }
        else {
            cjoExtension::registerExtensionPoint('ACTION_UPDATED', $posted); 
        }
        
        if (cjo_post('cjoform_save_button', 'boolean')) {
            if ($function == 'edit') {
                cjoMessage::addSuccess(cjoI18N::translate("msg_action_updated"));
            }
            else {
                cjoMessage::addSuccess(cjoI18N::translate("msg_action_added"));
            }
            unset($function);
        }
    }
}

if ($function == '') {

    //LIST Ausgabe
    $list = new cjolist("SELECT id, sadd, sedit, sdelete, CONCAT(name,IF(prepost=1,' [POST]',' [PRE]')) AS name FROM ".TBL_ACTIONS,
                        "name",
                        '',
                        100);

    $cols['icon'] = new staticColumn('<img src="img/silk_icons/lightning.png" alt="" />',
                                     cjoUrl::createBELink(
                                     			  '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                   array('function' => 'add', 'oid' => ''),
                                                   $list->getGlobalParams(),
                                                  'title="'.cjoI18N::translate("button_add").'"'));

    $cols['icon']->setHeadAttributes('class="icon"');
    $cols['icon']->setBodyAttributes('class="icon"');
    $cols['icon']->delOption(OPT_SORT);

    $cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon"');

    $cols['name'] = new resultColumn('name', cjoI18N::translate("label_name").' ');

    $cols['sadd'] = new staticColumn('sadd', cjoI18N::translate("label_action_add"));
    $cols['sadd']->addCondition('sadd', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
    $cols['sadd']->addCondition('sadd', 0, '&nbsp;');

    $cols['sedit'] = new staticColumn('sedit', cjoI18N::translate("label_action_edit"));
    $cols['sedit']->addCondition('sedit', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
    $cols['sedit']->addCondition('sedit', 0, '&nbsp;');

    $cols['sdelete'] = new staticColumn('sdelete', cjoI18N::translate("label_action_delete"));
    $cols['sdelete']->addCondition('sdelete', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
    $cols['sdelete']->addCondition('sdelete', 0, '&nbsp;');

    // Bearbeiten link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
    $cols['edit']->setHeadAttributes('colspan="2"');
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

    $img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));

    //Spalten zur Anzeige hinzufügen
    $list->addColumns($cols);
    //Tabelle anzeigen
    $list->show(false);
}