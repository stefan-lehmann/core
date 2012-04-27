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
 * Basisklasse für alle cjoList Komponenten
 */
class cjoListComponent {

    // Referenz zur dazugehörigen Liste
    public $cjolist;

    // Parameter für Links
    public $params;

    public function cjoListComponent($cjolist = null) {
        $this->cjolist = $cjolist;
    }

    public function link($value, $params = array (), $tags = '') {
        if ($value == '&nbsp;' || $value == ' ')
        return '&nbsp;'; // Hack Stefan Lehmann

        if (count($params) == 0) {
            $params = $this->params;
        }
        return cjoAssistance::createBELink($value, $params, $this->getGlobalParams(), $tags);
    }

    public function addGlobalParam($name, $value) {
        $this->cjolist->addGlobalParam($name, $value);
    }

    public function addGlobalParams($params) {
        $this->cjolist->addGlobalParams($params);
    }

    public function getGlobalParams() {
        return $this->cjolist->getGlobalParams();
    }

    public function setParams($params) {
        if (is_array($params)) {
            $this->params = array_merge($this->params, $params);
        } else {
            $this->params = $params;
        }
    }
}