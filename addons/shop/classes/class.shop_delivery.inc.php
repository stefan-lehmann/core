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
 * <strong><u>Class cjoShopDelivery</u></strong>
 * This class provides methods for calculating
 * the delivery costs of an order.
 *
 * @var float $size 	- size in packing units
 * @var string $country - deliver country
 * @var array $data  	- array for dynamically defined
 * 						  variables
 */

class cjoShopDelivery {
        
    protected static $mypage = 'shop';
	private $size;
	private $country;
	private $method;
	private $data;

	/**
	 * This method finds the cheapest deliverer for a given country.
	 *
	 * @param string $country
	 * @return array $data - array for dynamically defined variables
	 */
	function __construct($country = '') {

		global $CJO;

		$this->method  = $CJO['ADDON']['settings'][self::$mypage]['DELIVERY_METHOD'];
		$this->data    = array('costs' => 0, 'taxes' => 0);
		$this->country = $country;
		$this->size    = 0;

		if ($country != '') {

				// find cheapest package matching required size
				// method writes costs and deliverer_id
				$this->getClassData();

				$sql = new cjoSql();
				// get deliverer name
				$qry = "SELECT deliverer FROM ".TBL_21_DELIVERER." WHERE id=".$this->data['deliverer_id'];
				$sql->setQuery($qry);

				$name = $sql->getValue('deliverer', 0);
				$sql->flush();
				$this->data['deliverer'] = $name;

		}
	} // end construct

	// get methods
	public function getCosts()				{	return $this->data['costs'];		}
	public function getTaxes()				{	return $this->data['taxes'];		}
	public function getDeliverer()			{	return $this->data['deliverer'];	}
	public function getDelivererSize()		{	return $this->data['size'];			}

	public function getTotalCosts()	{
		return $this->data['costs'] * (1 + $this->data['taxes'] / 100);
	}

	public function getTotalTaxes() {
		return $this->data['costs'] * $this->data['taxes'] / 100;
	}

	private function getDelivererTaxes() {

		if (isset($this->data['deliverer_id'])) {

			$sql = new cjoSql();
			$qry = "SELECT tax FROM ".TBL_21_DELIVERER." WHERE id='".$this->data['deliverer_id']."' LIMIT 1";
			$tax = array_shift($sql->getArray($qry));

			return $tax['tax'];
		}
		return 0;
	}

	/**
	 * This method outputs costs for a delivery as currency value.
	 *
	 * @return string $costs
	 */
	public function costsAsCurrency() {
		return cjoShopPrice::toCurrency($this->data['costs']);
	}


	/**
	 * Gets all in tbl_21_deliverer_zones defined deliverer_zones
	 * for a deliverer as a '|'-separated string.
	 *
	 * @param int $id  - the deliverer id in tbl_21_deliverer
	 * @return string  - all defined zones for deliverer
	 */
	public static function getDelivererZones($id) {
		$sql = new cjoSql();
		$qry = "SELECT
					a.zone
				FROM "
					.TBL_21_COUNTRY_ZONE." a
				INNER JOIN "
					.TBL_21_DELIVERER_ZONE." b
				ON
					a.id=b.zone_id
				WHERE
					b.deliverer_id=".$id."
				ORDER BY a.zone ASC";

		$results = $sql->getArray($qry);
		$sql->flush();
		$zones = array();
		foreach ($results as $result) {
		    $zones[] = $result['zone'];
		}
		return implode(' | ',$zones);
	}


	/**
	 * Gets all in tbl_21_deliverer_details defined package sizes
	 * for a deliverer zone as a '|'-separated string.
	 *
	 * @param int $id  - the deliverer_zone_id in tbl_21_deliverer_zone
	 * @return string  - all defined package sizes for a deliverer zone
	 */
	public static function getDelivererSizes($id) {
		$sql = new cjoSql();
		$qry = "SELECT size FROM ".TBL_21_DELIVERER_DETAILS." WHERE deliverer_zone_id=".$id." ORDER BY size_in_units ASC";
		$results = $sql->getArray($qry);
		$sql->flush();
		$sizes = array();
		foreach ($results as $result) {
		    $sizes[] = $result['size'];
		}
		return implode(' | ',$sizes);
	}

	public static function getDeliveryLink($packing_units = false){

	    global $CJO, $I18N_21;

	    if ($packing_units) $packing_units = '<br/><span class="shop_packing_units">'.$packing_units.' [translate_21: shop_packing_units]</span>';

	    return sprintf('<p class="shop_delivery"><a href="%s">%s</a>%s</p>',
	                    cjoRewrite::getUrl($CJO['ADDON']['settings'][self::$mypage]['DELIVERY_ARTICLE_ID']),
	                    $I18N_21->msg('shop_excl_delivery_cost'),
	                    $packing_units);
	}

	public static function getDeliveryDuration($value) {

		global $I18N_21;
		if ($value == '') return false;
	    return sprintf('<p class="shop_delivery">%s</p>',
	                    $I18N_21->msg('delivery_duration_'.$value));
	}

	public static function getDurationSelection($name, $selected, $class='class="inp50"') {

	    global $I18N_21;

	    $select = new cjoSelect();
    	$select->setsize(1);
    	$select->setName($name);
    	$select->setSelected($selected);
    	$select->setStyle($class);
    	$select->addOption($I18N_21->msg('delivery_duration_0'),  '0');
    	$select->addOption($I18N_21->msg('delivery_duration_2'),  '2');
    	$select->addOption($I18N_21->msg('delivery_duration_7'),  '7');
    	$select->addOption($I18N_21->msg('delivery_duration_10'), '10');
    	$select->addOption($I18N_21->msg('delivery_duration_14'), '14');
    	$select->addOption($I18N_21->msg('delivery_duration_21'), '21');
    	$select->addOption($I18N_21->msg('delivery_duration_28'), '28');
    	$select->addOption($I18N_21->msg('delivery_duration_35'), '35');
	    $select->addOption($I18N_21->msg('delivery_duration_42'), '42');
	    $select->addOption($I18N_21->msg('delivery_duration_56'), '56');
	    $select->addOption($I18N_21->msg('delivery_duration_ask'),'ask');
	    $select->addOption($I18N_21->msg('delivery_duration_no'), '-1');
	    return $select->get();
	}

	/**
	 * This method provides data for the class
	 * constructor if a country is defined. It will
	 * write the member variables data['costs'],
	 * data['size'] and data['deliverer_id']. If the
	 * order can be delivered with one package, the
	 * method checks if it is cheaper to send it
	 * with several smaller packages.
	 *
	 * @param string $country - the country to which
	 * 							shall be delivered
	 * @access private
	 * @see class constructor
	 */
	private function getClassData() {

		global $CJO;

		$session_id = session_id();
		$sql = new cjoSql();

		// if delivery costs depend on order value
		if ($this->method == "0") {

			$order_value = cjoShopBasket::getOrderValue($session_id);
			$order_value = str_replace(',','.',$order_value);

			// get delivery costs if they depend on order value
			$qry = "SELECT
						dc.costs AS costs,
						dc.tax AS tax
					FROM "
						.TBL_21_DELIVERY_COSTS." dc
					INNER JOIN "
						.TBL_21_COUNTRY_ZONE." cz
					ON
						dc.zone_id=cz.id
					WHERE
						cz.countries LIKE '".$this->country."'
					AND
						dc.order_value >= '".$order_value."'
					ORDER BY
						dc.costs
					LIMIT 1";

			$sql->setQuery($qry);

			$this->data['costs'] = $sql->getValue('costs', 0);
			$this->data['taxes'] = $sql->getValue('tax', 0);
			$this->data['size'] = '';
			$this->data['deliverer'] = '';
			return;
		}

		// calculate package size if delivery costs depend on package size
		$qry = "SELECT
					a.amount AS amount,
					b.value5 AS units
				FROM "
					.TBL_21_BASKET." a
				INNER JOIN "
					.TBL_ARTICLES_SLICE." b
				ON
					a.slice_id=b.id
				WHERE
					a.session_id = '".$session_id."'";

		$products = $sql->getArray($qry);
		$sql->flush();

		if (empty($products)) return;

		foreach($products as $product) {
			foreach($product as $key => $prod){
				$product[$key] = cjoShopPrice::convToFloat($product[$key]);
			}
			if (!empty($product['units']))
			    $this->size += $product['amount'] * (float) $product['units'];
		}

		// get all package sizes for this zone
		$qry = "SELECT
					b.deliverer_id AS deliverer_id,
					c.size AS size,
					c.size_in_units AS size_in_units,
					c.costs AS costs
				FROM "
					.TBL_21_COUNTRY_ZONE." a
				INNER JOIN ("
					.TBL_21_DELIVERER_ZONE." b
				INNER JOIN "
					.TBL_21_DELIVERER_DETAILS." c
				ON
					c.deliverer_zone_id = b.id)
				ON
					a.id = b.zone_id
				WHERE
					a.countries LIKE '".$this->country."'" ;

		$results = $sql->getArray($qry);

		// return if there is no package defined for this
		// zone
		if (empty($results)) return;

		// starting values for the calculating of
		// the required package amount
		$size = $this->size;
		$deliverer_id = '';
		$max_package_amount = 0;
		$costs = 0;

		// check if the whole order can be delivered in
		// one package
		if (!$this->getPackages($results, $size, '')) {

			$all_in_one = $this->data['package'];
			$this->data['package'] = array();

			// delete all packages from results
			// that are equal or larger than
			// the size of the order
			for ($i = 0; $i < count($results); $i++) {
				if ($results[$i]['size_in_units'] >= $size) {
					unset($results[$i]);
				}
			}
		}

		/*
		 * calculate the required package(s) for
		 * this order. If this order can be
		 * delivered in one package, this will
		 * check if it is cheaper to deliver it
		 * in several packages
		 */

		// starting values for getting the maximum sized package
		$max_package['size_in_units'] 	= 0;

		// get maximum available size with lowest costs
		foreach($results as $result) {
			if ($result['size_in_units'] >= $max_package['size_in_units']) {
				$max_package = $result;
			}
		}

		// get required packages for this order
		// will calculate more than one if required
		while ($this->getPackages($results, $size, $deliverer_id) && !empty($products)) {
			// count amount of maximum sized packages required for this order
			$max_package_amount++;

			// reduce size in units for next loop
			$size -= $this->resizeProductArray($products, $max_package['size_in_units']);

			// add cost of the maximum package all costs
			$taxes = $max_package['costs']*$max_package['tax']/100;
			$costs += $max_package['costs'];

			// define deliverer id if more than 1 package is required
			// so that the order will always be delivered
			// by one deliverer
			$deliverer_id = $max_package['deliverer_id'];
		}

		// write id and costs into data array
		$this->data['deliverer_id'] = $this->data['package']['deliverer_id'];
		$this->data['costs'] = $costs + $this->data['package']['costs'];

		// check if delivery is cheaper when
		// sending the order with one package
		// (see line 268++)
		if (isset($all_in_one)) {
			// send order in one package if it is
			// cheaper than sending it in several ones
			// or partititioning it on several packages
			// can not host all products
			if ($all_in_one['costs'] <= $this->data['costs'] ||
			    !empty($this->data['unpacked'])) {
				$this->data['deliverer_id'] = $all_in_one['deliverer_id'];
				$this->data['size'] 		= $all_in_one['size'];
				$this->data['costs']        = $all_in_one['costs'];
				$this->data['taxes']		= $this->getDelivererTaxes();
				unset($this->data['package']);
				return;
			}
		}

		// write the size string
		if ($this->data['package'] == $max_package) {
			$max_package_amount++;
			unset($this->data['package']);
		}

		$this->data['size']  = $max_package_amount == 0 ? ''
							 : $max_package_amount.' x '.$max_package['size'];
		$this->data['size'] .= $max_package_amount != 0 && isset($this->data['package']['size'])
							 ? ' & '.$this->data['package']['size']
							 : $this->data['package']['size'];
		$this->data['taxes'] = $this->getDelivererTaxes();
		unset($this->data['package']);

	} // end function getClassData

	/**
	 * This method is called by the method getClassData to
	 * get the required package size for an order. Returns
	 * false if a matching size was found.
	 *
	 * @param resultset $results  - a resultset with package
	 * 								sizes and costs
	 * @param float $size         -	the required size for the
	 * 								whole order
	 * @param int $deliverer_id	  - the deliverer id from the
	 * 								deliverer providing the
	 * 								cheapest package of max size
	 * @return bool				  - true if a matching package
	 * 								was found
	 * @access private
	 * @see function getClassData()
	 */
	private function getPackages($results, $size, $deliverer_id) {

		$return = true;
		$costs = 1000000;

		// find cheapest package
		foreach($results as $result) {
			// the id is needed if more than one package is
			// required so that the whole order will be
			// delivered by one deliverer
			if(!empty($deliverer_id)) {
				if($deliverer_id != $result['deliverer_id'])
					continue;
			}
			// if a package fits the given size set return to false
			// find the cheapest matching package as well
			if($result['costs'] < $costs && $result['size_in_units'] >= $size) {
				$this->data['package'] = $result;
				$costs = $result['costs'];
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * This method is called by getClassData() to determine
	 * the amounts and costs of packages required
	 * for an order.
	 *
	 * @param array &$products - the ordered products
	 * @param float $max_size  - the maximum package size
	 * 							 in packing units
	 * @return float $size	  - the size of products that
	 * 							can be packed into into one
	 * 							maximum package in packing
	 * 							units
	 * @access private
	 * @see function getClassData()
	 */
	private function resizeProductArray(&$products, $max_size) {

		$size = 0;
		while (!empty($products)) {
			// if there is no package large enough
			// to seize this product remove it from
			// product array
			if ($products[0]['units'] > $max_size) {
				// count products that doesn't fit a package
				$this->data['unpacked'] += $products[0]['amount'];

				array_shift($products);
				continue;
			}

			// delete products from product-array
			// as long as the size of products to
			// delete is is smaller than max_size
			while ($products[0]['amount'] > 0 && !empty($products[0]['units'])) {
				if ($products[0]['units'] + $size >= $max_size) return $size;
				$size += $products[0]['units'];
				$products[0]['amount']--;
			}
			// delete product from product-array
			array_shift($products);
		}

		return $size;
	}

	/**
	 * Returns all delivery costs for a defined
	 * delivery zone as resultset or formatted string.
	 *
	 * @param string $zone_id
	 * @param bool $format
	 * @return string/array - a formatted string or resultset
	 */
	public static function getZoneDeliveryCosts($zone_id, $format = true){

		global $CJO, $I18N_21;

		// return if delivery costs do not depend on order value
		if ($CJO['ADDON']['settings'][self::$mypage]['DELIVERY_METHOD'] != '0') return;

		$sql = new cjoSql();
		$qry = "SELECT
					*
				FROM "
					.TBL_21_DELIVERY_COSTS."
				WHERE
					zone_id='".$zone_id."'
				ORDER BY order_value";

		$results = $sql->getArray($qry);
		$return = array();
		$i = 0;
		foreach($results as $result){

			$return[] = $I18N_21->msg('shop_order_value_smaller_then',
						cjoShopPrice::toCurrency($result['order_value']),
						cjoShopPrice::toCurrency($result['costs']),
						cjoShopPrice::formatNumber($result['tax'],true));
			if($i == 2){
				$return[2] = '...';
				break;
			}
			$i++;
		}
		$return = implode('<br/>', $return);

		return $format ? $return : $results;
	}

	/**
	 * This method returns the formated link to
	 * the delivery terms article.
	 *
	 * @return string
	 * @access public
	 */
//	public static function getDeliveryLink()
//	{
//
//	    global $CJO, $I18N_21;
//
//	    return sprintf('<p class="shop_delivery"><a href="%s">%s</a></p>',
//	                    cjoRewrite::getUrl($CJO['ADDON']['settings'][self::$mypage]['DELIVERY_ARTICLE_ID']),
//	                    $I18N_21->msg('shop_excl_delivery_cost'));
//	}
//
//	/**
//	 * This method returns the formated delivery duration.
//	 *
//	 * @param string $value - the unformated delivery duration
//	 * @return string
//	 * @access public
//	 */
//	public static function getDeliveryDuration($value)
//	{
//		global $I18N_21;
//
//		if ($value == '') return false;
//
//	    return sprintf('<p class="shop_delivery"> %s </p>',
//	                    $I18N_21->msg('delivery_duration_'.$value));
//	}
//
//	/**
//	 * This method generates a select box for
//	 * selecting the delivery duration of a shop product.
//	 *
//	 * @param string $name - name of the form element
//	 * @param string|int $selected - value to pre select an element
//	 * @param string $class - css class to style the select box
//	 * @return string
//	 * @access public
//	 */
//	public static function getDurationSelection($name, $selected, $class='inp50')
//	{
//	    global $I18N_21;
//
//	    $select = new cjoSelect();
//    	$select->setsize(1);
//    	$select->setName($name);
//    	$select->setSelected($selected);
//    	$select->setStyle('class="'.$class.'"');
//    	$select->addOption($I18N_21->msg('delivery_duration_0'),  '0');
//    	$select->addOption($I18N_21->msg('delivery_duration_2'),  '2');
//    	$select->addOption($I18N_21->msg('delivery_duration_7'),  '7');
//    	$select->addOption($I18N_21->msg('delivery_duration_10'), '10');
//    	$select->addOption($I18N_21->msg('delivery_duration_14'), '14');
//    	$select->addOption($I18N_21->msg('delivery_duration_21'), '21');
//    	$select->addOption($I18N_21->msg('delivery_duration_28'), '28');
//    	$select->addOption($I18N_21->msg('delivery_duration_35'), '35');
//	    $select->addOption($I18N_21->msg('delivery_duration_42'), '42');
//	    $select->addOption($I18N_21->msg('delivery_duration_56'), '56');
//	    $select->addOption($I18N_21->msg('delivery_duration_ask'),'ask');
//	    $select->addOption($I18N_21->msg('delivery_duration_no'), '-1');
//	    return $select->get();
//	}


} // end class cjoSopDelivery
