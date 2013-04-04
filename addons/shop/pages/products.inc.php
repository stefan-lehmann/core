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

// declare or define required vars
global $CJO;
$addon = 'shop';

/**********************
 *
 * FUNCTION = EMPTY
 *
 **********************/

// update product status if requested
if ($function == 'edit') {

	if ($mode == 'delete') {

		// get slice_id of predeccessing article
		$re_article_slice_id = cjo_request('re_id', 'string');

		// get id of successing article
		$sql = new cjoSql();
		$qry = "SELECT id FROM ".TBL_ARTICLES_SLICE." WHERE re_article_slice_id=".$oid;
		$sql->setQuery($qry);
		$update_id = $sql->getValue('id', 0);

		if ($update_id != '') {

			// update successor article_slice before deleting
			$update = new cjoSql();
			$update->setTable(TBL_ARTICLES_SLICE);
			$update->setWhere('id='.$update_id);
			$update->setValue('re_article_slice_id', $re_article_slice_id);
			$update->Update();

			// delete article_slice
			$delete = new cjoSql();
			$delete->setTable(TBL_ARTICLES_SLICE);
			$delete->setWhere('id='.$oid);
			$delete->Delete(cjoI18N::translate('msg_article_deleted'));

			// update article template
			cjoGenerate::generateArticle($article_id, true, $clang);

		} // end if update_id != ''

	}
	elseif($mode == 'status') {

		$status = cjo_request('status', 'string', '');

		// update online status
		$update = new cjoSql();
		$update->setTable(TBL_ARTICLES_SLICE);
		$update->setWhere('id='.$oid.' AND clang='.$clang);
		$update->setValue('value19', $status);
		$update->Update(cjoI18N::translate('msg_data_saved'));
	} // end if mode == status

} // end if isset mode

// get shop modul id
$shop_modul_id = $CJO['ADDON']['settings'][$addon]['SHOP_MODUL_ID'];

// query for product list
$qry = "SELECT
				*,
				CONCAT('slice', id) AS anchor,
				IF(value1>0,value1,0) AS value1,
				IF(value15>0,value15,0) AS value15,
				IF(value14>0,value14,0) AS value14,
				CONCAT(value8,'~~',value6) AS name

		FROM "
			.TBL_ARTICLES_SLICE."
		WHERE
			modultyp_id = '".$shop_modul_id."' AND
			clang = '".$clang."'";


// define list
$list = new cjolist($qry, 'article_id', 'DESC', 'clang', 20);
$list->addGlobalParams(cjoUrl::getDefaultGlobalParams());

$cols['order_id'] = new resultColumn('value12', cjoAddon::translate(21,'shop_order_id_short'));
$cols['order_id']->setHeadAttributes('class="icon"');
$cols['order_id']->setBodyAttributes('class="icon"');
$cols['order_id']->addCondition('value12', '', '--');

// product image
$cols['img'] = new resultColumn('file1', '', 'call_user_func',
								 array('OOMedia::toThumbnail',array('%s'))
							    );
$cols['img']->setBodyAttributes('width="80"');
$cols['img']->delOption(OPT_ALL);

// product name
$cols['name'] = new resultColumn('name', cjoAddon::translate(21,'shop_name'), 'call_user_func',
								  array('cjoShopProductAttributes::getNameAndAttributes',array('%s')));
$cols['name']->setBodyAttributes('width="250"');


// netto price
$cols['netto_price'] = new resultColumn('value2', cjoAddon::translate(21,'shop_netto_price'), 'call_user_func',
								  array('cjoShopPrice::toCurrency',array('%s')));

// tax rate
$cols['tax_rate'] = new resultColumn('value3', cjoAddon::translate(21,'shop_tax'), 'call_user_func',
									 array('cjoShopPrice::formatNumber', array('%s', '%%')));

// discount
$cols['discount'] = new resultColumn('value4', cjoAddon::translate(21,'shop_discount'), 'call_user_func',
									 array('cjoShopPrice::formatNumber', array('%s','%%' )));
$cols['discount']->addCondition('value4', 0, '--');


$cols['products_in_stock'] = new resultColumn('value1', ' &nbsp; ', 'sprintf', '<img src="img/silk_icons/package.png" alt="" />&nbsp;%s');
$cols['products_in_stock']->setBodyAttributes('width="55" title="'.cjoAddon::translate(21,'shop_products_in_stock').'" style="white-space:nowrap;""');
$cols['products_in_stock']->addCondition('value17', 1, '%s <span style="color: red;">[ ! ]</a>');
$cols['products_in_stock']->delOption(OPT_SEARCH);

// times product was putted into basket
$cols['into_basket_amount'] = new resultColumn('value15', ' &nbsp; ', 'sprintf', '<img src="img/silk_icons/cart.png" alt="" /> %s');
$cols['into_basket_amount']->setBodyAttributes('width="55" title="'.cjoAddon::translate(21,'shop_shop_into_basket_amount').'"');
$cols['into_basket_amount']->delOption(OPT_SEARCH);

// times product was ordered
$cols['shop_ordered_amount'] = new resultColumn('value14', ' &nbsp; ', 'sprintf', '<img src="img/silk_icons/money.png" alt="" /> %s');
$cols['shop_ordered_amount']->setBodyAttributes('width="55" title="'.cjoAddon::translate(21,'shop_ordered_amount').'"');
$cols['shop_ordered_amount']->delOption(OPT_SEARCH);

// update link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").
	   '" alt="'.cjoI18N::translate("button_edit").'" />';
$cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setHeadAttributes('colspan="3"');

// direct to edit article content page
// opens the edit page for the article belonging
// to this product and parameters
$cols['edit']->setParams(array('function'		=> 'edit',
							   'mode'			=> 'edit',
							   'page' 			=> 'edit',
							   'subpage' 		=> 'content',
							   'article_id' 	=> '%article_id%',
							   'slice_id'		=> '%id%',
							   'clang'			=> '%clang%',
							   'ctype'			=> '%ctype%',
							   '#'				=> '%anchor%'));

// online status
$offline  = '<img src="img/silk_icons/eye_off.png" alt="offline" />';
$online   = '<img src="img/silk_icons/eye.png" alt="online" />';

$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->setBodyAttributes('style="border-left: none;"');
$cols['status']->setBodyAttributes('class="cjo_status"');


// add display conditions
$cols['status']->addCondition('value19', '1', $online, array ('function'    => 'edit',
															  'mode' 		=> 'status',
															  'oid' 		=> '%id%',
															  'article_id' 	=> '%article_id%'
															  ));

$cols['status']->addCondition('value19', '', $offline, array ('function'    => 'edit',
															  'mode' 		=> 'status',
															  'status'		=> '1',
															  'oid' 		=> '%id%',
															  'article_id' 	=> '%article_id%'
															  ));
// default condition
$cols['status']->addCondition('status', '-1', '&nbsp;');

// delete link
$img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');

// redirect to this page
$cols['delete']->setParams(array('function'   => 'edit',
    							 'mode' 	  => 'delete',
								 'oid'		  => '%id%' ,
								 're_id' 	  => '%re_article_slice_id%',
								 'article_id' => '%article_id%' ));

// add columns to list
$list->addColumns($cols);


$browseBar = new browseBar();
$browseBar->setAddButtonStatus(false);
$list->addToolbar($browseBar, 'top', 'half');
$list->addToolbar(new searchBar(), 'top', 'half');
$list->addToolbar(new statusBar(), 'bottom', 'half');
$list->addToolbar(new maxElementsBar(), 'bottom', 'half');

// show table
$list->show(false);
