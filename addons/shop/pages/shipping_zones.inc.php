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
$mypage = 'shop';
$delivery_method = $CJO['ADDON']['settings'][$mypage]['DELIVERY_METHOD'];
$currency_sign = $CJO['ADDON']['settings'][$mypage]['CURRENCY']['DEFAULT_SIGN'];

// reset all lists and forms
cjoAssistance::resetAfcVars();


// delete country zone
// directs to --> function = '', mode = '',
// file settings_be.inc.php
if ($function == 'delete') {
	// get deliverer_zone_id's to clear tbl_21_deliverer_details from zone
	$sql = new cjoSql();
	$qry = "SELECT id FROM ".TBL_21_DELIVERER_ZONES." WHERE zone_id = ".$zone_id;
	$results = $sql->getArray($qry);
	$sql->flush();

	// if zone appears in tbl_21_deliverer_details delete all entries
	if (isset($results)) {

		$or = '';
		foreach($results as $result) {
			if (empty($or)) {
				$or = " WHERE deliverer_zone_id = '".$result['id']."'";
			} else {
				$or .= " OR deliverer_zone_id = '".$result['id']."'";
			}
		}

		if (!empty($or)) {
			$qry = "DELETE FROM ".TBL_21_DELIVERER_DETAILS.$or;
			$sql->setQuery($qry);
			$sql->flush();
		}
	}

	// delete zone in tbl_21_deliverer_zones
	$qry = "DELETE FROM ".TBL_21_DELIVERER_ZONES." WHERE zone_id = ".$zone_id;
	$sql->setQuery($qry);
	$sql->flush();

	// delete zone from tbl_21_delivery_costs
	$qry = "DELETE FROM ".TBL_21_DELIVERY_COSTS." WHERE zone_id=".$zone_id;
	$sql->setQuery($qry);
	$sql->flush();

	// delete zone
	$qry = "DELETE FROM ".TBL_21_COUNTRY_ZONE." WHERE id = ".$zone_id;
	$sql->setQuery($qry);
	$sql->flush();

	// redirect to previous page
	cjoAssistance::redirectBE(array('function' => '', 'mode' => ''));
} // end if function = delete



// add countries to selected zone
// directs to --> function = '', mode = '',
// file: settings_be.inc.php
if ($function == 'edit' || $function = 'add') {

	//create formular
    $form = new cjoForm();
    // show update button only when zone_id is set
    $form->setEditMode(false);

    // add hidden fields for GET-vars
    $hidden['mode'] = new hiddenField('mode');
	$hidden['mode']->setValue($mode);
	$hidden['zone_id'] = new hiddenField('zone_id');
	$hidden['zone_id']->setValue($zone_id);

    // fields
    $fields['zone'] = new textField('zone', $I18N_21->msg('shop_zone_name'));
    $fields['zone']->addValidator('notEmpty', $I18N_21->msg("msg_name_notEmpty"), false, false);
	$fields['zone']->needFullColumn(true);

    $fields['new_country'] = new selectField('countries', $I18N_21->msg('shop_edit_countries'));
    $fields['new_country']->setMultiple(true);
    $fields['new_country']->addAttribute('size', '20');
    $fields['new_country']->addValidator('notEmpty', $I18N_21->msg("msg_country_name_notEmpty"), false, false);
	$fields['new_country']->needFullColumn(true);

    // add select options
    foreach(cjoShopZone::getCountryNames($zone_id) as $key=>$country) {
    	$fields['new_country']->addOption($country, $key);
    	$fields['new_country']->disableOption($key);
    }

    if ($delivery_method == '0') {

        $count = 0;
        foreach(cjoShopDelivery::getZoneDeliveryCosts($zone_id, false) as $value){

        	$i = $value['id'];
            $count++;

    		$fields['headline_'.$i] = new readOnlyField('headline_'.$i, '', array('class' => 'formheadline'));
		    $fields['headline_'.$i]->setValue($count.'. '.$I18N_21->msg('shop_delivery_costs'));
    		$fields['headline_'.$i]->needFullColumn(true);

    	    $fields['order_value_'.$i] = new textField('order_value_'.$i,$I18N_21->msg('shop_be_order_value'));
    		$fields['order_value_'.$i]->setValue(cjoShopPrice::toCurrency($value['order_value'], true));
    		$fields['order_value_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
			$fields['order_value_'.$i]->addValidator('notEmpty', $I18N_21->msg("msg_package_costs_notEmpty"),false, false);
	    	$fields['order_value_'.$i]->addValidator('isPrice', $I18N_21->msg("msg_package_costs_no_price"),false, false);
    	    $fields['order_value_'.$i]->setNote($currency_sign, 'style="width: auto!important"');

    	    $fields['delete_'.$i] = new checkboxField('delete_'.$i, '&nbsp;');
    		$fields['delete_'.$i]->addBox( $I18N_21->msg('shop_delete'), 1);
    		$fields['delete_'.$i]->setHelp($I18N_21->msg('shop_help_edit_zone_costs'), 'style="float: right!important"');

    		$fields['costs_'.$i] = new textField('costs_'.$i, $I18N_21->msg('shop_netto_price'));
    		$fields['costs_'.$i]->setValue(cjoShopPrice::toCurrency($value['costs'], true));
    		$fields['costs_'.$i]->setNote($currency_sign, 'style="width: auto!important"');
    		$fields['costs_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
    		$fields['costs_'.$i]->addValidator('notEmpty', $I18N_21->msg("msg_package_costs_notEmpty"), false, false);
	    	$fields['costs_'.$i]->addValidator('isPrice', $I18N_21->msg("msg_package_costs_no_price"), false, false);

	    	$fields['tax_'.$i] = new textField('tax_'.$i, $I18N_21->msg('shop_tax'));
	    	$fields['tax_'.$i]->setValue(cjoShopPrice::formatNumber($value['tax'], true));
    		$fields['tax_'.$i]->setNote('%','style="width: auto!important"');
    		$fields['tax_'.$i]->addAttribute('style', 'width: 49px; text-align: right');
    		$fields['tax_'.$i]->addValidator('notEmpty', $I18N_21->msg("msg_package_costs_notEmpty"),false, false);
	    	$fields['tax_'.$i]->addValidator('isRegExp', $I18N_21->msg("msg_packing_units_no_number"),array('expression' => '/^\d+([,\.]\d+)?$/'), false);
    	}

		$fields['headline_add'] = new readOnlyField('headline_'.$i, '', array('class' => 'formheadline slide'));
	    $fields['headline_add']->setValue($I18N_21->msg('shop_delivery_costs_add'));
		$fields['headline_add']->needFullColumn(true);

    	$fields['order_value_new'] = new textField('order_value_new', $I18N_21->msg('shop_be_order_value'));
    	$fields['order_value_new']->addAttribute('style', 'width: 49px; text-align: right');
    	$fields['order_value_new']->setNote($currency_sign, 'style="width: auto!important"');

    	$fields['delte_new'] = new readOnlyField('delte_new', '&nbsp;');
    	$fields['delte_new']->setHelp($I18N_21->msg('shop_help_edit_zone_costs'));

    	$fields['costs_new'] = new textField('costs_new', $I18N_21->msg('shop_netto_price'));
    	$fields['costs_new']->setNote($currency_sign, 'style="width: auto!important"');
    	$fields['costs_new']->addAttribute('style', 'width: 49px; text-align: right');

    	$fields['tax_new'] = new textField('tax_new', $I18N_21->msg('shop_tax'));
    	$fields['tax_new']->setNote('%','style="width: auto!important"');
    	$fields['tax_new']->addAttribute('style', 'width: 49px; text-align: right');
    }

    $fields['button'] = new buttonField();
	$fields['button']->addButton('cjoform_save_button', $I18N->msg('button_save'), true, 'img/silk_icons/disk.png');
    $fields['button']->addButton('cjoform_update_button', $I18N->msg('button_update'), true, 'img/silk_icons/tick.png');
	$fields['button']->addButton('cjoform_cancel_button', $I18N->msg('button_cancel'), true, 'img/silk_icons/cancel.png');
	$fields['button']->needFullColumn(true);

	$msg = ($function == 'add') ? $I18N_21->msg('shop_add_country_zone') : $I18N_21->msg('shop_edit_country_zone');

    // add to form
    $section = new cjoFormSection(TBL_21_COUNTRY_ZONE, $msg, array('id' => $zone_id), array('35%', '65%'));
    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);

    // redirect to previous page
    if ($form->validate()) {
        
		if (cjo_post('cjoform_cancel_button')) {
			cjoAssistance::redirectBE(array('function' => '', 'mode' => ''));
		}
		elseif(cjo_post('cjoform_save_button')) {
		    $redirect = cjoShopDeliverySettings::saveZoneSettings();
			if ($redirect !== false) {
				cjoAssistance::redirectBE(array('function' => '', 'mode' => '', 'msg' => 'msg_data_saved'));
			}
		}
		else {
			$redirect = cjoShopDeliverySettings::saveZoneSettings();
			if ($redirect != false) {
				$redirect['msg'] = 'msg_data_saved';
				cjoAssistance::redirectBE($redirect);
			}
			$form->valid_master = false;
		}
	}

	$form->show(false);

} // end if function = edit
