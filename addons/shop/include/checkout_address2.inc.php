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

 //****		CHECKOUT SUPPLY ADDRESS FORMULAR FIELDS		****//


// get already written data
if(!empty($posted['checkout']['address2']))
	$address2 = new cjoShopSupplyAddress($posted['checkout']['address2']);


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
$elements_in['values']		= ' |'.cjoAddon::translate(21,'shop_mr').'|'.cjoAddon::translate(21,'shop_mrs');
$elements_in['default']		= isset($address2) ? $address2->getTitle() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_title');
$elements_in['label']		= cjoAddon::translate(21,'shop_title');
$elements_in['css']			= 'form_elm_med';
$form_elements_in[]         = $elements_in;

// firstname input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'firstname';
$elements_in['default']		= isset($address2) ? $address2->getFirstname() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_firstname');
$elements_in['label']		= cjoAddon::translate(21,'shop_firstname');
$form_elements_in[]         = $elements_in;

// name input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'name';
$elements_in['default']		= isset($address2) ? $address2->getName() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_lastname');
$elements_in['label']		= cjoAddon::translate(21,'shop_lastname');
$form_elements_in[]         = $elements_in;

// company input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'company';
$elements_in['default']		= isset($address2) ? $address2->getCompany() : '';
$elements_in['label']		= cjoAddon::translate(21,'shop_company');
$form_elements_in[]         = $elements_in;

// dividing element
$elements_in                = array();
$elements_in['type']        = 'fieldset';
$form_elements_in[]         = $elements_in;

// street input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'street';
$elements_in['default']		= isset($address2) ? $address2->getStreet() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_street');
$elements_in['label']		= cjoAddon::translate(21,'shop_street');
$form_elements_in[]         = $elements_in;

// streetnr input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'street_nr';
$elements_in['default']		= isset($address2) ? $address2->getStreetNr() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_street_nr');
$elements_in['label']		= cjoAddon::translate(21,'shop_street_nr');
$elements_in['css']			= 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// postalcode input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'postal_code';
$elements_in['default']		= isset($address2) ? $address2->getPostalCode() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_postal_code');
$elements_in['label']		= cjoAddon::translate(21,'shop_postal_code');
$elements_in['css']			= 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// place input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'place';
$elements_in['default']		= isset($address2) ? $address2->getPlace() : '';
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_place');
$elements_in['label']		= cjoAddon::translate(21,'shop_place');
$form_elements_in[]         = $elements_in;

// po-box. input
$elements_in                = array();
$elements_in['type'] 		= 'text';
$elements_in['name'] 		= 'po-box';
$elements_in['default']		= isset($address2) ? $address2->getPoBox() : '';
$elements_in['required']	= 0;
$elements_in['validate']	= 'alphanumeric';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_letters_or_nums');;
$elements_in['label']		= cjoAddon::translate(21,'shop_po_box');
$elements_in['css']			= 'form_elm_smll';
$form_elements_in[]         = $elements_in;

// country input
$elements_in                = array();
$elements_in['type'] 		= 'select';
$elements_in['name'] 		= 'country';
// select only in tbl_21_country_zone defined countries
$elements_in['values']		= array_diff(cjo_get_country_codes(), cjoShopZone::getCountryNames(-1));
$elements_in['default']	    = isset($address2) ? $address2->getCountry() : strtoupper($CJO['CLANG_ISO'][cjoProp::getClang()]);
$elements_in['required']	= 1;
$elements_in['validate']	= 'not_empty';
$elements_in['error_msg']	= cjoAddon::translate(21,'shop_err_no_customer_country');
$elements_in['label']		= cjoAddon::translate(21,'shop_customer_country');
$elements_in['css']         = 'form_elm_norm';
$form_elements_in[]         = $elements_in;

// prepare form elements for output
list($form_elements_out, $mail_elements_out, $is_valid)  = shopOutput::getFormElements($page, $form_elements_in);
