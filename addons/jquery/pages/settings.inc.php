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


$versions = cjoAssistance::parseDir(cjoUrl::addon($addon, 'jquery'),
            						array(), true, 1, 1,
            						'/^jquery[a-z0-9_\-\.].*(?=.js$)/i', '', '');

$plugins = cjoAssistance::parseDir(cjoUrl::addon($addon, 'jquery'),
						           array(), false);


//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->onIsValid('cjojQuery::copyjQueryFiles');

$fields['VERSION'] = new selectField('VERSION', cjoAddon::translate(11,'label_jquery_version'));
$fields['VERSION']->addAttribute('size', count($versions));
$fields['VERSION']->addValidator('notEmpty', cjoAddon::translate(11,"err_empty_version"));

foreach(cjoAssistance::toArray($versions) as $name=>$version) {
	$fields['VERSION']->addOption($name, $version);
}

$fields['PLUGINS'] = new selectField('PLUGINS', cjoAddon::translate(11,'label_plugins'));
$fields['PLUGINS']->addAttribute('size', count($plugins));
$fields['PLUGINS']->setMultiple(true);
$fields['PLUGINS']->setHelp(cjoAddon::translate(11,"note_plugins", cjoPath::addonAssets($addon, 'jquery')));
$fields['PLUGINS']->addOption('jQuery-Plugins', '');
$fields['PLUGINS']->disableOption('jQuery-Plugins');

foreach(cjoAssistance::toArray($plugins) as $name=>$plugin) {
	$fields['PLUGINS']->addOption($name, $plugin);
}

$fields['GZIP'] = new checkboxField('GZIP', '&nbsp;');
$fields['GZIP']->setUncheckedValue(0);
$fields['GZIP']->addBox(cjoAddon::translate(11,'label_use_gzip'), '1');

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_update_button',cjoI18N::translate("button_update"), true, 'img/silk_icons/tick.png');


//Add Fields
$section = new cjoFormSection($addon, cjoAddon::translate(11,"label_font_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show(false);


