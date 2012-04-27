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

class readOnlyField extends cjoFormField {

    public $format;
    public $format_type;
    public $activateSave;
    public $tags;

    public function readOnlyField($name, $label, $attributes = array (), $id = '') {
        $this->cjoFormField($name, $label, $attributes, $id);
        $this->format = '';
        $this->format_type = '';
        // default werden Werte nicht gespeichert
        $this->activateSave(false);
        $this->setContainer();
    }

    public function setContainer($tags='span') {
        $this->tags = $tags;
    }

    public function getContainer() {
        return $this->tags;
    }

    public function get() {
        global $I18N;

        $multiple_note = '';

        $value = $this->getValue();
        if ($value == '') $value = $this->value;

        return sprintf('<%s id="%s"%s>%s</%s>%s' . $multiple_note, $this->getContainer(), $this->getId(), $this->getAttributes(), $value, $this->getContainer(), $this->getNote());
    }
}