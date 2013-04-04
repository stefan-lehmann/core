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

/**
 * Leiste die ein Textfeld zum ändern der Datensätze pro Seite anzeigt
 */
class maxElementsBar extends cjoListToolbar {

	public $stepping;

	public function maxElementsBar() {
		$this->cjoListToolbar();
		$this->stepping = cjo_request('stepping', 'int', '');
	}

	public function prepare() {
		if ($this->stepping != '') {
			$this->cjolist->setStepping($this->stepping);
		}	
	}

	public function show() {

		global $I18N;

		return '<label for="stepping">'.cjoI18N::translate('label_number').':</label> '.
               '<input type="text" id="stepping" name="stepping" value="'.$this->cjolist->getStepping().'" '.
               '        style="width: 28px" maxlength="3" title="'.cjoI18N::translate('label_entries_per_page').'"/> '.
               '<input type="submit" value="'.cjoI18N::translate('button_show').'" title="'.cjoI18N::translate('button_show').'"/>'."\r\n";
	}
}