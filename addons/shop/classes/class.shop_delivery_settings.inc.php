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
 * This class holds methods to rewrite delivery data,
 * e.g. deliverer names, package sizes, deliverer zones.
 */

class cjoShopDeliverySettings {
        
    protected static $mypage = 'shop';

	/**
	 * This method will be called by the
	 * shipping_deliverer.inc.php-script,
	 * in order to save new data or changes.
	 *
	 * @return bool/sting - error message or true on success
	 */
	public static function saveSettings() {

		global $I18N_21;
		$rewrote_deliverer_zone = true;
		$to_save = array();
		$posted = $_REQUEST;
		$tax = empty($_REQUEST['tax']) ? 0 : $_REQUEST['tax'];

		// get data to be saved
		foreach($posted as $key => $value) {

			$index = str_replace('_','', strrchr($key, '_'));
			if (is_numeric($index)) {

				$key = substr($key, 0, strrpos($key, '_'));
				$to_save[$index][$key] = $value;
			}
			else{
				$$key = $value;
			}
		}

		// remove data from array that shall be deleted
		foreach($to_save as $key => $values) {

			if (isset($values['delete'])) {
				unset($to_save[$key]);
				continue;
			}
			foreach($values as $value) {
				if (empty($value)) {
					unset($to_save[$key]);
					break;
				}
			}
			if (isset($to_save[$key])) $to_save[$key]['tax'] = $tax;
		}

		if (empty($deliverer)) {
			cjoMessage::addError($I18N_21->msg('err_shop_no_deliverer'));
			return false;
		}

		if (empty($deliverer_zone)) {
			cjoMessage::addError($I18N_21->msg('err_shop_no_zone'));
			return false;
		}

		// check if deliverer or deliver zone has changed
		$sql = new cjoSql();
		$qry = "SELECT id,deliverer FROM ".TBL_21_DELIVERER." WHERE id='".$deliverer_id."' LIMIT 1";
		$old_deliverer = $sql->getArray($qry);
		$qry = "SELECT id,zone FROM ".TBL_21_COUNTRY_ZONE." WHERE id='".$zone_id."' LIMIT 1";
		$old_zone = $sql->getArray($qry);

		$redirect_params = array(	'function' 				=> 'edit',
									'mode' 					=> 'deliverer',
									'deliverer_zone_id' 	=> $deliverer_zone_id,
									'deliverer'				=> $deliverer,
									'deliverer_id'			=> $deliverer_id,
									'zone_id'				=> $zone_id,
									'deliverer_zone'		=> $deliverer_zone,
		 							'msg'					=> 'msg_data_saved');

		// if deliverer or zone has  changed
		if ($old_deliverer[0]['deliverer'] != $deliverer ||
		    $old_zone[0]['zone'] != $deliverer_zone) {

		   	$redirect_params = self::rewriteDelivererZone($deliverer, $deliverer_zone, $deliverer_zone_id);
		}

		// rewrite package data or return error
		if ($redirect_params !== false) {
			self::rewriteDelivererDetails($deliverer_zone_id, $to_save, $deliverer_id, $tax);
			return $redirect_params;
		}
		else {
			return false;
		}

	} // end function saveSettings

	/**
	 * Checks if the posted combination of deliverer
	 * and zone already exists. If not a new
	 * deliverer zone will be created.
	 *
	 * @param string $deliverer - the deliverer name
	 * @param string $zone - the zone name
	 * @param by_ref string $deliverer_zone_id
	 * @return bool - success information
	 */
	private static function rewriteDelivererZone($deliverer, $zone, &$deliverer_zone_id) {

		global $I18N_21;

		$deliverer_id = self::getId(TBL_21_DELIVERER, 'deliverer', $deliverer);
		$zone_id	  = self::getId(TBL_21_COUNTRY_ZONE, 'zone', $zone);

		$sql = new cjoSql();
		$qry = "SELECT
				 	*
				FROM "
					.TBL_21_DELIVERER_ZONE."
				WHERE
					zone_id='".$zone_id."'
				AND
					deliverer_id='".$deliverer_id."'
				LIMIT 1";
		$results = $sql->getArray($qry);

		if (!empty($results)) {
			cjoMessage::addError($I18N_21->msg('shop_deliverer_zone_exists'));
			return false;
		}

		return self::updateDelivererAndZone($deliverer, $deliverer_id, $zone_id, $deliverer_zone_id, $zone);

	} // end function rewriteDelivererZone

	/**
	 * Returns the id belonging to an overgiven value.
	 * Will return the first match.
	 *
	 * @param string $table - the table to get the id from
	 * @param string $field - the field name for the where clause
	 * @param string $value - the value of the field
	 * @return string/int - the id if one was found else ''.
	 */
	private static function getId($table, $field, $value) {

		$sql = new cjoSql();
		$qry = "SELECT id FROM ".$table." WHERE ".$field."='".$value."' LIMIT 1";
		$sql->setQuery($qry);

		$return = array_shift($sql->getArray($qry));
		return $return['id'];
	}

	/**
	 * Rewrites deliverer name an deliverer zone if
	 * required.
	 *
	 * @param string $deliverer - the deliverer name
	 * @param by_ref string $deliverer_id
	 * @param string $zone_id
	 * @param by_ref string $deliverer_zone_id
	 * @return bool - success or error
	 */
	private static function updateDelivererAndZone($deliverer, &$deliverer_id, &$zone_id, &$deliverer_zone_id, $zone) {

		global $I18N;
		$err_msg = $I18N->msg('err_data_not_saved');

		// if a new deliverer shall be added
		if ($deliverer_id == '' && $zone_id != '') {

			$insert = new cjoSql();
			$insert->setTable(TBL_21_DELIVERER);
			$insert->setValue('deliverer', $deliverer);
			$insert->Insert();

			$error = $insert->getError();

			if (!empty($error)) {
				cjoMessage::addError($err_msg);
				return false;
			}
			else {
				$deliverer_id = $insert->getLastId();
			}

			$new_deliverer = true;
		}

		// add a new deliverer zone or
		// update the old
		if ($deliverer_zone_id == '') {

			$insert = new cjoSql();
			$insert->setTable(TBL_21_DELIVERER_ZONE);
			$insert->setValue('deliverer_id', $deliverer_id);
			$insert->setValue('zone_id', $zone_id);
			$insert->Insert();

			$error = $insert->getError();
			if (!empty($error)) {
				cjoMessage::addError($err_msg);
				return false;
			}
			else {
				$deliverer_zone_id = $insert->getLastId();
			}

		}
		else {

			$update = new cjoSql();
			$update->setTable(TBL_21_DELIVERER_ZONE);
			$update->setWhere("id='".$deliverer_zone_id."'");
			$update->setValue('zone_id', $zone_id);
			$update->setValue('deliverer_id', $deliverer_id);
			$update->Update();

			$error = $update->getError();
			if (!empty($error)) {
				cjoMessage::addError($err_msg);
				return false;
			}

			if ($new_deliverer) return true;

			$update->flush();
			$update->setTable(TBL_21_DELIVERER);
			$update->setWhere("id='".$deliverer_id."'");
			$update->setValue('deliverer', $deliverer);
			$update->Update();

			$error = $update->getError();
			if (!empty($error)) {
				cjoMessage::addError($err_msg);
				return false;
			}
		}

		$redirect_params = array(	'function' 				=> 'edit',
									'mode' 					=> 'deliverer',
									'deliverer_zone_id' 	=> $deliverer_zone_id,
									'deliverer'				=> $deliverer,
									'deliverer_id'			=> $deliverer_id,
									'zone_id'				=> $zone_id,
									'deliverer_zone'		=> $zone,
		 							'msg'					=> 'msg_data_saved');

		return $redirect_params;

	} // end function updateDelivererAndZone

	/**
	 * Rewrites package data.
	 *
	 * @param string $deliverer_zone_id
	 * @param array $to_save - array with data to be saved
	 * @return bool - success or error
	 */
	private static function rewriteDelivererDetails($deliverer_zone_id, $to_save, $deliverer_id, $tax) {

		global $I18N,$I18N_21;
		$err_msg = $I18N->msg('err_data_not_saved');

		// delete old entries
		$delete = new cjoSql();
		$delete->setTable(TBL_21_DELIVERER_DETAILS);
		$delete->setWhere("deliverer_zone_id='".$deliverer_zone_id."'");
		$delete->Delete();

		$error = $delete->getError();
		if (!empty($error)) {
			cjoMessage::addError($err_msg);
			return false;
		}

		// write new data
		foreach($to_save as $key => $value) {

			$insert = new cjoSql();
			$insert->setTable(TBL_21_DELIVERER_DETAILS);
			$insert->setValue('deliverer_zone_id', $deliverer_zone_id);
			$insert->setValue('size', $value['size']);
			$insert->setValue('size_in_units', cjoShopPrice::convToFloat($value['size_in_units']));
			$insert->setValue('costs', cjoShopPrice::convToFloat($value['costs']));
			$insert->Insert();

			$error = $insert->getError();
			if (!empty($error)) {
				cjoMessage::addError($err_msg);
				return false;
			}

			$update = new cjoSql();
			$update->setTable(TBL_21_DELIVERER);
			$update->setWhere("id='".$deliverer_id."'");
			$update->setValue('tax', !empty($tax) ? cjoShopPrice::convToFloat($tax) : 0);
			$update->Update();

			$error = $update->getError();
			if (!empty($error)) {
				cjoMessage::addError($err_msg);
				return false;
			}

		}

		return true;

	} // end function rewriteDelivererDetails

	/**
	 * This methods saves the delivery costs settings
	 * if they depend on order value.
	 *
	 * @return bool/string - error or the parameters for redirection
	 */
	public static function saveZoneSettings() {

		global $CJO;

		$delivery_method = $CJO['ADDON']['settings'][self::$mypage]['DELIVERY_METHOD'];
		$redirect_params = array('function' => 'edit', 'mode' => 'zone');

		$requested = $_REQUEST;
		$to_save = array();

		foreach($requested as $key => $value) {

			$index = str_replace('_','', strrchr($key, '_'));
			if (is_numeric($index)) {
				$key = substr($key, 0, strrpos($key, '_'));
				$to_save[$index][$key] = $value;
			}
			elseif ($index === 'new') {
				$key = substr($key, 0, strrpos($key, '_'));
				$to_save['new'][$key] = $value;
			}
			else {
				$$key = $value;
			}
		}

		// remove data to delete from array to_save
		foreach($to_save as $key => $value) {
			if (isset($value['delete'])) {
				unset($to_save[$key]);
				continue;
			}
			foreach($value as $val) {
				if (empty($val) && ($val != 0 || $key == 'new')) {
					unset($to_save[$key]);
					break;
				}
			}
		}
		$redirect_params['zone_id'] = $zone_id;

		// update delivery zone data
		$sql = new cjoSql();
		$sql->setTable(TBL_21_COUNTRY_ZONE);
		$sql->setValue('zone', $zone);

		if (!empty($countries)) {
			$countries = implode('|', $countries);
		}
		$sql->setValue('countries', $countries);

		if (!empty($zone_id)) {
			$sql->setWhere("id='".$zone_id."'");
			$sql->Update();
		}
		else {
			$sql->Insert();
			$zone_id = $sql->getLastId();
			$redirect_params['zone_id'] = $zone_id;
		}

		if ($sql->getError() != '') {
			cjoMessage::addError($I18N->msg('err_data_not_saved'));
			return false;
		}

		// return if delivery costs depend on size/weight
		if ($delivery_method != '0') return $redirect_params;

		// write the delivery costs for this zone
		if (cjoShopDeliverySettings::writeDeliveryCosts($zone_id, $to_save)) {
			return $redirect_params;
		}
		else {
			return true;
		}

	} // end function saveZoneSettings

	/**
	 * Writes the delivery costs to db if they depend on
	 * order value.
	 * @param string $zone_id - the id of the zone for that
	 * 							data shall be written
	 * @param array $to_save  - the data to be saved
	 * @return bool			  - error or success
	 */
	private static function writeDeliveryCosts($zone_id, $to_save) {

		global $I18N;

		// delete all entries for this zone
		$delete = new cjoSql();
		$delete->setTable(TBL_21_DELIVERY_COSTS);
		$delete->setWhere("zone_id='".$zone_id."'");
		$delete->Delete();

		if ($delete->getError() != '') {
			cjoMessage::addError($I18N->msg('err_data_not_saved'));
			return false;
		}

		// write new data
		foreach($to_save as $value) {

			$insert = new cjoSql();
			$insert->setTable(TBL_21_DELIVERY_COSTS);
			$insert->setValue('zone_id', $zone_id);
			foreach($value as $key => $val) {
				$insert->setValue($key, cjoShopPrice::convToFloat($val));
			}
			$insert->insert();

			if ($insert->getError() != '') {
				cjoMessage::addError($I18N->msg('err_data_not_saved'));
				return false;
			}
		}
		return true;
	}

} // end class cjoShopDeliverySettings
