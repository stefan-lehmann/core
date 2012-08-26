<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
 * @version     2.7.x
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

$article['name']        = '_Send new Password';
$article['template_id'] = '1';
$article['type_id']     = 'out';

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => '',
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['send_password'],
    'value4'  => '_Send new Password',
    'value5'  => '1',
    'value6'  => '<p>Für Ihren Account wurde ein neues Passwort erstellt und an die  angegebene E-Mail-Adresse gesandt. </p><p>Sollten Sie weiterhin  Schwierigkeiten beim Anmelden haben, freuen wir uns über eine kurze  Nachricht. Vielen Dank.</p>',
    'value7'  => 'absenden',
    'value8'  => '_Send new Password',
    'value9'  => '1',
    'value10' => '1',
    'value11' => 'text{\r\n    label:E-Mail;\r\n    name:sender_email;\r\n    validate:(equal|not_empty);\r\n    required: 1;\r\n    error_msg:(E-Mail nicht gefunden!|Keine korrekte E-Mail-Adresse!)\r\n}\r\nfieldset{\r\n    label:Spamschutzsystem\r\n}\r\nantispam{\r\n    error_msg:Falsches Ergebnis!;\r\n    css: form_elm_smll\r\n}',
    'value12' => 'Benötigte Angaben',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => 'Ihr neues Passwort lautet: %new_password%\r\n\r\nHinweis: Sie können es nach erfolgreicher Anmeldung ändern.',
    'value19' => '0'
);