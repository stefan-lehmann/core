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
 * Klasse für Statische-Spalten innerhalb der Liste.
 * Eine Statische Spalte hat immer den gleichen Text.
 */
class foreignColumn extends cjoListColumn {

    // Name des Spalte im ResultSet
    public $name;
    // statischer Text
    public $text;

    public function foreignColumn($name, $label, $query = "", $format_type = 'sprintf', $format = '%s', $options = OPT_NONE) {

        $this->name = $name;
        $this->text = $text;
        $this->format_type = $format_type;
        $this->format = $format;
        $this->cjoListColumn($label, $options);
        $this->setParams(array("query"=> $query));
    }

    public function format($row) {

        $query = trim($this->params['query']);

        if (strtoupper(substr($query,  0, 6)) != 'SELECT') return false;
        preg_match_all("/(?<=%)[^%|^s]*(?=%)/", $query, $matches, PREG_SET_ORDER);   
        
        foreach($matches as $match) {
            $query = str_replace('%'.$match[0].'%', $row[$match[0]], $query);
        }
        
        $sql = new cjoSql();
        $sql->setQuery($query);

        $value  = $sql->getValue("value");
        $format = cjoFormatter::format($value, $this->format_type, $this->format);
        
        if (strlen($format) != 0) {
            return $format;
        }

        return $value == 0 ? '' : $value;
    }

}