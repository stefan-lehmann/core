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
 * Klasse für Delete-Spalten innerhalb der Liste.
 */
class deleteColumn extends staticColumn {
    
    private $ajax;

    public function __construct($params, $ajax=true, $icon=NULL) {
        
        if ($icon === NULL) 
            $icon = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
        
        parent::__construct($icon, NULL);
        $this->setBodyAttributes('width="60"');
        $this->setBodyAttributes('class="cjo_delete"');
        $this->setParams($params);  
        $this->ajax = (bool) $ajax;        
    }
    
    public function link($value, $params = array (), $tags = '') {
        
        if (!$this->ajax) return parent::link($value, $params, $tags);
        
        if ($value == '&nbsp;' || $value == ' ')
        return '&nbsp;';

        if (count($params) == 0) {
            $params = $this->params;
        }

        return cjoUrl::createAjaxLink($value, $params, $tags);
    }

}