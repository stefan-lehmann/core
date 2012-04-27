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

$article['name']        = '_Activate User';
$article['template_id'] = '1';
$article['type_id']     = 1;

$article['slices'][] =  array(

    'modultyp_id' => $this->install_ids['modules']['formular_generator'],
    'value1'  => $CJO['ERROR_EMAIL'],
    'value2'  => '',
    'value3'  => $this->install_ids['templates']['activate_user'],
    'value4'  => '_Activate User',
    'value5'  => '1',
    'value6'  => '<p>Aktivierung erfolgt!</p><p>Vielen Dank. Sie haben Ihr neues  Benutzerkonto soeben aktiviert. Wir werden nun schnellstmöglich Ihre  Berechtigung prüfen und Ihren Zugang endgültig freischalten.  Anschließend erhalten Sie eine Bestätigung per E-Mail.</p>',
    'value7'  => '',
    'value8'  => '_Activate User',
    'value9'  => '1',
    'value10' => '0',
    'value11' => '',
    'value12' => '',
    'value13' => '',
    'value14' => '',
    'value15' => '',
    'value16' => '',
    'value17' => '',
    'value18' => 'Ein Benutzer hat sich registriert und bittet um Freischaltung seines Zugangs.\r\n\r\nFolgende Account-Daten wurden eingetragen:\r\n%userdata%\r\n\r\nBenutzer freischalten:\r\n%link%',
    'value19' => '0',
    'value20' => ''
);