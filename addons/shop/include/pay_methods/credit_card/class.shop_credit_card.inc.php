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
 * <strong><u>Class cjoShopCreditCard></u></strong>
 *
 * This class provides methods to handle credit card data
 *
 * @var $card_id (string) - the card number
 * @var $cvs (int)	 - the card security number
 * @var $card_provider (string) - the card providing institution
 * @var $expiration_date (date/string) - date on that the card becomes invalid
 * @var $firstname (string) - the card owners firstname
 * @var $name (string) - the card owners name
 */
class cjoShopCreditCard extends cjoShopPayment {

	private $card_id;
	private $cvs;
	private $card_provider;
	private $expiration_date;
	private $firstname;
	private $name;
	private $costs;

	/**
	 * Constructor
	 * @param $value (string or array), default=null - the credit card data as string
	 * 												   or resultset(array)
	 */

	function __construct($value = null) {

		if (is_array($value)) {
			$this->card_id 			= $value['card_id'];
			$this->cvs				= $value['cvs'];
			$this->card_provider	= $value['card_provider'];
			$this->expiration_date	= $value['expiration_date'];
			$this->firstname		= $value['firstname'];
			$this->name				= $value['name'];
		}
		elseif (!empty($value)) {
			$arr = explode('|', $value);
			$this->card_id 			= $arr[0];
			$this->cvs				= $arr[1];
			$this->card_provider	= $arr[2];
			$this->expiration_date	= $arr[3];
			$this->firstname		= $arr[4];
			$this->name				= $arr[5];
		}

		$all_costs = cjoShopPayMethod::getAllCosts();
		$this->costs = $all_costs['credit_card'];
	}

	// get methods
	public function getCardId()				{	return $this->card_id;			}
	public function getCVS()				{	return $this->cvs;				}
	public function getCardProvider()		{	return $this->card_provider;	}
	public function getExpirationDate()		{	return $this->expiration_date;	}
	public function getFirstname()			{	return $this->firstname;		}
	public function getName()				{	return $this->name;				}
	public function getCosts()				{	return $this->costs;			}

	/**
	 * Converts the object into a string for saving.
	 * The delimiter is '|'.
	 *
	 * @return $string (string)
	 */
	public function toString() {
		$string  = 	   $this->card_id;
		$string .= '|'.$this->cvs;
		$string .= '|'.$this->card_provider;
		$string .= '|'.$this->expiration_date;
		$string .= '|'.$this->firstname;
		$string .= '|'.$this->name;
		$string .= '|'.$this->costs;
		return $string;
	}

	/**
	 * Outputs the object as a formatted string
	 * (with newlines and whitespaces)
	 *
	 * @return string $string
	 * @access public
	 */
	public function out() {
		global $I18N_21, $CJO;
		$mypage = 'shop';
		$exchange_ratio = $CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_RATIO'];

		$string  = $this->firstname." ".$this->name."\r\n";
		$string .= $I18N_21->msg('shop_credit_card_id').": ".$this->card_id."\r\n";
		$string .= $I18N_21->msg('shop_credit_card_CVS').": ".$this->cvs."\r\n";
		$string .= $I18N_21->msg('shop_credit_card_provider').": "
		           .$this->card_provider."\r\n";

		$string .= $I18N_21->msg('shop_credit_card_expiration_date').": "
		           .$this->expiration_date."\r\n";

		// display costs in backend if there are some
		if($CJO['CONTEJO'] && !empty($this->costs)) {
			$this->costs = round($exchange_ratio * $this->costs, 2);
			$string .= $I18N_21->msg('shop_credit_card_costs').": "
		           	   .cjoShopPrice::toCurrency($this->costs)."\r\n";
		}

	    return $string;
	}

}// end class cjoShopCreditCard