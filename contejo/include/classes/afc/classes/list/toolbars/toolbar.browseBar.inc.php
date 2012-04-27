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

/**
 * Datensatz Navigation
 */
class browseBar extends cjoListToolbar {

	public $first_params;
	public $add_params;
	public $last_params;
	public $addButtonStatus;

	public function browseBar() {
        
		$this->setFirstParams(array ('next' => 'first', 'function' => ''));
		$this->setAddParams(array ('public function' => 'add'));
		$this->setLastParams(array ('next' => 'last', 'function' => ''));
		$this->setAddButtonStatus(false);
	}

	public function show() {

		global $I18N;

		$steps = $this->cjolist->getSteps();
		$found = $this->cjolist->num_rows;
		$stepping = $this->cjolist->getStepping();	

		$last = floor($found/$stepping)*$stepping;
		
	    if ($steps['curr'] != $steps['prev']) {
			$first = $this->link('<img src="img/silk_icons/control_rewind.png" title="'.$I18N->msg('label_first_page').'" alt="'.$I18N->msg('label_first_page').'" />',
			         array ('next' => 0, 'function' => ''));

			$prev = $this->link('<img src="img/silk_icons/control_play_backwards.png" title="'.$I18N->msg('label_prev_page').'" alt="'.$I18N->msg('label_prev_page').'" />',
			        array ('next' => $steps['prev'], 'function' => ''));
	    }
	    else {
			$first = '<img src="img/silk_icons/control_rewind_off.png" alt="" />';
			$prev = '<img src="img/silk_icons/control_play_backwards_off.png" alt="" />';
	    }

	    if ($steps['curr'] <  $last) {
			$next = $this->link('<img src="img/silk_icons/control_play.png" title="'.$I18N->msg('label_next_page').'" alt="'.$I18N->msg('label_next_page').'" />',
			        array ('next' => $steps['next'], 'function' => ''));

			$last = $this->link('<img src="img/silk_icons/control_fastforward.png" title="'.$I18N->msg('label_last_page').'" alt="'.$I18N->msg('label_last_page').'" />',
			        array ('next' => $last, 'function' => ''));
	    }
		else {
			$next = '<img src="img/silk_icons/control_play_off.png" alt="" />';
			$last = '<img src="img/silk_icons/control_fastforward_off.png" alt="" />';
	    }
	    
		$add = $this->getAddButtonStatus()
		     ? $this->link('<img src="img/silk_icons/add.png" title="'.$I18N->msg('label_add_entry').'" alt="'.$I18N->msg('label_add_entry').'" />',
		                   $this->add_params)
		     : '';

		return $first.' '.$prev.' '.$add.' '.$next.' '.$last."\r\n";
	}
	
	public function prepareQuery(& $listsql) {	    
		$steps = $this->cjolist->getSteps();
		$listsql->addLimit($steps['curr'], $this->cjolist->getStepping());		
	}

	public function setAddParams($params) {
		$this->add_params = $params;
	}

	public function setFirstParams($params) {
		$this->first_params = $params;
	}

	public function setLastParams($params) {
		$this->last_params = $params;
	}

	public function setAddButtonStatus($status) {
		$this->addButtonStatus = $status;
	}

	public function getAddButtonStatus() {
		return $this->addButtonStatus;
	}
}