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

class cjoMediaListField extends popupButtonField {

	public $connectaction;
	public $disconnectaction;

	public function cjoMediaListField($name, $label, $attributes = array (), $id = '') {

		global $I18N;

		if (empty ($attributes['style'])) {
			$attributes['style'] = 'clear:none;left:200px;';
		}
		$attributes['class'] = (empty ($attributes['class'])) ? 'cjo_media_list readonly' : $attributes['class'].' cjo_media_list readonly';
		$this->popupButtonField($name, $label, $attributes, $id);

		$this->setConnectAction("cjo.connectMediaList($(this)); return false;");
		$this->setDisconnectAction('cjo.jconfirm(\''.$I18N->msg('label_remove_media').' ?\', \'cjo.disconnectMediaList\', [$(this)]); return false;');
	}

	public function getInputFields() {

		global $I18N;

		$values = array();
		$separator = ',';

		if ($this->getValue() != '') {
			$values = explode($separator,$this->getValue());
		}

		$values = array_diff($values, array(''));

		$list = new cjoSelect;
		$list->setName($this->getName().'_sel');
		$list->setId($this->getId().'_sel');
		$list->setStyle($this->getAttributes());
		$list->setSize(6);
		$list->setMultiple(1);

		if ($values[0] != '') {
		    $list->addOptions($values);
		}

		$s = $list->get()."\r\n".
			 '<input type="hidden" value="'.$this->getValue().'" name="'.$this->getName().'" />'."\r\n".
			 '<span class="cjo_medialist_right">'."\r\n".
			 '	<a href="javascript:cjo.moveMediaListItem(\''.$this->getId().'_sel\',\'top\');" title="'.$I18N->msg('label_move_to_top').'"><img src="img/silk_icons/move_top_green.png" alt="top" /></a>'."\r\n".
			 '	<a href="javascript:cjo.moveMediaListItem(\''.$this->getId().'_sel\',\'up\');" title="'.$I18N->msg('label_move_up').'"><img src="img/silk_icons/move_up_green.png" alt="up" /></a>'."\r\n".
			 '	<a href="javascript:cjo.moveMediaListItem(\''.$this->getId().'_sel\',\'down\');" title="'.$I18N->msg('label_move_down').'"><img src="img/silk_icons/move_down_green.png" alt="down" /></a>'."\r\n".
			 '	<a href="javascript:cjo.moveMediaListItem(\''.$this->getId().'_sel\',\'bottom\');" title="'.$I18N->msg('label_move_to_bottom').'"><img src="img/silk_icons/move_bottom_green.png" alt="bottom" /></a>'."\r\n".
			 '</span>'."\r\n";
		return $s;
	}


	public function setConnectAction($connectaction) {
		$this->connectaction = $connectaction;
	}

	public function getConnectAction() {
		return $this->connectaction;
	}

	public function setDisconnectAction($disconnectaction) {
		$this->disconnectaction = $disconnectaction;
	}

	public function getDisconnectAction() {
		return $this->disconnectaction;
	}

	public function get() {

		global $I18N;

		$this->addButton($I18N->msg('label_remove_media'), $this->getDisconnectAction(), 'img/silk_icons/cross.png', 'class="small"');
		$this->addButton($I18N->msg('label_open_media'), $this->getConnectAction(), 'img/silk_icons/add.png', 'class="small"');
		return parent::get();
	}
}