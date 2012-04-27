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
 * CJO_MODULE_ID,
 * CJO_SLICE_ID,
 * CJO_CTYPE_ID
 */

class cjoVarGlobals extends cjoVars {

    // --------------------------------- Actions
    public function getACRequestValues($CJO_ACTION) {

        // SLICE ID im Update Mode setzen
        if ($this->isEditEvent()) {
            $CJO_ACTION['EVENT'] = 'EDIT';
            $CJO_ACTION['SLICE_ID'] = cjo_request('slice_id', 'int');
        }
        // SLICE ID im Delete Mode setzen
        elseif ($this->isDeleteEvent()) {
            $CJO_ACTION['EVENT'] = 'DELETE';
            $CJO_ACTION['SLICE_ID'] = cjo_request('slice_id', 'int');
        }
        // Im Add Mode 0 setze wg auto-increment
        else {
            $CJO_ACTION['EVENT'] = 'ADD';
            $CJO_ACTION['SLICE_ID'] = 0;
        }

        // Variablen hier einfuegen, damit sie in einer
        // Aktion abgefragt werden k�nnen
        $CJO_ACTION['ARTICLE_ID'] = cjo_request('article_id', 'int');
        $CJO_ACTION['CLANG_ID']   = cjo_request('clang', 'int');
        $CJO_ACTION['CTYPE_ID']   = cjo_request('ctype', 'int');
        $CJO_ACTION['MODULE_ID']  = cjo_request('module_id', 'int');

        return $CJO_ACTION;
    }

    public function getACDatabaseValues($CJO_ACTION, & $sql) {

        // Variablen hier einfuegen, damit sie in einer
        // Aktion abgefragt werden können
        $CJO_ACTION['ARTICLE_ID'] = $this->getValue($sql, 'article_id');
        $CJO_ACTION['CLANG_ID']   = $this->getValue($sql, 'clang');
        $CJO_ACTION['CTYPE_ID']   = $this->getValue($sql, 'ctype');
        $CJO_ACTION['MODULE_ID']  = $this->getValue($sql, 'modultyp_id');
        $CJO_ACTION['SLICE_ID']   = $this->getValue($sql, 'id');
        return $CJO_ACTION;
    }

    public function setACValues(& $sql, $CJO_ACTION, $escape = false) {
    }

    // --------------------------------- Output

    public function getBEOutput(& $sql, $content) {

        global $CJO;

        // Modulabhängige Globale Variablen ersetzen
        $content = preg_replace('/(?<!\[\[)CJO_MODULE_ID(?!\]\])/',   $this->getValue($sql, 'modultyp_id'), $content);
        $content = preg_replace('/(?<!\[\[)CJO_SLICE_ID(?!\]\])/',    $this->getValue($sql, 'id'), $content);
        $content = preg_replace('/(?<!\[\[)CJO_CTYPE_ID(?!\]\])/',    $this->getValue($sql, 'ctype'), $content);
        $content = preg_replace('/(?<!\[\[)CJO_RE_SLICE_ID(?!\]\])/', $this->getValue($sql, 're_article_slice_id'), $content);
        $content = preg_replace('/(?<!\[\[)CJO_IS_CONTEJO(?!\]\])/',  $CJO['CONTEJO'], $content);

        return $content;
    }
}