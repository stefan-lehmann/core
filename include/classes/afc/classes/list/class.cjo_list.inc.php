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

// Classes
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/class.cjo_listComponent.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/class.cjo_listColumn.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/list/class.cjo_listToolbar.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/class.cjo_formatter.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/afc/classes/class.oosql.inc.php';

/**
 * Platzhalter: Erscheint, wenn die Liste keine Datensätze enthält
 * @see cjoList::setVar()
 */
define('LIST_BEFORE_ALL', 13);
/**
 * Platzhalter: Vor der Liste
 * @see cjoList::setVar()
 */
define('LIST_VAR_TOP', 1);
/**
 * Platzhalter: Vor den Kopfleisten
 * @see cjoList::setVar()
 */
define('LIST_VAR_BEFORE_HEAD', 2);
/**
 * Platzhalter: Nach den Kopfleisten
 * @see cjoList::setVar()
 */
define('LIST_VAR_AFTER_HEAD', 3);
/**
 * Platzhalter: Vor dem Kopfbereich der Tabelle
 * @see cjoList::setVar()
 */
define('LIST_VAR_BEFORE_DATAHEAD', 4);
/**
 * Platzhalter: Nach dem Kopfbereich der Tabelle
 * @see cjoList::setVar()
 */
define('LIST_VAR_AFTER_DATAHEAD', 5);
/**
 * Platzhalter: Vor dem Datenbereich der Tabelle
 * @see cjoList::setVar()
 */
define('LIST_VAR_BEFORE_DATA', 6);
/**
 * Platzhalter: Nach dem Datenbereich der Tabelle
 * @see cjoList::setVar()
 */
define('LIST_VAR_AFTER_DATA', 7);
/**
 * Platzhalter: Vor den Fußleisten
 * @see cjoList::setVar()
 */
define('LIST_VAR_BEFORE_FOOT', 8);
/**
 * Platzhalter: Vor den Fußleisten
 * @see cjoList::setVar()
 */
define('LIST_VAR_INSIDE_FOOT', 12);
/**
 * Platzhalter: Nach den Fußleisten
 * @see cjoList::setVar()
 */

define('LIST_VAR_AFTER_FOOT', 9);
/**
 * Platzhalter: Nach der Liste
 * @see cjoList::setVar()
 */
define('LIST_VAR_BOTTOM', 10);
/**
 * Platzhalter: Erscheint, wenn die Liste keine Datensätze enthält
 * @see cjoList::setVar()
 */
define('LIST_VAR_NO_DATA', 11);

/**
 * Basisklasse zur Darstellung von Datenbanktabellen (Listen).
 *
 * Features:
 * - Spalte sortiertbar/suchbar schalten
 * - einzelne Leisten auslagern in ToolBars
 * - ein/ausblenden von ToolBars
 * - Elemente pro Seite von User einstellbar
 */
class cjoList {

    // Status-Meldungen
    public $messages;

    /**
     * Schritte der Aktuellen Seite
     * @var array
     * @access private
     */
    var $steps;

    /**
     * Anzahl Datensätze pro Seite
     * @var integer
     * @access private
     */
    public $stepping;

    /**
     * Listenname
     * @var string
     * @access private
     */
    public $name;

    /**
     * Listenüberschrift
     * @var string
     * @access private
     */
    public $label;

    /**
     * Spaltenbreiten
     * @var array
     * @access private
     */
    public $colgroup;

    /**
     * Globale Parameter für Links
     * @var array
     * @access private
     */
    public $params;

    /**
     * Bezeichnung der Liste
     * @var string
     */
    public $caption;

    /**
     * Attribute der Liste
     * @var string
     */
    public $attributes;

    /**
     * Array das alle Spaltenobjekte beinhaltet
     * @var array
     * @access private
     */
    public $columns;

    /**
     * Array der Kopf und Fußleisten
     * @var array
     * @access private
     */
    public $toolbars;

    /**
     * Standard-Anzahl Datensätze pro Seite
     * @var string
     * @access private
     */
    public $def_stepping;

    /**
     * Standardsortierungs Spaltenname
     * @var string
     * @access private
     */
    public $def_order_col;

    /**
     * Standardsortierungs Type
     * @var string asc/desc
     * @access private
     */
    public $def_order_type;

    /**
     * Name der Standardsuch-Spalte
     * @var string
     * @access private
     */
    public $def_search_col;

    /**
     * Suche erfolgreich
     * @var bool
     * @access private
     */
    public $search_result;
    /**
     * Datenbankverbindung
     * @var object
     * @access private
     */
    public $sql;

    /**
     * Query Builder (OOSQL)
     * @var object
     * @access protected
     */
    public $listsql;
    /**
     * Grundlegende SQL Abfrage
     * @var string
     * @access private
     */
    public $qry;

    /**
     * Datensätze im Resultset
     * @var array
     * @access private
     */
    public $rows;

    /**
     * Anzahl Datensätze im Dataset
     * @var integer
     * @access private
     */
    public $num_rows;

    /**
     * Datensätze im Resultset für die aktuelle Seite
     * @var array
     * @access private
     */
    public $curr_rows;

    /**
     * Anzahl Datensätze für die aktuelle Seite
     * @var integer
     * @access private
     */
    public $num_current_rows;

    /**
     * statische Layouttexte
     * @var array
     * @access private
     */
    public $layoutVars;

    /**
     * Debugflag
     * @var bool
     * @access public
     */
    public $debug;

    public function cjoList($qry = '', $default_order_col = '', $default_order_type = '', $default_search_col = '', $default_stepping = '', $attributes = '') {
        
        global $order_col, $order_type;

        $this->messages = array ();
        $this->steps = array ();
        $this->stepping = cjo_request('stepping', 'int', '');

        $this->label = '';
        $this->caption = '';
        $this->name = 'default';
        $this->colgroup = array ();
        $this->params = array ();
        $this->attributes = $attributes;
        $this->columns = array ();
        $this->toolbars = array ();

        $this->toolbars['top']['full'] = array ();
        $this->toolbars['top']['half'] = array ();
        $this->toolbars['bottom']['full'] = array ();
        $this->toolbars['bottom']['half'] = array ();
        $this->toolbars['content'] = array ();        

        $this->def_stepping = $default_stepping;
        $this->def_order_col = $default_order_col;
        $this->def_order_type = $default_order_type;
        $this->def_search_col = $default_search_col;
        $this->search_result = true;

        $this->layoutVars = array ();

        $this->sql = new cjoSql();
        $this->listsql = new OOSql($qry);
        $this->qry = $qry;
        $this->rows = '';
        $this->num_rows = '';
        $this->curr_rows = '';
        $this->num_current_rows = '';

        $this->debug = & $this->sql->debugsql;

        $this->addGlobalParams(cjo_a22_getDefaultGlobalParams());

        // Nur die Parameter anhängen, die vom default Wert abweichen
        if ($order_col != $default_order_col) {
            $this->addGlobalParam('order_col', $order_col);
        }

        if ($order_type != $default_order_type) {
            $this->addGlobalParam('order_type', $order_type);
        }
        
        cjoAssistance::resetAfcVars();
    }

    /**
     * Fügt einen globalen Parameter hinzu.
     * Dieser wird bei allen Links innerhalb der Liste angefügt.
     *
     * @param Name des Parameters
     * @param Wert des Parameters
     * @access public
     */
    public function addGlobalParam($name, $value) {
        $this->params[$name] = $value;
    }

    /**
     * Fügt der Liste ein Array von Parametern hinzu.
     *
     * @param array Array von Parametern
     * @access public
     */
    public function addGlobalParams($params) {
        foreach ($params as $_name => $_value) {
            if (!in_array($_name, array('function','oid')))
            $this->addGlobalParam($_name, $_value);
        }
    }

    /**
     * Gibt die Globalen Parameter zurück.
     *
     * @access protected
     * @return array Die Globalen Parameter
     */
    public function getGlobalParams() {
        return $this->params;
    }

    /**
     * Setzt die Anzahl Zeilen, die pro Seite angezeigt werden
     * @param integer Zeilenanzahl
     * @access protected
     */
    public function setStepping($stepping) {
        if ($stepping != $this->def_stepping) {
            $this->stepping = $stepping;
            $this->addGlobalParam('stepping', $stepping);
        }
    }

    /**
     * Setzt die Standard-Anzahl Zeilen, die pro Seite angezeigt werden
     *
     * @param integer Zeilenanzahl
     * @access protected
     */
    public function setDefaultStepping($stepping) {
        $this->def_stepping = $stepping;
    }

    /**
     * Setzt den Fuß der Liste.
     * Damit kann zwischen den unteren Toolbars und der Liste eigener Inhalt eingefügt werden
     *
     * @access public
     * @deprecated version - 27.02.2006
     */
    public function setFooter($footer) {
        $this->setVar(LIST_VAR_TOP, $footer);
    }

    /**
     * Setzt den Kopf der Liste.
     * Damit kann zwischen den obereb Toolbars und der Liste eigener Inhalt eingefügt werden
     *
     * @access public
     * @deprecated version - 27.02.2006
     */
    public function setHeader($header) {
        $this->setVar(LIST_VAR_BOTTOM, $header);
    }

    public function setAttributes($attributes) {
        if ($attributes != '' && !startsWith($attributes, ' ')) {
            $attributes = ' '.$attributes;
        }
        $this->attributes = $attributes;
    }

    public function getAttributes() {
        return $this->attributes;
    }
    /**
     * Setzt die Bezeichnung der Liste
     *
     * @param string die Überschrift
     * @access public
     */
    public function setCaption($caption) {
        $this->caption = $caption;
    }

    /**
     * Gibt die Bezeichnung der Liste zurück
     *
     * @return string die Überschrift
     * @access protected
     */
    public function getCaption() {
        return $this->caption;
    }

    /**
     * Setzt die Überschrift der Liste
     *
     * @param string die Überschrift
     * @access public
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Gibt die Liste zurück
     *
     * @return string die Überschrift
     * @access protected
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Setzt den Namen der Liste.
     *
     * @param string Name der Liste
     * @access public
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Gibt den Namen der Liste zurück.
     *
     * @param string Name der Liste
     * @access public
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Fügt der Liste eine Spalte hinzu.
     *
     * @param object Spaltenobjekt das hinzugefügt werden soll
     * @access public
     */
    public function addColumn(& $column) {
        if (!cjoListColumn :: isValid($column)) {
            trigger_error('cjoList: Unexpected type "'.gettype($column).'" for $column! Expecting "cjolistcolumn"-object.', E_USER_ERROR);
        }
        $column->cjolist = & $this;
        $this->columns[] = & $column;
    }

    /**
     * Fügt der Liste ein Array von Spalten hinzu.
     *
     * @param array Array von Spaltenobjekten die hinzugefügt werden sollen
     * @access public
     */
    public function addColumns(& $columns) {
        if (!is_array($columns)) {
            trigger_error('cjoList: Unexpected type "'.gettype($columns).'" for $columns! Expecting an Array!.', E_USER_ERROR);
        }
        foreach ($columns as $key => $column) {
            $this->addColumn($columns[$key]);
        }
    }

    /**
     * Fügt der Liste eine Toolbar hinzu.
     * Diese muss ein Objekt vom Typ "cjolisttoolbar" sein!
     *
     * @param $direction Stelle an der die Toolbar angefügt werden soll (top|bottom).
     * @param $mode Breite in der die Toolbar angezeigt werden soll (full|half).
     * @access public
     */
    public function addToolbar(& $toolbar, $direction = 'top', $mode = 'full') {
        if (!in_array($direction, array('top', 'bottom'))) {
            trigger_error('cjoList: Unexpected direction "'.$direction.'"!', E_USER_ERROR);
        }
        if (!in_array($mode, array('full', 'half'))) {
            trigger_error('cjoList: Unexpected mode "'.$mode.'"!', E_USER_ERROR);
        }
        if (!cjoListToolbar :: isValid($toolbar)) {
            trigger_error('cjoList: Unexpected type "'.gettype($toolbar).'" for $column! Expecting "cjolisttoolbar"-object.', E_USER_ERROR);
        }

        $toolbar->cjolist = & $this;
        $this->toolbars[$direction][$mode][get_class($toolbar)] = & $toolbar;
    }

    /**
     * Gibt ein Array von Toolbars zurück, die der Liste bereits hinzugefügt wurden.
     *
     * @return array Bereits hinzugefügte Toolbars
     * @access public
     */
    public function getToolbars() {
        return array_merge($this->toolbars['top']['full'], $this->toolbars['top']['half'], $this->toolbars['bottom']['full'], $this->toolbars['bottom']['half']);
    }

    /**
     * Fügt der Liste die Standard-Toolbars hinzu
     * @access protected
     */
    public function addDefaultToolbars() {
        $this->addToolbar(new browseBar(), 'top', 'half');
        $this->addToolbar(new searchBar(), 'top', 'half');
        $this->addToolbar(new statusBar(), 'top', 'half');
        $this->addToolbar(new browseBar(), 'bottom', 'half');
        $this->addToolbar(new maxElementsBar(), 'bottom', 'half');
        $this->addToolbar(new statusBar(), 'bottom', 'half');
    }

    /**
     * Gibt einen Link zurück mit den Parametern $params und den Attributen $tags
     *
     * @param string Text der verlinkt werden soll
     * @param array Parameter die an die URL des Links angefügt werden sollen
     * @param array Tags, die dem Link Element hinzugefügt werden sollen
     *
     * @return string Gibt einen formatierten HTML-Hyperlink zurück
     * @access protected
     */
    public function link($value, $params = array (), $tags = '') {
        if ($value == '&nbsp;' || $value == ' ') return ''; // Hack Stefan Lehmann
        return cjoAssistance::createBELink($value, $params, $this->params, $tags);
    }

    /**
     * Gibt die aktuellen Schritte der Liste zurück.
     *
     *     $this->steps['prev']; // vorheriger Schritt
     *     $this->steps['curr']; // aktueller Schritt
     *     $this->steps['next']; // nächster Schritt
     *
     * @return array Assoc-Array mit den Schritten
     * @access protected
     */
    public function getSteps() {
        global $CJO;

        if (empty ($this->steps)) {

            /*
             $this->steps['prev'];
             $this->steps['curr'];
             $this->steps['next'];
             */

            /*
             // SQL_CALC_FOUND_ROWS & FOUND_ROWS() ist ab MySQL 4.0 möglich
             if (substr($CJO['MYSQL_VERSION'], 0, 1) == '4')
             {
             $qry = preg_replace('/SELECT/i', 'SELECT SQL_CALC_FOUND_ROWS', $qry);
             }
             */

            // Calc next
            $curr = cjo_request('next','string','');
            $found = $this->numRows();
            $stepping = $this->getStepping();

            if (empty ($curr) || $curr == 'first') {
                $curr = 0;
            }
            elseif ($curr == 'last') {
                // SQL_CACHE ist ab MySQL 4.0 möglich
                if (substr($CJO['MYSQL_VERSION'], 0, 1) == '4') {
                    $count_qry = preg_replace('/SELECT/i', 'SELECT SQL_CACHE', $this->qry);
                } else {
                    $count_qry = $this->qry;
                }

                $this->sql->setQuery($count_qry);
                
                $rows = $this->sql->getRows();
                if ($rows - $stepping < 0) {
                    $curr = 0;
                } else {
                    $curr = $rows - $stepping +1;
                }
            } else {
                $curr = (int) $curr;
            }

            $stepping = $stepping;

            $this->steps = array ();
            $prev = $curr - $stepping >= 0 ? $curr - $stepping : 0;

            $next = 0;
            // Ist der nächste Wert größre als Datensätze vorhanden
            if ($curr + $stepping >= $found) {
                // Nur wenn mehr Datensätze vorhanden sind als für einen Schritt benötigt werden
                if ($found - $stepping +1 > 0) {
                    $next = $found - $stepping +1;
                }
            } else {
                $next = $curr + $stepping;
            }

            $this->steps['prev'] = $prev;
            $this->steps['curr'] = $curr;
            $this->steps['next'] = $next;
        }
        return $this->steps;
    }

    /**
     * Gibt zurück, wieviele Datensätze pro Seite angezeigt werden
     *
     * @return integer Anzahl der Datensätze pro Seite
     * @access protected
     */
    public function getStepping() {
        if ($this->stepping == '') {
            if ($this->def_stepping == '') {
                return 10;
            }
            return $this->def_stepping;
        }

        return $this->stepping;
    }

    /**
     * Gibt alle Datensätze zurück, die vom Ursprünglichen Query betroffen sind
     *
     * @return array Array von Datensätzen
     * @access protected
     */
    public function getRows() {
            
        if (empty($this->rows)) {
            $this->rows = $this->sql->getArray($this->qry);
        }
        return $this->rows;
    }

    /**
     * Gibt die Anzahl der Datensätze zurück,
     * die vom ursprünglichen Query betroffen sind.
     *
     * @return integer Die Anzahl der Datensätze
     * @access protected
     */
    public function numRows() {

        if ($this->num_rows == '') {
            $this->num_rows = count($this->getRows());
            /*
             // SQL_CALC_FOUND_ROWS & FOUND_ROWS() ist ab MySQL 4.0 möglich
             if (substr($CJO['MYSQL_VERSION'], 0, 1) == '4')
             {
             $this->sql->setQuery('SELECT FOUND_ROWS() AS FOUND');
             $res = $this->sql->getArray();
             $this->num_rows = $res[0]['FOUND'];
             }
             else
             {
             $this->sql->setQuery($this->qry);
             $this->num_rows = $this->getRows();
             }
             */
        }
        return $this->num_rows;
    }

    /**
     * Gibt die Datensätze der aktuellen Seite zurück.
     *
     * @return array Array von Datensätze der aktuellen Seite
     * @access protected
     */
    public function getCurrentRows() {

        global $I18N;

        if ($this->curr_rows == '') {
            $listsql = & $this->listsql;
            $toolbars = $this->getToolbars();
            
            foreach($toolbars as $key=>$toolbar) {
                $toolbars[$key]->prepareQuery($listsql);
            }
              
            $this->_generateToolbars($toolbars);
            $this->prepareQuery($listsql);
            $this->sql->setQuery($listsql->getQry());
            $this->curr_rows = $this->sql->getArray();

            if ($this->curr_rows == '' && !empty($listsql->where)) {
                $this->search_result = false;
            }
        }

        if ($this->search_result == false) {
            $this->curr_rows = $this->getRows();
            $this->col_options = false;
        }
        
        return $this->curr_rows;
    }

    /**
     * Gibt die Anzahl der Datensätze, auf der aktuellen Seite zurück.
     *
     * @return integer Anzahl der Datensätze
     * @access protected
     */
    public function numCurrentRows() {
        if ($this->num_current_rows == '') {
            $this->num_current_rows = count($this->getCurrentRows());
        }
        return $this->num_current_rows;
    }

    /**
     * Gibt die Anzahl der Spalten zurück
     *
     * @return integer Anzahl der Spalten
     * @access protected
     */
    public function numColumns() {
        return count($this->columns);
    }

    /**
     * Bereitet den Query für die Anfrage vor.
     * Hier werden letzte Modifikationen des SQLs vorgenommen.
     *
     * @param object OOSQL Object der Liste
     * @access protected
     */
    public function prepareQuery(& $listsql) {
        global $CJO;

        $order_col = cjo_request('order_col', 'string', $this->def_order_col);
        $order_type = cjo_request('order_type', 'string', $this->def_order_type);

        if ($order_col != '') {
            $listsql->addOrderBy($order_col, $order_type);
        }
    }

    /**
     * Dieser Methode löst das Event für die Toolbars aus,
     * letzte Modifikationen an der cjoList vorzunehmen,
     * bevor die Verarbeitung der Ausgabe beginnt.
     *
     * @access protected
     */
    public function prepareToolbars() {
        $toolbars = $this->getToolbars();

        if (!is_array($toolbars)) return false;

        foreach($toolbars as $key=>$toolbar) {
            $toolbars[$key]->prepare();
        }
    }

    /**
     * Funktion zum setzen von statischen Texten
     *
     * @param integer Name des Platzhalters(Konstante)
     * @param string Statisches HTML, welches an Stelle des Platzhalters erscheint
     * @access public
     */
    public function setVar($name, $value) {
        $this->layoutVars[$name] = $value;
    }

    /**
     * Gibt den Wert eines statischen Textes zurück
     *
     * @param integer Name des Platzhalters(Konstante)
     * @access protected
     */
    public function getVar($name, $default = '') {
        if (!empty ($this->layoutVars[$name])) {
            return $this->layoutVars[$name];
        }
        return $default;
    }

    public function hasToolbars() {
        return ($this->_getToolbars($this->toolbars['top']['full']) != '' || $this->_getHalfToolbars($this->toolbars['top']['half']) != '' || $this->_getHalfToolbars($this->toolbars['bottom']['half']) != '' || $this->_getToolbars($this->toolbars['bottom']['full']) != '') ? true : false;
    }

    /**
     * Gibt die gerenderte Liste zurück
     *
     * @param bool [Flag, ob die Standardtoolbars hinzugefügt werden sollen -
     *             true => ja/ false => nein; default:true]
     * @access public
     */
    public function get($addDefaultToolbars = true) {

        global $CJO, $I18N;

        $s = '';
        // Show Messages
        $s .= $this->formatMessages();

        // Default Toolbars hinzufügen
        if ($addDefaultToolbars) {
            $this->addDefaultToolbars();
        }

        $this->prepareToolbars();

        // Benötigte Variablen definieren
        $rows = $this->getCurrentRows();

        $s .= $this->getVar(LIST_BEFORE_ALL); // Platzhalter
        $s .= '<!-- cjoList start -->'."\r\n";
        $s .= '<div class="a22-cjolist">'."\r\n";
        $s .= $this->getVar(LIST_VAR_TOP); // Platzhalter

        if ($this->hasToolbars()) {
            $s .= '  <form action="index.php" method="post">'."\r\n";
            $s .= '    <fieldset>'."\r\n";

            $label = $this->getLabel();
            if ($label != '') {
                $s .= '      <legend>'.$label.'</legend>'."\r\n";
            }

            // Alle Parameter für einen Post als hidden Übergeben
            foreach ($this->params as $_name => $_value) {
                if ($_name != '' && $_value != '') {
                    $s .= '      <input type="hidden" name="'.$_name.'" value="'.$_value.'" />'."\r\n";
                }
            }
        }
        // ------------ Kopfleisten

        $s .= $this->getVar(LIST_VAR_BEFORE_HEAD); // Platzhalter
        if ($this->_getToolbars($this->toolbars['top']['full']) != '' || $this->_getHalfToolbars($this->toolbars['top']['half']) != '') {

            $tb = $this->_getToolbars($this->toolbars['top']['full']);
            $tb .= $this->_getHalfToolbars($this->toolbars['top']['half']);
            if (!empty($tb)) {
                $s .= '      <div class="a22-cjolist-toolbars-top">'."\r\n".$tb.'</div>'."\r\n";
            }
        }
        $s .= $this->getVar(LIST_VAR_AFTER_HEAD); // Platzhalter

        // ------------ Datenbereich

        $s .= $this->getVar(LIST_VAR_BEFORE_DATAHEAD); // Platzhalter
        $s .= '      <div class="a22-cjolist-data">'."\r\n";

        if ($this->numCurrentRows() == 0){
            $this->setAttributes('style="background: transparent; padding-bottom: 0;"');
        }
        // ------------ Tabellenkopf

        if ($this->numCurrentRows() == 0){

        }

        $s .= '        <table cellspacing="0" cellpadding="0" border="0" '.$this->getAttributes().'>'."\r\n";
        $s .= $this->_getColGroup();

        $caption = $this->getCaption();
        if ($caption != '') {
            $s .= '        <caption>'.$caption.'</caption>'."\r\n";
        }

        $s .= '          <thead>'."\r\n";
        $s .= '            <tr>'."\r\n";


        for ($i = 0; $i < $this->numColumns(); $i++) {
            $column = & $this->columns[$i];

            if ($column->getLabel() === NULL) continue;

            if (strpos($column->getHeadAttributes(), 'headerSortDown') !== false ||
                strpos($column->getHeadAttributes(), 'headerSortUp') !== false) {

                $attributes = $this->columns[$i]->getBodyAttributes();
                $attributes = (strpos($attributes, 'class') !== false)
                            ? str_replace('class="', 'class="SortRow ', $attributes)
                            : $attributes.' class="SortRow"';

                $this->columns[$i]->body_attributes = '';
                $this->columns[$i]->setBodyAttributes($attributes);
            }

            $s .= sprintf('              <th%s><span>%s</span></th>'."\r\n", $column->getHeadAttributes(), $column->getLabel());
        }
        $s .= '            </tr>'."\r\n";
        $s .= '          </thead>'."\r\n";
        $s .= $this->getVar(LIST_VAR_AFTER_DATAHEAD); // Platzhalter

        if ($this->numCurrentRows() == 0 || $this->search_result == false) {
            // keine Daten vorhanden

            $colspan = (empty($this->realcolumns)) ? $this->numColumns() : $this->realcolumns;
            $temp    = $this->getVar(LIST_VAR_NO_DATA);
            if (empty($temp)) {
                $def_message = $this->search_result == false
                ? $I18N->msg('msg_af_no_found')
                : $I18N->msg('msg_af_empty');
            }
            else {
                $def_message = $this->getVar(LIST_VAR_NO_DATA);
            }

            if (strpos($def_message,'</') === false){
                $s .= '<tbody>'."\r\n".
	                  '<tr><td colspan="'.$colspan.'" style="padding:0;">'."\r\n".
	                  '	<span class="warning" style="background-color: transparent; margin:0!important;">'."\r\n".
	                  '		'.$def_message."\r\n".
	                  '	</span>'."\r\n".
	                  '</td></tr>'."\r\n".
	                  '</tbody>'."\r\n"; // Platzhalter
            }
            else {
                $s .= $def_message;
            }
        }

        // ------------ Tabellendaten

        if (is_array($rows)) {
            $extension_point = 'CJO_LIST_'.strtoupper($this->getName()).'_ROW_ATTR';
            $extension_is_registered = cjoExtension::isExtensionRegistered($extension_point);
            $s .= '          <tbody>'."\r\n";
            // Platzhalter

            $s .= $this->getVar(LIST_VAR_BEFORE_DATA);


            if (isset($rows[0]) && is_array($rows[0])) {
                foreach ($rows[0] as $key => $val) {
                    switch ($key) {
                        case 'id' :
                            $id_key = $key;
                            break 2;
                        case (strpos($key, '_id') !== false) :
                            $id_key = $key;
                            break 2;
                        default :
                            $id_key = false;
                            break;
                    }
                }
            }

            for ($t = 0; $t < count($rows); $t++) {
                $row = & $rows[$t];

                $cells_extension_point = 'CJO_LIST_'.strtoupper($this->getName()).'_CELLS';
                $cells_extension_is_registered = cjoExtension::isExtensionRegistered($cells_extension_point);

                $rowAttributes = '';
                if ($extension_is_registered) {
                    $rowAttributes = cjoExtension::registerExtensionPoint($extension_point,
                    array('row' => $row,
                                                                          		'line_number' => $t));
                }


                if ($row[$id_key] != false) {

                    if ((isset ($_GET['id']) && $row[$id_key] == $_GET['id']) ||
                    (isset ($_GET['oid']) && $row[$id_key] == $_GET['oid']) ||
                    (isset ($_GET[$id_key]) && $row[$id_key] == $_GET[$id_key])) {

                        if (strpos($rowAttributes, 'class') === false) {
                            $rowAttributes .= ' class="current"';
                        } else {
                            $rowAttributes = str_replace('class="', 'class="current ', $rowAttributes);
                        }
                    }
                    if(strpos($rowAttributes,'id="') === false){
                        $rowAttributes .= ' id="row_'.strtolower($this->getName()).'_'.$row[$id_key].'"';
                    }
                }

                $cells = array();

                for ($i = 0; $i < count($this->columns); $i++) {
                    $column = & $this->columns[$i];

                    $curr_name = $this->columns[$i]->name;
                    $cells[$curr_name]['body'] = $column->getBodyAttributes();
                    $cells[$curr_name]['cell'] = $column->format($row);
                    $cells[$curr_name]['unformated'] = (isset($row[$column->cjolist->columns[$i]->name])) ? $row[$column->cjolist->columns[$i]->name] : '';
                    

                    if ($cells_extension_is_registered) {
                        $cells = cjoExtension::registerExtensionPoint(
                        $cells_extension_point,
                        array ('cells' => $cells,
                                           'name' => $column->cjolist->columns[$i]->name,
                                           'unformated' => isset($row[$column->cjolist->columns[$i]->name])
                                                        ? $row[$column->cjolist->columns[$i]->name] : '',
                                    	   'format' => $column->format,
                                    	   'format_type' => $column->format_type,
                                           'formated' => $column->format($row),
                                           'row' => $row,
                                           'cell_number' => $i,
                                           'columns_number' => count($this->columns)));
                    }
                }

                $s .= '            <tr'.$rowAttributes.'>'."\r\n";

                foreach($cells as $key=>$cell){
                    if($cell['cell'] !== null){
                        $s .= '            	<td '.$cell['body'].'>'.($cell['cell'] != '' ? $cell['cell'] : '&nbsp;').'</td>'."\r\n";
                    }
                }

                $s .= '            </tr>'."\r\n";
            }
            $s .= $this->getVar(LIST_VAR_AFTER_DATA); // Platzhalter
            $s .= '          </tbody>'."\r\n";
        }

        $s .= '        </table>'."\r\n";
        $s .= '      </div>'."\r\n";

        // ------------ Fußleisten

        $s .= $this->getVar(LIST_VAR_BEFORE_FOOT); // Platzhalter

        $s .= '      <div class="a22-cjolist-toolbars-btm">'."\r\n";
        if ($this->_getHalfToolbars($this->toolbars['bottom']['half']) != '' || $this->_getToolbars($this->toolbars['bottom']['full']) != '' || $this->getVar(LIST_VAR_INSIDE_FOOT) != '') {
            $s .= $this->_getHalfToolbars($this->toolbars['bottom']['half']);
            $s .= $this->_getToolbars($this->toolbars['bottom']['full']);
            $s .= $this->getVar(LIST_VAR_INSIDE_FOOT); // Platzhalter

        }
        $s .= '      </div>'."\r\n";
        $s .= $this->getVar(LIST_VAR_AFTER_FOOT); // Platzhalter

        if ($this->hasToolbars()) {
            $s .= '    </fieldset>'."\r\n";
            $s .= '  </form>'."\r\n";
        }
        $s .= $this->getVar(LIST_VAR_BOTTOM); // Platzhalter
        $s .= '</div>'."\r\n";
        $s .= '<!-- cjoList end -->'."\r\n";

        return $s;
    }

    /**
     * Gibt die gerenderte Liste aus
     *
     * @param bool [Flag, ob die Standardtoolbars hinzugefügt werden sollen -
     *             true => ja/ false => nein; default:true]
     * @access public
     */
    public function show($addDefaultToolbars = true) {
        echo $this->get($addDefaultToolbars);
    }

    public function & getMessages() {
        return $this->messages;
    }

    public function setMessage($message, $message_type) {
        $this->messages[] = array($message,$message_type);
    }

    public function setMessages($message_array) {

        if (!is_array($message_array))
        return false;

        foreach ($message_array as $message_type => $messages) {

            if (is_array($messages)) {
                foreach ($messages as $message) {
                    $this->messages[] = array (
                    $message,
                    $message_type
                    );
                }
            }
        }
    }

    public function formatMessages() {

        $message = $this->getMessages();

        if (empty($messages) || !is_array($messages)) return false;

        foreach ($messages as $message){

            switch($message[1]){
                case FORM_ERROR_MSG   : cjoMessage::addError($message[0]); break;
                case FORM_INFO_MSG    : cjoMessage::addSuccess($message[0]); break;
                case FORM_WARNING_MSG : cjoMessage::addWarning($message[0]); break;
            }
        }
    }

    /**
     * Gibt die Übergebenen Toolbars auf voller Breite aus
     *
     * @access private
     */
    private function _generateToolbars($toolbars) {

        if (!is_array($toolbars)) return false;

        foreach(array('statusBar', 'searchBar', 'browseBar','maxElementsBar') as $key) {
            if (!empty($this->toolbars['content'][$key]) || empty($toolbars[$key])) continue;
            $this->toolbars['content'][$key] = $toolbars[$key]->show();
        }
    }
    
    /**
     * Gibt die Übergebenen Toolbars auf voller Breite aus
     *
     * @access private
     */
    public function _getToolbars($toolbars) {

        if (!is_array($toolbars)) return false;

        $s = '';
        foreach($toolbars as $key=>$toolbar) {
            $s .= '      <p class="'.$key.'" style="clear:both;">'."\r\n";
            $s .= '        '.$this->toolbars['content'][$key];
            $s .= '      </p>'."\r\n";
        }
        return $s;
    }

    /**
     * Gibt die übergebenen Toolbars auf halber Breite aus
     * @access private
     */
    public function _getHalfToolbars(& $toolbars) {

        if (!is_array($toolbars)) return false;

        $s = '';
        $i = 0;
        foreach($toolbars as $key=>$toolbar) {
            // Abwecheselnd rechts-/linksbündig ausrichten
            $tb = $this->toolbars['content'][$key];
            $tb = trim($tb);

            $class = $i % 2 == 1 ? ' class="cjo_float_r '.$key.'"' : ' class="cjo_float_l '.$key.'"';
            $i++;
            if (empty($tb)) continue;

            $s .= '        <p'.$class.$toolbar->tags().'>'."\r\n";
            $s .= '          '.$tb;
            $s .= '        </p>'."\r\n";
        }
        return $s;
    }

    /**
     * Setzt die Spaltenbreiten der Tabelle
     *
     * @param array Spaltenbreiten, wobei jeder index des Arrays einer Spalte entspricht
     * @access public
     */
    public function setColGroup($colgroup) {
        cjo_valid_type($colgroup, 'array', __FILE__, __LINE__);
        $this->colgroup = $colgroup;
    }

    /**
     * Gibt die Spaltenbreiten zurück
     *
     * @access protected
     */
    public function getColGroup() {
        return $this->colgroup;
    }

    /**
     * Gibt die in HTML formatierte Colgroup zurück
     *
     * @access protected
     */
    public function _getColGroup() {
        $s = '';

        $colgroup = $this->getColgroup();
        if (empty ($colgroup)) {
            return $s;
        }

        $s .= '          <colgroup>'."\r\n";
        foreach ($colgroup as $column) {
            $s .= '            <col width="'.$column.'" />'."\r\n";
        }
        $s .= '          </colgroup>'."\r\n";

        return $s;
    }
}