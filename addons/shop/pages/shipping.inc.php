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

global $CJO;
$mypage = 'shop';

$delivery_method = $CJO['ADDON']['settings'][$mypage]['DELIVERY_METHOD'];

// declare REQUEST-variables (name and type)
$deliverer_id 			= cjo_request('deliverer_id', 'int');
$zone_id 				= cjo_request('zone_id', 'int');
$deliverer_zone_id 		= cjo_request('deliverer_zone_id', 'int');
$deliverer_details_id 	= cjo_request('deliverer_details_id', 'int');
$deliverer 				= cjo_request('deliverer', 'string', '', true, 'cjoShopExtension::maskPipe');
$deliverer_zone 		= cjo_request('deliverer_zone', 'string', '', true, 'cjoShopExtension::maskPipe');


// declare icons
$arrow 		= '<span></span>';
$lorry 		= '<img src="img/silk_icons/lorry.png" alt="'.$I18N_21->msg("shop_icon_lorry").'" />';
$world 		= '<img src="img/silk_icons/world.png" alt="'.$I18N_21->msg("shop_icon_world").'" />';

// navigation links
$links = '';
// redirect to deliver page
if (!empty($deliverer_zone_id)) {
	$links .= cjoAssistance::createBELink($deliverer,
	                                      array('function' 		=> '',
											  	'mode'			=> ''),
										  array());

	$links .= $arrow.cjoAssistance::createBELink($deliverer_zone,
												 array(	'function' 				=> '',
													  	'mode'					=> ''),
												 array());
}

// display links
if (!empty($links)) {
	echo '<div class="shop_deliverer_settings_path"><div class="container">'.$links.'</div></div>';
}

// includes
switch($mode) {

	case 'zone':
	        include_once "shipping_zones.inc.php";
			break;
	case 'deliverer':
	        include_once "shipping_deliverer.inc.php";
			break;
}


/*****************************
 *****************************
 		 START PAGE
 *****************************
 *****************************/

if ($function == '') {

	//create deliver zones settings output
	$qry = "SELECT * FROM ".TBL_21_COUNTRY_ZONE;
	$list = new cjoList($qry, 'zone', 'ASC', '', 10);
	$list->setLabel($I18N_21->msg('shop_country_zone_settings'));
	$cols = array();
	// add button
	$cols['icon'] = new resultColumn(	'label',
									 	cjoAssistance::createBELink
									 	(
						 			 		'<img src="img/silk_icons/add.png" alt="'.$I18N_21->msg("shop_add_deliver_zone").'" />',
									 		array
									 		(
									 			'function' 	=> 'add',
									 	   		'mode' 	  	=> 'zone'
									 		),
									 		$list->getGlobalParams(),
									 		'title="'.$I18N_21->msg("shop_add_deliver_zone").'"'
									 	),
									 	'sprintf',
									 	'<img src="img/silk_icons/%s.png" alt="true" />'
									 );

	// button add new zone
	$cols['icon']->addCondition('label', '', $world);
	$cols['icon']->setHeadAttributes('class="icon"');
	$cols['icon']->setBodyAttributes('class="icon"');
	$cols['icon']->delOption(OPT_ALL);

	// add columns
	$cols['zone'] 	   = new resultColumn( 'zone',$I18N_21->msg('shop_deliver_zone'));
	$cols['zone']->setBodyAttributes('width="150"');
	$cols['zone']->delOption(OPT_ALL);
	$cols['countries'] = new resultColumn('countries', $I18N_21->msg('shop_countries'),
										  'call_user_func',
	 									   array('cjoAssistance::convertToFlags', array('%s')));
	$cols['countries']->delOption(OPT_ALL);

	// add column if delivery costs depend on order value
	if ($delivery_method == '0') {

		$cols['costs'] = new resultColumn('id', $I18N_21->msg('shop_delivery_costs'),'call_user_func',
									   array('cjoShopDelivery::getZoneDeliveryCosts', array('%s')));
	    $cols['costs']->setBodyAttributes('width="230"');
		$cols['costs']->delOption(OPT_ALL);
	}

	// button edit country zones
	$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
	$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
	$cols['edit']->setHeadAttributes('colspan="2"');
	$cols['edit']->setBodyAttributes('width="16"');

	// set GET-vars
	$cols['edit']->setParams(array (	'function' 	=> 'edit',
										'mode' 		=> 'zone',
										'zone_id' 	=> '%id%'));

	// button delete country zone
	$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N_21->msg("button_delete").'" />';
	$cols['delete'] = new staticColumn($img, NULL);
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');

	// set GET-vars
	$cols['delete']->setParams(array (	'function'	=> 'delete',
										'mode' 		=> 'zone',
										'zone_id' 	=> '%id%'));

	// add cols to list
	$list->addColumns($cols);
	$list->show(false);

	// stop execution here if delivery costs depend on order value
	if ($delivery_method == "0") return;

	// reset country list
	cjoAssistance::resetAfcVars();

	// create new list for deliverer settings output
	$sql = new cjoSql();
	$qry = "SELECT
				d.id AS id,
				d.deliverer AS deliverer,
				cz.zone AS deliverer_zone,
				cz.id AS zone_id,
				dz.id AS deliverer_zone_id
			FROM "
				.TBL_21_DELIVERER." d
			INNER JOIN ("
				.TBL_21_DELIVERER_ZONE." dz
				INNER JOIN "
					.TBL_21_COUNTRY_ZONE." cz
				ON cz.id=dz.zone_id)
			ON
				dz.deliverer_id=d.id";

	$list = new cjoList($qry, 'deliverer', 'ASC', '', 10);
	$list->setLabel( $I18N_21->msg('shop_deliverer_settings'));
	$cols = array();
	// add button
	$cols['icon'] = new resultColumn(	'label',
									 	cjoAssistance::createBELink
									 	(
						 			 		'<img src="img/silk_icons/add.png" alt="'.$I18N_21->msg("shop_add_deliverer").'" />',
									 		array
									 		(
									 			'function' 			=> 'edit',
									 			'mode' 				=> 'deliverer'
									 		),
									 		$list->getGlobalParams(),
									 		'title="'.$I18N_21->msg("shop_add_deliverer").'"'
									 	),
									 	'sprintf',
									 	'<img src="img/silk_icons/%s.png" alt="true" />'
									 );
	// button add new deliverer
	// directs to --> function = add, mode = deliverer,
	// file: settings_be_deliverer.inc.php
	$cols['icon']->addCondition('label', '', $lorry);
	$cols['icon']->setHeadAttributes('class="icon"');
	$cols['icon']->setBodyAttributes('class="icon"');
	$cols['icon']->delOption(OPT_ALL);

	// add columns
	$cols['deliverer'] = new resultColumn('deliverer', $I18N_21->msg('shop_deliverer'));
	$cols['deliverer']->setBodyAttributes('width="150"');
	$cols['deliverer']->delOption(OPT_ALL);
	// get all defined delivery zones for this deliverer
	$cols['zone'] = new resultColumn('deliverer_zone', $I18N_21->msg('shop_deliver_zones'));
	$cols['packages'] = new resultColumn('deliverer_zone_id', $I18N_21->msg('shop_deliverer_sizes'),
										 'call_user_func', array('cjoShopDelivery::getDelivererSizes', array('%s')));

	// button edit deliverer
	// directs to --> function = edit, mode = deliverer,
	// file: settings_be_deliverer.inc.php
	$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
	$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
	$cols['edit']->setHeadAttributes('colspan="2"');
	$cols['edit']->setBodyAttributes('width="16"');

	// set GET-vars
	$cols['edit']->setParams(array ('function' 			=> 'edit',
									'mode' 				=> 'deliverer',
									'deliverer_id' 		=> '%id%',
									'deliverer' 		=> '%deliverer%',
									'deliverer_zone_id' => '%deliverer_zone_id%',
									'zone_id'			=> '%zone_id%',
									'deliverer_zone'	=> '%deliverer_zone%',
									'zone_id'			=> '%zone_id%'));

	// button delete deliverer
	// directs to --> function = delete, mode = deliverer
	// file: settings-be_zones.inc.php
	$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button").'" />';
	$cols['delete'] = new staticColumn($img, NULL);
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');

	// set GET-vars
	$cols['delete']->setParams(array (	'function' 			=> 'delete',
										'mode' 				=> 'deliverer',
										'deliverer_zone_id' => '%deliverer_zone_id%',
										'deliverer'			=> '%deliverer%',
										'deliverer_id'		=> '%id%' ));

	// add cols to list
	$list->addColumns($cols);
	$list->show(false);

} // end if function = ''
