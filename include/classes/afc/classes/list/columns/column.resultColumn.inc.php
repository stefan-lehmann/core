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
 * Klasse für Spalten mit Werten aus dem SQL.
 */
class resultColumn extends cjoListColumn {

    // Name des Spalte im ResultSet
    public $name;
    // Formatierung des Spaltenwertes
    public $format;
    // Type der Formatierung des Spaltenwertes
    public $format_type;

    /**
     * Konstruktor
     * @param $name Name des Spalte im ResultSet
     * @param $label Spaltenüberschrift
     * @param $format Formatstring/Formatarray
     * @param $format_type Formatierungstyp
     *
     * Mögliche Formatierungstypen sind '','sprintf','date','strftime','number'
     */
    public function resultColumn($name, $label, $format_type = 'sprintf ', $format = '%s', $options = OPT_ALL) {

        $this->name = $name;
        $this->format_type = $format_type;
        $this->format = $format;
        $this->cjoListColumn($label, $options);
    }

    public function getLabel() {

        global $I18N;

        $label = parent :: getLabel();

        $format     = '<img src="%s" alt="%s"/>';
        $next       = cjo_request('next','string', '');
        $order_col  = cjo_request('order_col','string', $this->cjolist->def_order_col);
        $order_type = cjo_request('order_type','string', $this->cjolist->def_order_type);

        // Spalten nach denen nicht sortiert werden darf
        if (!$this->hasOption(OPT_SORT) && strpos($order_col, $this->name) === false) {
            return $label;
        }

        // bereits gesetzte CSS-Klassen berücksichtigen
        preg_match('/class=\"(.*?)"/i', $this->getHeadAttributes(), $classes);
        
        $classes = !empty($classes[1]) ? $classes[1] : '';
            
        $pattern = '/<a.*href\="?(\S+)"([^>]*)>.+<\/a>/i';

        if ($order_col != $this->name) {
            $link = $this->link(sprintf($label, '', $I18N->msg('label_sort_asc')), array (
                'order_col' => $this->name,
                'order_type' => 'asc',
                'next' => $next
            ));

            preg_match($pattern, $link, $matches);
            $matches = !empty($matches[1]) ? $matches[1] : '';

            $this->head_attributes = '';
            $this->setHeadAttributes('class="header ' . $classes . '" title="'.$I18N->msg('label_sort_asc').'" onclick="window.location.href = \'' . preg_replace("/&(?!amp;)/", "&amp;", $matches) . '\'" style="cursor:pointer"');
        }
        else {

            $this->head_attributes = '';
            $attributes = '';

            if (strtolower($order_type) == 'desc') {
                $link = $this->link(sprintf($label, '', $I18N->msg('label_sort_asc')), array (
                    'order_col' => $this->name,
                    'order_type' => 'asc',
                    'next' => $next
                ));
                preg_match($pattern, $link, $matches);

                if($this->hasOption(OPT_SORT)){
                    $attributes = ' title="'.$I18N->msg('label_sort_asc').'" onclick="window.location.href = \'' . preg_replace("/&(?!amp;)/", "&amp;", $matches[1]). '\'" style="cursor:pointer"';
                }

                $this->setHeadAttributes('class="header headerSortUp '. $classes . '"'.$attributes);
            }
            else {
                
                $link = $this->link(sprintf($label, '', $I18N->msg('label_sort_desc')), array (
                    'order_col' => $this->name,
                    'order_type' => 'desc',
                    'next' => $next
                ));

                preg_match($pattern, $link, $matches);

                if($this->hasOption(OPT_SORT)){
                    $attributes = ' title="'.$I18N->msg('label_sort_desc').'" onclick="window.location.href = \'' . preg_replace("/&(?!amp;)/", "&amp;", $matches[1]) . '\'" style="cursor:pointer"';
                }
                $this->setHeadAttributes('class="header headerSortDown ' . $classes . '"'.$attributes);

            }
        }
        return $label;
    }

    /**
     * Fügtgt der Spalte einen Wert hinzu, der von einer Spalte abhngig ist.
     *
     * @param $cond_column Name der Spalte die geprüft werden soll [Default ist die eigene Column]
     * @param $cond_value Wert, auf den geprüft werden soll
     * @param $text Text der ausgegeben werden soll
     * @param $params Link-Parameter die auf $text als Link gesetzt werden sollen
     */
    public function addCondition($cond_column = '', $cond_value, $text, $params = array(),$tags='') {

        if (strlen($cond_column) == 0) {
            $cond_column = $this->name;
        }
        parent :: addCondition($cond_column, $cond_value, $text, $params,$tags);
    }

    public function format($row) {
        global $I18N;

        $format = parent :: format($row);
        if (strlen($format) != 0) {
            return $format;
        }

        $value = isset($row[$this->name]) ? $row[$this->name] : '';

        if ($this->format_type == '') {
            if ($this->format == '') {
                $this->format = '%' . $this->name . '%';
            }
            // Alle Spaltenamen ersetzen durch deren Werte %id%, %name%, etc.
            $value = $this->parseString($this->format, $row);
        } else {
            $value = cjoFormatter::format($value, $this->format_type, $this->format);
        }

        return $this->link($value, $this->parseParams($row));
    }
}