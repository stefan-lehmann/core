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

if ($function == "add" || $function == "edit" ) {

	//Form
	$form = new cjoForm();
	$form->setEditMode($oid);
    //$form->debug = true;

	//Fields
	$fields['name'] = new textField('name', cjoI18N::translate("label_name"));
	$fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_name_notEmpty"), false, false);

	$labels = array('application_gallery',
					'application_view_gallery_star',
					'application_view_list2',
					'application_view_tile',
					'page_white',
					'page_white_code_red',
					'page_white_text');
	sort($labels);

	$fields['label'] = new selectField('label', cjoI18N::translate("label_icon"));
	$fields['label']->addOption('--','');
	foreach($labels as $label) {
			$fields['label']->addOption($label,$label);
	}
	$fields['label']->addAttribute('size', '1');

	$fields['active_hidden'] = new hiddenField('active');
	$fields['active_hidden']->setValue('0');
	$fields['active'] = new checkboxField('active', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['active']->addBox(cjoI18N::translate("label_active"), '1');

	$fields['content'] = new codeField('content', cjoI18N::translate("label_input"));

    if (cjoProp::countCtypes() > 0) {
    	$fields['ctypes'] = new selectField('ctypes', cjoI18N::translate("label_ctype_connection"));
    	$fields['ctypes']->setMultiple();
    	$fields['ctypes']->setValueSeparator('|');
    	$fields['ctypes']->activateSave(false);

    	foreach(cjoProp::getCtypes() as $ctype_id) {
    		$fields['ctypes']->addOption(cjoProp::getCtypeName($ctype_id),$ctype_id);
    	}
    	$fields['ctypes']->addAttribute('size', cjoProp::countCtypes()+1);
    } else {
    	$fields['ctypes'] = new hiddenField('ctypes');
    	$fields['ctypes']->setValue('0');
    }


	if ($function == 'add') {

		$oid = '';

		$fields['createdate_hidden'] = new hiddenField('createdate');
		$fields['createdate_hidden']->setValue(time());

		$fields['createuser_hidden'] = new hiddenField('createuser');
		$fields['createuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));
	}
	else {

		$fields['updatedate_hidden'] = new hiddenField('updatedate');
		$fields['updatedate_hidden']->setValue(time());

		$fields['updateuser_hidden'] = new hiddenField('updateuser');
		$fields['updateuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));

		$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
		$fields['headline1']->setValue(cjoI18N::translate("label_info"));
		$fields['headline1']->needFullColumn(true);

		$fields['updatedate'] = new readOnlyField('updatedate', cjoI18N::translate('label_updatedate'), array(), 'label_updatedate');
		$fields['updatedate']->setFormat('strftime',cjoI18N::translate('dateformat_sort'));
		$fields['updatedate']->needFullColumn(true);

		$fields['updateuser'] = new readOnlyField('updateuser', cjoI18N::translate('label_updateuser'), array(), 'label_updateuser');
		$fields['updateuser']->needFullColumn(true);

		$fields['createdate'] = new readOnlyField('createdate', cjoI18N::translate('label_createdate'), array(), 'label_createdate');
		$fields['createdate']->setFormat('strftime',cjoI18N::translate('dateformat_sort'));
		$fields['createdate']->needFullColumn(true);

		$fields['createuser'] = new readOnlyField('createuser', cjoI18N::translate('label_createuser'), array(), 'label_createuser');
		$fields['createuser']->needFullColumn(true);
	}

    /**
     * Do not delete translate values for cjoI18N collection!
     * [translate: label_add_template]
     * [translate: label_edit_template]
     */
	$section = new cjoFormSection(TBL_TEMPLATES, cjoI18N::translate("label_".$function."_template"), array ('id' => $oid));

	$section->addFields($fields);
	$form->addSection($section);
	$form->show();

	if ($form->validate()) {
		if ($function == "add") {
			$oid = $form->last_insert_id;
			cjoAssistance::updatePrio(TBL_TEMPLATES,$oid,time());
		}

        $ctypes = cjo_post('ctypes','array', array());

    	$update = new cjoSql();
    	$update->setTable(TBL_TEMPLATES);
    	$update->setValue("ctypes",'|'.@implode('|',$ctypes).'|');

    	if ($function == "add") {
    		$update->setWhere("id='".$oid."'");
    		$update->addGlobalCreateFields();
    	}
    	else {
    		$update->setWhere("id='".$oid."'");
    		$update->addGlobalUpdateFields();

    	}
    	$update->Update();

		cjoGenerate::generateTemplates($oid);
		
		if (!cjoMessage::hasErrors()) {
		    
		    if ($function == "add") {
                cjoExtension::registerExtensionPoint('TEMPLATE_ADDED', array ("id" => $oid));
            } else {
                cjoExtension::registerExtensionPoint('TEMPLATE_UPDATED', array ("id" => $oid));
            }
		    
		    if (cjo_post('cjoform_save_button', 'boolean')) {
    			if ($function == 'edit') {
    			    cjoMessage::addSuccess(cjoI18N::translate("msg_template_updated", cjo_post('name', 'string')));
    			}
    			else {
    			    cjoMessage::addSuccess(cjoI18N::translate("msg_template_added", cjo_post('name', 'string')));
    			}
    			unset($function);
		    }
		}
	}
}

if ($function != '') return;

//LIST Ausgabe
$list = new cjoList("SELECT * FROM ".TBL_TEMPLATES, 'prior', 'ASC', '', 100);

$cols['icon'] = new resultColumn('label',
								 cjoUrl::createBELink(
								 			  '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("label_add_template").'" />',
											  array('function' => 'add', 'oid' => ''),
											  $list->getGlobalParams(),
											  'title="'.cjoI18N::translate("label_add_template").'"'),
								'sprintf',
								'<img src="img/silk_icons/%s.png" alt="true" />');

$cols['icon']->addCondition('label', '', '<img src="img/silk_icons/layout.png" alt="true" />');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', cjoI18N::translate("label_name").' ');

$cols['prio'] = new prioColumn();

$cols['status'] = new statusColumn('accept', NULL, false, cjoI18N::translate("label_active"));
$cols['status']->addCondition('active', '1', '');
$cols['status']->addOption(OPT_SORT);
    

$cols['ctypes'] = new resultColumn('ctypes', cjoI18N::translate("label_ctype_connection"), 'replace_array', array(cjoProp::get('CTYPE'),'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
$cols['ctypes']->setBodyAttributes('width="300"');

$cols['edit'] = new editColumn();
$cols['delete'] = new deleteColumn(array('function' => 'cjoTemplate::deleteTemplate', 'id' => '%id%'));

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

//Tabelle anzeigen
$list->show(false);
