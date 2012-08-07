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
 * <strong><u>Class shop_price</u></strong>
 * This class provides methods to convert, calculate,manipulate
 * and format prices, taxes, count
 *
 * @var char separator 			- the currency separator (, or .)
 * @var float exchange_ratio 	- the exchange_ratio	 of a given Currency
 * @var int $count  			- the amount of orderable products in stock
 * @var string $currency_name 	- name of the currency the price is shown as
 * @var array $vars 			- array for price, taxes, total price etc
 */

class cjoShopPrice {
        
    protected static $mypage = 'shop';

	private $vars = array();
	private $attributes = array();
	static $separator;
	static $exchange_ratio;
	static $currency_name;

	/**
	 * Class constructor.
	 *
	 * @param string $price
	 * @param string $attributes - the products attribute values as a '|'-separated string
	 * @param string $taxes, default = 0
	 * @param string $discount, default = 0
	 */
	function __construct($price, $attributes = '', $taxes = 0, $discount = 0) {

		global $CJO;

		$taxes = empty($taxes) ? 0 : $taxes;
		$discount = empty($discount) ? 0 : $discount;

		//  init class vars from 'config.inc'
		$cjo_shop 						= $CJO['ADDON']['settings'][self::$mypage]['CURRENCY'];
		$this->exchange_ratio 			= $cjo_shop['CURR_RATIO'];
		$this->currency_name 			= $cjo_shop['CURR_SIGN'];
		$this->separator 				= $cjo_shop['CURR_SEPARATOR'];

		// add attribute offsets to price
		$price = self::convToFloat($price);
		$offset = is_array($attributes) ? $attributes[0] : cjoShopProductAttributes::getAttributeOffsets($attributes);

		$this->vars['basic_price'] = self::convToFloat($price, 2);
		$price += $offset;
		$this->vars['offset'] = $offset;

		// format price for calculating
		$this->vars['netto_price'] 		= $price * $this->exchange_ratio;

		$this->vars['taxes'] 			= self::convToFloat($taxes);
		$this->vars['discount'] 		= self::convToFloat($discount);

		$this->vars['total_discount'] 	= round($this->exchange_ratio * $this->vars['discount']
											* $this->vars['netto_price'] / 100, 2) * -1;

		$this->vars['total_taxes'] 		= round($this->exchange_ratio * $this->vars['taxes']
										    * ($this->vars['netto_price'] + $this->vars['total_discount']) / 100, 2);

		$this->vars['final_price'] 		= round($this->vars['netto_price'] + $this->vars['total_taxes'] + $this->vars['total_discount'], 2);

		$this->vars['netto_price'] 	    = self::convToFloat(round($this->vars['netto_price'], 2), 2);
		$this->vars['total_discount'] 	= self::convToFloat($this->vars['total_discount'], 2);
		$this->vars['total_taxes'] 		= self::convToFloat($this->vars['total_taxes'], 2);
		$this->vars['final_price'] 		= self::convToFloat($this->vars['final_price'], 2);

	} // end __construct

	// set methods
	public function setBasicPrice($num)	    { $this->vars['basic_price'] = $num;	}
	// get methods
	public function getSeparator()			{   return $this->separator;   			}
	public function getExchangeRatio()		{	return $this->exchange_ratio;		}
	public function getCurrency()			{   return $this->currency_name;	  	}

	/**
	 * This method returns any value from the objects
	 * vars array as a floating point number.
	 *
	 * @param string $type
	 * @return float
	 */
	public function getValue($type){

		if (array_key_exists($type, $this->vars)) {
			return $this->vars[$type];
		}
		else{
			return 'undefined key: '.$type;
		}
	}

	/**
	 * This methods returns the formatted output value
	 * of the overgiven type.
	 *
	 * @param string/numeric $type - the name of the price that shall be displayed
	 * @param bool $format - shall it be formatted with a currency sign
	 * @param string prefix - the prefix of the value to be displayed (at least a '-' or a '+')
	 * @return string      - the formatted output value
	 */
	public function getFormattedValue($type, $format = true, $prefix = ''){

		if (array_key_exists($type, $this->vars)) {
			$return = number_format($this->vars[$type], 2,$this->separator, $prefix);
		}
		else {
			return 'undefined key: '.$type;
		}
		if ($format === true) {
			return $return.' '.$this->currency_name;
		}
		else {
			return $return.' '.$format;
		}
	}

	/**
	 * This methods returns a formatted price, tax-rate, etc. without
	 * an object context.
	 *
	 * @param float/int $num - the number to be formatted
	 * @param $suffix - a suffix to be displayed (like €, $ or %)
	 * @param $prefix - the prefix (at least '-' or '+')
	 * @return string - the formatted number
	 */
	public static function formatNumber($num, $suffix = false, $prefix = ''){

		global $CJO;

		$cjo_shop = $CJO['ADDON']['settings'][self::$mypage]['CURRENCY'];
		$separator = $cjo_shop['CURR_SEPARATOR'];

		if($suffix === false){
			$suffix =  ' '.$cjo_shop['CURR_SIGN'] ;
		}
		elseif($suffix == 1){
			unset($suffix);
		}
		else{
			$suffix = ' '.$suffix;
		}

		return $prefix.str_replace('.', $separator, $num).$suffix;
	}

	/**
	 * This method returns the total value of discount.
	 *
	 * @param string $price
	 * @param bool $currency, default = false 		 - if true the method returns value with a currency sign
	 * @param bool $positive_prefix, default = false - if true the value is set to negative
	 * @return string
	 * @access public
	 */
	public static function toCurrency($price, $currency = false, $positive_prefix = false, $precision = 2) {

		global $CJO;

		$cjo_shop = $CJO['ADDON']['settings'][self::$mypage]['CURRENCY'];
		$separator = $cjo_shop['CURR_SEPARATOR'];
		$exchange_ratio = $cjo_shop['CURR_RATIO'];
        $prefix = '';

		$price = self::convToFloat($price);
		$price *= $exchange_ratio;
		$price = number_format(round($price, $precision), $precision, '.', '');

		if ($positive_prefix && $price >= 0) {
		    $prefix = '+';
		}

		//$currency = $currency ? $currency : '';
		return self::formatNumber($price, $currency, $prefix);
	}

	/**
 	* This method converts the input string
 	* into a decimal number with 2 decimals.
 	*
 	* @param string $price
 	* @param int $precision - the number of decimals that the floated value shall have
 	* @return float $float
 	* @access public
 	*/
	public static function convToFloat($value, $precision = NULL) {

		//if (is_float($value)) return $value;
        // is nagetive value
		$is_negative = preg_match('/^\-/', trim($value), $matches);

		// remove all not numerical characters
		$float = preg_replace('/[^0-9.,]/','', $value);

		// replace all commas by points
		$float = str_replace(',','.', $float);
		// divide number into parts
		$parts = explode('.', $float);

		// get decimals and number before separator
		if (count($parts) == 1) {
			$pre = $parts[0];
			$post = '';
		}
		else {
			$post = array_pop($parts);
			$pre = implode('', $parts);
		}

		// fill decimals if the not have the correct amount
		if ($precision != NULL) {
			$len = strlen($post);
			for($i = $len; $i < $precision; $i++) {
				$post .= '0';
			}
		}

		$float = $is_negative ? '-'.$pre : $pre;
		$float .= !empty($post) ? '.'.$post : '';

		return $float;
	}

	/**
	 * This method prepares a price for output.
	 * Required if decimals shall be displayed
	 * differently.
	 *
	 * @param bool $format, default = false
	 * @return mixed
	 * @access public
	 * @see class method formattedValueOut
	 */
	public function formatValue($price, $format = false) {

		if (!$format) return $price;
		$price = str_replace(',','.', $price);
		return explode('.', $price);
	}

	/**
	 * This method outputs price, total price, taxes
	 * and discount in a HTML string.
	 *
	 * @param string $type					- type of price to format (e.g. taxes, discount)
	 * @param string $name					- translated name of the value
	 * @param bool $is_tax, default = false - displays extra information for taxes and discount if true
	 * @param bool $format, default = false - tells to function to format the the decimals of a price differently if true
	 *
	 * @return string $return
	 * @access public
	 */
	public function formattedValueOut($type, $name, $is_tax = false, $format = 0) {

		// get value to format
		$value = $this->vars[$type];
		$key = $type;
		if (empty($value)) return false;
        
        $format = (int) $format;

		// display the name e.g. Price, Taxes
		$return  = "<p class=\"shop_".$type."\">";

		// label is needed for discount and taxes
		if ($is_tax) {
		    $return .= " [translate_21: shop_incl] ";
		    $return .= " <span class=\"shop_".$type."percent\">".$this->formatNumber($value, '%')." </span> ".$name;
		}
		else {
		    $return .= "<span class=\"shop_label\">".$name."</span>";
		}

		// change key to get total values
		if ($type == 'taxes' || $type == 'discount') {
			$key = "total_".$type;
			$value = $this->vars[$key];
			// add taxes to discount
			if ($type == 'discount') {
			    $value = $value + ($value * $this->vars['taxes'] / 100);
			}
		}

		// unformatted displaying of price
		switch ($format) {
            case 0: $return .= " <span class=\"shop_".$key."_value\">";
        			if ($is_tax) $return .= " = ";
        			$return .= $this->getFormattedValue($key);
        			$return .= "</span>";
                    break;
                    
            case 1: $format_value = $this->formatValue($this->getValue($type),$format);
        			$return .= " <span class=\"shop_".$key."_pre_value\">".$format_value[0]."</span>";
        			$return .= " <span class=\"shop_separator\">".$this->separator."</span>";
        			$return .= " <span class=\"shop_".$key."_post_value\">".$format_value[1]."</span>";
        			$return .= " <span class=\"shop_".$key."_currency\">".$this->getCurrency()."</span>";
                    break;
		}
		$return .= "</p>";

		return $return;
	}


	/**
	 * Returns a HTML string with a selectbox
	 * for changing the displayed currency.
	 *
	 * @return string
	 * @access public
	 */
	public static function selectCurrency() {

		global $CJO;

		$settings = $CJO['ADDON']['settings'][self::$mypage];
		// get currency name
		preg_match_all('/(?<=^|\|)([^\|]*)=([^\|]*)(?=\||$)/',
                       $settings['CURRENCY_NAMES'],
                       $currencies,
                       PREG_SET_ORDER);

        // build array with currency names
        $currency_names = array();
        foreach($currencies as $currency_name) {
        	$currency_names[$currency_name[1]] = $currency_name[2];
        }

        $select = new cjoSelect();
        $select->setName("currency_select");
        $select->setSize(1);
        $select->setSelectExtra('onchange="this.form.submit();"');
        // add options
        foreach($currency_names as $key => $name) {

        	$select->addOption($name, $key);

        	// check if all values are available for this currency
        	preg_match('/(?<=\|'.$key.'\=|^'.$key.'\=).*?(?=\||$)/',
			   		 	$settings['CURRENCY_SIGNS'], $sign);

	   		preg_match('/(?<=\|'.$key.'\=|^'.$key.'\=).*?(?=\||$)/',
	   					$settings['EXCHANGE_RATIO'], $ratio);

	   		preg_match('/(?<=\|'.$key.'\=|^'.$key.'\=).*?(?=\||$)/',
	   					$settings['PRICE_SEPARATORS'], $separator);

	   		// if not disable option
			if (empty($sign[0]) || empty($ratio[0]) || empty($separator[0])) {
				$select->disableOption($key);
			}
        }

        $select->setSelected($settings['CURRENCY']['CURR_CODE']);

        $html_tpl_content = @file_get_contents($CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['CURRENCY_SELECT']);
	    $html_tpl = new cjoHtmlTemplate($html_tpl_content);

        // fill template with values
        $html_tpl->fillTemplate('TEMPLATE', array('CURRENCY_SELECT'	=>  $select->get()));

        // build html code
        return $html_tpl->get(false);

	} // end function selectCurrency


}// end class cjoShopPrice
