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

if ($function != 'add'){
    $sql = new cjoSql();
    $data = $sql->getArray("SELECT * FROM ".TBL_16_EVENTS." WHERE id=".$oid." AND clang=".$clang);
    $event = cjoAssistance::toArray($data[0]);
}

$id       = cjo_request('id', 'int', $oid);

//Form
$form = new cjoForm();

//Fields
$fields['clang'] = new hiddenField('clang');
$fields['clang']->setValue($clang);

$fields['title'] = new textField('title', cjoAddon::translate(16,"label_title"), array('class' => 'large_item'));
$fields['title']->addValidator('notEmpty', cjoAddon::translate(16,"msg_err_title_notEmpty"), false, false);
$fields['title']->needFullColumn(true);

$fields['status'] = new selectField('status', cjoAddon::translate(16,'label_status'));
$fields['status']->addAttribute('size', '1');
$fields['status']->addAttribute('style', 'width: 128px');
$fields['status']->addValidator('notEmpty', cjoAddon::translate(16,'msg_err_status_notEmpty'));
$fields['status']->addOption(cjoAddon::translate(16,'label_status_online'), '1');
$fields['status']->addOption(cjoAddon::translate(16,'label_status_offline'), '0');
$fields['status']->needFullColumn(true);

$start_date_id = array();
if (cjoAssistance::inMultival('end_date', $CJO['ADDON']['settings'][$mypage]['enabled_fields']))
    $start_date_id = array('start_date', 'end_date');

$fields['start_date'] = new datepickerField('start_date', cjoAddon::translate(16,"label_start_date"), '', $start_date_id);
$fields['start_date']->addSettings("buttonImage: 'img/silk_icons/calendar_begin.png'");
$fields['start_date']->addColAttribute('style', 'width: 37%');
$fields['start_date']->setDefault(strtotime('midnight +1 week'));
$fields['start_date']->addValidator('notEmptyOrNull', cjoAddon::translate(16,"msg_err_start_date_notEmpty"), false, false);

$fields['start_time'] = new textField('start_time', '', array('style' => 'width: 50px'));
$fields['start_time']->addAttribute('maxlength', '5');
$fields['start_time']->setNote(cjoAddon::translate(16,"label_start_time"));
$fields['start_time']->setFormat('strftime','%H:%M');
$fields['start_time']->addValidator('isDate', cjoAddon::translate(16,"msg_err_no_time"), false, false);
$fields['start_time']->addValidator('isRegExp', cjoAddon::translate(16,"msg_err_no_time"), array('expression' => '!([01][0-9]|2[0-3]):[0-5][0-9]+!i'), false);
$fields['start_time']->addValidator('notEmpty', cjoAddon::translate(16,"msg_err_no_time"),false, false);

$fields['start_time_clear'] = new readOnlyField('', '');

$fields['end_date'] = new datepickerField('end_date', cjoAddon::translate(16,"label_end_date"), '', 'end_date');
$fields['end_date']->addSettings("buttonImage: 'img/silk_icons/calendar_end.png'");
$fields['end_date']->addColAttribute('style', 'width: 37%');
$fields['end_date']->setDefault(strtotime('midnight +1 week'));
$fields['end_date']->addValidator('notEmptyOrNull', cjoAddon::translate(16,"msg_err_end_date_notEmpty"), false, false);

$fields['end_time'] = new textField('end_time', '', array('style' => 'width: 50px'));
$fields['end_time']->addAttribute('maxlength', '5');
$fields['end_time']->setNote(cjoAddon::translate(16,"label_end_time"));
$fields['end_time']->setFormat('strftime','%H:%M');
$fields['end_time']->addValidator('isDate', cjoAddon::translate(16,"msg_err_no_time"), false, false);
$fields['end_time']->addValidator('isRegExp', cjoAddon::translate(16,"msg_err_no_time"), array('expression' => '!([01][0-9]|2[0-3]):[0-5][0-9]+!i'), false);
$fields['end_time']->addValidator('notEmpty', cjoAddon::translate(16,"msg_err_no_time"),false, false);
$fields['end_time']->addValidator('notEmpty', cjoAddon::translate(16,"msg_err_no_time"),false, false);

$fields['end_time_clear'] = new readOnlyField('', '');

$fields['file'] = new cjoMediaButtonField('file', cjoAddon::translate(16,'label_event_file'), array('preview'=> array('enabled' => true)));
$fields['file']->needFullColumn(true);

$fields['article'] = new cjoLinkButtonField('article_id', cjoAddon::translate(16,'label_event_article'));
$fields['article']->needFullColumn(true);

$fields['online_from_hidden'] = new hiddenField('online_from');
$fields['online_from_hidden']->setDefault(time());

$fields['online_from'] = new datepickerField('online_from', cjoI18N::translate("label_from_to"), '', array('online_from','online_to'));
$fields['online_from']->addColAttribute('style', 'width: 37%');
$fields['online_from']->addSettings("defaultDate: 'd', buttonImage: 'img/silk_icons/calendar_begin.png'");
$fields['online_from']->setDefault(time());

$fields['online_to_hidden'] = new hiddenField('online_to');
$fields['online_to_hidden']->setValue(mktime(0, 0, 0, 1, 1, 2020));

$fields['online_to'] = new datepickerField('online_to', '', '', 'online_to');
$fields['online_to']->addColAttribute('style', 'width: 63%');
$fields['online_to']->addSettings("defaultDate: new Date(2020, 1 - 1, 1), buttonImage: 'img/silk_icons/calendar_end.png'");
$fields['online_to']->setDefault(mktime(0, 0, 0, 1, 1, 2020));

$fields['headline_desc'] = headlineField(cjoAddon::translate(16,'label_description'), true);

$fields['short_description'] = new cjoWYMeditorField('short_description', cjoAddon::translate(16,'label_short_description'));
$fields['short_description']->setWidth('650');
$fields['short_description']->setHeight('80');
$fields['short_description']->needFullColumn(true);

$fields['description'] = new cjoWYMeditorField('description', cjoAddon::translate(16,'label_description'));
$fields['description']->setWidth('650');
$fields['description']->setHeight('200');
$fields['description']->needFullColumn(true);

$fields['keywords'] = new textAreaField('keywords', cjoAddon::translate(16,"label_keywords"));
$fields['keywords']->addAttribute('rows', '5');
$fields['keywords']->addAttribute('cols', '10');
$fields['keywords']->setNote(cjoAddon::translate(16,"note_delimiter_comma"));
$fields['keywords']->needFullColumn(true);

$fields['headline_attr'] = new headlineField(cjoAddon::translate(16,'label_attributes'), true);

$attributes_enabled = false;

for($i=1;$i<=10;$i++) {

    $attribute        = 'attribute'.$i;
    $attribute_typ    = $CJO['ADDON']['settings'][$mypage]['attribute_typ'.$i];
    $attribute_title  = $CJO['ADDON']['settings'][$mypage]['attribute_title'.$i];
    $attribute_values = preg_replace('/\r\n|\r/', "\n", $CJO['ADDON']['settings'][$mypage]['attribute_values'.$i]);
    $attribute_values = cjo_to_array($attribute_values, "\n");

    if (!empty($attribute_typ)) $attributes_enabled = true;

    switch($attribute_typ) {

        case "text":
            $fields[$attribute] = new textField($attribute, $attribute_title);
            $fields[$attribute]->needFullColumn(true);
            break;

        case "textarea":
            $fields[$attribute] = new textAreaField($attribute, $attribute_title);
            $fields[$attribute]->addAttribute('rows', '5');
            $fields[$attribute]->addAttribute('cols', '10');
            $fields[$attribute]->needFullColumn(true);
            break;

        case "wymeditor":
            $fields[$attribute] = new cjoWYMeditorField($attribute, $attribute_title);
            $fields[$attribute]->setHeight('200');
            $fields[$attribute]->needFullColumn(true);
            break;

         case "select":
            $fields[$attribute] = new selectField($attribute, $attribute_title);
            $fields[$attribute]->addOption('','');
            foreach($attribute_values as $value) {
                $fields[$attribute]->addOption($value,$value);
            }
            $fields[$attribute]->addAttribute('size', 1);
            $fields[$attribute]->needFullColumn(true);
            break;

        case "datepicker":
            $fields[$attribute] = new datepickerField($attribute, $attribute_title);
            $fields[$attribute]->addSettings("buttonImage: 'img/silk_icons/calendar.png'");
            $fields[$attribute]->setDefault(time());
            $fields[$attribute]->needFullColumn(true);
            break;

       case "time":
            $fields[$attribute] = new textField($attribute, $attribute_title, array('style' => 'width: 50px'));
            $fields[$attribute]->addAttribute('maxlength', '5');
            $fields[$attribute]->addValidator('isDate', cjoAddon::translate(16,"msg_err_no_time"), false, false);
            $fields[$attribute]->addValidator('isRegExp', cjoAddon::translate(16,"msg_err_no_time"), array('expression' => '!([01][0-9]|2[0-3]):[0-5][0-9]+!i'), false);
            $fields[$attribute]->needFullColumn(true);
            break;

        case "media":
            $fields[$attribute] = new cjoMediaButtonField($attribute, $attribute_title);
            $fields[$attribute]->needFullColumn(true);
            break;

        case "article":
            $fields[$attribute] = new cjoLinkButtonField($attribute, $attribute_title);
            $fields[$attribute]->needFullColumn(true);
            break;

        default: break;
    }
}

if ($function == 'add'){

	$oid = '';

	$fields['createdate_hidden'] = new hiddenField('createdate');
	$fields['createdate_hidden']->setValue(time());

	$fields['createuser_hidden'] = new hiddenField('createuser');
	$fields['createuser_hidden']->setValue($CJO_USER->getValue("name"));
}
else {

    $fields['createdate_hidden'] = new hiddenField('createdate');
	$fields['createdate_hidden']->setDefault(time());

	$fields['createuser_hidden'] = new hiddenField('createuser');
	$fields['createuser_hidden']->setDefault(cjoProp::getUser()->getValue("name"));

	$fields['updatedate_hidden'] = new hiddenField('updatedate');
	$fields['updatedate_hidden']->setValue(time());

	$fields['updateuser_hidden'] = new hiddenField('updateuser');
	$fields['updateuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));

	$fields['headline_info'] = new headlineField(cjoAddon::translate(16,"label_info"), true);

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

	$fields['headline_copy'] = new headlineField(cjoAddon::translate(16,"label_copy_event"), true);

    $fields['copy_event'] = new checkboxField('id', '',  array('style' => 'width: auto; margin-left: -280px'));
    $fields['copy_event']->setUncheckedValue($oid);
    $fields['copy_event']->addBox(cjoAddon::translate(16,"label_copy_event"), '');
}

/**
 * Do not delete translate values for cjoI18N collection!
 * [translate_16: label_add_event]
 * [translate_16: label_edit_event]
 */
$section = new cjoFormSection(TBL_16_EVENTS, cjoAddon::translate(16,"label_".$function."_event"), array ('id' => $id), array('50%', '50%'));

$enabled_fields = $CJO['ADDON']['settings'][$mypage]['enabled_fields'];

if (!cjoAssistance::inMultival('times', $enabled_fields)) unset($fields['start_time']);
if (!$fields['start_time']) unset($fields['end_time']);
if (cjoAssistance::inMultival('times', $enabled_fields)) unset($fields['start_time_clear']);
if (!cjoAssistance::inMultival('end_date', $enabled_fields) ||
    $fields['end_time']) unset($fields['end_time_clear']);
if (!cjoAssistance::inMultival('end_date', $enabled_fields)) unset($fields['end_date']);
if (!cjoAssistance::inMultival('file', $enabled_fields)) unset($fields['file']);
if (!cjoAssistance::inMultival('article', $enabled_fields)) unset($fields['article']);

if (!cjoAssistance::inMultival('online_from_to', $enabled_fields)) unset($fields['online_from']);
if (!cjoAssistance::inMultival('online_from_to', $enabled_fields)) unset($fields['online_to']);

if (!cjoAssistance::inMultival('short_description', $enabled_fields)) unset($fields['short_description']);
if (!cjoAssistance::inMultival('description', $enabled_fields)) unset($fields['description']);
if (!cjoAssistance::inMultival('keywords', $enabled_fields)) unset($fields['keywords']);
if (!isset($fields['short_description']) &&
    !isset($fields['description']) &&
    !isset($fields['keywords'])) unset($fields['headline_desc']);

if (!$attributes_enabled) unset($fields['headline_attr']);

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {

    if (cjo_post('start_time','bool')) {
        $start_time = cjoAssistance::toArray(cjo_post('start_time','string'),':');
        $_POST['start_time'] = ((int) $start_time[0]*60*60) + ((int) $start_time[1] *60);
    }
    if (cjo_post('end_time','bool')) {
        $end_time = cjoAssistance::toArray(cjo_post('end_time','string'),':');
        $_POST['end_time'] = ((int) $end_time[0]*60*60) + ((int) $end_time[1] *60);
    }
}

$form->show();

if ($form->validate()) {

	if (cjo_post('cjoform_save_button','bool')) {
	   cjoUrl::redirectBE(array('function' => '', 'oid'=>'',  'msg' => 'msg_data_saved'));
	}
    cjoUrl::redirectBE(array( 'msg' => 'msg_data_saved'));
}

