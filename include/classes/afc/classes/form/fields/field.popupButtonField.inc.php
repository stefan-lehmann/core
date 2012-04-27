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

class popupButtonField extends readOnlyTextField {

	public $buttons;

	public function popupButtonField($name='', $label='', $attributes = array (), $id = '') {
		$this->readOnlyTextField($name, $label, $attributes, $id);
		$this->buttons = array ();
	}

	/**
	 * Fügt dem ButtonField einen Button hinzu
	 * @param $title Titel des Buttons
	 * @param $href Link des Buttons
	 * @param $title Dateiname des Bilder für den Button
	 */
	public function addButton($title, $event = 'return false;', $image = 'img/silk_icons/application_side_tree.png', $attributes="") {
		$this->buttons[] = array (
            'title' => $title,
            'event' => $event,
            'img' => $image,
			'attr' => $attributes
		);
		
	}

	public function getInputFields() {
		return parent :: get();
	}

	public function getInsertValue() {
		return $this->_getInsertValue();
	}

	public function getButtons() {

		$s      = '';
		$prefix = '';
        $suffix = '';		
		$id     = $this->getId();

		foreach ($this->buttons as $button) {

    		if ($button['img']) {
            	$img = (strpos($button['img'], '<img') === false) ? '<img src="'.$button['img'].'" alt="" />' : $button['img'];
        	}

    		foreach ($button as $attr_name => $attr_value) {
    			$button[$attr_name] = str_replace('%id%', $id, $attr_value);
    		}

    		$attributes = (strpos($button['attr'], 'class') !== false)
            	? str_replace('class="', 'class="cjo_form_button ', $button['attr'])
            	: $button['attr'].' class="cjo_form_button"';

            $event = ($button['event'] != false) ? ' onclick="'.$button['event'].'"' : '';

			$s .= sprintf('%s<button%s tabindex="%s" title="%s" %s>%s<span>%s</span></button>%s',
        			   $prefix, $event, cjo_a22_nextTabindex(), $button['title'], $attributes, $img, $button['title'], $suffix);
		}
		return $s;
	}

	public function get() {

		$s = '	<div class="clearfix cjo_select_button '.strtolower(get_class($this)).'">' . "\r\n";
		$s .= '	' . $this->getInputFields() . "\r\n";
		$s .= '		<div class="cjo_small_form_button">' . "\r\n";
		$s .= '			' . $this->getButtons() . "\r\n";
		$s .= '		</div>' . "\r\n";
		$s .= '	</div>' . "\r\n";
		$s .= $this->getNote() . "\r\n";
		return $s;
	}
}