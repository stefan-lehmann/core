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
 * <strong>Class shop_zone</strong>
 * This class provides method to output several country flags
 *
 * @var $zone (string)
 * $countries (array(string))
 */
class cjoShopZone {

	private $zone;
	private $countries;

	/**
	 * Constructor, parameter must be a resultset or null.
	 * @param $result (resultset/null)
	 */
	function __construct($result = null) {

		if ($result != null) {
			$this->zone = $result['zone'];
			$countries = explode('|', $result['countries']);
		}
		$this->countries = array();
		$this->zone = '';
	}

	public function setZone($zone) {
		$this->zone = $zone;
	}

	public function addCountry($country) {
		foreach($this->countries as $val)
			if ($country == $val) return;
		$this->countries[] = $country;
	}

	public function getZone() {
		return $this->zone;
	}

	public function removeCountry($country) {
		foreach($this->countries as $val) {
			if ($country == $val) $val = '';
		}
	}

	public function countriesToString() {
		return implode('|', $this->countries);
	}

	/**
	 * Get the full names of countries.
	 * @param $id (int)
	 * @return $return (array)
	 */
	public static function getCountryNames($id) {
		// get already used countries as array
		$sql = new cjoSql();
		$qry = "SELECT countries FROM ".TBL_21_COUNTRY_ZONE." WHERE id <> ".$id;

		// get countries and convert them to one string
		$results = $sql->getArray($qry);
		// convert resultset into string
		$resultstring = '';
		foreach($results as $result)
			$resultstring .= $result['countries'].'|';

		// remove last pipe
		$resultstring = substr($resultstring, 0, strlen($resultstring) - 1);

		// convert to array
		$result = explode('|', $resultstring);
		$sql->flush();

		// delete countries already in use from names
		$names = array();
		$names = cjo_get_country_codes();
		foreach($names as $key => $country) {
			if (in_array($key, $result)) {
				continue;
			} else {
				$return[$key] = $country;
			}
		}
		return $return;
	}

	/**
	 * Saves a new country into the countries string.
	 * @param $country (string)
	 * @return string
	 */
	public function save($country) {
		$names = cjo_get_country_codes();

		foreach($names as $key => $val) {
			if($val == $country) {
				$code = $key;
				break;
			}
		}
		$this->addCountry($key);
		return $this->countriesToString();
	}

	/**
	 * Get all zones as an array.
	 * @param $id (int)
	 * @return array(string)
	 */
	public static function getAvailableZones($id) {

		// get id's of already used zones
		$sql = new cjoSql();
		$qry = "SELECT zone_id FROM ".TBL_21_DELIVERER_ZONE." WHERE deliverer_id = ".$id;
		$results = $sql->getArray($qry);
		$sql->flush();
		$or = '';

		if (!empty($results)) {
			foreach($results as $result) {
				if (empty($or)) {
					$or = ' WHERE NOT (id = '.$result['zone_id'];
				} else {
					$or .= ' OR id = '.$result['zone_id'];
				}
			}
			$or .= ')';
		}
		// get name and id of still available zones
		$qry = "SELECT zone, id FROM ".TBL_21_COUNTRY_ZONE.(empty($or) ? '' : $or);
		$results = $sql->getArray($qry);
		$sql->flush();
		return $results;
	}

} // end class cjoShopZone
