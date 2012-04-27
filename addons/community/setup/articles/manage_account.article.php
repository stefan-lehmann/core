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

$article['name']        = '_Manage My Account';
$article['template_id'] = '1';
$article['type_id']     = 'in';

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => '',
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['manage_account'],
    'value4'  => '',
    'value5'  => '0',
    'value6'  => '<p>Die Änderungen wurden gespeichert!</p>',
    'value7'  => 'absenden',
    'value8'  => '_Manage My Account',
    'value9'  => '1',
    'value10' => '0',
    'value11' => 'text{\r\n    label:Benutzername;\r\n    name: username;\r\n    required: 1;\r\n    validate:(not_empty|not_equal);\r\n    error_msg: (Bitte geben Sie einen Benutzernamen an!|Der Benutzername wird bereits verwendet);\r\n    css: form_elm_norm\r\n}\r\ntext{\r\n    label:E-Mail;\r\n    name: email;\r\n    required: 1;\r\n    validate:(email|not_equal);\r\n    error_msg: (Keine korrekte E-Mail-Adresse!|Die E-Mail wird bereits verwendet);\r\n    css: form_elm_norm\r\n}\r\nseparator{\r\n}\r\nselect{\r\n    label: Anrede;\r\n    name: gender;\r\n    values: ( |m=Herr|f=Frau);\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Anrede auswählen!;\r\n    css: form_elm_small\r\n}\r\ntext{\r\n    label: Vorname;\r\n    name: firstname;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Vornamen angeben!;\r\n    css: form_elm_norm\r\n}\r\ntext{\r\n    label:Name;\r\n    name:name;\r\n    required:1;\r\n    validate:not_empty;\r\n    error_msg:Bitte Name angeben!;\r\n    css: form_elm_norm\r\n}\r\nseparator{\r\n}\r\ncheckbox{\r\n  label: Newsletter abonnieren;\r\n   name: newsletter;\r\n   value: 1;\r\n   default: 1;\r\n css: form_elm_auto\r\n}\r\nseparator{\r\n}\r\npassword{\r\n    label:bisheriges Passwort;\r\n    name:password;\r\n    required:1;\r\n    validate:equal_md5;\r\n    error_msg:Passwort nicht korrekt!;\r\n    css: form_elm_norm\r\n}\r\n\r\npassword{\r\n    label:neues Passwort;\r\n    name:new_password;\r\n    validate:longer_than;\r\n    equal_value: 5;\r\n    error_msg:Das Passwort muss mindestens 5 Zeichen haben!;\r\n    css: form_elm_norm\r\n}',
    'value12' => 'Benötigte Angaben',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => '',
    'value19' => '0',
    'value20' => ''
);