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

// only include in frontend
if ($CJO['CONTEJO']) return false;


/**
 * <strong><u>Class cjoShopCheckout</u></strong>
 * This class provides static methods
 * for data handling during the checkout.
 */

class cjoShopCheckout {
    
    protected static $mypage = 'shop';
    
	/**
	 * Calls a function to save user input, depending
	 * on the checkout page from which the data was
	 * committed.
	 *
	 * @param array &$posted - the POST-vars
	 * @param string $page   - the page from which was
	 * 						   posted
	 * @access public
	 */

	public static function refreshData(&$posted, $page) {

		if(empty($posted)) return false;

	    switch($page) {
			case 'address1':	 	cjoShopCheckout::refreshAddress1($posted);
									break;

			case 'address2':	 	cjoShopCheckout::refreshAddress2($posted);
							 		break;

			case 'pay_data':		cjoShopCheckout::refreshPayData($posted);
									break;

			case 'confirm':			if (!empty($posted['comment'])) {
										$posted['checkout']['comment'] = $posted['comment'];
			                        }
									break;
		}

	} // end function refreshData


	/**
	 * Rewrites customers address data, and
	 * checks if supply address is needed.
	 *
	 * @param array &$posted - the posted values
	 * @access private
	 */
	private static function refreshAddress1(&$posted) {

		$adr = new cjoShopSupplyAddress($posted);
		$posted['checkout']['address1'] = $adr->toString();
		$posted['checkout']['personals'] = $posted['birth'].'|'.$posted['email'].'|'.$posted['phone_nr'];

		// delete supply address if not posted
		if (empty($posted['supply_address']) || 
			$CJO['ADDON']['settings'][$mypage]['ADRESS2_ENABLED'] == '0') {
			$posted['checkout']['address2'] = '';
		}
	}


	/**
	 * Rewrite customers supply address data.
	 *
	 * @param array &$posted - the posted values
	 * @access private
	 */
	private static function refreshAddress2(&$posted) {
		$adr = new cjoShopSupplyAddress($posted);
		$posted['checkout']['address2'] = $adr->toString();
	}


	/**
	 * Rewrites customers payment data
	 * if posted.
	 *
	 * @param array &$posted - the posted values
	 * @access private
	 */
	private static function refreshPayData(&$posted) {

		global $CJO;

		$curr_method = $posted['pay_method'];

		// include required files
		$pay_methods_path = $CJO['ADDON']['settings'][self::$mypage]['PAY_METHODS_PATH'];
		$file = $pay_methods_path.'/'.$curr_method.'/class.shop_'.$curr_method.'.inc.php';

		if(is_readable($file))
			include_once $file;

		// get class
		$count = 0;
		$class = explode('_', $curr_method);

		// set pay method
		$posted['checkout']['pay_method'] = $curr_method;
		$construct = 'cjoShop'.ucfirst($class[0]).ucfirst($class[1]);

		// return if no class exists
		if (!class_exists($construct)) return;

		// check if pay data was posted
		foreach($posted as $key => $post_var) {
			if (is_array($post_var) || $key == 'pay_method') {
				continue;
			} else {
				$count += 1;
			}
		}
		// write data if existing
		$has_value = ($count > 1) ? true : false;
		$payment = new $construct($posted);

		$posted['checkout']['pay_data'] = $has_value ? $payment->toString() : '';
	}



	/**
	 * This methods returns a string of html-tag attributtes for
	 * the navigation buttons of the checkout template.
	 *
	 * @param string $new_page 		- page that the button directs to
	 * @param bool $curr_page 	 	- page that shall be displayed now
	 * @param bool $enabled 		- if the current attribute is enabled
	 * @return string $attributes 	- a html-attributes string
	 * @access public
	 */
	public static function setNaviAttributes($new_page, $curr_page, $enabled=false) {

		$attributes = 'class="';
		$attributes .= ($new_page == $curr_page) ? ' current' : '';

		//add disabled class and disabled attribute if not enabled
		$attributes .= !$enabled ? ' disabled" disabled="disabled"' : '"';
		return $attributes;
	}

	/**
	 * Function saves data to TBL_21_ORDERS.
	 *
	 * @param array $posted - the posted customer and order data
	 * @access public
	 */
	public static function saveOrder($posted) {

		global $CJO;

		$pay_methods_path = $CJO['ADDON']['settings'][self::$mypage]['PAY_METHODS_PATH'];
		$supply_address   = cjo_post('supply_address','bool') && $CJO['ADDON']['settings'][self::$mypage]['ADRESS2_ENABLED'] == '1';

		// get all data that need to be saved
		$address1 	 	= new cjoShopSupplyAddress($posted['checkout']['address1']);
		$address2 	 	= $supply_address
						? new cjoShopSupplyAddress($posted['checkout']['address2'])
						: $address1;

		$total_price 	= cjoShopBasket::getOrderValue();
		$personals 	 	= explode('|', $posted['checkout']['personals']);
		$delivery 	 	= new cjoShopDelivery($address2->getCountry());
		$pay_method	 	= $posted['checkout']['pay_method'];
		$pay_data	 	= $posted['checkout']['pay_data'];

		// save basket and get product list
		// with availability of every product
		$products_available = cjoShopBasket::saveBasket();
		$products 	 		= array_shift($products_available);

		//*** payment *** //

		// include config file if readable
		if (is_readable($config_file)) {
			include_once $pay_methods_path.'/'.$pay_method.'/config.inc.php';
		}
		// get costs of chosen pay method
		$pay_costs = cjoShopPayMethod::getAllCosts();
		$pay_costs = $pay_costs[$pay_method];

		// get total costs of the whole order

		$total_price += $pay_costs + $delivery->getTotalCosts();
		$comment = cjoAssistance::cleanInput($posted['checkout']['comment']);

		// save data to tbl_21_orders
		$insert = new cjoSql();
		$insert->setValue('title', $address1->getTitle());
		$insert->setValue('firstname', $address1->getFirstName());
		$insert->setValue('name', $address1->getName());
		$insert->setValue('company', $address1->getCompany());
		$insert->setValue('address1', $address1->getAddress());
		$insert->setValue('address2', $address2->toString());
		$insert->setValue('email', $personals[1]);
		$insert->setValue('phone_nr', $personals[2]);
		$insert->setValue('total_price', cjoShopPrice::convToFloat($total_price));
		$insert->setValue('pay_method', $pay_method);
		$insert->setValue('pay_data', $pay_data);
		$insert->setValue('pay_costs', cjoShopPrice::convToFloat($pay_costs));
		$insert->setValue('state', 1);
		$insert->setValue('products', $products);
		$insert->setValue('delivery_method', $delivery->getDeliverer().': '.$delivery->getDelivererSize());
		$insert->setValue('delivery_cost', cjoShopPrice::convToFloat($delivery->getTotalCosts()));
		$insert->setValue('birth_date', strtotime($personals[0]));
		$insert->addGlobalCreateFields($address1->getName());
		$insert->addGlobalUpdateFields('--');
        $insert->setValue('comment', $comment);
		$insert->setTable(TBL_21_ORDERS);
		$insert->Insert();

		if ($insert->getError() != '') return -1;

		// send confirmation mail (error catch not working yet)
		if (!cjoShopMail::sendMail('ORDER_CONFIRM_SUBJECT', $insert->getLastId(), $products_available)) return -2;

		return $insert->getLastId();

	} // end function saveOrder

	/**
	 * Reads all posted values from the checkout formular and removes
	 * | and ~ from incoming strings to prevent errors.
	 *
	 * @param string $page - the checkout page name
	 * @return array $posted - the cleaned posted values
	 */
	public static function getPostVars($page){

		$posted = cjo_post($page, 'array' , array(), false);

		foreach($posted as $key => $value) {
			if ($key == 'checkout') continue;
			$value = cjoAssistance::cleanInput($value);
			$posted[$key] = str_replace(array('|', '~'), array('', ''), $value);
		}
		return $posted;
	}

} // end class cjoShopCheckout
