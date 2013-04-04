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



foreach(cjoProp::get('CTYPE') as $key => $val){

	$fields['ctype_'.$key] = new textField($key, cjoI18N::translate("label_ctype").' (ID='.$key.')');
	$fields['ctype_'.$key]->addValidator('notEmpty', cjoI18N::translate("msg_ctype_name_notEmpty"), false, false);

	if ($key > 0)
		$fields['ctype_'.$key]->setNote('<input name="delete_ctype" class="cjo_confirm" value="'.$key.'"
												src="img/silk_icons/cross.png" type="image"
												alt="'.cjoI18N::translate("button_delete").'"
												title="'.cjoI18N::translate("button_delete").'" />');
}
ksort($fields);

$add_key = $key+1;

$fields['ctype_add'] = new textField($add_key, cjoI18N::translate("label_add_ctype"));

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_update_button',cjoI18N::translate("button_update"), true, 'img/silk_icons/tick.png');


//Add Fields:
$section = new cjoFormSection(cjoProp::get('CTYPE'), cjoI18N::translate("label_ctype"));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
cjoValidateEngine :: connect($form->getValidator());
cjoValidateEngine :: register_form($form->getName(), true);

if ($form->validate()) {

	if (!cjoFile::isWritable($CJO['FILE_CONFIG_CTYPES'])){
	    $error = cjoMessage::removeLastError();
		cjoMessage::addError(cjoI18N::translate("msg_data_not_saved"));
		cjoMessage::addError($error);
		$form->valid_master = false;
	}
	else {

		ksort($_POST,SORT_STRING);

		$missing = 0;
		$content_array = array();

		foreach($_POST as $key=>$val){

			if (!is_numeric($key)) break;
			if ($val == '') continue;

			// neuen CTYPE einsetzen, wenn  Schlüssel gleich CTYPE-Länge
			if ($key == $add_key){
				if (!cjo_post('delete_ctype', 'boolean')){
					$content_array[$missing] = "$"."CJO['CTYPE'][".$missing."] = \"".$val."\";";
				}
				break;
			}

			if (!cjo_post('delete_ctype', 'boolean') ||
				cjo_post('delete_ctype', 'int') != $key){
				$content_array[$key] = "$"."CJO['CTYPE'][".$key."] = \"".$val."\";";
			}
			// fehlende CTYPE merken und für neuen CTYPE benutzen
			if ($missing == $key){
				$missing = $key+1;
			}
		}

		// sicherstellen, daß CTYPE[0] existiert
		if (empty($content_array[0]))
			$content_array[0] = "$"."CJO['CTYPE'][0] = \"main\";";

		ksort($content_array);

		$new_content  = "// --- DYN"."\r\n\r\n";
		$new_content .= implode("\r\n",$content_array);
		$new_content .= "\r\n\r\n"."// --- /DYN";

		if (cjoGenerate::replaceFileContents($CJO['FILE_CONFIG_CTYPES'], $new_content)){
            cjoExtension::registerExtensionPoint('CTYPES_UPDATED');  
			cjoUrl::redirectBE( array('msg' => 'msg_data_saved'));
		}
	}
}
$form->show(false);