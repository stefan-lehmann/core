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

class cjoSaveField extends saveField {

	public $updateButtonName;
	public $cancelButtonName;
	public $deleteButtonName;

	public function cjoSaveField($attributes = array ()) {

		global $I18N;

		$this->updateButtonName = 'cjoform_update_button';
		$this->cancelButtonName = 'cjoform_cancel_button';
		$this->deleteButtonName = 'cjoform_delete_button';

		$this->addButton($this->updateButtonName, cjoI18N::translate('button_update'), true, 'img/silk_icons/tick.png');
		$this->addButton($this->cancelButtonName, cjoI18N::translate('button_cancel'), true, 'img/silk_icons/cancel.png');
		$this->addButton($this->deleteButtonName, cjoI18N::translate('button_delete'), false, 'img/silk_icons/bin.png');

		$this->saveField($attributes);
		$this->needFullColumn(true);
	}

	public function setUpdateButtonStatus($status) {
		$this->setButtonStatus($this->updateButtonName, $status);
	}

	public function setCancelButtonStatus($status) {
		$this->setButtonStatus($this->cancelButtonName, $status);
	}

	public function setDeleteButtonStatus($status) {
		$this->setButtonStatus($this->deleteButtonName, $status);
	}

	public function get() {

		global $I18N;

		$section = & $this->getSection();
		$form = & $section->getForm();
		$s = '';

		// linksbündige Buttons
		$s .= '<div class="button_field">';
		$s .= '	<div class="cjo_float_l">';

		if ($form->isEditMode()) {
			$s .= $this->formatButton($this->updateButtonName, 'class="' . $this->updateButtonName . '"', '', '&nbsp;');
		}

		if ($form->section->tablename) {
			$s .= $this->formatButton($this->saveButtonName, 'class="' . $this->saveButtonName . '"', '', '&nbsp;');
		}

		$s .= $this->formatButton($this->cancelButtonName, 'class="' . $this->cancelButtonName . '"', '', '&nbsp;');
		$s .= '	</div>';
		$s .= '</div>';

		return $s;
	}
}