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
 * Abstrakte Basisklasse für CJO_VARS
 */

class cjoVars {
    // --------------------------------- Actions

    /**
     * Actionmethode:
     * Zum füllen des sql aus dem $CJO_ACTION Array
     */
    public function setACValues(& $sql, $CJO_ACTION, $escape = false) {
        // nichts tun
    }

    /**
     * Actionmethode:
     * Zum f�llen des $CJO_ACTION Arrays aus den Input Formularwerten
     * @return CJO_ACTION Array
     */
    public function getACRequestValues($CJO_ACTION) {
        return $CJO_ACTION;
    }

    /**
     * Actionmethode:
     * Zum f�llen des $CJO_ACTION Arrays aus der Datenbank (cjo_sql)
     * @return CJO_ACTION Array
     */
    public function getACDatabaseValues($CJO_ACTION, & $sql) {
        return $CJO_ACTION;
    }

    /**
     * Actionmethode:
     * Ersetzen der Werte in dem Aktionsscript
     * @return output String
     */
    public function getACOutput($CJO_ACTION, $content) {
        $sql = new cjoSql();
        $this->setACValues($sql, $CJO_ACTION);
        return $this->getBEOutput($sql, $content);
    }

    // --------------------------------- Ouput

    /**
     * Ausgabe eines Modules f�rs Frontend
     * sql Objekt mit der passenden Slice
     *
     * FE = Frontend
     */
    public function getFEOutput(& $sql, $content) {
        return $this->getBEOutput($sql, $content);
    }

    /**
     * Ausgabe eines Modules im Backend bei der Ausgabe
     * sql Objekt mit der passenden Slice
     *
     * BE = Backend
     */
    public function getBEOutput(& $sql, $content) {
        return $content;
    }

    /**
     * Ausgabe eines Modules im Backend bei der Eingabe
     * sql Objekt mit der passenden Slice
     *
     * BE = Backend
     */
    public function getBEInput(& $sql, $content) {
        return $this->getBEOutput($sql, $content);
    }

    /**
     * Ausgabe eines Templates
     */
    public function getTemplate($content, $article_id = false, $template_id = false) {
        return $content;
    }

    /**
     * Ausgabe eines HtmlTemplates
     */
    public function getHtmlTemplate($content, $slice = false) {
        return $content;
    }

    /**
     * Wandelt PHP Code in Einfache Textausgaben um
     */
    protected function stripPHP($content) {
        $content = str_replace(array("<?","?>"),array("&lt;?","?&gt;"),$content);
        return $content;
    }

    protected function cleanupInputs($content) {

        $replace_chars = array ('„' => '"', '“' => '"', '--' => '–', ' -- ' => ' – ', ' - ' => ' – ', '–' => '–', "\r\n"    => ' ');

        $content = html_entity_decode($content,ENT_QUOTES, "utf-8");
        $content = str_replace(array_keys($replace_chars),array_values($replace_chars),$content);
        $content = preg_replace('/<(h[1-6]|pre|div|address|blockquote)[^>]*>(.*?)<\/\1>/ims', '<p>\2</p>', $content); //konvertiert "unerwünschte" Elemente
        $content = preg_replace('/<(style|object|applet)\b[^>]*>(.*?)<\/\1>/i', '', $content);
        $content = preg_replace('/<\/*?(font|center|small|big)\b[^>]*>/i', '', $content);
        $content = preg_replace('/(^<br\s*\/>)(?=<p*\b[^>]*>|$)/ims', '', $content); //entfrent Umbrüche vor p-Elementen
        //$content = preg_replace('/(<(a|strong|u|em)*\b[^>]*>)(<br \/>)/ims', '', $content); //entfrent Umbrüche am Anfang eines a-Elemente
        //$content = preg_replace('/<a\s[^>]*?href=\"mailto:([^?"]*)(\"|\?.*?\")*?[^>]*>(.*?)<\/a>/ims', ' \1 ', $content); //entfernt eMail-Links
        $content = preg_replace('/<(a|strong|u|em)\b[^>]*>(\s?)<\/>/ims', '', $content); //entfernt leere Elemente
        $content = str_replace(array('“','”','"','“','”'), '"', $content);

        if (strpos($content, '<') !== false) {
            $content = preg_replace('/\x22(?=[^<>]*<)/ims', '___"___', $content);
            $content = preg_replace('/(?![\(\s|>])___"___(?=[\w|\d|\/])|„/', '„', $content);
            $content = preg_replace('/(?!\w)___"___(?=[\)|\x20|\W|\D|<|.$])|(?!\s)___"___(?=\W)|___"___$|“|”/','“', $content);
        } else {
            $content = preg_replace('/(?![\(\s|>])"(?=[\w|\d|\/])|„/', '„', $content);
            $content = preg_replace('/(?!\w)"(?=[\)|\W|\D|<|.$])|(?!\w)"$|"|“|”/','“', $content);
        }
        return $content;
    }

    /**
     * GetValue Wrapper, da hier immer auf die gleiche Tabelle gearbeitet wird und
     * mit MySQL 3.x mit Tabellenprefix angegeben werden muss, da der SQL gleichnamige
     * Spalten unterschiedlicher Tabellen enthält.
     */
    protected function getValue(& $object, $value) {
            
        if (OOArticleSlice::isValid($object)) {
            
            if (preg_match('/(\D+)(\d+)/',$value, $matches)) { 
                switch($matches[1]) {
                    case 'value'     : return $object->getValue($matches[2]);
                    case 'link'      : return $object->getLink($matches[2]);
                    case 'file'      : return $object->getFile($matches[2]);   
                    case 'filelist'  : return $object->getFile($matches[2]);                   
                }
            }
            return $object;
        } 
        else if (cjoSql::isValid($object)) {
            return $object->getValue('sl.'.$value);
        }
    }
    /**
     * setValue Wrapper, da hier immer auf die gleiche Tabelle gearbeitet wird und
     * mit MySQL 3.x mit Tabellenprefix angegeben werden muss, da der SQL gleichnamige
     * Spalten unterschiedlicher Tabellen enthält.
     */
    protected function setValue(& $sql, $fieldname, $value, $escape = false) {
        //if ($escape) $value = addslashes($value);
        return $sql->setValue($fieldname, $value);
    }

    /**
     * Callback um nicht explizit gehandelte OutputParameter zu behandeln
     */
    public function handleDefaultParam($varname, $args, $name, $value) {

         switch($name) {
            case '0'        : $name = 'id';
            case 'id'       :
            case 'prefix'   :
            case 'suffix'   :
            case 'ifempty'  :
            case 'instead'  :
            case 'equal'    : 
            case 'notequal' :                                
            case 'callback' : $args[$name] = (string) $value;
        }

        return $args;
    }

    /**
     * Parameter aus args auf die Ausgabe eines Widgets anwenden
     */
    public function handleGlobalWidgetParams($varname, $args, $value) {
        return $value;
    }

    /**
     * Parameter aus args auf den Wert einer Variablen anwenden
     */
    public static function handleGlobalVarParams($varname, $args, $value) {

        if (isset($args['callback'])) {
            $args['subject'] = $value;
            return cjoExtension::callFunction($args['callback'], $args);
        }

        if (isset($args['equal'])) return $args['equal'] == $value;
        
        if (isset($args['notequal'])) return $args['notequal'] != $value;
        
        $prefix = '';
        if (isset($args['prefix'])) $prefix = $args['prefix'];

        $suffix = '';
        if (isset($args['suffix']))  $suffix = $args['suffix'];
        if (isset($args['instead']) && $value != '') $value = $args['instead'];
        if (isset($args['ifempty']) && $value == '') $value = $args['ifempty'];

        return $prefix.$value.$suffix;
    }

    /**
     * Parameter aus args zur Laufzeit auf den Wert einer Variablen anwenden.
     * Wichtig für Variablen, die Variable ausgaben haben.
     */
    public static function handleGlobalVarParamsSerialized($varname, $args, $value) {
        $varname = str_replace('"', '\"', $varname);
        $args = str_replace('"', '\"', serialize($args));
        return 'cjoVars::handleGlobalVarParams("'. $varname .'", unserialize("'. $args .'"), '. $value .')';
    }

    /**
     * Findet die Parameter der Variable $varname innerhalb des Strings $content.
     *
     * @access protected
     */
    public function getVarParams($content, $varname) {

        $result = array ();
        $match  = $this->matchVar($content, $varname);

        foreach ($match as $param_str) {

            $args = array();
            $params = $this->splitString($param_str);
            foreach ($params as $name => $value) {
                $args = $this->handleDefaultParam($varname, $args, $name, $value);
            }

            $result[] = array($param_str, $args );
        }
        return $result;
    }

    /**
     * Durchsucht den String $content nach Variablen mit dem Namen $varname.
     * Gibt die Parameter der Treffer (Text der Variable zwischen den []) als Array zur�ck.
     */
    public function matchVar($content, $varname) {

        $result = array ();

        if (preg_match_all('/'.preg_quote($varname, '/').'\[((?:\[.*?\]|.)*?)\]/ms', $content, $matches)) {
            foreach ($matches[1] as $match)  {
                $result[] = $match;
            }
        }
        return $result;
    }

    public function extractArg($name, $args, $default = null) {

        $val = $default;

        if (isset($args[$name]))  {
            $val = $args[$name];
            unset($args[$name]);
        }
        return array($val, $args);
    }

    /**
     * Trennt einen String an Leerzeichen auf.
     * Abschnitte die in "" oder '' stehen, werden als ganzes behandelt und
     * darin befindliche Leerzeichen nicht getrennt.
     * @access protected
     */
    public function splitString($string) {
        return cjoAssistance::splitString($string);
    }

    public function isAddEvent() {
        return cjo_request('function', 'string') == 'add';
    }

    public function isEditEvent() {
        return cjo_request('function', 'string') == 'edit';
    }

    public function isDeleteEvent() {
        return cjo_request('function', 'string') == 'delete';
    }
}