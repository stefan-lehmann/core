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

class foreignField extends readOnlyField {

    public $table;
    public $field;
    public $foreignField;

    public function foreignField($name, $label, $attributes = array (), $id = '') {
        $this->cjoFormMultiValueField($name, $label, $attributes, $id);
        $this->multiple = false;
    }

    /**
     * Setzt die Verknüpfung des ForeignFields mit der FormSection
     *
     * Beispiel:
     * <code>
     * $field->setForeignField( 'cjo_article', 'name', 'id');
     * </code>
     *
     * Der aktuelle Datensatz wird mit dem Feld "name" aus der Tabelle "cjo_article" verknüpft.
     * Die Verknüpfung findet wird vorgenommen über das Feld "id".
     *
     * @access public
     */
    public function setForeignField($table, $field = '', $foreignfield = '') {
        $this->table = $table;
        $this->field = $field;
        $this->foreignField = $foreignfield;
    }

    public function getForeignField() {
        $field = $this->foreignField;

        if ($field == '') {
            $field = $this->getName();
        }

        return $field;
    }

    public function getField() {
        $field = $this->field;

        if ($field == '') {
            $field = $this->getName();
        }

        return $field;
    }

    public function getTable()  {
        return $this->table;
    }

    /**
     * Gibt den HTML Content zurück
     */
    public function get()  {
        $table = $this->getTable();
        $field = $this->getField();
        $foreignField = $this->getForeignField();
        $value = $this->formatValue();

        $qry = 'SELECT '.$field.' FROM '.$table.' WHERE '.$foreignField.' = "'.$value.'"';
        $sql = new cjoSql();
        // $sql->debugsql = true;
        $sql->setQuery($qry);

        if ($sql->getRows() == 1)  {
            return $sql->getValue($field);
        }

        return '';
    }
}