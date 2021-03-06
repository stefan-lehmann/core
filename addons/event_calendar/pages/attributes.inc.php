<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  event_calendar
 * @version     2.7.x
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

$cjo_form_name = cjo_post('cjo_form_name', 'string');

if (!cjo_post('cjo_form_name', 'bool')) {
    $dataset = $CJO['ADDON']['settings'][$mypage];
} else{
    $dataset = array_merge($CJO['ADDON']['settings'][$mypage], $_POST);
}

//Form
$form = new cjoForm();

for ($i=1;$i<=10;$i++) {

    $slide = ($i != 1 && $dataset['attribute_typ'.$i] == '') ? ' slide' : '';

    $fields['headline_attr'.$i] = new readOnlyField('headline_attr'.$i, '', array('class' => 'formheadline'.$slide));
    $fields['headline_attr'.$i]->setValue($I18N_16->msg('label_attribute', $i));

    $fields['attribute_typ'.$i] = new selectField('attribute_typ'.$i, $I18N_16->msg('label_attribute_typ'), array('onchange'=>$form->getName().'.submit()'));
    $fields['attribute_typ'.$i]->addOptions($CJO['ADDON']['settings'][$mypage]['list_types']);
    $fields['attribute_typ'.$i]->addAttribute('size', 1);

    if ($dataset['attribute_typ'.$i] != ''){

        $fields['attribute_title'.$i] = new textField('attribute_title'.$i, $I18N_16->msg('label_attribute_title'));

        if ($dataset['attribute_typ'.$i] == 'select'){
            $fields['attribute_values'.$i] = new textAreaField('attribute_values'.$i, $I18N_16->msg('label_attribute_values'));
            $fields['attribute_values'.$i]->addAttribute('rows', '5');
            $fields['attribute_values'.$i]->addAttribute('cols', '10');
            $fields['attribute_values'.$i]->setNote($I18N_16->msg("note_separate_by_new_line"));
        }

        if ($dataset['attribute_typ'.$i] == 'media'){
            $fields['attribute_crop_num'.$i] = new selectField('attribute_crop_num'.$i, $I18N_16->msg('label_crop_num'));
            $fields['attribute_crop_num'.$i]->addSQLOptions("SELECT name, id FROM ".TBL_IMG_CROP." WHERE status!=0 ORDER BY status, id");
            $fields['attribute_crop_num'.$i]->addOption('&nbsp;'.$I18N->msg('label_use_original_size'), '-');
            $fields['attribute_crop_num'.$i]->addAttribute('size', '1');
        }

        if ($dataset['attribute_typ'.$i] == 'datepicker'){
            $fields['attribute_date_format'.$i] = new selectField('attribute_date_format'.$i, $I18N_16->msg('label_date_output_format'));
            $fields['attribute_date_format'.$i]->addOptions($CJO['ADDON']['settings'][$mypage]['date_output_formats']);
            $fields['attribute_date_format'.$i]->addAttribute('size', 1);
        }
        $fields['attribute_display_hidden'.$i] = new hiddenField('attribute_display'.$i);
        $fields['attribute_display_hidden'.$i]->setValue('0');
        $fields['attribute_display'.$i] = new checkboxField('attribute_display'.$i, $I18N_16->msg('label_attribute_display'),  array('style' => 'width: auto;'));
        $fields['attribute_display'.$i]->addBox($I18N_16->msg("label_display"), '1');
    }
}

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_update_button2',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');

//Add Fields
$section = new cjoFormSection($dataset, $I18N_16->msg("label_attribute_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()){

    for ($i=1;$i<=10;$i++) {

        if ($_POST['attribute_typ'.$i] != '' &&
            $_POST['attribute_title'.$i] == '') {
            cjoMessage::addError($I18N_16->msg("msg_err_attribute_title_notEmpty", $i));
            $fields['attribute_title'.$i]->addAttribute('class', 'invalid');
            $form->valid_master = false;
        }

        if ($_POST['attribute_typ'.$i] == 'select' &&
            $_POST['attribute_values'.$i] == '') {
            cjoMessage::addError($I18N_16->msg("msg_err_attribute_values_notEmpty", $i));    
            $fields['attribute_values'.$i]->addAttribute('class', 'invalid');
            $form->valid_master = false;
        }
    }

    if ($form->valid_master == true) {

        $config_file = $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];
        
        if (cjoGenerate::updateSettingsFile($config_file)) {
            cjoAssistance::redirectBE(array('msg' => 'msg_data_saved'));
        }
        else {
            $form->valid_master = false;
            cjoMessage::addError($I18N->msg("msg_data_not_saved"));
            cjoMessage::addError($I18N->msg("msg_file_no_chmod",
                                 cjoAssistance::absPath($config_file)));
        }
    }
}
$form->show(false);
