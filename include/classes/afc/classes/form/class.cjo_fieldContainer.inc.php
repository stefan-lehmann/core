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
 * Basisklasse um cjoFormField komponenten zu handeln
 * @access protected
 */

class cjoFieldContainer {

    /**
     * Array von cjoFormField-Objekten
     * @var array
     */
    public $fields;

    /**
     * Klassenkonstruktor
     */
    public function cjoFieldContainer() {
        $this->fields = array ();
    }

    /**
     * Fügt dem Container ein Feld hinzu.
     * @param object cjoFormField-Objekt des hinzugefügt werden soll
     * @param object cjoFormSection-Objekt, mit dem das Feld verknüpft werden soll
     * @access protected
     */
    public function addField(& $field, & $section) {
        if (!cjoFormField :: isValid($field)) {
            trigger_error('cjoForm: Unexpected type "' . gettype($field) . '" for $field! Expecting "cjoformfield"-object.', E_USER_ERROR);
        }
        $field->cjosection = & $section;
        $this->fields[] = & $field;
    }

    /**
     * Gibt alle Felder des Containers zurück
     * @return array Array von cjoFormField-Objekten
     * @access public
     */
    public function & getFields() {
        return $this->fields;
    }

    /**
     * Gibt die Werte aller Felder des Containers zurück
     * @return array Die Werte der Felder als Array
     * @access protected
     */
    public function getFieldValues() {
        $fields = $this->getFields();
        $values = array ();

        for ($i = 0; $i < $this->numFields(); $i++) {
            $values[$fields[$i]->getName()] = $fields[$i]->getValue();
        }

        return $values;
    }

    /**
     * Zählt wieviele Felder sich im Container befinden
     * @return integer Anzahl an Felder im Container
     * @access public
     */
    public function numFields() {
        return count($this->getFields());
    }

    /**
     * Durchsucht den Fieldcontainer nach einem Feld
     * @param string Name des Feldes, wonach gesucht werden soll
     * @return object|null Bei erfolgreicher Suche wird ein cjoFormField-Objekt zurückgegeben, sonst null
     * @access public
     */
    public function searchField($name) {
        $fields = $this->getFields();
        if (is_array($fields)) {
            for ($i = 0; $i < $this->numFields(); $i++) {
                $field = & $fields[$i];
                if ($field === null) {
                    continue;
                }

                if ($field->getName() == $name) {
                    return $field;
                }
            }
        }
        return null;
    }
}
