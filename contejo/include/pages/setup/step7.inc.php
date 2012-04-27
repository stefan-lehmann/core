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
 * @version     2.6.0
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

$icons['addons'] = 'tick';

include_once $CJO['FILE_CONFIG_ADDONS'];

OOAddon :: readAddonsFolder();
foreach($CJO['SYSTEM_ADDONS'] as $addonname){

    switch ($addonname) {
        case 'import_export':   $menu = 1; break;
        case 'image_processor': $menu = 'media'; break;
        case 'phpmailer':
        case 'opf_lang':        $menu = 'addons'; break;
        default:                $menu = 0;
    }
	OOAddon :: installAddon($ADDONS,$addonname);
	OOAddon :: activateAddon($ADDONS,$addonname);
	OOAddon :: enableMenuAddon($ADDONS,$addonname,$menu);
}

if (cjoMessage::hasErrors()){
	$icons['addons'] = 'exclamation';
}

//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;

//Hidden Fields
$hidden['prev_subpage'] = new hiddenField('prev_subpage');
$hidden['prev_subpage']->setValue('step6');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
$fields['headline1']->setValue($I18N->msg("label_install_standard_addons"));

$fields['info'] = new readOnlyField('info', '', array('style'=>'margin-left: 200px;'));
$fields['info']->setContainer('div');
$fields['info']->setValue($I18N->msg("msg_setup_step7_info",
    					  			 '<img src="img/silk_icons/'.$icons['addons'].'.png">'));

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',$I18N->msg("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',$I18N->msg("button_next_step8"), true, 'img/silk_icons/control_play.png');
$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');

if (cjoMessage::hasErrors()) {
	$fields['button']->setButtonAttributes('cjoform_next_button', ' disabled="disabled"');
}
//Add Fields:
$section = new cjoFormSection('', $I18N->msg("label_setup_".$subpage."_title"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
    cjoAssistance::redirectBE(array('subpage' => 'step8', 'lang' => $lang));
}
$form->show(false);