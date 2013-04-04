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

if (!cjoProp::isBackend()) return false;
 

/**
 * Bindet eine CSS Datei via Extension Point in den Quelltext ein
 *
 * @param string Quelltext des Artikels
 * @param string|array Datei(en) die eingebunden werden sollen
 * @access private
 */
function cjo_insertCss($content, $files) {

    $styles = '';

    foreach (cjoAssistance::toArray($files) as $file) {
        if (empty($file) || cjoProp::get('AFC|css|'.$file)) continue;
        // CSS-Datei merken, damit jedes nur einmal eingebunden wird
        cjoProp::set('AFC|css|'.$file, true);
        $styles .= '<link rel="stylesheet" type="text/css" href="'.cjoUrl::backend('get_file.php?file='.rawurlencode($file)).'" />'."\n";
    }
    if ($content === false) {
        echo $styles;
        return;
    }
    return ($styles != '') ? preg_replace('/<\/head>/i', $styles.'</head>', $content, 1) : $content;
}

/**
 * Bindet eine JS Datei via Extension Point in den Quelltext ein
 *
 * @param string Quelltext des Artikels
 * @param string|array Datei(en) die eingebunden werden sollen
 * @access private
 */
function cjo_insertJS($content, $files) {

    $js = '';
    foreach (cjoAssistance::toArray($files) as $file) {
        if (empty($file) || cjoProp::get('AFC|js|'.$file)) continue;
        // JS-Datei merken, damit jedes nur einmal eingebunden wird
        cjoProp::set('AFC|js|'.$file, true);
        $js .= '<script type="text/javascript" src="'.cjoUrl::backend('get_file.php?file='.rawurlencode($file)).'"></script>'."\n";
    }
    if ($content === false) {
        echo $js;
        return;
    }
    return ($js != '') ? str_replace('</head>', $js.'</head>', $content) : $content;
}


/**
 * Gibt den aktuellen Tabindex zurück
 * Der Tabindex ist eine stetig fortlaufende Zahl,
 * welche die Priorität der Tabulatorsprünge des Browsers regelt.
 *
 * @return integer aktueller Tabindex
 */
function cjo_a22_getTabindex() {
    if (!cjoProp::get('tabindex')) {
        cjoProp::set('tabindex', 0);
    }
    return cjoProp::get('tabindex');
}

/**
 * Gibt den nächsten freien Tabindex zurück.
 * Der Tabindex ist eine stetig fortlaufende Zahl,
 * welche die Priorität der Tabulatorsprünge des Browsers regelt.
 *
 * @return integer nächster freier Tabindex
 */
function cjo_a22_nextTabindex() {
    if (!cjoProp::get('tabindex')) {
        cjoProp::set('tabindex', 0);
    }
    cjoProp::set('tabindex', cjoProp::get('tabindex') +1);
    return cjoProp::get('tabindex');
}

function array_delete_key(& $array, $keyname) {
    unset ($array[$keyname]);
    $array = array_values($array);
}

function array_resort_keys(& $array) {
    return array_values($array);
}