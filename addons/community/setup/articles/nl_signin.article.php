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

$article['name']        = '_Newsletter-SignIn';
$article['template_id'] = '1';
$article['type_id']     = 1;

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => '',
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['nl_signin'],
    'value4'  => 'Newsletter Anmeldung',
    'value5'  => '1',
    'value6'  => '<p>Vielen Dank für die Anmeldung!</p><p>Bitte bestätigen Sie das Newsletter-Abo durch Klick auf den Aktivierungs-Link in der soeben an Sie verschickten Bestätigungs-E-Mail. Senden Sie uns keine Antwort per E-Mail, da die Anmeldung sonst nicht abgeschlossen werden kann!</p>',
    'value7'  => 'absenden',
    'value8'  => '_Newsletter-SignIn',
    'value9'  => '1',
    'value10' => '1',
    'value11' => 'hidden{\r\n    name: groups;\r\n    default: 1;\r\n}\r\nselect{\r\n    label: Anrede;\r\n    name: gender;\r\n    values: ( |m=Herr|w=Frau);\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Anrede auswählen!;\r\n    css: form_elm_smll\r\n}\r\ntext{\r\n    label: Vorname;\r\n    name: firstname;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Vornamen angeben!\r\n}\r\ntext{\r\n    label: Name;\r\n    name: name;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Name angeben!\r\n}\r\ntext{\r\n     label: E-Mail;\r\n     name: sender_email;\r\n     required: 1;\r\n     validate: email;\r\n     error_msg: Keine korrekte E-Mail-Adresse!;\r\n     css: form_elm_norm;\r\n}\r\nfieldset{\r\n     default: Spamschutzsystem;\r\n}\r\nantispam{\r\n     error_msg: Falsches Ergebnis!;\r\n     css: form_elm_smll;\r\n}',
    'value12' => 'Benötigte Angaben',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => 'Sehr geehrte/r %title% %name%,\r\n\r\num Ihre Newsletter-Anmeldung abzuschließen, klicken Sie bitte auf den folgenden Link:\r\n\r\n-------------------------------------------------------------------------\r\n%confirm_link%\r\n-------------------------------------------------------------------------',
    'value19' => '0',
    'value20' => ''
);