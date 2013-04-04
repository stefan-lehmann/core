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
 * Klasse für Edit-Spalten innerhalb der Liste.
 */
class editColumn extends staticColumn {

    public function __construct($params=array()) {
        
        if (empty($params)) $params = array ('function' => 'edit', 'clang' => cjoProp::getClang(), 'oid' => '%id%');
        
        $img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
        parent::__construct($img, cjoI18N::translate("label_functions"));
        $this->setBodyAttributes('width', 16);
        $this->setBodyAttributes('class', 'cjo_edit');
        $this->setParams($params);  
    }

}