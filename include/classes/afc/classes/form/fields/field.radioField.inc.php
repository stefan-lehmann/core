<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@contejo.com>
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

class radioField extends cjoFormMultiValueField {

	public function radioField($name, $label, $attributes = array (), $id = '') {
		$this->cjoFormMultiValueField($name, $label, $attributes, $id);
	}

	/**
	 * Fügt eine Box hinzu
	 * @param $label Label der Option
	 * @param $value Wert der Option
	 * @access public
	 */
	public function addRadio($label, $value = '') {
		$this->addValue($label, $value);
	}

	/**
	 * Fügt ein Array von Boxen hinzu
	 * @param $options Array von Optionen
	 * @access public
	 */
	public function addRadios($boxes) {
		$this->addValues($boxes);
	}

	/**
	 * Fügt Boxen via SQL-Query hinzu
	 * @param $query SQL-Query, der ein 2 spaltiges Resultset beschreibt
	 * @access public
	 */
	public function addSqlRadios($query) {
		$this->addSqlValues($query);
	}

	/**
	 * Gibt alle Boxen als Array zurück
	 * @access public
	 */
	public function getRadios() {
		return $this->getValues();
	}

	/**
	 * Gibt den HTML Content zurück
	 */
	public function get() {
		$s = '';
		$name = $this->getName();
		$id = $this->getId();
		$value = $this->getValue();
		$attributes = $this->getAttributes();

		$i = 0;
		foreach ($this->getRadios() as $box) {
			$boxid = $id . $i;
			$label_attributes = (strpos($attributes, 'invalid') !== false) ? ' invalid' : '';
			$checked = (in_array($box[1], $value)) ? ' checked="checked"' : '';

			$s .= sprintf('<input type="radio" name="%s" value="%s" id="%s" tabindex="%s"%s%s /><label for="%s" class="right%s">%s</label>%s', $name, $box[1], $boxid, cjo_a22_nextTabindex(), $checked, $attributes, $boxid, $label_attributes, $box[0], $this->getNote());
			$i++;
		}

		return $s;
	}
}