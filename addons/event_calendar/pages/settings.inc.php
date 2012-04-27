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
 * @version     2.6.0
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
} else {
	$dataset = array_merge($CJO['ADDON']['settings'][$mypage], $_POST);
}
$select_fields = array ();

if (cjoAssistance::inMultival('keywords', $CJO['ADDON']['settings'][$mypage]['enabled_fields']))
    $select_fields[] =  array ($I18N_16->msg('label_keywords'), 'keywords');

for ($i=1;$i<=10;$i++) {

    $attribute        = 'attribute'.$i;
    $attribute_typ    = $CJO['ADDON']['settings'][$mypage]['attribute_typ'.$i];
    $attribute_title  = $CJO['ADDON']['settings'][$mypage]['attribute_title'.$i];
    
    $search_fields = array();
    switch($attribute_typ) {

        case "text":
            $search_fields[] = array ($attribute_title, $attribute);
            $select_fields[] = array ($attribute_title, $attribute);
            break;

        case "textarea":
            $search_fields[] = array ($attribute_title, $attribute);
            break;

        case "wymeditor":
            $search_fields[] = array ($attribute_title, $attribute);
            break;

         case "select":
            $search_fields[] = array ($attribute_title, $attribute);
            $select_fields[] = array ($attribute_title, $attribute);
            break;

        case "time":
            $select_fields[] = array ($attribute_title, $attribute);
            break;

        default: break;
    }
}



//Form
$form = new cjoForm();

$fields['enabled_fields_hidden'] = new hiddenField('enabled_fields');
$fields['enabled_fields_hidden']->setValue('0');

$fields['enabled_fields'] = new selectField('enabled_fields', $I18N_16->msg('label_enabled_fields'));
$fields['enabled_fields']->addOptions($CJO['ADDON']['settings'][$mypage]['enabled_types']);
$fields['enabled_fields']->setMultiple();
$fields['enabled_fields']->addAttribute('size', count($CJO['ADDON']['settings'][$mypage]['enabled_types']));

$fields['headline_filter'] = new readOnlyField('headline_filter', '', array('class' => 'formheadline $slide'));
$fields['headline_filter']->setValue($I18N_16->msg('headline_filter'));

$fields['date_input_enabled_hidden'] = new hiddenField('date_input_enabled');
$fields['date_input_enabled_hidden']->setValue('0');
$fields['date_input_enabled'] = new checkboxField('date_input_enabled', $I18N_16->msg('label_date_input_enabled'),  array('style' => 'width: auto;'));
$fields['date_input_enabled']->addBox($I18N_16->msg("label_display"), '1');

// Zeitformat
$fields['date_input_format'] = new selectField('date_input_format', $I18N_16->msg('label_date_input_format'));
$fields['date_input_format']->addOptions($CJO['ADDON']['settings'][$mypage]['date_input_formats']);
$fields['date_input_format']->addAttribute('size', 1);

$fields['search_fields_hidden'] = new hiddenField('search_fields');
$fields['search_fields_hidden']->setValue('0');

$fields['search_fields'] = new selectField('search_fields', $I18N_16->msg('label_search_fields'));
$fields['search_fields']->addOptions($CJO['ADDON']['settings'][$mypage]['available_search_fields']);
$fields['search_fields']->setMultiple();
$fields['search_fields']->addAttribute('size', count($CJO['ADDON']['settings'][$mypage]['available_search_fields']));

$fields['select_fields_hidden'] = new hiddenField('select_fields');
$fields['select_fields_hidden']->setValue('0');

$fields['select_fields'] = new selectField('select_fields', $I18N_16->msg('label_select_fields'));
$fields['select_fields']->addOptions($select_fields);
$fields['select_fields']->setMultiple();
$fields['select_fields']->addAttribute('size', count($select_fields));


$fields['cookie_enabled'] = new checkboxField('cookie_enabled', $I18N_16->msg('label_cookie_enabled'),  array('style' => 'width: auto;'));
$fields['cookie_enabled']->setUncheckedValue();
$fields['cookie_enabled']->addBox($I18N_16->msg("label_enabled"), '1');

$fields['headline_list'] = new readOnlyField('headline_list', '', array('class' => 'formheadline $slide'));
$fields['headline_list']->setValue($I18N_16->msg('headline_list'));

$fields['date_output_format'] = new selectField('date_output_format', $I18N_16->msg('label_date_output_format'));
$fields['date_output_format']->addOptions($CJO['ADDON']['settings'][$mypage]['date_output_formats']);
$fields['date_output_format']->addAttribute('size', 1);
$fields['date_output_format']->addAttribute('style', 'width: 200px');

$fields['list_crop_num'] = new selectField('list_crop_num', $I18N_16->msg('label_crop_num'));
$fields['list_crop_num']->addSQLOptions("SELECT name, id FROM ".TBL_IMG_CROP." WHERE status!=0 ORDER BY status, id");
$fields['list_crop_num']->addOption('&nbsp;'.$I18N->msg('label_use_original_size'), '-');
$fields['list_crop_num']->addAttribute('size', '1');
$fields['list_crop_num']->addAttribute('style', 'width: 200px');

$fields['elements_per_page'] = new textField('elements_per_page', $I18N_16->msg('label_elements_per_page'));
$fields['elements_per_page']->addValidator('notEmpty', $I18N_16->msg("msg_err_elements_per_page_noEmpty"), false, false);
$fields['elements_per_page']->addValidator('isRange', $I18N_16->msg('msg_err_elements_per_page_wrong'), array('low' => '1', 'high' => 200));
$fields['elements_per_page']->addAttribute('style', 'width: 50px');
$fields['elements_per_page']->addAttribute('maxlength', '3');

$fields['results_order_by'] = new selectField('results_order_by', $I18N_16->msg('label_results_order_by'));

foreach(cjoSql::getFieldnames(TBL_16_EVENTS) as $fielname){
    $fields['results_order_by']->addOption($fielname, $fielname);
}
$fields['results_order_by']->addAttribute('size', 1);
$fields['results_order_by']->addAttribute('style', 'width: 200px');

$fields['results_order_dir'] = new selectField('results_order_dir', $I18N_16->msg('label_results_order_dir'));
$fields['results_order_dir']->addOption($I18N_16->msg('label_order_desc'), 'DESC');
$fields['results_order_dir']->addOption($I18N_16->msg('label_order_asc'), 'ASC');
$fields['results_order_dir']->addAttribute('size', 1);
$fields['results_order_dir']->addAttribute('style', 'width: 200px');

$fields['no_data_text'] = new cjoWYMeditorField('no_data_text', $I18N_16->msg('label_no_data_text'));
$fields['no_data_text']->setWidth('650');
$fields['no_data_text']->setHeight('80');
$fields['no_data_text']->needFullColumn(true);

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_update_button1',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');

//Add Fields
$section = new cjoFormSection($dataset, $I18N_16->msg("label_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);


if ($form->validate()){

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
