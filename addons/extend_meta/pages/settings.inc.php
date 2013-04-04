<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  extend_meta
 * @version     2.6.2
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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


$addon    = 'extend_meta';
//create formular
$form = new cjoForm();
$form->setEditMode(true);
$CJO['ADDON']['settings'][$addon]['FIELDS']['name'] = array_diff(cjoAssistance::toArray($CJO['ADDON']['settings'][$addon]['FIELDS']['name']),
                                                                  array(''));

$dataset = $_POST ? $_POST : cjoAddon::getParameter('FIELDS', $addon);

$length = count($dataset);
$ii = 0;
for($i=0; $i<=$length;$i++) {
    
    if (empty($dataset['name'][$i]) && $i<$length) continue;
    
    if ($i<$length) {
        $fields['headline_'.$i] = new headlineField(cjoAddon::translate(30,'label_form_headline', $i+1));
    }
    else {
        $fields['headline_'.$i] = new headlineField(cjoAddon::translate(30,'label_form_headline_new'), true);
    }
    $fields['label_'.$i] = new textField('label['.$ii.']', cjoAddon::translate(30,'label_label'));
    if ($i<$length)
    $fields['label_'.$i]->addValidator('notEmpty', cjoAddon::translate(30,'err_empty_label'), false);
    if (isset($dataset['label'][$i]))
    $fields['label_'.$i]->setValue($dataset['label'][$i]);        
    
    $fields['name_'.$i] = new textField('name['.$ii.']', cjoAddon::translate(30,'label_name'));
    if ($i<$length) {
        $fields['name_'.$i]->addAttribute('readonly', 'readonly');
        $fields['name_'.$i]->addValidator('notEmpty', cjoAddon::translate(30,'err_empty_name'), false);
    }
    else {
        $fields['name_'.$i]->addValidator('isNot', cjoAddon::translate(30,'err_name_not_unique'), $CJO['ADDON']['settings'][$addon]['FIELDS']['name']);
    } 
    if (isset($dataset['name'][$i]))
    $fields['name_'.$i]->setValue($dataset['name'][$i]);
    
    $fields['field_'.$i] = new selectField('field['.$ii.']', cjoAddon::translate(30,'label_field'), array('size'=>1));
    if (isset($dataset['field'][$i]))
    $fields['field_'.$i]->setValue($dataset['field'][$i]);
    $fields['field_'.$i]->setHelp(cjoAddon::translate(30,"note_field"));
    if ($i<$length)
    $fields['field_'.$i]->addValidator('notEmpty', cjoAddon::translate(30,'err_empty_field'), false);
    $fields['field_'.$i]->addOption('', ''); 
    foreach($CJO['ADDON']['settings'][$addon]['FIELDTYPES'] as $type) {
        $fields['field_'.$i]->addOption($type, $type); 
    }  
   
    $fields['empty_hidden_'.$i] = new hiddenField('empty['.$ii.']'); 
    $fields['empty_hidden_'.$i]->setValue(0);      
   
    $fields['empty_'.$i] = new checkboxField('empty['.$ii.']', '&nbsp;');
    $fields['empty_'.$i]->addBox(cjoAddon::translate(30,'label_field_must_not_be_empty'), '1');  
    
    $fields['validator_'.$i] = new selectField('validator['.$ii.']', cjoAddon::translate(30,'label_validator'), array('size'=>1));
    if (isset($dataset['validator'][$i]))
    $fields['validator_'.$i]->setValue($dataset['validator'][$i]);
    $fields['validator_'.$i]->setHelp(cjoAddon::translate(30,"note_validator"));
    $fields['validator_'.$i]->addOption('', '');   
    foreach($CJO['ADDON']['settings'][$addon]['VALIDATORTYPES'] as $type) {
        $fields['validator_'.$i]->addOption($type, $type); 
    }
    
    $fields['compare_value_'.$i] = new textField('compare_value['.$ii.']', cjoAddon::translate(30,'label_compare_value'));
    $fields['compare_value_'.$i]->setHelp(cjoAddon::translate(30,"note_compare_value"));
    if (isset($dataset['compare_value'][$i]))
    $fields['compare_value_'.$i]->setValue($dataset['compare_value'][$i]);
    
    $fields['message_'.$i] = new textAreaField('message['.$ii.']', cjoAddon::translate(30,'label_message'), array('rows'=>1));
    if (isset($dataset['message'][$i]))
    $fields['message_'.$i]->setValue($dataset['message'][$i]);
    
    $fields['helptext_'.$i] = new textAreaField('helptext['.$ii.']', cjoAddon::translate(30,'label_helptext'), array('rows'=>1));
    if (isset($dataset['message'][$i]))
    $fields['helptext_'.$i]->setValue($dataset['helptext'][$i]);
    
    if ($i<$length) {
        $fields['remove_'.$i] = new checkboxField('remove['.$ii.']', '&nbsp;');
        $fields['remove_'.$i]->addBox('<strong style="color:red">'.cjoAddon::translate(30,'label_remove_field').'</strong>', '1');
    }
    $ii++;
}

$section = new cjoFormSection($dataset, cjoI18N::translate('title_edit_settings'));

$section->addFields($fields);
$form->addSection($section);


if ($form->validate()) {
    
    $key = $length;


    if (!empty($_POST['name'][$key])) {
        if (empty($_POST['label'][$key])) {
            cjoMessage::addError(cjoAddon::translate(30,'err_empty_label'));
            $fields['label_'.$key]->addAttribute('class', 'invalid');
            $form->valid_master = false;
        }
        if (empty($_POST['field'][$key])) {
            cjoMessage::addError(cjoAddon::translate(30,'err_empty_field'));
            $fields['field_'.$key]->addAttribute('class', 'invalid');
            $form->valid_master = false;
        }
    }
    else {
        if (!empty($_POST['label'][$key]) ||
            !empty($_POST['field'][$key])) {
            cjoMessage::addError(cjoAddon::translate(30,'err_empty_name'));
            $fields['name_'.$key]->addAttribute('class', 'invalid');
            $form->valid_master = false;
        } else {
            $_POST['remove'][$key] = 1;
        }
    }

    $temp = $_POST['name'];
    array_unique($temp);
    
    if (count($temp) != count($_POST['name'])) {
        cjoMessage::removeLastSuccess();
        cjoMessage::addError(cjoI18N::translate('msg_data_not_saved'));
        cjoMessage::addError(cjoAddon::translate(30,'err_name_not_unique'));
        $form->valid_master = false;
    } 

    if ($form->valid_master) {
        
        if (cjo_post('remove', 'bool')) {
            $remove = array_keys(cjo_post('remove', 'array'));
            foreach($remove as $key) {
                cjoExtendMeta::removeField($_POST['name'][$key]);
                unset($_POST['label'][$key]); 
                unset($_POST['name'][$key]); 
                unset($_POST['field'][$key]); 
                unset($_POST['empty'][$key]);
                unset($_POST['validator'][$key]);
                unset($_POST['compare_value'][$key]); 
                unset($_POST['message'][$key]);      
                unset($_POST['helptext'][$key]);     
            }
        }
        $data = array('label'         => array_values($_POST['label']),
                      'name'          => array_values($_POST['name']), 
                      'field'         => array_values($_POST['field']), 
                      'empty'         => array_values($_POST['empty']), 
                      'validator'     => array_values($_POST['validator']),
                      'compare_value' => array_values($_POST['compare_value']), 
                      'message'       => array_values($_POST['message']),
                      'helptext'      => array_values($_POST['helptext']));
                      
        foreach($data as $field=>$values) {
          foreach($values as $key=>$value) {
            $data[$field][$key] = htmlspecialchars($data[$field][$key]);
          }
        }
        
        $content  = '// --- DYN'."\r\n";
        $content .= '$CJO[\'ADDON\'][\'settings\'][$addon][\'FIELDS\'] = "'.addslashes(json_encode($data)).'";'."\r\n";
        $content .= '// --- /DYN'."\r\n";

    	if (cjoGenerate::replaceFileContents($CJO['ADDON']['settings'][$addon]['SETTINGS'], $content)) {
    	    cjoGenerate::generateAll();
    		cjoUrl::redirectBE(array('function'=>'','msg'=>'msg_data_saved'));
    	}
    }
}
$form->show(true);