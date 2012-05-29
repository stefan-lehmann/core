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

 //****		CHECKOUT MAIN ADDRESS FORMULAR FIELDS		****//

// get already written data
if (!empty($posted['checkout']['address1']))
	$address1 = new cjoShopSupplyAddress($posted['checkout']['address1']);

if (!empty($posted['checkout']['personals']))
	$personals = explode('|', $posted['checkout']['personals']);

/*
-----------------------------------
 prepare array of form elements,
 it is needed to build the formular
 and make it validable
-----------------------------------
 */
$form_elements_in = array();

// title input
$elements_in                = array();
$elements_in['type'] 		= 'select';
$elements_in['name'] 		= 'title';
$elements_in['values']		= ' |'.$I18N_21->msg('shop_mr').'|'.$I18N_21->msg('shop_mrs');
$elements_in['default']		= isset($address1) ? $address1->getTitle() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= $I18N_21->msg('shop_err_no_title');
$elements_in['label']		= $I18N_21->msg('shop_title');
$elements_in['css']			= 'form_elm_med';
$form_elements_in[]         = $elements_in;

// firstname input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'firstname';
$elements_in['default']		= isset($address1) ? $address1->getFirstname() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= $I18N_21->msg('shop_err_no_firstname');
$elements_in['label']		= $I18N_21->msg('shop_firstname');
$form_elements_in[]         = $elements_in;

// name input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'name';
$elements_in['default']		= isset($address1) ? $address1->getName() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= $I18N_21->msg('shop_err_no_lastname');
$elements_in['label']		= $I18N_21->msg('shop_lastname');
$form_elements_in[]         = $elements_in;

// company input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'company';
$elements_in['default']     = isset($address1) ? $address1->getCompany() : '';
$elements_in['label']       = $I18N_21->msg('shop_company');
$form_elements_in[]         = $elements_in;

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$elements_in['css']         = '';
$form_elements_in[]         = $elements_in;

// street input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'street';
$elements_in['default']     = isset($address1) ? $address1->getStreet() : '';
$elements_in['required']    = 1;
$elements_in['validate']    = 'not_empty';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_street');
$elements_in['label']       = $I18N_21->msg('shop_street');
$form_elements_in[]         = $elements_in;

// streetnr input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'street_nr';
$elements_in['default']     = isset($address1) ? $address1->getStreetNr() : '';
$elements_in['required']    = 1;
$elements_in['validate']    = 'not_empty';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_street_nr');
$elements_in['label']       = $I18N_21->msg('shop_street_nr');
$elements_in['css']         = 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// postalcode input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'postal_code';
$elements_in['default']     = isset($address1) ? $address1->getPostalCode() : '';
$elements_in['required']    = 1;
$elements_in['validate']    = 'notEmpty|digit_only';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_postal_code').'|'.$I18N_21->msg('shop_err_nums_only');
$elements_in['label']       = $I18N_21->msg('shop_postal_code');
$elements_in['css']         = 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// place input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'place';
$elements_in['default']     = isset($address1) ? $address1->getPlace() : '';
$elements_in['required']    = 1;
$elements_in['validate']    = 'not_empty';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_place');
$elements_in['label']       = $I18N_21->msg('shop_place');
$form_elements_in[]         = $elements_in;

// po-box. input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'po-box';
$elements_in['default']     = isset($address1) ? $address1->getPoBox() : '';
$elements_in['validate']    = 'digit_only';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_nums_only');
$elements_in['label']       = $I18N_21->msg('shop_po_box');
$elements_in['css']         = 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// country input
$elements_in                = array();
$elements_in['type']        = 'select';
$elements_in['name']        = 'country';
// select only in tbl_21_country_zone defined countries
$elements_in['values']      = array_diff(cjo_get_country_codes(), cjoShopZone::getCountryNames(-1));
$elements_in['default']     = isset($address1) ? $address1->getCountry() : strtoupper($CJO['CLANG_ISO'][$CJO['CUR_CLANG']]);
$elements_in['required']    = 1;
$elements_in['validate']    = 'not_empty';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_customer_country');
$elements_in['label']       = $I18N_21->msg('shop_customer_country');
$elements_in['css']         = 'form_elm_norm';
$form_elements_in[]         = $elements_in;

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$elements_in['css']         = '';
$form_elements_in[]         = $elements_in;


// birth date input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'birth';
$elements_in['default']     = isset($personals) ? $personals[0] : '';
$elements_in['required']    = 1;
$elements_in['validate']    = 'not_empty|date_dd.mm.yyyy';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_birth_date').'|'.$I18N_21->msg('shop_err_date_format');
$elements_in['label']       = $I18N_21->msg('shop_birth_date');
$elements_in['css']         = 'form_elm_med';
$form_elements_in[]         = $elements_in;

// email input
$elements_in                = array();
$elements_in['type']        = 'text';
$elements_in['name']        = 'email';
$elements_in['default']     = isset($personals) ? $personals[1] : '';
$elements_in['required']    = 1;
$elements_in['validate']    = 'not_empty|email';
$elements_in['error_msg']   = $I18N_21->msg('shop_err_no_mail').'|'.$I18N_21->msg('shop_err_mail_format');
$elements_in['label']       = 'E-Mail';
$form_elements_in[]         = $elements_in;

// telephone number input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'phone_nr';
$elements_in['default']	    = isset($personals) ? $personals[2] : '';
$elements_in['validate']	= 'not_empty|telefon';
$elements_in['error_msg']	= $I18N_21->msg('shop_err_nums_only');
$elements_in['label']		= $I18N_21->msg('shop_phone_nr');
$form_elements_in[]         = $elements_in;



if ($CJO['ADDON']['settings'][$mypage]['ADRESS2_ENABLED'] == '1') {
    
    // dividing element
    $elements_in                = array();
    $elements_in['type']        = 'fieldset';
    $elements_in['css']         = '';
    $form_elements_in[]         = $elements_in;
    
    // different supply address
    $elements_in                = array();
    $elements_in['type'] 		= 'checkbox';
    $elements_in['name'] 		= 'supply_address';
    $elements_in['value']		= 1;
    $elements_in['default']	    = $supply_address;
    $elements_in['label']		= $I18N_21->msg('shop_different_supply_address');
    $form_elements_in[]         = $elements_in;
}
// prepare form elements for output
list($form_elements_out, $mail_elements_out, $is_valid)  = shopOutput::getFormElements($page, $form_elements_in);
