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


/**
 * <strong>Class cjoShopBankAccount</strong>
 * This class contains methods for saving and manipulating
 * bank account data
 *
 * @var $bank_name (string)
 * @var $account_id (string)
 * @var	$account_id (string)
 * @var $firstname (string) - the card owners firstname
 * @var $name (string) - the card owners name
 */

class cjoShopBankAccount extends cjoShopPayment {
	private $bank_name;
	private $account_id;
	private $bank_code;
	private $firstname;
	private $name;
	private $costs;

	/**
	 * Creates an empty object if $string is null. Else $string has to be of the type
	 * created by toString().
	 * @param $value (string or array), default=null - the bankaccount data as string
	 * 												   or resultset(array)
	 */
	function __construct($value = null) {

		if (is_array($value)) {
			$this->bank_name 	= $value['bank_name'];
			$this->account_id 	= $value['account_id'];
			$this->bank_code 	= $value['bank_code'];
			$this->firstname	= $value['firstname'];
			$this->name			= $value['name'];
			$this->costs		= $value['costs'];

		}
		elseif (!empty($value)) {
			$arr = explode('|', $value);
			$this->bank_name	 = $arr[0];
			$this->account_id	 = $arr[1];
			$this->bank_code 	 = $arr[2];
			$this->firstname 	 = $arr[3];
			$this->name 		 = $arr[4];
			$this->costs		 = $arr[5];
		}

		if ($this->costs === '')
			$all_costs = cjoShopPayMethod::getAllCosts();
			$this->costs = $all_costs['bank_account'];
	}

	// set methods
	public function setBankName($name)		{ $this->bank_name = $name;	}
	public function setAccountId($id)		{ $this->account_id = $id;	}
	public function setBankCode($code)		{ $this->bank_code = $code;	}
	public function setFirstname($name)		{ $this->firstname = $name;	}
	public function setName($name)			{ $this->name = $name;		}

	// get methods
	public function getBankName()	{	return $this->bank_name;	}
	public function getAccountId()	{	return $this->account_id;	}
	public function getBankCode()	{	return $this->bank_code;	}
	public function getFirstname()	{ 	return $this->firstname ;	}
	public function getName()		{ 	return $this->name;			}
	public function getCosts()		{	return $this->costs;		}


	/**
	 * Prepares object for saving. The Delimiter for variables
	 * is '*'. Order: bank*account_id*bank_code*iban*.
	 * @return string
	 */
	public function toString() {
		$string  = $this->bank_name;
		$string .= '|'.$this->account_id;
		$string .= '|'.$this->bank_code;
		$string .= '|'.$this->firstname;
		$string .= '|'.$this->name;
		$string .= '|'.$this->costs;
		return $string;
	}

	/**
	 * Returns a string with bank account data.
	 * @return string
	 */
	public function out() {
		global $I18N_21, $CJO;
		$mypage = 'shop';
		$exchange_ratio = $CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_RATIO'];

		$string = $this->firstname." ".$this->name."\r\n";

		$string .= $I18N_21->msg('shop_bank_account_id').": ".$this->account_id."\r\n";
		$string .= $I18N_21->msg('shop_bank_code').": ".$this->bank_code."\r\n";
		$string .= $I18N_21->msg('shop_bank_name').": ".$this->bank_name."\r\n";

		// display costs in backend if there are some
		if ($CJO['CONTEJO'] && !empty($this->costs)) {
			$this->costs = round($exchange_ratio * cjoShopPrice::convToFloat($this->costs), 2);
			$string .= $I18N_21->msg('shop_bank_account_costs').": "
		           	   .cjoShopPrice::toCurrency($this->costs)."\r\n";
		}

	    return $string;
	}

} //end class cjoShopBankAccount
