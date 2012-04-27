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

/**
 * Builds output for page confirm
 * in the checkout process
 */

global $CJO;
$mypage = 'shop';

$form_elements_in = array();
$confirm_details = array();

// ****  CHECKOUT CONFIRM PAGE  **** //

// prepare pay data output
$pay_method 		= $posted['checkout']['pay_method'];
$pay_methods_path 	= $CJO['ADDON']['settings'][$mypage]['PAY_METHODS_PATH'];

// get requiredclass and config file
$pay_methods_path = $CJO['ADDON']['settings'][$mypage]['PAY_METHODS_PATH'];
$class_file = $pay_methods_path.'/'.$pay_method.'/class.shop_'.$pay_method.'.inc.php';
$conf_file  = $pay_methods_path.'/'.$pay_method.'/config.inc.php';

// get pay data
$pay_object = cjoShopPayMethod::getPayObject($pay_method, $posted['checkout']['pay_data']);
$pay_costs = cjoShopPayMethod::getAllCosts();
$pay_costs = $pay_costs[$pay_method];

// get personal data
$personals = explode('|', $posted['checkout']['personals']);
$contact = $I18N_21->msg('shop_email').': '.$personals[1];
$contact .= !empty($personals[2]) ? "\r\n".$I18N_21->msg('shop_phone_nr').': '.$personals[2] : '';

//display all customer data
$confirm_details['page'][0]           = $page;
$confirm_details['address1'][0]       = new cjoShopSupplyAddress($posted['checkout']['address1']);
$confirm_details['address2'][0]       = !empty($posted['checkout']['address2'])
								  	  ? new cjoShopSupplyAddress($posted['checkout']['address2'])
								      : $confirm_details['address1'][0];
$confirm_details['product_table'][0]  = cjoShopBasket::out('PRODUCT_TABLE', $pay_costs, $confirm_details['address2'][0]->getCountry());
$confirm_details['pay_method'][0]     = $I18N_21->msg('shop_'.$pay_method);
$confirm_details['pay_data'][0]       = $pay_object->out();

$confirm_details['contact'][0]        = $contact;

$confirm_details['comment_name'][0]	  = 'confirm[comment]';
$confirm_details['comment_value'][0]  = $posted['checkout']['comment'];

// if $supply_address is not set
// redirect to page address1 from
// checkout-confirm-supply-address
$confirm_details['dest_address'][0]	  = $supply_address ? 'address2' : 'address1';

$business_terms = '';
if (file_exists($CJO['ADDON']['settings'][$mypage]['BUSINESS_TERMS'])) {
    $business_terms =  file_get_contents($CJO['ADDON']['settings'][$mypage]['BUSINESS_TERMS']);
    $business_terms = nl2br($business_terms);
}

// reset form elements
$form_elements_out = array();

// accept business terms
$elements_in                = array();
$elements_in['type'] 		= 'checkbox';
$elements_in['name'] 		= 'business_terms';
$elements_in['value']		= 1;
$elements_in['default']		= 0;
$elements_in['required']	= 1;
$elements_in['label']		= $I18N_21->msg('confirm_business_terms');
$elements_in['validate']	= 'equal';
$elements_in['error_msg']	= $I18N_21->msg('msg_not_confirmed_business_terms');
$elements_in['equal_value']	= 1;
$form_elements_in[]         = $elements_in;

// textarea for business terms
$elements_in                = array();
$elements_in['type'] 		= 'notice';
$elements_in['name'] 		= 'business_terms';
$elements_in['default']		= '<div id="shop_business_terms_txt">'.$business_terms.'</div>';
$form_elements_in[]         = $elements_in;

// prepare form elements for output
list($form_elements_out, $mail_elements_out, $is_valid)  = shopOutput::getFormElements($page, $form_elements_in);


// validate business terms confirmation only
// if send data was clicked
if (!isset($posted['submit']['send'])) {
	$form_elements_out['element_error_msg'] = array();
	$is_valid = true;
}
