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
 * Klasse zur Formatierung von Strings
 */
class cjoFormatter {

    /**
     * Formatiert den String <code>$value</code>
     *
     * @param $value zu formatierender String
     * @param $format_type Formatierungstype
     * @param $format Format
     *
     * Unterstützte Formatierugen:
     *
     * - <Formatierungstype>
     *    + <Format>
     *
     * - sprintf
     *    + siehe www.php.net/sprintf
     * - date
     *    + siehe www.php.net/date
     * - strftime
     *    + dateformat
     *    + datetime
     *    + siehe www.php.net/strftime
     * - number
     *    + siehe www.php.net/number_format
     *    + array( <Kommastelle>, <Dezimal Trennzeichen>, <Tausender Trennzeichen>)
     * - email
     *    + array( 'attr' => <Linkattribute>, 'params' => <Linkparameter>,
     * - url
     *    + array( 'attr' => <Linkattribute>, 'params' => <Linkparameter>,
     * - truncate
     *    + array( 'length' => <String-Laenge>, 'etc' => <ETC Zeichen>, 'break_words' => <true/false>,
     * - nl2br
     *    + siehe www.php.net/nl2br
     * - cjomedia
     *    + siehe www.php.net/nl2br
     */
    public static function format($value, $format_type, $format) {

        global $I18N, $CJO;

        if ($value === null) return '';

        // Stringformatierung mit sprintf()
        if ($format_type == 'sprintf') {
            $value = self::_formatSprintf($value, $format);
        }
        // Datumsformatierung mit date()
        elseif ($format_type == 'date') {
            $value = self::_formatDate($value, $format);
        }
        // Datumsformatierung mit strftime()
        elseif ($format_type == 'strftime') {
            $value = self::_formatStrftime($value, $format);
        }
        // Zahlenformatierung mit number_format()
        elseif ($format_type == 'number') {
            $value = self::_formatNumber($value, $format);
        }
        // Email-Mailto Linkformatierung
        elseif ($format_type == 'email') {
            $value = self::_formatEmail($value, $format);
        }
        // URL-Formatierung
        elseif ($format_type == 'url') {
            $value = self::_formatUrl($value, $format);
        }
        // String auf eine eine Länge abschneiden
        elseif ($format_type == 'truncate') {
            $value = self::_formatTruncate(cjoAssistance::htmlToTxt($value), $format);
        }
        // Newlines zu <br />
        elseif ($format_type == 'nl2br') {
            $value = self::_formatNl2br($value, $format);
        }
        // CONTEJO Medienpool files darstellen
        elseif ($format_type == 'cjomedia' && $value != '') {
            $value = self::_formatCjoMedia($value, $format);
        }
        // formatReplace
        elseif ($format_type == 'replace' && $value != '') {
            $value = self::_formatReplace($value, $format);
        }
        // formatReplace
        elseif ($format_type == 'replace_array' && $value != '') {
            $value = self::_formatReplaceArray($value, $format);
        }
        // format preg_Replace
        elseif ($format_type == 'preg_replace' && $value != '')  {
            $value = self::_formatPregReplace($value, $format);
        }
        // format preg_Replace
        elseif ($format_type == 'call_user_func' && $value != '') {
            $value = self::_formatCallUserFunc($value, $format);
        }
        return $value;
    }

    // Stefan Lehmann
    private static function _formatPregReplace($value, $format) {

        cjo_valid_type($format[0], 'string', __FILE__, __LINE__);
        cjo_valid_type($format[1], 'string', __FILE__, __LINE__);

        $pattern = $format[0];
        $replace = $format[1];

        $string = preg_replace($pattern,$replace,$value);

        return $string;
    }

    private static function _formatCallUserFunc($value, $format) {
        cjo_valid_type($format[0], 'string', __FILE__, __LINE__);
        cjo_valid_type($format[1], 'array', __FILE__, __LINE__);

        $function = $format[0];
        $params = implode("' , '", $format[1]);
        $params = sprintf($params, $value);
        $params = str_ireplace('\'null\'','null', $params);

        eval('$replace =  '.$function.'(\''.$params.'\');');
        return $replace;
    }

    private static function _formatReplaceArray($value, $format) {
        cjo_valid_type($format[0], 'array', __FILE__, __LINE__);
        cjo_valid_type($format[1], 'string', __FILE__, __LINE__);
        cjo_valid_type($format['delimiter_in'], 'string', __FILE__, __LINE__);
        cjo_valid_type($format['delimiter_out'], 'string', __FILE__, __LINE__);

        $replace_values = $format[0];

        if ($format['delimiter_in'] == '') return $value;

        $search_values = explode($format['delimiter_in'],sprintf($format[1], $value));
        $search_values = array_diff($search_values,array(''));
        $replace = '';

        foreach($search_values as $value){
            if(array_search($value, array_keys($replace_values)) !== false){
                $replace .= ($replace != '') ? $format['delimiter_out'] : '';
                $replace .= isset($replace_values[$value]) ? $replace_values[$value] : '';
            } else {
                $replace .= ($replace != '') ? $format['delimiter_out'] : '';
                $replace .= '--';
            }
        }
        return $replace;
    }

    private static function _formatReplace($value, $format) {
        cjo_valid_type($format[0], 'array', __FILE__, __LINE__);

        if (!empty($format[1])){
            cjo_valid_type($format[1], 'string', __FILE__, __LINE__);

            if($format[0][$value] == ''){
                return '--';
            }
        }
        return $format[0][$value];
    }
    // Stefan Lehmann

    private static function _formatSprintf($value, $format) {
        cjo_valid_type($format, 'string', __FILE__, __LINE__);

        if ($format == '') {
            $format = '%s';
        }
        if ($value != '') return sprintf($format, $value);
    }

    private static function _formatDate($value, $format) {
        cjo_valid_type($format, 'string', __FILE__, __LINE__);

        if (!is_string($value) || empty($value)) return '--';

        if ($format == '') {
            $format = 'd.m.y';
        }

        if (!is_numeric($value)) {
            $values = preg_split('/\D/', $value, -1, PREG_SPLIT_NO_EMPTY);
            $outut = '';
            foreach ($values as $value){
                $outut .= date($format, $value).' ';
            }
            return $outut;
        }

        return date($format, $value);
    }

    private static function _formatStrftime($value, $format) {

        global $I18N;

        cjo_valid_type($format, 'string', __FILE__, __LINE__);

        if (empty($value) && $value !== '0' && $value !== 0) {
            return '';
        }

        if ($format == '' || $format == 'dateformat') {
            // Default CJO-Dateformat
            $format = $I18N->msg('dateformat');
        }
        elseif ($format == 'datetime') {
            // Default CJO-Datetimeformat
            $format = $I18N->msg('datetimeformat');
        }
        return strftime($format, $value);
    }

    private static function _formatNumber($value, $format) {

        if (!is_array($format)) {
            $format = array ();
        }

        // Kommastellen
        if (empty ($format[0])) {
            $format[0] = 2;
        }
        // Dezimal Trennzeichen
        if (empty ($format[1])) {
            $format[1] = ',';
        }
        // Tausender Trennzeichen
        if (empty ($format[2])) {
            $format[2] = ' ';
        }
        return number_format($value, $format[0], $format[1], $format[2]);
    }

    private static function _formatEmail($value, $format) {
        if (!is_array($format)) {
            $format = array ();
        }

        // Linkattribute
        if (empty ($format['attr'])) {
            $format['attr'] = '';
        }
        // Linkparameter (z.b. subject=Hallo Sir)
        if (empty ($format['params'])) {
            $format['params'] = '';
        }
        else {
            if (!startsWith($format['params'], '?')) {
                $format['params'] = '?'.$format['params'];
            }
        }
        // Url formatierung
        return '<a href="mailto:'.$value.$format['params'].'"'.$format['attr'].'>'.$value.'</a>';
    }

    private static function _formatUrl($value, $format) {
        if (!is_array($format)) {
            $format = array ();
        }

        // Linkattribute
        if (empty ($format['attr'])) {
            $format['attr'] = '';
        }
        // Linkparameter (z.b. subject=Hallo Sir)
        if (empty ($format['params']))  {
            $format['params'] = '';
        }
        else {
            if (!startsWith($format['params'], '?')) {
                $format['params'] = '?'.$format['params'];
            }
        }
        // Protokoll
        if (!preg_match('@(http|https|ftp|ftps|telnet|contejo)://@', $value)) {
            $value = 'http://'.$value;
        }

        return '<a href="'.$value.$format['params'].'"'.$format['attr'].'>'.$value.'</a>';
    }

    private static function _formatTruncate($value, $format) {

        if (!is_array($format)) {
            $format = array ();
        }

        // String-laenge
        if (empty ($format['length'])) {
            $format['length'] = 80;
        }
        // ETC
        if (empty ($format['etc'])) {
            $format['etc'] = '...';
        }
        // Break-Words?
        if (empty ($format['break_words'])) {
            $format['break_words'] = false;
        }

        return truncate($value, $format['length'], $format['etc'], $format['break_words']);
    }

    private static function _formatNl2br($value, $format) {
        return nl2br($value);
    }

    private static function _formatCjoMedia($file, $format) {

        $params = (empty($format['params']) || !is_array($format['params'])) ? array() : $format['params'];

        // Resize aktivieren, falls nicht anders übergeben
        if (empty ($params['resize'])) {
            $params['resize'] = true;
        }
        // Bilder als Thumbnail
        if (OOMedia::isImage($value)) {
            $file = OOMedia::toImage($file, $params);
        }
        // Sonstige mit Mime-Icons
        else {
            $file = OOMedia::toIcon($file);
        }
        return $file;
    }
}