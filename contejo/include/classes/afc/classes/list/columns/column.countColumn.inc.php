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
 * Klasse die eine Fortlaufende Nummer repräsentiert
 */
class countColumn extends cjoListColumn {

    public $setSteps;
    public $counter;
    public $format;

    public function countColumn($label, $start = '', $resetOnEachPage = '', $format = '', $options = OPT_NONE) {

        $this->setSteps = $resetOnEachPage == '' ? false : $resetOnEachPage;
        $this->counter = $start == '' ? 1 : $start;
        $this->format = $format == '' ? '<b>%s</b>' : $format;
        $this->cjoListColumn($label, $options);
    }

    public function format($row) {

        $format = parent :: format($row);

        if (strlen($format) != 0) {
            return $format;
        }

        if (!$this->setSteps) {
            $steps = $this->cjolist->getSteps();
            $this->counter += $steps['curr'];
            $this->setSteps = true;
        }
        return sprintf($this->format, $this->counter++);
    }
}