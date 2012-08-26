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

//Form
$form = new cjoForm();
$form->setEditMode($oid);
//$form->debug = true;

$hidden['mode'] = new hiddenField('mode');
$hidden['mode']->setValue($mode);

//Fields
$fields['name'] = new textField('name', $I18N->msg("label_name"));
$fields['name']->addValidator('notEmpty', $I18N->msg("msg_name_notEmpty"));

if ($function == "add") {
	$fields['id'] = new selectField('id', $I18N->msg("label_id"));
	$fields['id']->addAttribute('size', '1');
	$fields['id']->addAttribute('class', 'inp10');

	$i = 0; $c = 0;
	while($i < 10) {
		$c++;
		foreach($modultyps as $modultyp) {
			if ($c == $modultyp['id'] && $modultyp['id'] != $oid) {
				continue 2;
			}
		}
		$fields['id']->addOption($I18N->msg("label_id").' '.$c,$c);
		$i++;
		if ($c == 100) break;
	}

	foreach($modultyps as $key=>$value) {
		if (strpos($value['module_id'],'_') === false && $value['id'] != $oid)
			$used_modultyps[] = $value['type_id'];
	}
	$fields['id']->addValidator('notEmpty', $I18N->msg("msg_id_notEmpty"));
}

$fields['templates'] = new selectField('templates', $I18N->msg("label_template_connection"));
$fields['templates']->setMultiple();
$fields['templates']->setValueSeparator('|');
$fields['templates']->addOption($I18N->msg("label_rights_all").' '.$I18N->msg("title_templates"),0);
$fields['templates']->addSqlOptions("SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_TEMPLATES." ORDER BY prior");
$fields['templates']->addAttribute('size', count($fields['templates']->values)+1);
$fields['templates']->activateSave(false);

if (count($CJO['CTYPE']) > 0) {
	$fields['ctypes'] = new selectField('ctypes', $I18N->msg("label_ctype_connection"));
	$fields['ctypes']->setMultiple();
	$fields['ctypes']->setValueSeparator('|');
	$fields['ctypes']->activateSave(false);

	foreach($CJO['CTYPE'] as $key=>$val) {
		$fields['ctypes']->addOption($val,$key);
	}
	$fields['ctypes']->addAttribute('size', count($CJO['CTYPE'])+1);

} else {
	$fields['ctypes'] = new hiddenField('ctypes');
	$fields['ctypes']->setValue('0');
}

if ($function == 'add') {
	$oid = '';
}
else {
	$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
	$fields['headline1']->setValue($I18N->msg("label_info"));
	$fields['headline1']->needFullColumn(true);

	$fields['updatedate'] = new readOnlyField('updatedate', $I18N->msg('label_updatedate'), array(), 'label_updatedate');
	$fields['updatedate']->setFormat('strftime',$I18N->msg('dateformat_sort'));
	$fields['updatedate']->needFullColumn(true);

	$fields['updateuser'] = new readOnlyField('updateuser', $I18N->msg('label_updateuser'), array(), 'label_updateuser');
	$fields['updateuser']->needFullColumn(true);

	$fields['createdate'] = new readOnlyField('createdate', $I18N->msg('label_createdate'), array(), 'label_createdate');
	$fields['createdate']->setFormat('strftime',$I18N->msg('dateformat_sort'));
	$fields['createdate']->needFullColumn(true);

	$fields['createuser'] = new readOnlyField('createuser', $I18N->msg('label_createuser'), array(), 'label_createuser');
	$fields['createuser']->needFullColumn(true);
}

//Add Fields:
$section = new cjoFormSection(TBL_MODULES, '', array ('id' => $oid));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show();

if ($form->validate()) {
    
    $id        = cjo_post('id','int');
    $name      = cjo_post('name','string');
    $templates = cjo_post('templates','array');
    $ctypes    = cjo_post('ctypes','array');

	$update = new cjoSql();
	$update->setTable(TBL_MODULES);
	$update->setValue("name", trim($name));
	$update->setValue("templates",'|'.@implode('|',$templates).'|');
	$update->setValue("ctypes",'|'.@implode('|',$ctypes).'|');

	if ($function == "add") {
		$update->setWhere("id='".$id."'");
		$update->addGlobalCreateFields();
        cjoAssistance::updatePrio(TBL_MODULES,$id,time());
	}
	else {
		$update->setWhere("id='".$oid."'");
		$update->addGlobalUpdateFields();

	}
	$update->Update();

	if ($update->getError()) {
		cjoMessage::addError($update->getError());
	}
	else {
	           
        if ($function == "add") {
            cjoExtension::registerExtensionPoint('MODULE_ADDED', array ("moduletyp_id" => $id));
        } else {
            cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                                 array('ACTION' => 'LOGIC_UPDATED',
                                                       'moduletyp_id' => $oid));  
        }
	    
		if (cjo_post('cjoform_save_button','boolean')) {
			if ($function == "add") {
			    //[translate: msg_module_added]
				cjoAssistance::redirectBE(array('mode'=>'logic', 'function'=>'', 'oid'=>$id,  'msg'=>'msg_module_added'));
			}
			else {
			    //[translate: msg_module_updated]
				cjoAssistance::redirectBE(array('mode'=>'', 'function'=>'', 'oid'=>'', 'msg'=>'msg_module_updated'));
			}
		}
	}
}