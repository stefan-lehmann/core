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
 * @version     2.6.0
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

class simpleButtonField extends cjoFormField {

    public function textField($name, $label, $attributes = array (), $id = '') {
		$this->cjoFormField($name, $label, $attributes, $id);
	}

	public function get() {
		return sprintf('<input type="submit" name="%s" value="%s" id="%s" tabindex="%s"%s />%s', $this->getName(), $this->value, $this->getId(), cjo_a22_nextTabindex(), $this->getAttributes(), $this->getNote());
	}
}