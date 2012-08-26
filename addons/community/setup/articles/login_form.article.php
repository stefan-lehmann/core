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

$article['name']        = '_Login Form';
$article['template_id'] = '1';
$article['type_id']     = 'out';

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => '',
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['login_form'],
    'value4'  => '',
    'value5'  => '0',
    'value6'  => '',
    'value7'  => 'absenden',
    'value8'  => '',
    'value9'  => '1',
    'value10' => '0',
    'value11' => 'hidden{\r\nname: re_id;\r\n    default: 1;\r\n}\r\nhidden{\r\n    name: clang;\r\n}\r\ntext{\r\n    label: Login;\r\n    name: username;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Benutzernamen angeben!\r\n}\r\npassword{\r\n    label: Passwort;\r\n    name: password;\r\n    required: 1;\r\n    validate: not_empty;\r\n    error_msg: Bitte Passwort eingeben!\r\n}',
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