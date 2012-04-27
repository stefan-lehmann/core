<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
 * @version     2.6.0
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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

global $CJO;

$article['name']        = '_Add-Comment';
$article['template_id'] = '1';
$article['type_id']     = 1;

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => $CJO['ERROR_EMAIL'],
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['add_comment'],
    'value4'  => $CJO['SERVERNAME']." | add_comment",
    'value5'  => '1',
    'value6'  => "<p><strong>Danke für Ihren Eintrag!</strong> Wir behalten uns jedoch das Recht vor, inhaltlich und rechtlich bedenkliche Kommentare sowie Spam-Einträge zu löschen.</p>",
    'value7'  => 'Absenden',
    'value8'  => '_Add-Comment',
    'value9'  => '1',
    'value10' => '0',
    'value11' => "text{\r\nlabel: Name;\r\nname: author;\r\nrequired: 1;\r\nvalidate: (not_empty|alphanumeric);\r\nerror_msg: (Bitte Namen angeben!|Die Eingabe enthält unerlaubte Zeichen!)\r\n}\r\ntext{\r\nlabel: Webseite;\r\nname: url;\r\nvalidate: url;\r\nerror_msg: URL bitte im Format \\(http\\://www.domain.xy\\) angeben!\r\n}\r\ntext{\r\nlabel: eMail;\r\nname: email;\r\nrequired: 1;\r\nvalidate: email;\r\nerror_msg: Die eMail-Adresse ist ungültig!\r\n}\r\nnotice{\r\ndefault: eMail-Adresse wird nicht veröffentlicht!\r\n}\r\nseparator{\r\n}\r\ntextarea{\r\nlabel: Kommentar;\r\nname: message;\r\nrequired: 1;\r\nvalidate: not_empty;\r\nerror_msg: Der Kommentar fehlt!\r\n}\r\nseparator{\r\n}\r\nheadline{\r\ndefault: Spamschutzsystem;\r\n}\r\nantispam{\r\nerror_msg: Falsches Ergebnis!;\r\ncss: form_elm_smll\r\n}",
    'value12' => 'Bitte Angeben!',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => '',
    'value19' => '0',
    'value20' => ''
);