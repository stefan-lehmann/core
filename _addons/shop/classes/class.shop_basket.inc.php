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
 * <strong><u>Class cjoShopBasket</u></strong>
 *
 * This class provides methods to update, manipulate
 * and display the basket fully or partly in several
 * ways. Contains only static methods
 */

class cjoShopBasket {
    
    protected static $mypage = 'shop';
    
	/**
	 * This method updates the basket
	 * if customer is on basket page.
	 *
	 * @access public
	 */
	public static function updateBasket() {

		$session = session_id();

		// return if this is a mal program
		if(cjoShopBasket::noBots($session)) return;

		$update = new cjoSql();

		// get the amount and md5_id of ordered products
		$orders = cjo_post('product_amount', 'array', array(), true);

		// **** compare with the old values in db **** //

		// create or string for query
		$md5_ids = array_keys($orders);
		foreach($md5_ids as $md5_id) {
			if (empty($or)) {
				$or = "'".$md5_id."'";
			}
			else {
				$or .= "OR md5_id='".$md5_id."'";
			}
		}

		// get old values
		$sql = new cjoSql();
		$qry = "SELECT
			   		md5_id,
			   		amount
			   	FROM  ".TBL_21_BASKET."
			   	WHERE md5_id=".$or;

		// create an array of it like $orders
		foreach($sql->getArray($qry) as $old_value) {
			$old_values[$old_value['md5_id']] = $old_value['amount'];
		}

		// get differences from the arrays as update values
			$to_update = array_diff_assoc($orders, $old_values);

		// update db
		foreach(cjoAssistance::toArray($to_update) as $md5 => $amount) {

			// get slice_id and attribute for identification
			$update->setTable(TBL_21_BASKET);
			$update->setWhere("md5_id = '".$md5."'");
			$update->setValue('amount', $amount);
			$update->setValue('updatedate', time());

			// delete if amount is 0
			if ($amount == 0) {
				$update->delete();
			} else {
				$update->update();
			}
		}
		$update->flush();
	}

	/**
	 * This method generates a table output of the basket.
	 * Can be used for several templates (e.g. basket, product_table).
	 *
	 * @param string $template    - the template name
	 * @param string $country	  - the country which has to be
	 * 								delivered to (needed for the
	 * 							    calculation of delivery costs)
	 * @param float $payment_costs  - the cost for payment
	 * @return string $html       - the html code to be
	 * 								displayed
	 * @access public
	 */
	public static function out($template = 'BASKET', $payment_costs = 0, $country = false) {

		// get setting vars
		global $CJO;
        
		$session_id     = session_id();
		$clang          = $CJO['CUR_CLANG'];
		$exchange_ratio = $CJO['ADDON']['settings'][self::$mypage]['CURRENCY']['CURR_RATIO'];

		// is set refresh or delete
		if (cjo_post('shop_basket_submit','bool') || cjo_post('product_amount', 'bool')) {
			self::updateBasket();
		}
		// get ordered products
		$sql = new cjoSql();
		$qry = "SELECT
					a.slice_id AS slice_id,
					a.product_id AS product_id,
					a.amount AS amount,
					a.attribute AS attribute,
					b.value2 AS price,
					b.value3 AS taxes,
					b.value4 AS discount,
					b.value6 AS attribute_name,
					b.value8 AS product_name,
					b.value18 AS count_down_stock,
					b.value17 AS enable_out_of_stock,
					b.value1 AS products_in_stock,
					b.file1 AS thumbnail,
					b.article_id AS article_id,
					a.md5_id AS md5_id,
					b.clang AS clang,
					a.updatedate AS updatedate,
					(SELECT
						SUM(c.amount)
					FROM
						".TBL_21_BASKET." c
					LEFT JOIN
						".TBL_ARTICLES_SLICE." d
					ON
						c.slice_id=d.id
					WHERE
						c.session_id = '".$session_id."' AND c.slice_id=a.slice_id GROUP BY c.slice_id )
					AS total_amount
				FROM
					".TBL_21_BASKET." a
				LEFT JOIN
					".TBL_ARTICLES_SLICE." b
				ON
					a.slice_id=b.id
				WHERE
					a.session_id = '".$session_id."'
				ORDER BY a.id ASC";

		$orders = $sql->getArray($qry);
		$sql->flush();

		// if there are any datasets
		$datasets = count($orders);

		if ($datasets > 0) {

    		// create md5 of session id to
    		// make the checkout proccess available
    		$session_md5 = $datasets > 0 ? md5($session_id) : '';
    		$order_value = 0;
    		$amount = 0;
    		$taxes = array();

    		// create a list for the template
    		$list = array();

    		// count datasets in foreach
    		$count = 0;

    		// order products by updatetime
    		$products = cjoShopBasket::orderProducts($orders);

    		// prepare output values as template arrays (see line 220+)
            foreach($orders as $order) {

            	$price = new cjoShopPrice($order['price'], $order['attribute'], $order['taxes'], $order['discount']);
    			$amount = $order['amount'];

    			// if product can run out of stock
    			if (!empty($order['enable_out_of_stock'])) {


    				// add to list
    				$list['available_amount'][$count] = cjoShopBasket::getProductAvailability($order, $products, $amount);

    			} // end if not empty enable out of stock

    			// define classes for <tr>-elements
    			$list['tr_class'][] = ($count % 2 == 0) ? 'even' : 'odd';

    			// fill list[] with the product values
                $list['slice_id'][]	= $order['md5_id'];

                // add link to product thumbnail for displaying in basket,
                // no link for product table !
                if($template == 'BASKET')  {

                	$thumbnail_params['fun']['int']['id'] 	 = $order['article_id'];
                	$thumbnail_params['fun']['int']['clang'] = $order['clang'];
                	$thumbnail_params['fun']['settings'] 	 = 4;
                	$thumbnail_params['img']['width'] 		 = 80;
                	$thumbnail_params['img']['height'] 		 = 80;
                	$thumbnail_params['des']['settings'] 	 = '-';
                	$list['thumbnail'][]					 = $order['thumbnail'] != false
                											 ? OOMedia::toResizedImage($order['thumbnail'], $thumbnail_params) : '' ;
                }
                else {
                	$list['thumbnail'][]	= $order['thumbnail'] != false ? OOMedia::toThumbnail($order['thumbnail']) : '' ;
                }

                // prepare output values(prices, name etc)
                $discount = $price->getValue('discount');
                $tax = $price->getValue('taxes');
                $list['product_id'][]		= $order['product_id'];
                $list['product_name'][] 	= $order['product_name'];
                $list['attribute'][]		= cjoShopProductAttributes::getAttributesAndValues($order['attribute']);
                $list['final_price'][]		= $price->getFormattedValue('final_price');
                $list['discount'][]			= empty($discount) ? '' : '[translate_21: shop_incl] '.$price->getFormattedValue('discount', '%').' [translate_21: shop_discount] ('.$price->getFormattedValue('total_discount').')';
                $list['taxes'][]			= empty($tax) ? '' : '[translate_21: shop_incl] '.$price->getFormattedValue('taxes', '%').' [translate_21: shop_tax] ('.$price->getFormattedValue('total_taxes').')';
                $list['amount'][] 	   		= $amount;
               	$curr_sum 					= $amount * $price->getValue('final_price');
    	        $list['final_amount_price'][]  	= cjoShopPrice::toCurrency($curr_sum);
    	        $order_value += $curr_sum;


                // calculate total value of all taxes, sum only equal rates
                $taxes[$tax] += $amount * $price->getValue('total_taxes');
                $count++;

                // get product url for img-link
                $product_article = OOArticle::getArticleById($order['article_id']);
                $list['product_url'][] = (OOArticle::isValid($product_article))
                    				   ? 'contejo://'.$product_article->getId().'.'.$order['clang']
                    				   : '#';

    	    } // end foreach $orders

            // define css-classes for the first and the last row
            $list['tr_subclass'][--$count] 	= 'last';
            $list['tr_class'][0] 			= 'first';

            // get total value of the order
            $total_order_value = $order_value + $payment_costs;

            // get delivery costs and add to the total value
            if (!$country) $country = $CJO['ADDON']['settings'][self::$mypage]['COUNTRY'];

        	$del = new cjoShopDelivery($country);
        	$total_order_value += $del->getTotalCosts();
        	$delivery_costs = cjoShopPrice::toCurrency($del->getTotalCosts());
        	$country_codes = cjo_get_country_codes();
        	$country_name = $country_codes[$country];

        	if($del->getTaxes() > 0)
                $taxes[$del->getTaxes()] += $del->getTotalTaxes($value);


		    // prepare total taxes and tax rates for displaying
            $tax_list = array();
            $count = 0;
            foreach($taxes as $key => $value) {
                if (empty($key) || empty($value)) continue;
              	$tax_list['tax_rate'][] 		= '[translate_21: shop_incl]  '.cjoShopPrice::formatNumber($key, '%').' [translate_21: shop_tax]';
              	$tax_list['taxes'][]			= cjoShopPrice::toCurrency($value);
                $tax_list['taxes'][$count]      = '('.$tax_list['taxes'][$count].')';
              	$count++;
            }

		} // end if datasets > 0

		// prepare template content
        $html_tpl_content = @ file_get_contents($CJO['ADDON']['settings'][self::$mypage]['HTML_TEMPLATE'][$template]);
        // prepare template
	    $html_tpl = new cjoHtmlTemplate($html_tpl_content);

        // fill template with values
        $html_tpl->fillTemplate('BASKET', array(
        						'ORDER_VALUE' 		=>  cjoShopPrice::toCurrency($order_value),
        						'TOTAL_ORDER_VALUE' =>  cjoShopPrice::toCurrency($total_order_value),
        						'DELIVERY_COUNTRY'	=>  $country_name,
        						'DELIVERY_COSTS'	=>  $delivery_costs,
        						'PAYMENT_COSTS'		=>  $payment_costs > 0 ? cjoShopPrice::toCurrency($payment_costs) : '',
        						'SESSION'			=> 	$session_md5,
        						'DATASETS'			=>  $datasets,
        						'CHECKOUT_URL'		=>  cjoRewrite::getUrl($CJO['ADDON']['settings'][self::$mypage]['CHECKOUT_ARTICLE_ID'])
        						));

        // get arrays for template sections
        $html_tpl->fillTemplateArray('PRODUCTS', $list);
        $html_tpl->fillTemplateArray('TAX_LIST', $tax_list);

        // build output html-file
        return $html_tpl->get(false);

	} // end function out

	/**
	 * This method sorts all ordered products by the updatedate.
	 *
	 * @param array $orders - the ordered products
	 * @return $products - the sorted product list
	 * @see method self->out()
	 */
	private static function orderProducts($orders){

		// copy all products with slices to
    	// array products and sort it by
    	// updatedate descend
    	foreach($orders as $order) {
    		$products[$order['slice_id']][$order['updatedate']][$order['md5_id']] = $order['amount'];
    	}
    	// sort products and change keys
    	// oldest updated products will
    	// be the first in each array
    	// belonging to a slice id
    	foreach($products as $key => $product) {
    		$i = 0;
    		$new_key_product = array();
    		ksort($product);

    		// change key (timestamp to 0,1,2 ...)
    		foreach($product as $value) {
    			$new_key_product[$i] = $value;
    			$i++;
    		}
    		$products[$key] = $new_key_product;
    	}

    	return $products;

	} // end function orderProducts

	/**
	 * This method returns a message if the amount in stock of a
	 * product is not as high the amount that wants to be ordered.
	 *
	 * @param array $order - the product's information
	 * @param array &$products - a sorted array with all products of
	 * 							 the order
	 * @param int &$amount - the amount of the product that is proccessed
	 * @return string $available_amount - the output
	 * @see method self->out()
	 */
	private static function getProductAvailability($order, &$products, &$amount){

		// reset amount in stock
    	$amount_in_stock = $order['products_in_stock'];

    	// in case that a product of the same type (slice id)
    	// already appeared in the basket, substract its amount
    	// from amount in stock
    	$i = 0;
    	foreach($products[$order['slice_id']] as $product) {
    		if (array_key_exists($order['md5_id'], $product)) break;
    		$amount_in_stock -= array_shift($product);
    	}

    	// rewrite amount available for order if required
    	if ($amount > $amount_in_stock) {

    	    $amount = $amount_in_stock;

    		if($amount == 0) {
    			$available_amount = '[translate_21: shop_product_not_deliverable]';
    		}
    		else {
    			$available_amount 	= '[translate_21: shop_available_product_amount_1] '.
    							      $amount.' [translate_21: shop_available_product_amount_2]';
    		}

    		if($amount > 0) {
    		    // update amount in tbl_21_basket
    			$update = new cjoSql();
    			$update->setTable(TBL_21_BASKET);
    			$update->setWhere('md5_id="'.$order['md5_id'].'"');
    			$update->setValue('amount', $amount);
    			$update->Update();
    		}
    		else {
    		    // delete product from tbl_21_basket
    			$delete = new cjoSql();
    			$delete->setTable(TBL_21_BASKET);
    			$delete->setWhere('md5_id="'.$order['md5_id'].'"');
    			$delete->Delete();
    		}
    	}

    	return $available_amount;
	}

	/**
	 * Adds a posted product to basket or updates the amount.
	 *
	 * @param array $posted - posted data
	 * @param array $set    - product settings
	 * @return bool
	 * @access public
	 */
	public static function addToBasket($posted, $set) {

        if (is_array($posted['amount'])) {
            $return = false;
            foreach($posted['amount'] as $attribute_id=>$amount) {
                if ((int) $posted['amount'] == 0) continue;
                if (self::addToBasket(array_merge($posted, array('amount'=>$amount,'attribute'=>$attribute_id)), $set)) {
                    $return = true;
                }
            }
            return $return;
        }

    	if ((isset($set['form_name']) && !cjo_post($set['form_name'].'_submit', 'bool')) ||
    	    (int) $posted['amount'] == 0 ||
    	    ($set['amount'] == 0 && $set['out_of_stock'])) return false;

        if ($set['out_of_stock'] && $posted['amount'] > $set['amount']) $posted['amount'] = $set['amount'];

        if (is_array($posted['attribute'])) $posted['attribute'] = implode('|', $posted['attribute']);

    	// delete oldest datasets if there are more then 2000
    	self::cleanBasketTable();

    	$set['md5']  = md5($set['session_id'].$posted['slice_id'].$posted['attribute']);
    	$set['time'] = time();

    	// search table for existing datasets
    	$sql = new cjoSql();
    	$qry = "SELECT * FROM ".TBL_21_BASKET." WHERE md5_id = '".$set['md5']."'";
    	$sql->setQuery($qry);

        $set['new_added_to_basket'] = $set['added_to_basket'] + $posted['amount'];

    	// if dataset exists -> update it else create a new one
    	if ($sql->getRows() < 1) {
            
            $set['type'] = 'insert';
    	    $set['new_amount'] = $posted['amount'];
            $insert = new cjoSql();
    		$insert->setTable(TBL_21_BASKET);
    		$insert->setValue('session_id', $set['session_id']);
    		$insert->setValue('slice_id', $set['slice_id']);
    		$insert->setValue('product_id', $set['product_id']);
    		$insert->setValue('attribute', (string) $posted['attribute']);
    		$insert->setValue('amount',$set['new_amount']);
    		$insert->setValue('updatedate',$set['time']);
    		$insert->setValue('md5_id', $set['md5']);
    		$insert->Insert();
    		if ($insert->getError()) return false;
    	}
    	else {
            $set['type'] = 'update';
    	    $set['new_amount'] = $sql->getValue('amount') + $posted['amount'];

    		$update = new cjoSql();
    		$update->setTable(TBL_21_BASKET);
    		$update->setWhere("md5_id='".$set['md5']."'");
    		$update->setValue('session_id', $set['session_id']);
    		$update->setValue('slice_id', $set['slice_id']);
    		$update->setValue('product_id', $set['product_id']);
    		$update->setValue('attribute', (string) $posted['attribute']);
    		$update->setValue('amount', $set['new_amount']);
    		$update->setValue('updatedate',$set['time']);
    		$update->setValue('md5_id', $set['md5']);
    		$update->Update();
    		if ($update->getError()) return false;
    	}
    	$sql->flush();

    	$update = $sql;
    	$update->setTable(TBL_ARTICLES_SLICE);
    	$update->setWhere("id = '".$set['slice_id']."'");
    	$update->setValue('value15',  $set['new_added_to_basket']);

    	if ($update->Update()) {
            cjoExtension::registerExtensionPoint('SHOP_ADDED_TO_BASKET', $set);
            return true;
    	}

	} // end function addToBasket

	/**
	 * Converts the ordered products into a string,
	 * for saving them into db after checkout. Reads
	 * data from db.
	 *
	 * @return string $products
	 * @access public
	 */

	public static function saveBasket() {

		// get basket
		$sql = new cjoSql();
		$session_id = session_id();
		$qry = "SELECT
					a.slice_id AS slice_id,
					b.value8 AS product_name,
					a.attribute AS attribute,
					a.amount AS amount,
					b.value2 AS price,
					b.value3 AS taxes,
					b.value4 AS discount,
					b.value6 AS attribute_name,
					b.value12 as product_id
				FROM "
					.TBL_21_BASKET." a
				LEFT JOIN "
					 .TBL_ARTICLES_SLICE." b
				ON
					b.id = a.slice_id
				WHERE
					a.session_id='".$session_id."'";

		$data = $sql->getArray($qry);
		$update = $sql;
		$products = '';

		// if there are not enough products in stock
		// save product-id's here
		$not_available = '';

		// build string
		foreach($data as $value) {

			$product = new cjoShopProduct($value);
			$products .= !empty($products) ? '~' : '';
			$products .= $product->toString();

        	$sql->flush();
        	$qry = "SELECT
        				   value14 AS amount_bought,
        				   value15 AS added_to_basket,
        				   value18 AS count_down_stock,
        				   value17 AS enable_out_of_stock,
        				   value1 AS products_in_stock
        		    FROM "
        				   .TBL_ARTICLES_SLICE."
        		    WHERE
        		    	id='".$value['slice_id']."' LIMIT 1";

        	$sql->setQuery($qry);
        	$result = array_shift($sql->getArray($qry));

        	$amount = $value['amount'];

        	// check if there enough products in stock if required
        	if (!empty($result['count_down_stock'])) {
        		if ($result['products_in_stock'] < $amount) {
        			$amount =  $result['products_in_stock'];
        			$not_available[$value['slice_id']]['amount'] = $amount;
        		}
        	}

        	$new_bought = $result['amount_bought'] + $amount;
        	$new_amount = $result['products_in_stock'] - $amount;

        	// update product amount and statitstics
			$update->flush();
            $update->setTable(TBL_ARTICLES_SLICE);
            $update->setWhere("id = '".$value['slice_id']."'");
            $update->setValue("value14",$new_bought);
            if ($result['enable_out_of_stock'] == 1)
                $update->setValue("value1",$new_amount);

            $update->Update();
		}

		// clear basket
		$session_id = session_id();
		$delete = new cjoSql();
		$delete->setWhere("session_id='".$session_id."'");
		$delete->setTable(TBL_21_BASKET);
		$delete->Delete();

		return array($products, $not_available);

	} // end function saveBasket

	/**
	 * Returns the total value of an
	 * order. This method is needed
	 * for basket info.
	 *
	 * @return float $value
	 * @access public
	 */
	public static function getOrderValue() {

		$session_id = session_id();
		$sql = new cjoSql();
		$qry = "SELECT
					a.amount,
					b.value2 AS price,
					b.value3 AS taxes,
					b.value4 AS discount,
					a.attribute AS attribute
				FROM "
					.TBL_21_BASKET." a
				INNER JOIN "
					.TBL_ARTICLES_SLICE." b
				ON
					b.id=a.slice_id
			    WHERE
			    	a.session_id='".$session_id."'";

		$results = $sql->getArray($qry);

		if (count($results) == 0) return 0;

		$value = 0;
		foreach($results as $result) {
			$price = new cjoShopPrice($result['price'], $result['attribute'], $result['taxes'], $result['discount']);
			$price = $price->getValue('final_price');
			$value += $result['amount'] * $price;
		}

		return cjoShopPrice::convToFloat($value);
	}

	/**
	 * Checks if the session holder puts products
	 * into basket too fast ( > 10 clicks / second)
	 *
	 * @param  int $session_id
	 * @return bool            - true if it is too fast
	 * @access public
	 */
	public static function noBots($session_id) {

		$sql = new cjoSql();
		$qry = "SELECT
					updatedate as time
				FROM "
					.TBL_21_BASKET."
				WHERE
					session_id=".$session_id."
				ORDER BY updatedate DESC LIMIT 0,10";

		$results = $sql->getArray($qry);
		$sql->flush();

		if(empty($results))
			return false;

		// check if 'customer' is putting products into
		// basket too fast
		$too_short = 0;
		for($i = 0; $i < count($results) - 1; $i++) {

			if ($results[$i]['time'] - $results[$i + 1]['time'] <= 100) {
				$to_short ++;
			}
			if ($toShort >= 5) {
				$sql->setWhere("session_id='".$session_id."'");
				$sql->Delete();
				$sql->flush();
				return true;
			}
		}
		return false;

	} // end function noBots

	/**
	 * Outputs a little information about
	 * products in basket and the current
	 * order value
	 *
	 * @return string - the html code to be
	 * 					displayed
	 * @access public
	 */
	public static function basketInfo() {

		global $CJO;
            
        if ($CJO['ARTICLE_ID'] == $CJO['ADDON']['settings'][self::$mypage]['BASKET_ARTICLE_ID'] ||
            $CJO['ARTICLE_ID'] == $CJO['ADDON']['settings'][self::$mypage]['CHECKOUT_ARTICLE_ID'] ||
            $CJO['ARTICLE_ID'] == $CJO['ADDON']['settings'][self::$mypage]['POST_ORDER_ARTICLE_ID']) return false;

		$settings       = $CJO['ADDON']['settings'][self::$mypage];
		$exchange_ratio = $settings['CURRENCY']['CURR_RATIO'];
		$basket_id      = $settings['BASKET_ARTICLE_ID'];
		$delivery_id    = $settings['DELIVERY_ARTICLE_ID'];

		$session_id = session_id();
		$sql = new cjoSql();
		$qry = "SELECT
					a.amount,
					b.value2 AS price,
					b.value3 AS taxes,
					b.value4 AS discount,
					a.attribute AS attribute
				FROM "
					.TBL_21_BASKET." a
				INNER JOIN "
					.TBL_ARTICLES_SLICE." b
				ON
					b.id=a.slice_id
			    WHERE
			    	a.session_id='".$session_id."'";

		$results = $sql->getArray($qry);

		$sql->flush();
		$order_value = 0;
		$order_amount = 0;
		$empty = '';

		// if basket is empty
		if (count($results) == 0) {
			$empty_basket = '[translate_21: shop_empty_basket]';
			$results = array();
		}

		foreach($results as $result) {
			$price = new cjoShopPrice($result['price'], $result['attribute'], $result['taxes'], $result['discount']);
			$price = $price->getValue('final_price');

			// add to required data
			$order_value += $result['amount'] * $price;
			$product_amount += $result['amount'];
		}

		$html_tpl_content 	= @ file_get_contents($CJO['ADDON']['settings'][self::$mypage]['HTML_TEMPLATE']['BASKET_INFO']);
	    $html_tpl 			= new cjoHtmlTemplate($html_tpl_content);

	    $basket_article 	= OOArticle::getArticleById($basket_id);
	    $basket_link        = '';
	    $basket_url         = '';

	    if (OOArticle::isValid($basket_article)) {
	        $basket_link = $basket_article->toLink('', array('class' =>'shop_info_basket_link'));
	        $basket_url  = $basket_article->getUrl();
	    }

		$delivery_article 	= OOArticle::getArticleById($delivery_id);
	    $delivery_url        = '';

	    if (OOArticle::isValid($delivery_article)) {
	        $delivery_url = $delivery_article->getUrl();
	    }



        // get delivery costs and add to the total value
        $country = $CJO['ADDON']['settings'][self::$mypage]['COUNTRY'];

    	$del = new cjoShopDelivery($country);
    	$total_order_value += $del->getTotalCosts();
    	$delivery_costs = cjoShopPrice::toCurrency($del->getTotalCosts());
    	$country_codes = cjo_get_country_codes();
    	$country_name = $country_codes[$country];

		// fill template with values
        $html_tpl->fillTemplate('TEMPLATE', array('ORDER_VALUE' 	 =>  cjoShopPrice::toCurrency($order_value),
        										  'DELIVERY_COSTS' 	 =>  $delivery_costs,
        										  'DELIVERY_COUNTRY' =>  $country_name,
                                                  'DELIVERY_URL'	 =>  $delivery_url,
        										  'PRODUCT_AMOUNT'	 =>  $product_amount,
        										  'EMPTY_BASKET'	 =>  $empty_basket,
        										  'BASKET_LINK'	 	 =>  $basket_link,
                                                  'BASKET_URL'       =>  $basket_url
        						));


        // build html code
        return $html_tpl->get(false);

	} // end function basketInfo

	/**
	 * Returns true if the basket has products
	 * @param  int $session_id
	 * @return bool
	 * @access public
	 */
	public static function basketHasProducts($session_id = false) {

		global $CJO;

        if ($session_id === false) $session_id = session_id();

		$sql = new cjoSql();
		$qry = "SELECT
					amount
				FROM "
					.TBL_21_BASKET."
			    WHERE
			    	session_id='".$session_id."'
			    LIMIT 1";

        $sql->setQuery($qry);
        return ($sql->getRows() > 0) ? true : false;

	} // end function basketHasProducts

	/**
	 * Deletes datasets from TBL_21_BASKET,
	 * if there are mor than 2000.
	 *
	 * @access public
	 */
	static public function cleanBasketTable(){

	    // delete oldest datasets if there are more then 2000
    	$sql = new cjoSql();
    	$qry = "SELECT updatedate FROM ".TBL_21_BASKET." ORDER BY updatedate DESC LIMIT 1998, 1";
    	$sql->setQuery($qry);

    	if ($sql->getRows() > 0) {
    		$temp = $sql->getValue('updatedate', 0);
    		$sql->flush();
    		$qry = "DELETE FROM ".TBL_21_BASKET." WHERE updatedate < '".$temp."'";
    		$sql->setQuery($qry);
    	}
	}

    /**
     * Returns the secure url to a article.
     * @see cjoRewrite::getUrl
     * @param int $id
     * @param int|boolean $clang
     * @param string|array $params parameters for query string
     * @param string $hash_string
     * @return string
     *
     * @access public
     */
	static public function getSecureUrl($id = '', $clang = false, $params = '', $hash_string = '')  {

	    global $CJO;

	    $temp   = array('HTTPS' => $_SERVER['HTTPS'], 'SERVER_NAME' => $_SERVER['SERVER_NAME']);

	    if ($CJO['ADDON']['settings'][self::$mypage]['HTTPS']) {
	        $secure_server = preg_replace('/\/$/', '', $CJO['ADDON']['settings'][self::$mypage]['HTTPS']);
	        $_SERVER['HTTPS'] = true;
	        $_SERVER['SERVER_NAME'] = preg_replace('/^\w+:\/\//', '', $secure_server);
	    }

        $url = cjoRewrite::getUrl($id, $clang, $params, $hash_string) ;

        $_SERVER['HTTPS']       = $temp['HTTPS'];
        $_SERVER['SERVER_NAME'] = $temp['SERVER_NAME'];

        return $url;
	}

} // end class cjoShopBasket
