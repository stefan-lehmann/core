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

class cjoWYMeditorField extends textField {

    public $width = '366px';
    public $height = '150';

    public function textField($name, $label, $attributes = array (), $id = '') {
        $this->cjoFormField($name, $label, $attributes, $id);
    }

    public function setWidth($width) {

        $this->width = (strpos($width, 'px') === false &&
                        strpos($width, '%') === false &&
                        strpos($width, 'em') === false) ? $width.'px' : $width;
    }

    public function getWidth() {
        return $this->width;
    }

    public function setHeight($height) {
        $this->height = str_replace(array('%','em','px'),'', $height);
    }

    public function getHeight() {
        return $this->height;
    }

    public function get() {

        $wymeditor = new WYMeditor();
        $wymeditor->id=$this->getName();
        $wymeditor->height=$this->getHeight();

        $this->attributes = array_merge(array ('class' => 'wym_container',
                                               'style' => 'width: '.$this->getWidth().'; overflow: hidden'),
                                                $this->attributes);

        $value = htmlspecialchars($this->getValue(), ENT_QUOTES, "UTF-8");


        $html = $wymeditor->render(false);
        $textarea = sprintf('<div %s><textarea rows="5" cols="20" name="%s" id="%s" tabindex="%s" class="%s hide_me">%s</textarea>%s</div>',
                            $this->getAttributes(),
                            $this->getName(),
                            $this->getId(),
                            cjo_a22_nextTabindex(),
                            substr($wymeditor->wymeditorSelector,1).'_'.$this->getName(),
                            $value,
                            $this->getNote());

        return preg_replace('/<textarea[^>]*><\/textarea>/i',$textarea,$html);
    }
}