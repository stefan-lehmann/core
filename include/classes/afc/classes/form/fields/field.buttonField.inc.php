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

class buttonField extends cjoFormField {

    var $buttons = array();

    public function buttonField($attributes = array (),$id='') {
        $this->cjoFormField('', '', $attributes,$id);
    }

    public function addButton($name, $label, $status = true, $img = '') {
        cjo_valid_type($name, 'string', __FILE__, __LINE__);
        cjo_valid_type($label, 'string', __FILE__, __LINE__);
        cjo_valid_type($status, 'boolean', __FILE__, __LINE__);
        cjo_valid_type($img, 'string', __FILE__, __LINE__);

        $this->buttons[$name] = array (
            'name' => $name,
            'label' => $label,
            'status' => $status,
            'img' => $img,
            'attributes' => ''
        );
    }

    public function getButton($name) {
        return $this->buttons[$name];
    }

    public function getButtons() {
        return $this->buttons;
    }

    public function removeButtons() {
        $this->buttons = array ();
    }

    public function setButtonStatus($name, $status) {
        cjo_valid_type($name, 'string', __FILE__, __LINE__);
        cjo_valid_type($status, 'boolean', __FILE__, __LINE__);
        $this->buttons[$name]['status'] = $status;
    }

    public function setButtonLabel($name, $label) {
        cjo_valid_type($name, 'string', __FILE__, __LINE__);
        cjo_valid_type($label, 'string', __FILE__, __LINE__);
        $this->buttons[$name]['label'] = $label;
    }

    public function setButtonAttributes($name, $attributes) {
        cjo_valid_type($name, 'string', __FILE__, __LINE__);
        cjo_valid_type($attributes, 'string', __FILE__, __LINE__);
        $this->buttons[$name]['attributes'] .= ' '.$attributes;
    }

    public function formatButton($name, $attributes = '', $prefix = '', $suffix = '') {

        $img = '';
        $css = '';

        $button = $this->getButton($name);

        if (!$button['status'])return '';

        if ($button['img']) {
            $img = '<img src="'.$button['img'].'" alt="" />';
            $css = ' class="button_text"';
        }

        $attributes = (strpos($attributes, 'class') !== false)
        ? str_replace('class="', 'class="cjo_form_button ', $attributes)
        : $attributes.' class="cjo_form_button"';

       	if (strpos($attributes, 'id') === false)
       	$attributes .= ' id="'.$name.'"';

        return sprintf('%s<button type="submit" name="%s" title="%s" value="%s" tabindex="%s" %s>%s<span%s>%s</span></button>%s',
        $prefix, $name, $button['label'], $button['label'], cjo_a22_nextTabindex(), $attributes, $img, $css, $button['label'], $suffix);
    }

    public function getInsertValue() {
        // null zurückgeben, damit der Wert nicht im SQL auftaucht
        return null;
    }

    public function _get() {

        $s = '';
        $buttons = $this->getButtons();
        
        if (!is_array($buttons)) return false;

        foreach ($buttons as $button) {
            $s .= $this->formatButton($button['name'], $button['attributes']);
        }
        return $s;
    }

    public function get() {
        
        $id = ($this->getId() != '') ? ' id="'.$this->getId().'"' : '';

        return sprintf('<div class="button_field"%s><div class="cjo_float_l"%s>%s</div></div>',$id, $this->getAttributes(), $this->_get());
    }
}