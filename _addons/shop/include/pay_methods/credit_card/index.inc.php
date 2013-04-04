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
/*----------------------------------
  this file will included in
  checkout_pay_data.inc.php if
  pay_method credit was chosen
------------------------------------*/

global $CJO, $I18N_21;

// create object for already written data
if (isset($posted['checkout']['pay_data'])) {
	$pay_object = new cjoShopCreditCard($posted['checkout']['pay_data']);
}

$expiration_dates = array('');
$y = date("y");
$m = date("m");
for($i=0; $i<=(6*12); $i++) {
    $date = $m.'/'.$y;
    $expiration_dates[$date] = $date;
    $m++;
    if (($m % 13) == 0) {
        $y++;
        $m = 1;
    }
    if (strlen($m) == 1) $m = '0'.$m;
}

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$elements_in['css']         = '';
$form_elements_in[]         = $elements_in;

// headline for credit card data
$elements_in                = array();
$elements_in['type']		= 'headline';
$elements_in['name']		= 'shop_checkout_credit_card_headline';
$elements_in['default']		= $I18N_21->msg('shop_insert_paydata');
$elements_in['css']			= 'form_elm_headline';
$form_elements_in[]         = $elements_in;

// card provider
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'card_provider';
$elements_in['label']		= $I18N_21->msg('shop_get_credit_card_provider');
$elements_in['default']		= isset($pay_object) ? $pay_object->getCardProvider() : '';
$elements_in['validate']	= 'notEmpty';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_credit_card_provider');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// credit card id
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'card_id';
$elements_in['label']		= $I18N_21->msg('shop_get_credit_card_id');
$elements_in['default']		= isset($pay_object) ? $pay_object->getCardId() : '';
$elements_in['validate']	= 'numeric';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_credit_card_number');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// expiration date
$elements_in                = array();
$elements_in['type'] 		= 'select';
$elements_in['name'] 		= 'expiration_date';
$elements_in['label']		= $I18N_21->msg('shop_get_credit_card_expiration');
$elements_in['values']		= $expiration_dates;
$elements_in['default']		= isset($pay_object) ? $pay_object->getExpirationDate() : '';
$elements_in['validate']	= 'larger_then';
$elements_in['equal_value'] = 2;
$elements_in['error_msg']	= $I18N_21->msg('msg_no_expiration_date');
$elements_in['required']	= 1;
$elements_in['css']			= 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// card cvs
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'cvs';
$elements_in['label']		= $I18N_21->msg('shop_get_credit_card_cvs');
$elements_in['default']		= isset($pay_object) ? $pay_object->getCVS() : '';
$elements_in['validate']	= 'not_empty|numeric|is_length';
$elements_in['equal_value'] = 3;
$elements_in['error_msg']	= $I18N_21->msg('msg_no_credit_card_cvs').'|'.$I18N_21->msg('shop_err_nums_only').'|'.$I18N_21->msg('shop_err_length_eq_3');
$elements_in['required']	= 1;
$elements_in['css']			= 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$elements_in['css']         = '';
$form_elements_in[]         = $elements_in;

// headline for owner name
$elements_in                = array();
$elements_in['type']		= 'headline';
$elements_in['name']		= 'shop_checkout_credit_card_owner';
$elements_in['default']		= $I18N_21->msg('shop_credit_card_owner');
$elements_in['css']			= 'form_elm_headline';
$form_elements_in[]         = $elements_in;

// credit owners first name
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'firstname';
$elements_in['label']		= $I18N_21->msg('shop_get_credit_card_owner_firstname');
$elements_in['default']		= isset($pay_object) ? $pay_object->getFirstname() : '';
$elements_in['validate']	= 'notEmpty';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_credit_card_owner_name');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// account owners name
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'name';
$elements_in['label']		= $I18N_21->msg('shop_get_credit_card_owner_name');
$elements_in['default']		= isset($pay_object) ? $pay_object->getName() : '';
$elements_in['validate']	= 'notEmpty';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_credit_card_owner_name');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;