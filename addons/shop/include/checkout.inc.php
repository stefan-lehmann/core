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

global $I18N_21, $CJO;
$mypage 	= 'shop';
$is_valid 	= true;

// get id of "data successfully sent" page
$success_page_id = $CJO['ADDON']['settings'][$mypage]['POST_ORDER_ARTICLE_ID'];

//reset form elements
$form_elements_out = array();

// *** get POST vars *** //

// get previus page, default=address1
$page 			= cjo_post('page', 'string', 'address1');

// get posted customer data and clean it
$posted			= cjoShopCheckout::getPostVars($page);
$supply_address = cjo_post('supply_address', 'bool') && $CJO['ADDON']['settings'][$mypage]['ADRESS2_ENABLED'] == '1';
$full_basket	= cjo_post('full_basket', 'string');

// write new data to hidden fields
cjoShopCheckout::refreshData($posted, $page);

// authentificate user and check if basket is empty
$basket_id = $CJO['ADDON']['settings'][$mypage]['BASKET_ARTICLE_ID'];

// is there anything in the basket
if (empty($full_basket)) {
	cjoAssistance::redirectFE($basket_id);
}
else {
	$session_id = session_id();
	$md5 = md5($session_id);
	// has this user anything in basket
	if ($md5 != $full_basket) cjoAssistance::redirectFE($basket_id);
}

// check if basket is still filled
$sql = new cjoSql();
$qry = "SELECT COUNT(session_id) as amount FROM ".TBL_21_BASKET." WHERE session_id='".$session_id."'";
$sql->setQuery($qry);

// if basket is empty redirect to basket page
if (0 == $sql->getValue('amount', 0)) cjoAssistance::redirectFE($basket_id);

// set supply address if posted
if (isset($posted['supply_address'])) $supply_address = $posted['supply_address'];

// define array for page navigation
$pages = ($supply_address == 1)
	   ? array('address1', 'address2', 'pay_data', 'confirm')
	   : array('address1', 'pay_data', 'confirm');

// get page index
$page_id = array_search($page, $pages);

// build formular elements
include_once $CJO['ADDON_PATH']."/".$mypage."/include/checkout_".$page.".inc.php";

// select next page if data is valid

if ($is_valid === true || !empty($posted['submit']['previous'])) {
	// get new page
    if(is_array($posted['submit'])) {
    	foreach($posted['submit'] as $key => $value) {
    
    		if(isset($value)) {
    
    			switch($key) {
    				case 'next'		: 	$page = $pages[++$page_id];
    									break;
    				case 'previous'	: 	$page = $pages[--$page_id];
    									break;
    				case 'send'		: 	$order_id = cjoShopCheckout::saveOrder($posted);
										if ($order_id > 0)
    				                    cjoAssistance::redirectFE($success_page_id, false, array('order'=> md5($order_id));
    									break;
    				case 'address2' :   if ($supply_address) $page = 'address2';
    									break;
    				default			: 	$page = $key;
    			}
    			// reset $posted
    
    			$temp = $posted['checkout'];
    			$posted = array('checkout' => $temp);
    			unset($temp);
    			break;
    		}
    	}
    }

	// build new formular
	include_once $CJO['ADDON_PATH']."/".$mypage."/include/checkout_".$page.".inc.php";

	// delete error messages if this is a reload
	if ($is_valid) $form_elements_out['element_error_msg'] = array();

	// unset $confirm in case you return from
	// page confirm ($confirm is defined in checkout_confirm.inc)
	if ($page == 'confirm') unset($confirm);
}
else {
	if ($page == 'pay_data') unset($posted['checkout']['pay_method']);

    // delete saved data if it is not valid
	$posted['checkout'][$page] = false;
}

// prepare template
$tmpl = file_get_contents($CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['CHECKOUT']);
$html = new cjoHtmlTemplate($tmpl);

// set hidden fields and navigation values
$html->fillTemplate('TEMPLATE', array(
		                              'HEADLINE'				=>		$I18N_21->msg('shop_checkout_'.$page),
									  'ADDRESS1'				=>		$posted['checkout']['address1'],
									  'ADDRESS2'				=>		$posted['checkout']['address2'],
									  'PERSONALS'				=>		$posted['checkout']['personals'],
									  'PAY_DATA'				=>		$posted['checkout']['pay_data'],
									  'PAY_METHOD'				=>		$posted['checkout']['pay_method'],
									  'COMMENT'					=>		$posted['checkout']['comment'],
									  'FULL_BASKET'				=>		$full_basket,
									  'PAGE'					=>		$page,
									  'PREVIOUS'				=>		$page != 'address1',
									  'NEXT'					=>		$page != 'confirm',
									  'SUPPLY_ADDRESS'			=>		$supply_address,
									  'CONFIRM'					=>		$page == 'confirm',
									  'SUBMIT'					=>		$submit,

                                      'CUR_PAGE_CLASS'			=>      'page_'.$page,
                                      'IS_PAGE_ADRESS1'			=>      $page == 'address1',
                                      'IS_PAGE_ADRESS2'			=>      $page == 'address2',
                                      'IS_PAGE_PAY_DATA'        =>      $page == 'pay_data',
                                      'IS_PAGE_CONFIRM'         =>      $page == 'confirm',

									  'COUNT_ADDRESS1'		    =>		1,
                                      'COUNT_ADDRESS2'			=>		2,
                                      'COUNT_PAY_DATA'			=>		(count($pages) == 3 ? 2 : 3),
                                      'COUNT_CONFIRM'			=>		(count($pages) == 3 ? 3 : 4),
									  'SUBMIT_ADDRESS1_ATTR'	=>       cjoShopCheckout::setNaviAttributes
																		('address1', $page, true),
									  'SUBMIT_ADDRESS2_ATTR'	=>       cjoShopCheckout::setNaviAttributes
																		('address2', $page, ($posted['checkout']['address1']
									                                                         || $posted['checkout']['address2'])
									                                     ),
									  'SUBMIT_PAY_DATA_ATTR'	=>      cjoShopCheckout::setNaviAttributes
									                                    ('pay_data',$page, (count($pages) == 3
	                                                                            ? ($posted['checkout']['address1'] ||
	                                                                           	   $posted['checkout']['pay_method'])
	                                                                            : ($posted['checkout']['address2'] ||
	                                                                               $posted['checkout']['pay_method'])
	                                                                            )
	                                                                     ),
									  'SUBMIT_CONFIRM_ATTR'		=>      cjoShopCheckout::setNaviAttributes
	                                                                    ('confirm', $page, $posted['checkout']['pay_method']),
									  'PAY_METHOD_NOT_VALID'	=> 		$pay_method_not_valid ));

if ($page == 'confirm' && !empty($confirm_details)) {
    // output confirm elements
    $html->fillTemplateArray('CONFIRM_DETAILS', $confirm_details);
}

// output form elements
$html->fillTemplateArray('FORM_ELEMENTS', $form_elements_out);

$checkout = $html->get(false);
/*
* all yet possible combinations for 'shop_checkout_'.$page (see line 138)
* this lets i18n.php php find all texts that need to be
* translated
*
* $I18N_21->msg('shop_checkout_address1');
* $I18N_21->msg('shop_checkout_address2');
* $I18N_21->msg('shop_checkout_pay_data');
* $I18N_21->msg('shop_checkout_confirm');
*/
