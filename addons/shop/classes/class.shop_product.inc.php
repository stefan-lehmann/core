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
 * <strong>Class cjoShopProduct</strong>
 * This class handles all data of a product.
 *
 * @var int $slice_id 		- the contejo article slice id of the
 * 						  	  modul article
 * @var int $amount   		- the ordered amount
 * @var string $attribute 	- the attribute chosen by customer
 * @var string $product_name
 * @var string $price
 */

class cjoShopProduct {
        
    protected static $mypage = 'shop';

	private $product_id;
	private $slice_id;
	private $amount;
	private $attribute;
	private $product_name;
	private $price;
	private $offset;


	/**
	 * Constructor, parameter must be a resultset or a string.
	 * of the type of return value of class method toString().
	 *
	 * @param mixed $value - may be array or string
	 */
	function __construct($value = null) {

		if (is_array($value)) {    // read from tbl_21_basket
			$this->product_id 	= $value['product_id'];
			$this->slice_id 	= $value['slice_id'];
			$this->product_name = $value['product_name'];
			$this->attribute 	= cjoShopProductAttributes::getAttributesAndValues($value['attribute'], false);
			$this->amount 		= $value['amount'];
			$this->offset		= cjoShopProductAttributes::getAttributeOffsets($value['attribute']);
			$this->price 		= new cjoShopPrice($value['price'], array($this->offset), $value['taxes'], $value['discount']);
		}
		elseif ($value != null) {// read from tbl_21_orders
			$arr = explode('|', $value);
			$this->product_id	= $arr[0];
			$this->slice_id 	= $arr[1];
			$this->product_name = $arr[2];
			$this->attribute 	= $arr[3];
			$this->amount 		= $arr[4];
			$this->offset		= $arr[8];
			$this->price 		= new cjoShopPrice($arr[5], array($arr[8]), $arr[6], $arr[7]);
		}
	}

	// get methods
	public function getProductId()			{	return $this->product_id;					}
	public function getSliceId()			{	return $this->slice_id;						}
	public function getCurrency()			{	return $this->price->getCurrency();			}
	public function getSeparator()			{	return $this->price->getSeparator();		}
	public function getAmount()				{	return $this->amount;						}
	public function getName()				{	return $this->product_name;					}
	public function getAttribute()			{	return $this->attribute;					}
	public function getOffset()				{	return $this->offset;						}

	/**
	 * Calls method getValue of class
	 * cjoShopPrice.
	 *
	 * @param string $type
	 * @return float
	 * @see class cjoShopPrice->getValue
	 */
	public function getProductValue($type){
		return $this->price->getValue($type);
	}

	/**
	 * Calls method getFormattedValue of class
	 * cjoShopPrice.
	 *
	 * @param string $type
	 * @return float
	 * @see class cjoShopPrice->getValue
	 */
	public function getFormattedProductValue($type, $format = true, $prefix = ''){
		return $this->price->getFormattedValue($type, $format, $prefix);
	}

	/**
	 * Returns the formatted value of amount * price
	 * of ordered products of a single type.
	 *
	 * @return string
	 * @access public
	 */
	public function getFullPrice() {
		return  cjoShopPrice::toCurrency($this->getProductValue('final_price') * $this->amount);
	}

	/**
	 * This method converts the object
	 * into a string. The delimiter is '|'.
	 *
	 * @return string
	 * @access public
	 */
	public function toString() {
		$string	 =		$this->product_id;
		$string .=	'|'.$this->slice_id;
		$string .= 	'|'.$this->product_name;
		$string .=  '|'.$this->attribute;
		$string .=  '|'.$this->amount;
		$string .=  '|'.$this->getProductValue('basic_price');
		$string .=  '|'.$this->getProductValue('taxes');
		$string .=  '|'.$this->getProductValue('discount');
		$string .=  '|'.$this->offset;
		return $string;
	}

	/**
	 * Returns a string with products data
	 * and the order value for order confirm mail.
	 *
	 * @param string $string 								- a string with product info
	 * @param array $products_available (default = array())	- holds information about
	 * 														  availabilty of the ordered
	 * 														  products
	 * @return string
	 * @access public
	 * @see ./class.shop_mail.inc.php
	 */
	public static function productsOut($string, $products_available = array()) {

		global $I18N_21;

	    $products   = array();
		$prods      = explode('~', $string);
		$i          = 0;
		$max_name   = 25;
		$max_attr   = 0;
		$sum        = 0;
		$currency   = $CJO['ADDON']['settings'][self::$mypage]['CURRENCY']['CURR_SIGN'];
		$divider    = "\r\n\r\n".' -- '."\r\n\r\n";

		// get max product name length and
		// max attribut length for formatting
		foreach($prods as $prod){

			$products[] = new cjoShopProduct($prod);

			// get max product name length
			if (strlen($products[$i]->getName()) > $max_name) {
				$max_name = strlen($products[$i]->getName());
			}
			$i++;
		}

		$return = array();

		foreach($products as $key => $product) {

		    // build output string
			$temp = $product->getAmount()." x ";
			$spaces = self::addWhiteSpaces(strlen($temp));
			$return[$key] .= $temp.$product->getName()."\t\t\t";
			$return[$key] .= $spaces;
			$return[$key] .= strtoupper($I18N_21->msg("shop_final_amount_price")).': ';
			$return[$key] .= $product->getFullPrice()."\r\n";

			if (!empty($product->product_id)) {
			    $return[$key] .= $spaces.$I18N_21->msg("shop_product_id").': '.$product->getProductId()."\r\n";
			}

            $return[$key] .= "\r\n".$spaces;
            $return[$key] .= $I18N_21->msg("shop_final_price").': '.$product->getFormattedProductValue('final_price');

		    if (!empty($product->attribute)) {
                $return[$key] .= "\r\n".$spaces;
                $return[$key] .= str_replace("\n", "\n".$spaces, $product->getAttribute());
		    }

			$return[$key] .= "\r\n".$spaces;
			$return[$key] .= $I18N_21->msg("shop_incl_taxes").': ';
			$return[$key] .= $product->getFormattedProductValue('taxes', '%');
			$return[$key] .= ' ('. $product->getFormattedProductValue('total_taxes').')';

			if ($product->price->getValue('discount') != 0) {
    			$return[$key] .= "\r\n".$spaces;
    			$return[$key] .= $I18N_21->msg("shop_incl_discount").': ';
    			$return[$key] .= $product->getFormattedProductValue('discount', '%');
    			$return[$key] .= ' ('. $product->getFormattedProductValue('total_discount').')';
			}

			// add note if there are not as much products available
			// as requested
			if (!empty($products_available) &&
			    array_key_exists($product->getSliceId(), $products_available)) {
					$product_available = $products_available[$product->getSliceId()];

					// if product is out of stock
				if ($product_available['amount'] == 0) {
					$return[$key] .= "\r\n".$spaces;
					$return[$key] .= $I18N_21->msg('shop_product_not_deliverable');
				}
				elseif($product_available['amount'] == 1) {
					$return[$key] .= "\r\n".$spaces;
					$return[$key] .= $I18N_21->msg('shop_only_1_product_available');
				}
				else {
					$return[$key] .= "\r\n".$spaces;
					$return[$key] .= $I18N_21->msg('shop_available_product_amount_1');
					$return[$key] .= ' '.$product_available['amount'].' ';
					$return[$key] .= $I18N_21->msg('shop_available_product_amount_2');
				}
			}
			// calculate order value
			$sum += cjoShopPrice::convToFloat($product->getFullprice());
		}

		return implode($divider,$return);

	} // end function productsOut

	/**
	 * Outputs the ordered products in backend.
	 *
	 * @param int $id 		 - the order id
	 * @return string $table - html-table with product info
	 * @access public
	 * @see ../pages/orders.inc.php
	 */
	public static function toTable($id) {

		global $I18N_21;

		$products = array();
		$sql = new cjoSql();
		$qry = "SELECT
					products,
					pay_costs,
					delivery_cost,
					total_price
				FROM "
					.TBL_21_ORDERS."
				WHERE
					id = ".$id." LIMIT 1";

		$sql->setQuery($qry);
		$result 		= $sql->getValue('products', 0);
		$delivery_costs = $sql->getValue('delivery_cost', 0);
		$pay_costs 		= $sql->getValue('pay_costs', 0);
		$total_price 	= $sql->getValue('total_price', 0);

		$results = explode('~', $result);
		$order_value = 0;
		$i = 0;


		// get product data and calculate the value of the order
		foreach($results as $product) {

			$products[] = new cjoShopProduct($product);
			$order_value +=   cjoShopPrice::convToFloat($products[$i]->getProductValue('final_price'))
							* $products[$i]->getAmount();
			$i++;
		}

		$order_value = round($order_value, 2);
		$order_value = str_replace('.',$products[0]->getSeparator(), $order_value);

		// create table
		$table = "<div class=\"cjo_with_border shop_product_table\">
				  <table>
				  <thead>
				  <tr>
				  <th class=\"center\" style=\"width:30px\">".$I18N_21->msg("shop_amount")."</th>
				  <th style=\"width:30px\">".$I18N_21->msg("shop_product_id_short")."</th>
				  <th>".$I18N_21->msg("shop_name")." / ".$I18N_21->msg("shop_attribute")."</th>";
                  
		$table .= "<th class=\"right\">".$I18N_21->msg("shop_netto_price")."</th>";
        $table .= "<th class=\"right\">".$I18N_21->msg("shop_discount")."</th>";
        $table .= "<th class=\"right\">".$I18N_21->msg("shop_tax")."</th>";
        $table .= "<th class=\"right\">".$I18N_21->msg("shop_final_amount_price")." (".$I18N_21->msg("shop_brutto").")</th>
				  </tr>
				  </thead>
				  <tbody>";

		foreach($products as $product) {

			$total_sum = $product->getProductValue('final_price') * $product->getAmount();
			$total_sum = cjoShopPrice::toCurrency($total_sum);
			$table .= "<tr>
					   <td class=\"center\" style=\"width:30px\">".$product->amount."</td>
					   <td style=\"width:30px\">".$product->product_id."</td>
					   <td style=\"white-space:pre\"><strong>".$product->product_name."</strong>\r\n".$product->attribute."</td>
					   <td class=\"right\">".$product->getFormattedProductValue('netto_price')."</td>
					   <td class=\"right\" style=\"white-space:pre\">".cjoShopPrice::formatNumber($product->getProductValue('discount'), '%').
					   "\r\n(".$product->getFormattedProductValue('total_discount').")</td>
					   <td class=\"right\" style=\"white-space:pre\">".cjoShopPrice::formatNumber($product->getProductValue('taxes'), '%').
					   "\r\n(".$product->getFormattedProductValue('total_taxes').")</td>
					   <td class=\"right\"><b>".$total_sum."</b></td>
					   </tr>";
		}
		$table .= "<tr>
				   		<td colspan=\"6\" class=\"right\"><b>".$I18N_21->msg("shop_order_value")." (".$I18N_21->msg("shop_brutto").")</b></td>
				   		<td class=\"right\" style=\"width:60px\"><b>".cjoShopPrice::toCurrency($order_value)."</b></td>
				   </tr>";        
	   if ($pay_costs > 0) {           
        $table .= "<tr>
				   		<td colspan=\"6\" class=\"right\"><b>".$I18N_21->msg("shop_pay_costs")." (".$I18N_21->msg("shop_brutto").")</b></td>
				   		<td class=\"right\" style=\"width:60px\"><b>".cjoShopPrice::toCurrency($pay_costs)."</b></td>
				   </tr>";
        }                  
        $table .= "<tr>
                        <td colspan=\"6\" class=\"right\"><b>".$I18N_21->msg("shop_delivery_costs")." (".$I18N_21->msg("shop_brutto").")</b></td>
				   		<td class=\"right\" style=\"width:60px\"><b>".cjoShopPrice::toCurrency($delivery_costs)."</b></td>
				   </tr><tr>
				   		<td colspan=\"6\" class=\"right\"><b>".$I18N_21->msg("shop_total_price")." (".$I18N_21->msg("shop_brutto").")</b></td>
				   		<td class=\"right\" style=\"width:60px\"><b>".cjoShopPrice::toCurrency($total_price)."</b></td>
				   </tr>
                   </tbody>
				   </table>
				   </div>";
cjo_Debug($table,'____'.$delivery_costs.'________'.cjoShopPrice::toCurrency($delivery_costs));
		return $table;

	} // end function toTable

	/**
	 * Returns a string of whitespaces.
	 *
	 * @param $n		- length of return string
	 * @return string $spaces
	 * @access private
	 * @see
	 */
	private static function addWhiteSpaces($n) {

		$spaces = '';
		for($i = 0; $i < $n; $i++)
			$spaces .= ' ';
		return $spaces;
	}

	/**
	 * This method returns a list of
	 * summed taxes foreach tax rate.
	 * Required for checkout(product_list).
	 *
	 * @param string $string - string with product info
	 * @return string $return
	 * @access public
	 */
	public static function getTaxList($string) {

		global $I18N_21;
		// get all products
		$products = explode('~', $string);
		$taxes = array();

		foreach($products as $product_string) {
			$product = new cjoShopProduct($product_string);
			$taxes[$product->getProductValue('taxes')] += $product->getProductValue('total_taxes');
		}

		// build list for rtf
		$return = '';
		foreach($taxes as $key => $value) {
		 	$return .= $I18N_21->msg('shop_tax'). '('.cjoShopPrice::formatNumber($key, '%').' = '.cjoShopPrice::toCurrency($value).'\par';
		}
		return $return;
	}

	 public static function formatActionShopModul(&$CJO_ACTION, $type='PRE') {

        global $CJO;

	    if ($type != 'PRE') return false;

        $shop_attributes = cjo_post('shop_attributes', 'array');
        $attributes = array();
        $temp = array();

        foreach($shop_attributes as $id=>$attribute) {
            $temp[$id] = $attribute['prior'].$attribute['name'].$id;
        }

        natsort($temp);

        foreach($temp as $id=>$attribute) {

            $attribute = $shop_attributes[$id];

            if (empty($attribute['values']) ||
                !is_array($attribute['values'])) continue;

            $attributes[$id] = implode('|', $attribute['values']);
        }

        if ($CJO_ACTION['VALUE'][13] == 0) {
           $CJO_ACTION['VALUE'][14] = time();
           $CJO_ACTION['VALUE'][14] = 0;
           $CJO_ACTION['VALUE'][15] = 0;
        }

        $CJO_ACTION['VALUE'][6] = implode('|', $attributes);
        $CJO_ACTION['VALUE'][2] = cjoShopPrice::convToFloat($CJO_ACTION['VALUE'][2]);
        $CJO_ACTION['VALUE'][3] = cjoShopPrice::convToFloat($CJO_ACTION['VALUE'][3]);
        $CJO_ACTION['VALUE'][3] = preg_replace('/\.00$|0$/','',$CJO_ACTION['VALUE'][3]);
        $CJO_ACTION['VALUE'][4] = cjoShopPrice::convToFloat($CJO_ACTION['VALUE'][4]);
        $CJO_ACTION['VALUE'][4] = preg_replace('/\.00$|\.0$/','',$CJO_ACTION['VALUE'][4]);
        if($CJO_ACTION['VALUE'][17] == 1) $CJO_ACTION['VALUE'][18] = 1;
    }
} // end class cjoShopProduct
