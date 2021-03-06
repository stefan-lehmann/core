<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.7.x
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
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


global $CJO;
$mypage = 'shop';


// get available moduls and language extensions pay method costs
$pay_methods_path = $CJO['ADDON']['settings'][$mypage]['PAY_METHODS_PATH'];
$dir_content = cjoAssistance::parseDir($pay_methods_path, array(), false, 2, 1, '/.*/i', '', '');
$pay_methods = explode('|', $CJO['ADDON']['settings'][$mypage]['PAY_METHODS']);

// get costs for all pay methods
$costs = cjoShopPayMethod::getAllCosts();


// are we coming from this page
if (cjo_post('cjo_form_name','string') == $mypage.'_'.$subpage.'_form') {
	$dataset = $_POST;
} else {
	$dataset['set_active'] = cjoAssistance::toArray($pay_methods);
}

//create formular
$form = new cjoForm();
$form->setEditMode(true);

// select field for all available pay methods
$fields['set_active'] = new selectField('set_active', $I18N_21->msg('shop_activate_pay_method'));
$fields['set_active']->setMultiple(true);
$fields['set_active']->addAttribute('size', '5');
$fields['set_active']->addValidator('notEmpty', $I18N->msg("msg_pay_method_notEmpty"), false, false);

/*
* all yet possible combinations for 'shop_'.$key (see line 77, 82)
* this lets i18n.php php find all texts that need to be
* translated
*
* $I18N_21->msg('shop_bank_account');
* $I18N_21->msg('shop_credit_card');
* $I18N_21->msg('shop_invoice');
* $I18N_21->msg('shop_pre_payment');
*/

// add available pay methods
foreach($dir_content as $key => $dir) {
	if (is_readable($dir.'/config.inc.php')) {
    	include_once $dir.'/config.inc.php';
	}
    $fields['set_active']->addOption($I18N_21->msg('shop_'.$key),$key);
}

// headline for business terms
$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
$fields['headline1']->setValue($I18N_21->msg('shop_payment_costs'));

// get activated pay methods
foreach($dir_content as $key => $pay_method) {

	$fields[$key] = new textField($key, $I18N_21->msg('shop_'.$key));
	$fields[$key]->setValue($costs[$key]);
	$fields[$key]->setNote($CJO['ADDON']['settings'][$mypage]['CURRENCY']['DEFAULT_SIGN']);
	$fields[$key]->setFormat('call_user_func', array('cjoShopPrice::toCurrency', array('%s', true)));
}

$fields['update_button'] = new buttonField();
$fields['update_button']->addButton('cjoform_update_button',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');
$fields['update_button']->setButtonAttributes('cjoform_update_button', 'id="cjoform_update_button1"');

// build formular
$section = new cjoFormSection('', $I18N_21->msg("shop_payment_settings"), array());
$section->dataset = $dataset;
$section->addFields($fields);
$form->addSection($section);
$form->show(false);

// save posted data
if ($form->validate()) {
    
	// get POST vars, default = empty array
	$set_active = cjo_post('set_active', 'array');
	$new_costs = array();

	// rewrite costs array
	foreach($dir_content as $key => $value) {
		$new_costs[] = (cjo_post($key, 'bool'))
		             ? $key.'='.cjoShopPrice::convToFloat($_POST[$key])
		             : $key.'='.cjoShopPrice::convToFloat($costs[$key]);
	}

	// prepare new costs string
	$new_costs = implode('|', $new_costs);

	$msg = 'msg_data_saved';

	$config_file = $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
	$config_data = file_get_contents($config_file);

	// rewrite settings file
	if (is_array($set_active)) $set_active = implode('|',$set_active);

	$pattern = "!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['PAY_METHODS'\].?\=.?)[^;]*!";
	$config_data = preg_replace($pattern,"\\1\"".$set_active."\"",$config_data);
	$pattern = "!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['PAY_COSTS'\].?\=.?)[^;]*!";
	$config_data = preg_replace($pattern,"\\1\"".$new_costs."\"",$config_data);

	cjoGenerate::putFileContents($config_file, $config_data);

	cjoAssistance::redirectBE( array('msg' => $msg));

}
