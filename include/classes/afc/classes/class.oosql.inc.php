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
 * ObjectOrientierter SQL Builder
 */

class OOSql {

    public $qry;
    public $where;
    public $order_by;
    public $group_by;
    public $having;
    public $limit;

    public function OOSql($qry) {

        $this->qry = $qry;
        $this->where = array ();
        $this->order_by = array ();
        $this->group_by = array ();
        $this->having = array ();
        $this->limit = array ();
    }

    public function addLimit($start, $offset = '') {
        // Limit darf es nur einmal geben, deshalb immer überschreiben
        $this->limit = array ('start' => $start, 'offset' => $offset);
    }

    public function addWhere($cond, $op = 'AND') {
        $this->where[] = array ('cond' => $cond, 'op' => $op);
    }

    public function addOrderBy($column, $direction = 'ASC') {
        $this->order_by[] = array ('column' => $column, 'direction' => $direction);
    }

    public function addGroupBy($column) {
        $this->group_by[] = array ('column' => $column);
    }

    public function addHaving($cond) {
        $this->having[] = array ('cond' => $cond);
    }

    public function getQry() {
        $qry = $this->qry;

        // WHERE String
        for ($i = 0; $i < count($this->where); $i ++) {
            $where = $this->where[$i];

            if ($i == 0 && (preg_match_all('/FROM/i', $qry, $var) != (preg_match_all('/WHERE/i', $qry, $var)))) {
                $qry .= ' WHERE '.$where['cond'];
            }
            else {
                $qry .= ' '.$where['op'].' '.$where['cond'];
            }
        }

        // ORDER BY String
        for ($i = 0; $i < count($this->order_by); $i ++) {
            $order_by = $this->order_by[$i];

            if ($i == 0) {
                $qry .= ' ORDER BY ';
            }
            else {
                $qry .= ', ';
            }
            $qry .= $order_by['column'].' '.$order_by['direction'];
        }

        // GROUP BY String
        for ($i = 0; $i < count($this->group_by); $i ++) {
            $group_by = $this->group_by[$i];

            if ($i == 0) {
                $qry .= ' GROUP BY';
            }
            $qry .= ' '.$group_by['column'];
        }

        // HAVING String
        for ($i = 0; $i < count($this->having); $i ++) {
            $having = $this->having[$i];

            if ($i == 0) {
                $qry .= ' HAVING';
            }
            $qry .= ' '.$having['cond'];
        }

        // LIMIT String
        if (!empty ($this->limit)) {

            $limit = $this->limit;
            $qry .= ' LIMIT '.$limit['start'];

            if ($limit['offset'] != '') {
                $qry .= ', '.$limit['offset'];
            }
        }
        return $qry;
    }
}