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
 * <strong><u>Class cjoShopPayment</u></strong>
 *
 * This abstract class must be a parent class of
 * all classes belonging to any pay method. It declares
 * several abstract methods that have to be implemented
 * in the extending classes and all other methods that
 * are definitely called by any script.
 */

abstract class cjoShopPayment {
        
    protected static $mypage = 'shop';

	/**
	 * This method is called if pay_data is
	 * going to be saved to db or hidden POST-variables.
	 * The class constructor must be able to create an
	 * object with all required data from this type of
	 * string
	 *
	 * @see class cjoShopBankAccount
	 * @return string
	 * @access public
	 */
	abstract public function toString();

	/**
	 * This method is called when displaying pay data in
	 * back- and frontend. The data string will be displayed
	 * in html-tags with preformated white-space attribute.
	 *
	 * @see class cjoShopBankAccount
	 * @return string
	 * @access public
	 */
	abstract public function out();

	/**
	 * Returns costs of a pay method if set.
	 *
	 * @return float
	 * @access public
	 */
	public function getCosts() { return 0; }

	/**
	 * If a pay method requires external
	 * validation, this method has to return
	 * the message to be displayed in the
	 * checkout proccess. Custumor will be
	 * unable to continue checkout until
	 * this method returns an empty
	 * value.
	 *
	 * @return string
	 * @access public
	 */
	public function getValidationMsg() { return ''; }
}

/**
 * <strong><u>Class cjoShopEmptyPayMethod</u></strong>
 * An object of this class will be
 * initialized if if the class file of the
 * required class can't be included for any
 * reasons. Prevents errors.
 */
class cjoShopEmptyPayMethod extends cjoShopPayment {

	function __construct(){}

	public function toString()		{ return ''; }
	public function out()			{ return ''; }
}


/**
 * <strong><u>Class CjoShopPayMethod</u></strong>
 * Provides static methods that build and return
 * an object belonging to a overgiven pay method,
 * a method to get the costs of every available
 * pay method and a method that returns the
 * translated name of a pay method.
 */

class cjoShopPayMethod {
    
    protected static $mypage = 'shop';

	/**
	 * Returns the object belonging to
	 * the overgiven pay method if the class
	 * exists, else an object of class cjoShopEmptyPayMethod
	 * will be created.
	 *
	 * @param string $pay_method  		  - the pay method,
	 * 							     		MUST match the following
	 * 								 		pattern : part1_part2_ ...
	 * 								 		and the class name must match
	 * 								 		cjoShopPart1Part2... with the
	 * 								 		first letter of each part is
	 * 								 		upper case
	 *
	 * @param mixed $data, default=empty  - the parameter of the class constructor,
	 * 				      			 		may be array or string
	 * @return object					  - the created object
	 * @access public
	 */
	public static function getPayObject($pay_method, $data = '') {

	    global $CJO;

		// get required files
		$pay_methods_path = $CJO['ADDON']['settings'][self::$mypage]['PAY_METHODS_PATH'];
		$class_file = $pay_methods_path.'/'.$pay_method.'/class.shop_'.$pay_method.'.inc.php';
		$conf_file  = $pay_methods_path.'/'.$pay_method.'/config.inc.php';

		// include required files
		if (is_readable($class_file)) include_once $class_file;
		if (is_readable($conf_file))  include_once $conf_file;

		$class = explode('_', $pay_method);
		$construct = 'cjoShop';

		foreach($class as $value) {
			$construct .= ucfirst($value);
		}
		return (class_exists($construct)) ? new $construct($data) : new cjoShopEmptyPayMethod();
	}

	/**
	 * Returns an array with the costs of every
	 * pay method that has costs.
	 *
	 * @return array $costs
	 * @access public
	 */
	public static function getAllCosts() {

		global $CJO;

		$costs = array();

		foreach(cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['PAY_COSTS']) as $value) {
			$value = explode('=', $value);
			$costs[$value[0]] = $value[1];
		}
		return $costs;
	}

	/**
	 * Returns the translated name of a pay method.
	 *
	 * @param string $pay_method
	 * @return string
	 * @access public
	 */
	public function getName($pay_method) {

		global $I18N_21;
		return $I18N_21->msg('shop_'.$pay_method);
	}

} // end class cjoShopPayMethod
