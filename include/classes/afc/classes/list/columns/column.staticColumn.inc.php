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
 * Klasse für Statische-Spalten innerhalb der Liste.
 * Eine Statische Spalte hat immer den gleichen Text.
 */
class staticColumn extends cjoListColumn {

    // Name des Spalte im ResultSet
    public $name;
    // statischer Text
    public $text;

    public function staticColumn($text, $label, $params = array (), $options = OPT_NONE) {

        $this->name = $text;
        $this->text = $text;
        $this->format_type = 'sprintf';
        $this->format = '%s';
        // Statische Spalten sind nicht durchsuch- u. sortierbar
        $this->cjoListColumn($label, $options);
        $this->setParams($params);
    }

    public function format($row) {

        $format = parent :: format($row);
        if (strlen($format) != 0) {
            return $format;
        }
        // Link mit den Parametern aus der cjoList
        return $this->link($this->text, $this->parseParams($row));
    }

    /**
     * Fügt der Spalte einen Wert hinzu, der von einer Spalte abhängig ist.
     *
     * @param $cond_column Name der Spalte die geprüft werden soll
     * @param $cond_value Wert, auf den geprüft werden soll
     * @param $text Text der ausgegeben werden soll
     * @param $params Link-Parameter die auf $text als Link gesetzt werden sollen
     */
    public function addCondition($cond_column, $cond_value, $text, $params = '', $tags = '') {

        $this->conditions[] = array (
            $cond_column,
            $cond_value,
            $text,
            $params,
            $tags
        );
    }
}