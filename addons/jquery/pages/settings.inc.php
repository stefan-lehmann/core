<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  jquery
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

$dataset = array();
if (!cjo_post('cjoform_update_button','bool')) {
    foreach(cjoAssistance::toArray($CJO['ADDON']['settings'][$mypage]) as $key=>$val) {
    	if ($key == 'PLUGINS') $val = explode('|',$val);
    	$dataset[$key] = $val;
    }
} else {
    $dataset = $_POST;
}

$versions = cjoAssistance::parseDir($CJO['ADDON']['settings'][$mypage]['JQ_SRC'],
            						array(), true, 1, 1,
            						'/^jquery[a-z0-9_\-\.].*(?=.js$)/i', '', '');

$plugins = cjoAssistance::parseDir($CJO['ADDON']['settings'][$mypage]['JQ_SRC'],
						           array(), false);

//Form
$form = new cjoForm();
$form->setEditMode(false);

$fields['VERSION'] = new selectField('VERSION', $I18N_11->msg('label_jquery_version'));
$fields['VERSION']->addAttribute('size', count($versions));
$fields['VERSION']->addValidator('notEmpty', $I18N_11->msg("err_empty_version"));

foreach(cjoAssistance::toArray($versions) as $name=>$version) {
	$fields['VERSION']->addOption($name, $version);
}

$fields['PLUGINS'] = new selectField('PLUGINS', $I18N_11->msg('label_plugins'));
$fields['PLUGINS']->addAttribute('size', count($plugins));
$fields['PLUGINS']->setMultiple(true);
$fields['PLUGINS']->setHelp($I18N_11->msg("note_plugins", $CJO['ADDON']['settings'][$mypage]['JQ_INCL']));

	$fields['PLUGINS']->addOption('jQuery-Plugins', '');
	$fields['PLUGINS']->disableOption('jQuery-Plugins');

foreach(cjoAssistance::toArray($plugins) as $name=>$plugin) {
	$fields['PLUGINS']->addOption($name, $plugin);
}

$fields['GZIP'] = new checkboxField('GZIP', '&nbsp;');
$fields['GZIP']->setUncheckedValue();
$fields['GZIP']->addBox($I18N_11->msg('label_use_gzip'), '1');

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_update_button',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');


//Add Fields
$section = new cjoFormSection($dataset, $I18N_11->msg("label_font_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {

    cjojQuery::copyjQueryFiles();
    
}

$form->show(false);