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

$article['name']        = '_Register User';
$article['template_id'] = '1';
$article['type_id']     = 'out';

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => '',
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['register_user'],
    'value4'  => '_Register User',
    'value5'  => '1',
    'value6'  => '<p>Ihr Benutzerkonto wurde angelegt. In kürze erhalten Sie eine E-Mail mit  dem Link zur Aktivierung. Nach der Aktivierung werden wir Ihre  Zugangsberechtigung überprüfen und den Zugang endgültig freigeben. </p>',
    'value7'  => 'absenden',
    'value8'  => '_Register User',
    'value9'  => '1',
    'value10' => '1',
    'value11' => 'hidden{\r\n    name: groups;\r\n    default: 1;\r\n}\r\ntext{\r\n    label: Benutzername;\r\n    name: username;\r\n    required: 1;\r\n    validate: (not_empty|not_equal);\r\n    error_msg: (Bitte Benutzernamen angeben!|Der Benutzername ist bereits vergeben!)\r\n}\r\ntext{\r\n    label: E-Mail;\r\n    name: sender_email;\r\n    required: 1;\r\n    validate: (mail|not_equal);\r\n    error_msg: (Keine korrekte E-Mail-Adresse!|Die E-Mail-Adresse ist bereits vorhanden!)\r\n}\r\npassword{\r\n    label: Passwort;\r\n    name: password;\r\n    required: 1;\r\n    validate: (not_empty|longer_than);\r\n    equal_value: 6;\r\n    error_msg: (Das Passwort fehlt!|Das Password muss mindestens 6 Zeichen haben!)\r\n}\r\nseparator{\r\n}\r\nselect{\r\n    label: Anrede;\r\n    name: gender;\r\n    values: ( |m=Herr|w=Frau);\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Anrede auswählen!;\r\n    css: form_elm_smll\r\n}\r\ntext{\r\n    label: Vorname;\r\n    name: firstname;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Vornamen angeben!\r\n}\r\ntext{\r\n    label: Name;\r\n    name: name;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Name angeben!\r\n}\r\ntext{\r\n    label: Firma;\r\n    name: company_name;\r\n    required: 0;\r\n    validate: not_empty;\r\n    error_msg: Bitte Firmennamen angeben!\r\n}\r\nseparator{\r\n}\r\ntext{\r\n    label: Straße Nr.;\r\n    name: street;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Straße und Hausnummer angeben!\r\n}\r\ntext{\r\n    label: Postleitzahl;\r\n    name: plz;\r\n    required: 1;\r\n    validate: plz;\r\n    error_msg: Bitte Postleitzahl korrekt angeben! [Format 12345]\r\n}\r\ntext{\r\n    label: Stadt;\r\n    name: town;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Name angeben!\r\n}\r\ntext{\r\n    label: Telefon;\r\n    name: phone;\r\n}\r\ntext{\r\n    label: Telefax;\r\n    name: company_fax;\r\n}\r\nseparator{\r\n}\r\ncheckbox{\r\n label: Newsletter abonnieren;\r\n   name: newsletter;\r\n   value: 1;\r\n   default: 1;\r\n css: form_elm_auto\r\n}\r\nfieldset{\r\n    default: Spamschutzsystem;\r\n}\r\nantispam{\r\n    error_msg: Falsches Ergebnis!;\r\n    css: form_elm_smll\r\n}',
    'value12' => 'Benötigte Angaben',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => 'Sehr geehrte/r %title% %name%,\r\n\r\nIhr Benutzerkonto wurde angelegt.\r\n\r\n-------------------------------------------------------\r\nBenutzername: %username%\r\nPasswort: %password%\r\n-------------------------------------------------------\r\n\r\nDas Konto muss noch mit folgendem Link aktiviert werden.\r\nNach der Aktivierung werden wir Ihre Berechtigung prüfen und den gewünschten Zugang endgültig freischalten.\r\n\r\n-------------------------------------------------------\r\n%confirm_link%\r\n-------------------------------------------------------',
    'value19' => '0',
    'value20' => ''
);