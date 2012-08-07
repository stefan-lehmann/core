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
 * Gibt die Tabellen einer Datenbank zurück
 * @access public
 */
function cjo_dbmeta_db_tables($database = '') {

    global $CJO;
    $database = _cjo_dbmeta_get_db($database);

    if (empty ($CJO['DB']['META'][$database]['TABLES'])) {
        // fetch db infos
        _cjo_dbmeta_get_tables($database);
    }

    return $CJO['DB']['META'][$database]['TABLES'];
}

/**
 * Gibt die Spaltennamen einer Tabelle zurück
 * @access public
 */
function cjo_dbmeta_table_cols($table, $database = '') {
    global $CJO;
    $database = _cjo_dbmeta_get_db($database);

    if (empty ($CJO['DB']['META'][$database]['TABLES'][$table]['COLNAMES'])) {
        // fetch db infos
        _cjo_dbmeta_get_colinfos($table, $database);
    }

    return $CJO['DB']['META'][$database]['TABLES'][$table]['COLNAMES'];
}

/**
 * Gibt ein Array mit den Spaltennanmen des PrimaryKeys zurück
 * @access public
 */
function cjo_dbmeta_table_primkey($table, $database = '') {
    global $CJO;
    $database = _cjo_dbmeta_get_db($database);

    if (empty ($CJO['DB']['META'][$database]['TABLES'][$table]['PRIMKEYS'])) {
        // fetch db infos
        _cjo_dbmeta_get_colinfos($table, $database);
    }

    return $CJO['DB']['META'][$database]['TABLES'][$table]['PRIMKEYS'];
}

/**
 * Gibt den Namen der AutoIncrement Spalte zurück
 * @access public
 */
function cjo_dbmeta_table_autoinccol($table, $database = '') {
    global $CJO;
    $database = _cjo_dbmeta_get_db($database);

    if (empty ($CJO['DB']['META'][$database]['TABLES'][$table]['AUTOINC'])) {
        // fetch db infos
        _cjo_dbmeta_get_colinfos($table, $database);
    }

    return $CJO['DB']['META'][$database]['TABLES'][$table]['AUTOINC'];
}

/**
 * Gibt erweiterte Spalteninformationen zurück
 * @access public
 */
function cjo_dbmeta_table_col_details($table, $database = '') {
    global $CJO;
    $database = _cjo_dbmeta_get_db($database);

    if (empty ($CJO['DB']['META'][$database]['TABLES'][$table]['COLUMNS'])) {
        // fetch db infos
        _cjo_dbmeta_get_colinfos($table, $database);
    }
    return $CJO['DB']['META'][$database]['TABLES'][$table]['COLUMNS'];
}

/**
 * @access private
 */
function _cjo_dbmeta_get_db($database = '') {
    if (strlen($database) == 0) {
        global $CJO;
        return $CJO['DB']['1']['NAME'];
    }
    return $database;
}

/**
 * @access private
 */
function _cjo_dbmeta_get_tables($database) {
    global $CJO;

    // Validate Arguments
    _cjo_dbmeta_validate($database, ' ', __FILE__, __LINE__);

    if (empty ($CJO['DB']['META'][$database]['TABLES'])) {
        $sql = new cjoSql();
        $result = $sql->getArray('SHOW TABLES FROM ' . $database, PDO::FETCH_NUM);
        $tables = array ();

        if (is_array($result)) {
            foreach ($result as $row) {
                $tables[] = $row[0];
            }
        }

        $CJO['DB']['META'][$database]['TABLES'] = $tables;
    }
}

/**
 * @access private
 */
function _cjo_dbmeta_get_colinfos($table, $database) {
    global $CJO;

    // Validate Arguments
    _cjo_dbmeta_validate($table, $database, __FILE__, __LINE__);

    if (empty ($CJO['DB']['META'][$database]['TABLES'][$table]['COLNAMES'])) {
        $sql = new cjoSql();
        $result = $sql->getArray('SHOW FULL COLUMNS FROM ' . $table . ' FROM ' . $database, PDO::FETCH_NUM);

        $colums = array ();
        $colnames = array ();
        $primkeys = array ();
        $autoinc = '';
        if (is_array($result)) {
            $serverVersion = sql :: getServerVersion();
            $mainVersion = $serverVersion {
                0 };

                foreach ($result as $row) {
                    $column = array ();
                    $column['NAME'] = $row[0];
                    $column['TYPE'] = $row[1];
                    $column['NULL'] = $row[2];

                    // Mysql 4<->5 versionsweiche
                    switch ($mainVersion) {
                        case 4 :
                            {
                                $column['KEY'] = $row[3];
                                $column['DEFAULT'] = $row[4];
                                $column['EXTRA'] = $row[5];
                                break;
                            }
                        case 5 :
                            {
                                $column['KEY'] = $row[4];
                                $column['DEFAULT'] = $row[5];
                                $column['EXTRA'] = $row[6];
                                break;
                            }
                    }

                    $colums[] = $column;
                    $colnames[] = $column['NAME'];

                    // AutoInc
                    if ($column['EXTRA'] == 'auto_increment') {
                        $autoinc = $column['NAME'];
                    }

                    // PrimaryKeys
                    if ($column['KEY'] == 'PRI') {
                        $primkeys[] = $column['NAME'];
                    }
                }
        }

        $CJO['DB']['META'][$database]['TABLES'][$table]['COLUMNS'] = $colums;
        $CJO['DB']['META'][$database]['TABLES'][$table]['COLNAMES'] = $colnames;
        $CJO['DB']['META'][$database]['TABLES'][$table]['PRIMKEYS'] = $primkeys;
        $CJO['DB']['META'][$database]['TABLES'][$table]['AUTOINC'] = $autoinc;
    }
}

/**
 * @access private
 */
function _cjo_dbmeta_validate($table, $database, $file, $line) {
    if (empty ($table)) {
        trigger_error('cjoDBMeta: Table name $table is empty in <b>' . $file . '</b> on Line <b>' . $line . '</b>', E_USER_ERROR);
    }

    if (empty ($database)) {
        trigger_error('cjoDBMeta: Database name $database is empty in <b>' . $file . '</b> on Line <b>' . $line . '</b>', E_USER_ERROR);
    }
}