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
        
    protected static $addon = 'shop';

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
	 * @param string $string - a string with product info
	 * @return string
	 * @access public
	 * @see ./class.shop_mail.inc.php
	 */
	public static function productsOut($string) {

		global $I18N_21;

	    $products   = array();
		$prods      = explode('~', $string);
		$i          = 0;
		$max_name   = 25;
		$max_attr   = 0;
		$sum        = 0;
		$currency   = $CJO['ADDON']['settings'][self::$addon]['CURRENCY']['CURR_SIGN'];
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
			$return[$key] .= $temp.$product->getName()."  ";
			$return[$key] .= strtoupper(cjoAddon::translate(21,"shop_final_amount_price")).': ';
			$return[$key] .= $product->getFullPrice()."\r\n";

			if (!empty($product->product_id)) {
			    $return[$key] .= $spaces.cjoAddon::translate(21,"shop_product_id").': '.$product->getProductId()."\r\n";
			}

            $return[$key] .= "\r\n".$spaces;
            $return[$key] .= cjoAddon::translate(21,"shop_final_price").': '.$product->getFormattedProductValue('final_price');

		    if (!empty($product->attribute)) {
                $return[$key] .= "\r\n".$spaces;
                $return[$key] .= str_replace("\n", "\n".$spaces, $product->getAttribute());
		    }

			$return[$key] .= "\r\n".$spaces;
			$return[$key] .= cjoAddon::translate(21,"shop_incl_taxes").': ';
			$return[$key] .= $product->getFormattedProductValue('taxes', '%');
			$return[$key] .= ' ('. $product->getFormattedProductValue('total_taxes').')';

			if ($product->price->getValue('discount') != 0) {
    			$return[$key] .= "\r\n".$spaces;
    			$return[$key] .= cjoAddon::translate(21,"shop_incl_discount").': ';
    			$return[$key] .= $product->getFormattedProductValue('discount', '%');
    			$return[$key] .= ' ('. $product->getFormattedProductValue('total_discount').')';
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
	 * @return string $backend - is backend view
	 * @access public
	 * @see ../pages/orders.inc.php
	 */
	public static function toTable($id, $backend=true) {

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


		// get product data and calculate the value of the order
		foreach($results as $result) {

			$product = new cjoShopProduct($result);
			$order_value +=   cjoShopPrice::convToFloat($product->getProductValue('final_price'))
							* $product->getAmount();
                            
			$products[] = $product;
		}

		$order_value = round($order_value, 2);
		$order_value = str_replace('.',$products[0]->getSeparator(), $order_value);
		  
		$table = array(
		              array('<th class="center" style="width:40px;text-align:center">'.cjoAddon::translate(21,"shop_amount").'</th>'."\r\n"),
				      array('<th style="width:30px">'.cjoAddon::translate(21,"shop_product_id_short").'</th>'."\r\n"),
				      array('<th>'.cjoAddon::translate(21,"shop_name").'</th>'."\r\n"),
                      array('<th class="right">'.cjoAddon::translate(21,"shop_netto_price").'</th>'."\r\n"),
                      array('<th class="right">'.cjoAddon::translate(21,"shop_discount").'</th>'."\r\n"),
                      array('<th class="right">'.cjoAddon::translate(21,"shop_tax").'</th>'."\r\n"),
                      array('<th class="right">'.cjoAddon::translate(21,"shop_final_amount_price").' ('.cjoAddon::translate(21,"shop_brutto").')</th>'."\r\n"));

        $i = 1;
		foreach($products as $product) {

			$table[0][$i] = '<td class="center" style="width:40px;text-align:center">'.$product->amount.'</td>'."\r\n";

            if ($product->product_id)
                $table[1][$i] = '<td style="width:30px">'.$product->product_id.'</td>'."\r\n";
			
			$table[2][$i] = '<td><strong>'.$product->product_name.'</strong><br/>'."\r\n".$product->attribute.'</td>'."\r\n";
            
			$table[3][$i] = '<td class="right">'.$product->getFormattedProductValue('netto_price').'</td>'."\r\n";
            
            if ($product->getProductValue('discount'))
                $table[4][$i] = '<td class="right">'.cjoShopPrice::formatNumber($product->getProductValue('discount'), '%')."\r\n(".$product->getFormattedProductValue('total_discount').')</td>'."\r\n";
			
			$table[5][$i] = '<td class="right">'.cjoShopPrice::formatNumber($product->getProductValue('taxes'), '%')."\r\n(".$product->getFormattedProductValue('total_taxes').')</td>'."\r\n";
			$table[6][$i] = '<td class="right"><b>'.cjoShopPrice::toCurrency($product->getProductValue('final_price') * $product->getAmount()).'</b></td>'."\r\n";
			$i++;
		}

        $html = $backend
              ? '<div class="cjo_with_border shop_product_table" style="overflow-x:auto; max-width: 700px"><table style="margin:10px"><thead>'."\r\n"
              : '<table style="width: 100%"><thead>'."\r\n";

        $rowspan = -1;
        
        for ($i=0;$i<count($table[2]);$i++) {
           $html .= '<tr>'."\r\n";
           for ($ii=0;$ii<count($table);$ii++) {
               if (count($table[$ii]) == 1) continue;
               if ($i == 0) $rowspan++;
               $html .= $table[$ii][$i];
           }
           $html .= $i == 0 ? "</tr>\r\n</thead>\r\n<tbody>\r\n" : '</tr>'."\r\n";
        }

		$html .= '<tr>
				   		<td colspan="'.$rowspan.'" class="right shop_order_value"><b>'.cjoAddon::translate(21,"shop_order_value").' ('.cjoAddon::translate(21,"shop_brutto").')</b></td>
				   		<td class="right shop_order_value" style="width:60px"><b>'.cjoShopPrice::toCurrency($order_value).'</b></td></tr>';        
	   if ($pay_costs > 0) {           
        $html .= '<tr>
				   		<td colspan="'.$rowspan.'" class="right shop_pay_costs"><b>'.cjoAddon::translate(21,"shop_pay_costs").' ('.cjoAddon::translate(21,"shop_brutto").')</b></td>
				   		<td class="right shop_pay_costs" style="width:60px"><b>'.cjoShopPrice::toCurrency($pay_costs).'</b></td></tr>';
        }                  
        $html .= '<tr>
                        <td colspan="'.$rowspan.'" class="right shop_delivery_costs"><b>'.cjoAddon::translate(21,'shop_delivery_costs').' ('.cjoAddon::translate(21,'shop_brutto').')</b></td>
				   		<td class="right shop_delivery_costs" style="width:60px"><b>'.cjoShopPrice::toCurrency($delivery_costs).'</b></td>
				   </tr><tr>
				   		<td colspan="'.$rowspan.'" class="right shop_total_price"><b>'.cjoAddon::translate(21,'shop_total_price').' ('.cjoAddon::translate(21,'shop_brutto').')</b></td>
				   		<td class="right shop_total_price" style="width:60px"><b>'.cjoShopPrice::toCurrency($total_price).'</b></td>
				   </tr>
                   </tbody>
				   </table>
				   </div>';
                   
		return $html;

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
		 	$return .= cjoAddon::translate(21,'shop_tax'). '('.cjoShopPrice::formatNumber($key, '%').' = '.cjoShopPrice::toCurrency($value).'\par';
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
