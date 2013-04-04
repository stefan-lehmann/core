<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@contejo.com>
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

$dir = $CJO['INCLUDE_PATH'].'/lang/';

//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;

$buttons = new buttonField();

$handle = opendir($dir);
while (false!==($item = readdir($handle))){
	if($item == '.' || $item == '..' || $item == '.svn')  continue;
	preg_match('/^([a-z_].*)\.lang$/i',$item, $matches1);
	$file = file_get_contents($dir.'/'.$item);

	preg_match('/'.$matches1[1].' = (.*)/i',$file, $matches2);

	$name = 'cjoform_button_'.$matches1[1];

	$buttons->addButton($name,strtoupper($matches2[1]), true, 'img/flags/'.$matches1[1].'.png');
	$buttons->setButtonAttributes($name, ' class="cjo_setup_buttons"');
	$buttons->setButtonAttributes($name, ' onclick="location.href = \'index.php?page=setup&subpage=step2&lang='.$matches1[1].'\'; return false;"');
}
closedir($handle);

$fields['lang'] = new readOnlyField('lang','', array('style'=>'text-align: center;'));
$fields['lang']->setContainer('div');
$fields['lang']->setValue($buttons->_get());

$fields['button'] = new buttonField();

//Add Fields:
$section = new cjoFormSection('', cjoI18N::translate("label_select_language"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show(false);