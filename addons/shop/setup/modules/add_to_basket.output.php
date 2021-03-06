<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_shop
 * @subpackage 	modul
 *
 * @author 		Matthias Schomacker <ms@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

global $CJO;

if (OOAddon::isActivated('shop')) {

    $set               = array();
    $set['mypage']     = 'shop';
    $set['session_id'] = session_id();
    $set['added']      = false;
    // the product amount in stock
    $set['amount']     = (int) "CJO_VALUE[1]";
    // online status of the product
    $set['online']     = "CJO_VALUE[19]";

    if (!empty($set['online'])) {

    	// name of into basket formular
    	$set['form_name'] = 'add_to_basket_CJO_SLICE_ID';

    	// get POST vars and session-id, and article id
    	$posted = cjo_post($set['form_name'], 'array', array());

    	// was there a product added
    	if (isset($posted['slice_id']) &&
    	    $posted['slice_id'] == "CJO_SLICE_ID" &&
    	    empty($posted['product_added'])) {
    		$set['added'] = true;
    	}
            
        $attribute_format = $CJO['ADDON']['settings']['shop']['ATTRIBUTE_FORMAT'];

        $set['slice_id']         = "CJO_SLICE_ID";
        $set['product_id']       = "CJO_VALUE[12]";
    	$set['price']            = "CJO_VALUE[2]";
    	$set['taxes']            = "CJO_VALUE[3]";
    	$set['discount']         = "CJO_VALUE[4]";
    	$set['product_title']    = "CJO_VALUE[8]";
    	$set['product_image']    = OOMedia::toThumbnail("CJO_MEDIA[1]");
    	// put attributes into selectbox
    	$set['attributes']       = "CJO_VALUE[6]";    
    	$set['order_id']         = "CJO_VALUE[10]";
        $set['max_amount']       = "CJO_VALUE[11]" < 1 ? 10 : (int) "CJO_VALUE[11]"; 
        
    	// count down byed of stock
    	$set['count_down_stock'] = "CJO_VALUE[18]";
    	// disable buying if out of stock
    	$set['out_of_stock']     = "CJO_VALUE[17]";
    	// show current amount of products in stock
    	$set['show_in_stock']    = "CJO_VALUE[16]";

        //
    	$sql = new cjoSql();
    	$qry = "SELECT value1, value15 FROM ".TBL_ARTICLES_SLICE." WHERE id = '".$set['slice_id']."' LIMIT 1";
    	$sql->setQuery($qry);
    	$set['added_to_basket'] = (int) $sql->getValue('value15');
        
        if ($set['count_down_stock']){
            $temp = (int) $sql->getValue('value1');
            $set['max_amount'] = $temp < $set['max_amount'] ? $temp : $set['max_amount'];
        }

    	// create object to format price
    	$set['price_obj'] = new cjoShopPrice($set['price'], 0, $set['taxes'], $set['discount']);

    	// put count into select box
    	$set['amount_sel'] = new cjoSelect();
    	$set['amount_sel']->setsize(1);
    	$set['amount_sel']->setName($set['form_name'].'[amount]');
    	$set['amount_sel']->setSelected($posted['amount']);
        $set['amount_sel']->addOption('0',0);
    	for($i = 1; $i <= ($set['amount'] < $set['max_amount'] && $set['out_of_stock'] ? $set['amount'] : $set['max_amount']); $i++) {
    		$set['amount_sel']->addOption($i,$i);
    	}

        // ***  add to basket  ***//
        $set['added'] = cjoShopBasket::addToBasket($posted, $set);

    	// set names for price output
    	$trans                  = array();
    	$trans['netto'] 	    = '[translate_21: shop_netto_price]';
    	$trans['brutto']		= '[translate_21: shop_brutto_price]';
    	$trans['final']         = '[translate_21: shop_final_price]';
    	$trans['tax'] 		    = '[translate_21: shop_tax]';
    	$trans['discount'] 		= '[translate_21: shop_discount]';

    	$set['discount'] 	    = $set['price_obj']->getValue('discount');

    	cjoModulTemplate::addVars('TEMPLATE', array(
    							  'FORM_NAME'			=>		$set['form_name'],
    							  'NETTO_PRICE'			=>		$set['price_obj']->formattedValueOut('netto_price', $trans['netto'], '', true),
    							  'TAXES'				=>	    $set['price_obj']->formattedValueOut('taxes', $trans['tax'], true, ($attribute_format == 0 ? 0 : 2)),
    						      'DISCOUNT'			=>		!empty($set['discount']) ? $set['price_obj']->formattedValueOut('discount', $trans['discount'], true, ($attribute_format == 0 ? 0 : 2)) : '',
    							  'DELIVERY_DURATION'   =>		cjoShopDelivery::getDeliveryDuration("CJO_VALUE[10]"),
                                  'DELIVERY_LINK'		=>      cjoShopDelivery::getDeliveryLink("CJO_VALUE[5]"),
    							  'DELIVERY_LINK_ID'	=>      $CJO['ADDON']['settings'][$set['mypage']]['DELIVERY_ARTICLE_ID'],
    							  'BRUTTO_PRICE'		=>      $set['price_obj']->formattedValueOut('brutto_price', $trans['brutto'], false, false),
    							  'FINAL_PRICE'			=>		$set['price_obj']->formattedValueOut('final_price', $trans['final'], '', true),
                                  'OUT_OF_STOCK'        =>      ($set['out_of_stock'] && $set['amount'] < 1),
                                  'SHOW_IN_STOCK'       =>      $set['show_in_stock'],
    							  'IN_STOCK'            =>      $set['amount'],

    							  'URL'				    =>      cjoShopBasket::getSecureUrl('CJO_ARTICLE_ID'),
    							  'PRODUCT_ID'		    =>		$set['product_id'],
    							  'PRODUCT_TITLE'		=>		$set['product_title'],
    							  'PRODUCT_IMAGE'		=>		$set['product_image'],
    							  'ORDER_ID'		    =>		$set['order_id'],
    							  'PRODUCT_AMOUNT'		=>		$attribute_format < 2 ? $set['amount_sel']->get() : '',
                                  'ATTRIBUTE_LIST'      =>      $attribute_format == 2,
    							  'ONLINE'				=>		$set['online'],
    							  'ADDED'				=>		$set['added'],
    							  'NOT_ADDED'			=>		empty($set['added']),
    							  'PRODUCT_ADDED_MSG'	=>		$CJO['ADDON']['settings'][$set['mypage']]['PRODUCT_ADDED_MESSAGE']
    							  ));

    	cjoModulTemplate::addVarsArray('ATTRIBUTES', cjoShopProductAttributes::getFEAttributeSelections($set));


    } // end if not empty $online
    else {
    	cjoModulTemplate::addVars('TEMPLATE', array());
    }

    cjoModulTemplate::getModul();

    if ($CJO['CONTEJO']) {
        cjo_insertCss(false, $CJO['ADDON']['settings']['shop']['CSS']['BACKEND']);
    }

} elseif ($CJO['CONTEJO']) {
    echo $I18N->msg('msg_addon_not_activated', 'Shop');
}
?>