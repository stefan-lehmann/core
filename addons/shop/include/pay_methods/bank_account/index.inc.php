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

/*----------------------------------
  this file will included in
  checkout_pay_data.inc.php if
  pay_method bank_account was chosen
------------------------------------*/

global $CJO, $I18N_21;

// get already saved data
if (isset($posted['checkout']['pay_data']))
	$pay_object = new cjoShopBankAccount($posted['checkout']['pay_data']);

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$elements_in['css']         = '';
$form_elements_in[]         = $elements_in;

// headline for account data
$elements_in                = array();
$elements_in['type']		= 'headline';
$elements_in['name']		= 'bank_account_headline';
$elements_in['default']		= $I18N_21->msg('shop_insert_paydata');
$elements_in['css']			= 'form_elm_headline';
$form_elements_in[]         = $elements_in;

// account id
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'account_id';
$elements_in['label']		= $I18N_21->msg('shop_get_bank_account_id');
$elements_in['default']		= isset($pay_object) ? $pay_object->getAccountId() : '';
$elements_in['validate']	= 'not_empty|numeric';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_bank_account_number').'|'.$I18N_21->msg('shop_err_nums_only');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// bank code
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'bank_code';
$elements_in['label']		= $I18N_21->msg('shop_get_bank_code');
$elements_in['default']		= isset($pay_object) ? $pay_object->getBankCode() : '';
$elements_in['validate']	= 'not_empty|numeric';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_bank_code').'|'.$I18N_21->msg('shop_err_nums_only');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// bank name
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'bank_name';
$elements_in['label']		= $I18N_21->msg('shop_get_bank_name');
$elements_in['default']		= isset($pay_object) ? $pay_object->getBankName() : '';
$elements_in['validate']	= 'notEmpty';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_bank_name');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$elements_in['css']         = '';
$form_elements_in[]         = $elements_in;

// headline for owner name
$elements_in                = array();
$elements_in['type']		= 'headline';
$elements_in['name']		= 'shop_checkout_bank_account_owner';
$elements_in['default']		= $I18N_21->msg('shop_bank_account_owner');
$elements_in['css']			= 'form_elm_headline';
$form_elements_in[]         = $elements_in;

// account owners first name
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'firstname';
$elements_in['label']		= $I18N_21->msg('shop_get_bank_owner_firstname');
$elements_in['default']		= isset($pay_object) ? $pay_object->getFirstname() : '';
$elements_in['validate']	= 'notEmpty';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_bank_owner_name');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;

// account owners name
$elements_in                = array();
$elements_in['type']		= 'text';
$elements_in['name'] 		= 'name';
$elements_in['label']		= $I18N_21->msg('shop_get_bank_owner_name');
$elements_in['default']		= isset($pay_object) ? $pay_object->getName() : '';
$elements_in['validate']	= 'notEmpty';
$elements_in['error_msg']	= $I18N_21->msg('msg_no_bank_owner_name');
$elements_in['required']	= 1;
$form_elements_in[]         = $elements_in;