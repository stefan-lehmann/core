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
 * Basisklasse für Toolbars innerhalb der Liste
 */
class cjoListToolbar extends cjoListComponent {

    public $content;
    
    public function cjoListToolbar() {
        // nichts tun
    }

    /**
     * Gibt die Tags zurück, die im TD angedruckt werden sollen,
     * indem sich die Toolbar befindet. (z.b. für Hintergrundfarbe/Ausrichtung/etc)
     */
    public function tags() {
        return '';
    }

    /**
     * @access public
     * @static
     */
    public static function isValid($column) {
        return is_object($column) && is_a($column, 'cjolisttoolbar');
    }

    /**
     * für Funktionen der Toolbars aus, vorm anzeigen der Liste
     */
    public function prepare() {
        // nichts tun
    }

    /**
     * Modifiziert den Qry der Liste
     */
    public function prepareQuery(& $listsql) {
        // nichts tun
    }

    /**
     * Gibt die Eigentliche Toolbar zurück
     */
    public function show() {
        return '';
    }
}

// Toolbar Klassen einbinden
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/toolbars/toolbar.searchBar.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/toolbars/toolbar.browseBar.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/toolbars/toolbar.statusBar.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/toolbars/toolbar.maxElementsBar.inc.php';
