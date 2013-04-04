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
$fields['name'] = new textField('name', cjoI18N::translate("label_name"));
$fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_name_notEmpty"));

if ($function == "add") {
	$fields['id'] = new selectField('id', cjoI18N::translate("label_id"));
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
		$fields['id']->addOption(cjoI18N::translate("label_id").' '.$c,$c);
		$i++;
		if ($c == 100) break;
	}

	foreach($modultyps as $key=>$value) {
		if (strpos($value['module_id'],'_') === false && $value['id'] != $oid)
			$used_modultyps[] = $value['type_id'];
	}
	$fields['id']->addValidator('notEmpty', cjoI18N::translate("msg_id_notEmpty"));
}

$fields['templates'] = new selectField('templates', cjoI18N::translate("label_template_connection"));
$fields['templates']->setMultiple();
$fields['templates']->setValueSeparator('|');
$fields['templates']->addOption(cjoI18N::translate("label_rights_all").' '.cjoI18N::translate("title_templates"),0);
$fields['templates']->addSqlOptions("SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_TEMPLATES." ORDER BY prior");
$fields['templates']->addAttribute('size', count($fields['templates']->values)+1);
$fields['templates']->activateSave(false);

if (cjoProp::countCtypes() > 0) {
	$fields['ctypes'] = new selectField('ctypes', cjoI18N::translate("label_ctype_connection"));
	$fields['ctypes']->setMultiple();
	$fields['ctypes']->setValueSeparator('|');
	$fields['ctypes']->activateSave(false);

	foreach(cjoProp::get('CTYPE') as $key=>$val) {
		$fields['ctypes']->addOption($val,$key);
	}
	$fields['ctypes']->addAttribute('size', cjoProp::countCtypes()+1);

} else {
	$fields['ctypes'] = new hiddenField('ctypes');
	$fields['ctypes']->setValue('0');
}

if ($function == 'add') {
	$oid = '';
}
else {
	$fields['headline1'] = new headlineField(cjoI18N::translate("label_info"), true);

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
				cjoUrl::redirectBE(array('mode'=>'logic', 'function'=>'', 'oid'=>$id,  'msg'=>'msg_module_added'));
			}
			else {
			    //[translate: msg_module_updated]
				cjoUrl::redirectBE(array('mode'=>'', 'function'=>'', 'oid'=>'', 'msg'=>'msg_module_updated'));
			}
		}
	}
}