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
 * @version     2.6.0
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


 //****		CHECKOUT SUPPLY ADDRESS FORMULAR FIELDS		****//

/*
-----------------------------------
 prepare array of form elements,
 it is needed to build the formular
 and make it validable
-----------------------------------
 */
global $CJO;
global $I18N_21;
$mypage = 'shop';


// get available pay methods
$pay_methods = cjoAssistance::toArray($CJO['ADDON']['settings'][$mypage]['PAY_METHODS']);

// get containing folder
$pay_methods_path = $CJO['ADDON']['settings'][$mypage]['PAY_METHODS_PATH'];

//// check if posted paymethod index-file exists
//$pay_method_exists = file_exists($pay_methods_path.'/'.$posted['pay_method'].'/index.inc.php');

// get current paymethod (new selected or saved)
if(count($pay_methods) > 1) {
    $curr_pay_method = (isset($posted['pay_method'])) ? $posted['pay_method'] : $posted['checkout']['pay_method'];
}
else {
    $curr_pay_method = $pay_methods[0];
}
if (!$curr_pay_method) $curr_pay_method = $pay_methods[0];

// check if a pay modul has to be included
$pay_method_changed	= ($posted['checkout']['pay_method'] != $posted['pay_method'] &&
				       isset($posted['pay_method']) && $pay_methods_exists);

// get costs for all pay methods
$payment_costs = cjoShopPayMethod::getAllCosts();

$form_elements_in = array();

// build selectbox
$select_pay_method = new cjoSelect();
$select_pay_method->setSize(1);
$select_pay_method->addOption('[translate_21: shop_no_pay_method]', '');
$select_pay_method->setName($page.'[pay_method]');
$select_pay_method->setSelected($curr_pay_method);
$select_pay_method->setSelectExtra('onchange="this.form.submit();"');

foreach($pay_methods as $method) {

	// extend lang object for translations
	include_once $pay_methods_path.'/'.$method.'/config.inc.php';

	$extra_costs = '';
	// build array for selectbox values
	if ($payment_costs[$method] != 0) {
	    $extra_costs = ' (+'.cjoShopPrice::toCurrency($payment_costs[$method]).')';
	}
	$select_pay_method->addOption($I18N_21->msg('shop_'.$method).$extra_costs, $method);
}

$elements_in                = array();
$elements_in['type']		= 'notice';
$elements_in['name'] 		= 'pay_method';
$elements_in['default']		= $select_pay_method->get();
$elements_in['label']		= $I18N_21->msg('shop_pay_method');
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= $I18N_21->msg('shop_no_pay_method');
$form_elements_in[]         = $elements_in;

//// define current pay method if it isn't so yet
//$curr_pay_method = empty($curr_pay_method) ? $pay_methods[0] : $curr_pay_method;

// include index and class file for the selected paymethod
$class_file = $pay_methods_path."/".$curr_pay_method."/class.shop_".$curr_pay_method.".inc.php";

if (is_readable($pay_methods_path.'/'.$curr_pay_method.'/index.inc.php') &&
    is_readable($class_file)) {

	include_once ($class_file);
	include_once $pay_methods_path.'/'.$curr_pay_method.'/index.inc.php';
}

// get paymethod selectbox
list($form_elements_out, $mail_elements_out, $is_valid) = shopOutput::getFormElements($page, $form_elements_in);

// process external validation if neccessary
if (isset($pay_object)) {
	$pay_method_not_valid = $pay_object->getValidationMsg();
}
else {
    $is_valid = true;
}

// no validation because pay modul has changed
if ($pay_method_changed) {
	$is_valid = NULL;
	$form_elements_out['element_error_msg']= array();
}
elseif (!empty($pay_method_not_valid) || !$posted['pay_method']) {
	$is_valid = false;
}
