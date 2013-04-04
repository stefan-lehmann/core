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
 * Klasse für Status-Änderungs-Spalten innerhalb der Liste.
 */

 

 
 
class StatusColumn extends staticColumn {
    
    private $ajax; 

    public function __construct($column, $params=array(), $ajax=true, $label=NULL) {
        
        if (empty($params) && $params !== NULL) $params = array ('function' => $column, 'clang' => cjoProp::getClang(), 'oid' => '%id%');
     
        $this->name = $column;
        $this->text = '&nbsp;';
        $this->format_type = 'sprintf';
        $this->format = '%s';

        $this->cjoListColumn($label, OPT_NONE);
        $this->setBodyAttributes('width', 16);
        $this->setBodyAttributes('class', 'cjo_status'); 
        $this->setParams($params);  
        $this->ajax = (bool) $ajax;   
    }

    public function addCondition($cond_column, $cond_value, $text = NULL, $params = NULL, $tags = '') {

        //if (!empty($params)) $params = array_merge($this->params,$params);

        $this->conditions[] = array (
            $cond_column,
            $cond_value,
            $this->getCellContent($text, $cond_value),
            $params
        );
    }
    
    protected function getCellContent($text, $cond_value) {
        
        if ($text === NULL) return '&nbsp;';

        $file = empty($cond_value) ? $this->name.'_off.png' : $this->name.'.png'; 
        $file = cjoUrl::backend('img/silk_icons/'.$file);
        if (!$file || !file_exists($file) || strpos($text,'<img') !== false) return $text;
        return '<img src="'.$file.'" title="'.$text.'" alt="'.$file.'" />';
        
    }
    
    public function link($value, $params = array (), $tags = '') {

        if ($params === NULL) return $value;

        if (!$this->ajax) return parent::link($value, $params, $tags);
        
        if ($value == '&nbsp;' || $value == ' ')
        return '&nbsp;';

        if (count($params) == 0) {
            $params = $this->params;
        }

        return cjoUrl::createAjaxLink($value, $params, $tags);
    }
    

}