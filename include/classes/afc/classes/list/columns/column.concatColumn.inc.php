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
class concatColumn extends resultColumn {
    
    private $detail_html   = '';
    private $detail_cols   = array();
    private $detail_params = array();

    public function __construct($name, $label, $format_type = 'sprintf ', $format = '%s', $options = OPT_ALL) {
        parent::__construct($name, $label, $format_type = 'sprintf ', $format = '%s', $options);
        $this->detail_cols = $details;
    }
    
    public function addDetail($name, $format_type = 'sprintf ', $format = '%s') {
        $this->detail_cols[$name] = array('name'=>$name, 'format_type' => $format_type, 'format' => $format);
    }
    
    public function setDetailParams($name, $params) {
        $this->detail_params[$name] = $params;
    }
    
    public function setDetailHtml($html) {
        $this->detail_html = $html;
    }
    
    public function formatDetail($row, $name, $format_type, $format) {

        $value = isset($row[$name]) ? $row[$name] : '';

        if ($this->format_type == '') {
            if ($format == '') {
                $format = '%'.$name.'%';
            }
            $value = $this->parseString($format, $row);
        } else {
            $value = cjoFormatter::format($value, $format_type, $format);
        }

        return is_array($this->detail_params[$name]) 
            ? $this->link($value, $this->detail_params[$name])
            : $value;
    }

    public function format($row) {
        
        $data = parent::format($row);

        $args = array();
        foreach($this->detail_cols as $col) {
            $args[] = $this->formatDetail($row, $col['name'], $col['format_type'], $col['format']);
        }

        $data .= vsprintf($this->detail_html, $args);

        return $data;
    }
}