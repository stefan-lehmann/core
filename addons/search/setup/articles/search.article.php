<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  search
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

$article['name']        = '_Search';
$article['template_id'] = '1';
$article['type_id']     = '1';

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => '',
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['search'],
    'value4'  => '_Send new Password',
    'value5'  => '0',
    'value6'  => '',
    'value7'  => 'finden',
    'value8'  => '_Search',
    'value9'  => '',
    'value10' => '0',
    'value11' => 'hidden{\r\n name: stype;\r\n default: boolean\r\n}\r\nhidden{\r\n name: submit;\r\n default: 1\r\n}\r\ntext{\r\n  label: Suchbegriff;\r\n  name: search;\r\n  validate: longer_than;\r\n  equal_value: 4;\r\n  error_msg: Der Suchbegriff ist zu kurz!\r\n}',
    'value12' => '',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => '',
    'value19' => '0'
);