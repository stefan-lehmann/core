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

class fieldsetField extends cjoFormField {

    public $legend;
    public $container;

    public function fieldsetField() {
        $this->cjoFormField('', '', array (), '');
        $this->container = new cjoFieldContainer();
    }

    public function setLegend($legend) {
        $this->legend = $legend;
    }

    public function getLegend() {
        return $this->legend;
    }

    /**
     * Fügt dem Container ein Feld hinzu.
     * @param object cjoFormField-Objekt des hinzugefügt werden soll
     * @param object cjoFormSection-Objekt, mit dem das Feld verknüpft werden soll
     * @access protected
     * @see cjoFieldContainer::addField()
     */
    public function addField(& $field) {
        $this->container->addField($field, $this->getSection());
    }

    /**
     * Fügt dem Container mehrere Felder hinzu
     * @param array Array von cjoFormField-Objekten
     * @access public
     * @see cjoFieldContainer::addFields()
     */
    public function addFields(& $fields) {
        if (!is_array($fields)) return false;
        foreach ($fields as $key => $field) {
            $this->addField($fields[$key]);
        }
    }

    /**
     * Gibt alle Felder des Containers zurück
     * @return array Array von cjoFormField-Objekten
     * @access public
     * @see cjoFieldContainer::getFields()
     */
    public function & getFields() {
        return $this->container->getFields();
    }

    /**
     * Gibt die Werte aller Felder des Containers zurück
     * @return array Die Werte der Felder als Array
     * @access protected
     * @see cjoFieldContainer::getFieldValues()
     */
    public function getFieldValues() {
        return $this->container->getFieldValues();
    }

    /**
     * Zählt wieviele Felder sich im Container befinden
     * @return integer Anzahl an Felder im Container
     * @access public
     * @see cjoFieldContainer::numFields()
     */
    public function numFields() {
        return $this->container->numFields();
    }

    /**
     * Durchsucht den Fieldcontainer nach einem Feld
     * @param string Name des Feldes, wonach gesucht werden soll
     * @return object|null Bei erfolgreicher Suche wird ein cjoFormField-Objekt zurückgegeben, sonst null
     * @access public
     * @see cjoFieldContainer::searchField()
     */
    public function & searchField($name) {
        return $this->container->searchField($name);
    }

   public function get() {
        $s = '';
        $s .= '<fieldset>';

        $legend = $this->getLegend();
        if ($legend != '') {
            $s .= '<legend>' . $legend . '</legend>';
        }

        $fields = & $this->getFields();
        for ($i = 0; $i < $this->numFields(); $i++) {
            $s .= $fields[$i]->get();
        }

        $s .= '</fieldset>';
        return $s;
    }
}