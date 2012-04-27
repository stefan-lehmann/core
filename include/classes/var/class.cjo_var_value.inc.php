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
 * CJO_VALUE[1-19],
 * CJO_VALUE[id=1 equal=Baum],
 * CJO_HTML_VALUE[1-19],
 * CJO_IS_VALUE[1-19],
 * CJO_CHECKED_VALUE[1-19],
 * CJO_PHP
 */

class cjoVarValue extends cjoVars{

    public function getACRequestValues($CJO_ACTION) {

        $values = cjo_request('VALUE', 'array');
        for ($i = 1; $i <= 20; $i++) {

            // if ($i <= 19 && $CJO_ACTION['WYM'][$i] != '') {
                // $CJO_ACTION['VALUE'][$i] = $CJO_ACTION['WYM'][$i];
                // continue;
            // }
            $value = isset($values[$i]) ? $values[$i] : '';
            $CJO_ACTION['VALUE'][$i] = $value;
        }
        $CJO_ACTION['INPUT_PHP'] = stripslashes(cjo_request('INPUT_PHP', 'string'));
        $CJO_ACTION['INPUT_HTML'] = $this->stripPHP(stripslashes(cjo_request('INPUT_HTML', 'string')));

        $CJO_EXT_VALUE = cjo_request('CJO_EXT_VALUE', 'array');
        $media   = cjo_request('MEDIA', 'array');

        if (empty($CJO_EXT_VALUE))  return $CJO_ACTION;

        $value20 = $CJO_EXT_VALUE;

        for($i=1; $i <= 10; $i++) {
            if (empty($media[$i])) {
                unset($value20[$i]);
                continue;
            }
            if (!isset($CJO_EXT_VALUE[$i])) $value20[$i] = $CJO_EXT_VALUE[$i];
        }
        $CJO_ACTION['VALUE'][20] = serialize($value20);

        return $CJO_ACTION;

    }

    public function getACDatabaseValues($CJO_ACTION, & $sql) {

        for ($i = 1; $i <= 20; $i++) {
            $CJO_ACTION['VALUE'][$i] = $this->getValue($sql, 'value'. $i);
        }
        $CJO_ACTION['INPUT_PHP'] = $this->getValue($sql, 'php');
        $CJO_ACTION['INPUT_HTML'] = $this->getValue($sql, 'html');

        return $CJO_ACTION;
    }

    public function setACValues(& $sql, $CJO_ACTION, $escape = false) {

        global $CJO;

        for ($i = 1; $i <= 20; $i++) {
            $this->setValue($sql, 'value'.$i, $CJO_ACTION['VALUE'][$i], $escape);
        }
        $this->setValue($sql, 'php', $CJO_ACTION['INPUT_PHP'], $escape);
        $this->setValue($sql, 'html', $CJO_ACTION['INPUT_HTML'], $escape);
    }

    // --------------------------------- Output

    public function getBEOutput(& $sql, $content) {
        $content = $this->getOutput($sql, $content, true);
        $php_content = $this->getValue($sql, 'php');
        $content = str_replace('CJO_PHP', $this->stripPHP($php_content), $content);
        return $content;
    }

    public function getBEInput(& $sql, $content) {
        $content = $this->getOutput($sql, $content);
        $content = str_replace('CJO_PHP', htmlspecialchars($this->getValue($sql, 'php'),ENT_QUOTES), $content);
        return $content;
    }

    public function getFEOutput(& $sql, $content) {
        $content = $this->getOutput($sql, $content, true);
        // $content = str_replace('CJO_PHP', $this->getValue($sql, 'php'), $content);
        return $content;
    }

    public function getOutput(& $sql, $content, $nl2br = false) {

        $content = $this->matchValue($sql, $content, $nl2br);
        $content = $this->matchHtmlValue($sql, $content);
        $content = $this->matchIsValue($sql, $content);
        $content = $this->matchCheckedValue($sql, $content);   
        $content = $this->matchPhpValue($sql, $content);
        // $content = str_replace('CJO_HTML', $this->getValue($sql, 'html'), $content);

        return $content;
    }

    /**
     * Wert für die Ausgabe
     */
    private function _matchValue(& $sql, $content, $var, $escape = false, $nl2br = false, $stripPHP = false, $booleanize = false) {

        $matches = $this->getVarParams($content, $var);
        $performed = array();
        
        foreach ($matches as $match) {
            
            list ($param_str, $args) = $match;
            list ($id, $args) = $this->extractArg('id', $args, 0);
            $identifier = (empty($param_str)) ? $id : $param_str;

            if (!empty($performed[$var][$identifier]) || $id < 1 || $id > 20) continue; 

            $replace = $this->getValue($sql, 'value'.$id);

            if ($booleanize) {
                $replace = empty($replace) ? 'false' : 'true';
            }
            else {
                if ($escape) {
                    $replace = htmlspecialchars($replace,ENT_QUOTES);
                }
                if ($nl2br) {
                    $replace = nl2br($replace);
                }
                if ($stripPHP) {
                    $replace = $this->stripPHP($replace);
                }
            }

            $replace = $this->handleGlobalVarParams($var, $args, $replace);

            if ($var == 'CJO_HTML_VALUE') $replace = addslashes($replace);
            
            if ($var == 'CJO_CHECKED_VALUE') {
                $replace = $replace == 'true' ? 'checked="checked"' : '';
            }

            $content = preg_replace('/(?<!\[\[)'.$var.'\['.preg_quote($param_str).'\](?!\]\])/', $replace, $content);
            $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
            
            $performed[$var][$identifier] = true;
        } 
        return $content;
    }

    private function matchValue(& $sql, $content, $nl2br = false) {
        return $this->_matchValue($sql, $content, 'CJO_VALUE', true, $nl2br);
    }

    private function matchHtmlValue(& $sql, $content) {
        return $this->_matchValue($sql, $content, 'CJO_HTML_VALUE', false, false, true);
    }

    private function matchPhpValue(& $sql, $content) {
        return $this->_matchValue($sql, $content, 'CJO_PHP_VALUE', false, false, false);
    }

    private function matchIsValue(& $sql, $content) {
        return $this->_matchValue($sql, $content, 'CJO_IS_VALUE', false, false, false, true);
    }
    
    private function matchCheckedValue(& $sql, $content) {
        return $this->_matchValue($sql, $content, 'CJO_CHECKED_VALUE', false, false, false, true);
    }
}