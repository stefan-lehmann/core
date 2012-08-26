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
 * <strong>Class cjoShopAddress</strong>
 * This class contains methods for saving and manipulating
 * address data.
 *
 * @var string $street
 * @var string $street
 * @var string $postalcode
 * @var string $place
 * @var string $po_box
 * @var string $country
 */

class cjoShopAddress
{
	protected $street;
	protected $street_nr;
	protected $postal_code;
	protected $place;
	protected $po_box;
	protected $country;

	/**
	 * Creates an empty object if $value is null.
	 * Else $value has to be of the type
	 * created by toString() or a resultset.
	 *
	 * @param mixed $value, default=null
	 */
	function __construct($value = NULL) {

		if (is_array($value)) {
			$this->street 		= $value['street'];
			$this->street_nr 	= $value['street_nr'];
			$this->postal_code 	= $value['postal_code'];
			$this->place 		= $value['place'];
			$this->po_box 		= $value['po-box'];
			$this->country 		= $value['country'];
		}
		elseif ($value != NULL) {
			$arr = explode("|", $value);
			$this->street 		= $arr[0];
			$this->street_nr 	= $arr[1];
			$this->postal_code 	= $arr[2];
			$this->place 		= $arr[3];
			$this->country		= $arr[4];
			$this->po_box 		= $arr[5];
		}
	}

	// set methods
	public function setStreet($street)	{	$this->street = $street;	}
	public function setStreetNr($num)	{	$this->street_nr = $num;	}
	public function setPostalCode($poc)	{	$this->postal_code = $poc;	}
	public function setPlace($place)	{	$this->place = $place;		}
	public function setPoBox($pob)		{	$this->po_box = $pob;		}
	public function setCountry($country){	$this->country = $country;	}

	// get methods
	public function getStreet()		{	return $this->street;		}
	public function getStreetNr()	{	return $this->street_nr;	}
	public function getPostalCode()	{	return $this->postal_code;	}
	public function getPlace()		{	return $this->place;		}
	public function getPoBox()		{	return $this->po_box;		}
	public function getCountry()	{	return $this->country;		}

	/**
	 * Prepares object for saving.
	 * The Delimiter for variables is '|'.
	 *
	 * @return string $string
	 * @access public
	 */
	public function toString() {

		$chars = array('|', '~');
		$string 	 = 	    str_replace($chars, '', $this->street);
		$string 	.= 	'|'.str_replace($chars, '', $this->street_nr);
		$string 	.= 	'|'.str_replace($chars, '', $this->postal_code);
		$string 	.= 	'|'.str_replace($chars, '', $this->place);
		$string 	.= 	'|'.str_replace($chars, '', $this->country);
		$string 	.= 	'|'.str_replace($chars, '', $this->po_box);
		return $string;
	}

	/**
	 * Prepares object for output.
	 *
	 * @return string $string
	 * @access public
	 */
	public function out() {

		global $I18N_21;

	    $string 	 = 	  	    $this->street;
		$string 	.= 	 	" ".$this->street_nr;
		$string 	.= 	"\r\n".$this->postal_code;
		$string 	.= 	 	" ".$this->place;
		$string 	.= 	"\r\n".$this->country;
		if($this->po_box != '')
		$string 	.= 	"\r\n".$I18N_21->msg("shop_po_box")." ".$this->po_box;
		return $string;
	}

	/**
	 * This method is needed when displaying the order table in the backend.
	 * Return the full adress string or a part of it.
	 *
	 * @param int $id
	 * @param int $length - the length of the return string
	 * @return string
	 * @access public
	 */
	public static function addressOut($id, $length = -1) {

		$sql = new cjoSql();
		$qry = "SELECT address1 FROM ".TBL_21_ORDERS." WHERE id = ".$id;
		$sql->setQuery($qry);
		$adr = new cjoShopAddress($sql->getValue('address1', 0));

		$return = $adr->out();
		if ($length != -1) {
			$return = $length < strlen($return) ? substr($return, 0, $length).'...' : $return;
		}
		return $return;
	}

} // end class cjoShopAddress


/**
 * This class holds more data then cjoShopAddress,
 * e.g. names, titles.
 *
 * @var string $title
 * @var string $firstname
 * @var string $name
 */
class cjoShopSupplyAddress extends cjoShopAddress {

	private $title;
	private $firstname;
	private $name;
	private $company;

	function __construct($string = null){

	    if (is_array($string)) {
	        parent::__construct($string);
			$this->title 		= $string['title'];
			$this->firstname 	= $string['firstname'];
			$this->name 		= $string['name'];
			$this->company 		= $string['company'];
			$this->po_box 		= $string['po-box'];
		}
		elseif ($string != null) {
			$adr = explode('~', $string);
			parent::__construct($adr[1]);
			$arr             = explode('|', $adr[0]);
			$this->title     = $arr[0];
			$this->firstname = $arr[1];
			$this->name      = $arr[2];
			$this->company   = $arr[3];
		}
	}

	// set methods
	public function setTitle($title)		{	$this->title = $title;		}
	public function setFirstname($name)		{	$this->firstname = $name;	}
	public function setName($name)			{	$this->name = $name;		}
	public function setCompany($comp)		{ 	$this->company = $comp;		}


	// get methods
	public function getTitle()				{	return $this->title;		}
	public function getName()				{	return $this->name;			}
	public function getFirstname()			{	return $this->firstname;	}
	public function getCompany()			{	return $this->company;		}
	public function getAddress()			{   return parent::toString();	}


	/**
	 * Prepares object for saving.
	 * The Delimiter for variables is '|'.
	 *
	 * @return string
	 * @access public
	 */
	public function toString() {

		$chars = array('|', '~');
		$string  = $this->title;
		$string .= '|'.str_replace($chars, '',$this->firstname);
		$string .= '|'.str_replace($chars, '',$this->name);
		$string .= '|'.str_replace($chars, '',$this->company);
		$string .= '~'.parent::toString();
		return $string;
	}

	/**
	 * Prepares object for output.
	 *
	 * @return string
	 * @access public
	 */
	public function out() {
		return $this->title." ".$this->firstname." ".$this->name."\r\n".$this->company."\r\n".parent::out();
	}

	/**
	 * This method is needed when displaying the order table in the backend.
	 * Return the full adress string or a part of it.
	 *
	 * @param int $id
	 * @param int $length - the length of the return string
	 * @return string
	 * @access public
	 */
	public static function addressOut($id, $length = -1) {
		$sql = new cjoSql();
		$qry = "SELECT address2 FROM ".TBL_21_ORDERS." WHERE order_id = ".$id;
		$sql->setQuery($qry);
		$adr = new cjoShopSupplyAddress($sql->getValue('address2', 0));
		$sql->flush();
		$return = $adr->out();
		if($length != -1)
			$return = $length < strlen($return) ? substr($return, 0, $length).'...' : $return;

		return $return;
	}

	/**
	 * Calls method out() of this class.
	 * @return string
	 * @access public
	 */
	public function __toString() {
	    return $this->out();
	}

} // end class cjoShopSupplyAddress
