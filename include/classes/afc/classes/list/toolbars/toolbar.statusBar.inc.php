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
 * Statusleiste zur Anzeige der Pagination
 */
class statusBar extends cjoListToolbar {

	public function show() {

        global $I18N;

		$found = $this->cjolist->numRows();
		$steps = $this->cjolist->getSteps();
		$stepping = $this->cjolist->getStepping();	
		$first = $steps['curr'];
		$last = $steps['curr'] + $stepping;

		if ($last > $found) $last = $found;

		// First beginnt bei 1
		if ($first +1 <= $found) $first++;
		
        $dec_point     = trim($I18N->msg('dec_point'));
        $thousands_sep = trim($I18N->msg('thousands_sep'));
        
		return $this->format(number_format($first, 0, $dec_point, $thousands_sep),
		                     number_format($last, 0, $dec_point, $thousands_sep),
		                     number_format($found, 0, $dec_point, $thousands_sep));
	}

	/**
	 * Formatiert die Statusbar Komponenten
	 * @param $first Nr. des ersten Angezeigten Elements
	 * @param $last Nr. des letzten Angezeigten Elements
	 * @param $max Anzahl an Elementen in der Liste
	 */
	public function format($first, $last, $max) {

		global $I18N;
        
		return ($max == 0) ? '' : '<b title="'.$I18N->msg('label_entries_from_to', $first, $last, $max).'" style="display:block;padding-top:.3em;">'. $first .' - '. $last .' / '. $max .'</b>'."\n";

	}
}