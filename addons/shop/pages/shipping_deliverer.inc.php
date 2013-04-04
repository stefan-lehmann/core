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

global $CJO;
$addon = 'shop';
$currency_sign = $CJO['ADDON']['settings'][$addon]['CURRENCY']['DEFAULT_SIGN'];

// stop execution if delivery costs depend on order value
if ($CJO['ADDON']['settings'][$addon]['DELIVERY_METHOD'] == "0") return;

// reset all lists and forms
cjoAssistance::resetAfcVars();


if ($function == 'delete') {

	// clean tbl_21_deliverer_details
	$delete = new cjoSql();
	$delete->setTable(TBL_21_DELIVERER_DETAILS);
	$delete->setWhere("deliverer_zone_id='".$deliverer_zone_id."'");
	$delete->Delete();
	$delete->flush();

	// clean tbl_21_deliverer_zone
	$delete->setTable(TBL_21_DELIVERER_ZONE);
	$delete->setWhere("id='".$deliverer_zone_id."'");
	$delete->Delete();
	$delete->flush();

	// check if there are more zones defined for deliverer
	$sql = new cjoSql();
	$qry = "SELECT COUNT(*) AS count FROM ".TBL_21_DELIVERER_ZONE." WHERE deliverer_id='".$deliverer_id."'";
	$result = array_shift($sql->getArray($qry));

	// delete deliverer if there are no more zones defined
	if ($result['count'] == 0) {
		$delete->setTable(TBL_21_DELIVERER);
		$delete->setWhere("id='".$deliverer_id."'");
		$delete->Delete();
	}
	// redirect to start page
	cjoUrl::redirectBE(array(		'function' 	=> '',
											'mode'		=> '' ));
} // end if function = delete


// get data from db
$qry = "SELECT
			dd.id AS deliverer_details_id,
			dd.deliverer_zone_id AS deliverer_zone_id,
			dd.size_in_units AS size_in_units,
			dd.size AS size,
			dd.costs AS costs,
			d.tax AS tax
		FROM "
			.TBL_21_DELIVERER_DETAILS." dd
		INNER JOIN ("
				.TBL_21_DELIVERER_ZONE." dz
					INNER JOIN "
						.TBL_21_DELIVERER." d
					ON
					d.id=dz.deliverer_id)
		ON
			dz.id=dd.deliverer_zone_id
		WHERE
			deliverer_zone_id='".$deliverer_zone_id."'
		ORDER BY
			size_in_units";

$sql = new cjoSql();
$results = $sql->getArray($qry);

// build dataset
$dataset = array('deliverer' 		=> $deliverer,
				 'deliverer_zone' 	=> $deliverer_zone,
				 'tax'				=> $results[0]['tax']);

// get amount of defined packages
$package_count = count($results);

for($i = 0; $i < $package_count; $i++) {
	$dataset['deliverer_details_id_'.$i] = !empty($results[$i]['deliverer_details_id']) ? $results[$i]['deliverer_details_id'] : -1;
	$dataset['size_'.$i]				 = $results[$i]['size'];
	$dataset['size_in_units_'.$i]		 = $results[$i]['size_in_units'];
	$dataset['costs_'.$i]				 = $results[$i]['costs'];
	$dataset['delete_'.$i]				 = 0;
}

//create formular
$form = new cjoForm($addon.'_'.$subpage.'_'.$mode.'_form');
$form->setEditMode(true);

$hidden['mode'] = new hiddenField('mode');
$hidden['mode']->setValue($mode);
$hidden['zone_id'] = new hiddenField('zone_id');
$hidden['zone_id']->setValue($zone_id);

if ($function == 'edit') {
	$hidden['deliverer_id'] = new hiddenField('deliverer_id');
	$hidden['deliverer_id']->setValue($deliverer_id);

	$hidden['deliverer_zone_id'] = new hiddenField('deliverer_zone_id');
	$hidden['deliverer_zone_id']->setValue($deliverer_zone_id);
}

// build static formular fields
$fields['deliverer'] = new textField('deliverer', cjoAddon::translate(21,'shop_deliverer'));
$fields['deliverer']->addValidator('notEmpty', cjoAddon::translate(21,"msg_deliverer_notEmpty"), false, false);
$fields['deliverer']->needFullColumn(true);
$fields['zone'] = new selectField('deliverer_zone', cjoAddon::translate(21,'shop_deliver_zone'),
								  array('size' => 1));

$qry = "SELECT zone FROM ".TBL_21_COUNTRY_ZONE;
$fields['zone']->addSqlOptions($qry);
$fields['zone']->needFullColumn(true);
$fields['tax'] 			= new textField('tax', cjoAddon::translate(21,'shop_tax'));
$fields['tax']->setFormat('call_user_func', array('cjoShopPrice::formatNumber', array('%s', true)));
$fields['tax']->activateSave(false);
$fields['tax']->addValidator('notEmpty', cjoAddon::translate(21,"msg_package_tax_notEmpty"), false, false);
$fields['tax']->addValidator('isRegExp', cjoAddon::translate(21,"msg_package_tax_no_price"), array('expression' => '!^\d+([,\.]\d+)?$!'), false);
$fields['tax']->addAttribute('style', 'width: 49px; text-align: right');
$fields['tax']->setNote('%');
$fields['tax']->needFullColumn(true);

// build dynamic formular fields
for($i = 0; $i < ((count($dataset) - 3) / 5); $i++) {

	$fields['headline_'.$i] = new headlineField(($i+1).'. '.cjoAddon::translate(21,'shop_package'));

    $fields['size_'.$i] = new textField('size_'.$i, cjoAddon::translate(21,'shop_name'));
    $fields['size_'.$i]->activateSave(true);
    $fields['size_'.$i]->addAttribute('style', 'width: 100px; text-align: left');
    $fields['size_'.$i]->addValidator('notEmpty', cjoAddon::translate(21,"msg_name_notEmpty"), false, false);

	$fields['delete_'.$i] = new checkboxField('delete_'.$i, '&nbsp;');
    $fields['delete_'.$i]->activateSave(true);
	$fields['delete_'.$i]->addBox(cjoAddon::translate(21,'shop_delete'), 1);

    $fields['costs_'.$i] = new textField('costs_'.$i, cjoAddon::translate(21,'shop_netto_price'));
    $fields['costs_'.$i]->setFormat('call_user_func', array('cjoShopPrice::toCurrency', array('%s', true)));
    $fields['costs_'.$i]->activateSave(true);
    $fields['costs_'.$i]->addValidator('notEmpty', cjoAddon::translate(21,"msg_package_costs_notEmpty"), false, false);
    $fields['costs_'.$i]->addValidator('isPrice', cjoAddon::translate(21,"msg_package_costs_no_price"), false, false);
	$fields['costs_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
	$fields['costs_'.$i]->setNote($currency_sign, 'style="width: auto!important"');

    $fields['size_in_units_'.$i] = new textField('size_in_units_'.$i, cjoAddon::translate(21,'shop_packing_units'));
    $fields['size_in_units_'.$i]->setFormat('call_user_func', array('cjoShopPrice::formatNumber', array('%s', true)));
    $fields['size_in_units_'.$i]->addValidator('notEmpty', cjoAddon::translate(21,"msg_packing_units_notEmpty"), false, false);
    $fields['size_in_units_'.$i]->addValidator('isRegExp', cjoAddon::translate(21,"msg_packing_units_no_number"), array('expression' => '/^\d+([,\.]\d+)?$/'), false);
    $fields['size_in_units_'.$i]->activateSave(true);
	$fields['size_in_units_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
    $fields['size_in_units_'.$i]->setHelp(cjoAddon::translate(21,'help_size_in_units'));
}

// final fieldset for adding a new package
$i++;

$fields['headline_'.$i] = new headlineField(cjoAddon::translate(21,'shop_package_add'), true);

$fields['size_'.$i] = new textField('size_'.$i, cjoAddon::translate(21,'shop_name'));
$fields['size_'.$i]->activateSave(true);
$fields['size_'.$i]->addAttribute('style', 'width: 100px; text-align: left');
$fields['size_'.$i]->needFullColumn(true);

$fields['costs_'.$i] = new textField('costs_'.$i, cjoAddon::translate(21,'shop_netto_price'));
$fields['costs_'.$i]->setFormat('call_user_func', array('cjoShopPrice::toCurrency', array('%s', false)));
$fields['costs_'.$i]->activateSave(true);
$fields['costs_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
$fields['costs_'.$i]->setNote($currency_sign, 'style="width: auto!important"');

$fields['size_in_units_'.$i] = new textField('size_in_units_'.$i, cjoAddon::translate(21,'shop_packing_units'));
$fields['size_in_units_'.$i]->setFormat('call_user_func', array('cjoShopPrice::formatNumber', array('%s', false)));
$fields['size_in_units_'.$i]->addValidator('isRegExp', cjoAddon::translate(21,"msg_packing_units_no_number"),array('expression' => '/^\d+([,\.]\d+)?$/'), false);
$fields['size_in_units_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
$fields['size_in_units_'.$i]->setHelp(cjoAddon::translate(21,'help_size_in_units'));

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_save_button', cjoI18N::translate('button_save'), true, 'img/silk_icons/disk.png');
$fields['button']->addButton('cjoform_update_button', cjoI18N::translate('button_update'), true, 'img/silk_icons/tick.png');
$fields['button']->addButton('cjoform_cancel_button', cjoI18N::translate('button_cancel'), true, 'img/silk_icons/cancel.png');
$fields['button']->needFullColumn(true);

$section_headline = $function == 'add'
				  ? cjoAddon::translate(21,"shop_new_deliverer_zone")
				  : cjoAddon::translate(21,"shop_edit_deliverer_settings");
$section = new cjoFormSection($dataset, $section_headline, array('id' => $deliverer_zone_id), array('35%', '65%'));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

// redirect on event
if ($form->validate()) {

	if (cjo_post('cjoform_cancel_button')) {
		cjoUrl::redirectBE(array('function' 	=> '', 'mode' => ''));
	}
	elseif(cjo_post('cjoform_save_button')){
		$redirect = cjoShopDeliverySettings::saveSettings();
		if($redirect !== false){
			cjoUrl::redirectBE(array('function' 	=> '',
											'mode'		=> '',
											'msg'		=> 'msg_data_saved'));
		}
	}
	else {

		$redirect = cjoShopDeliverySettings::saveSettings();

		if ($redirect !== false) {
			cjoUrl::redirectBE($redirect);
		} elseif (cjoMessage::hasError(cjoAddon::translate(21,'shop_deliverer_zone_exists'))) {
			$fields['deliverer']->addAttribute('class', 'invalid');
    		$fields['zone']->addAttribute('class', 'invalid');
		}
		$form->valid_master = false;
	}
}
$form->show(false);
