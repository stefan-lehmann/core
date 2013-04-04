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

//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;

//Hidden Fields
$hidden['prev_subpage'] = new hiddenField('prev_subpage');
$hidden['prev_subpage']->setValue('step1');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new headlineField(cjoI18N::translate("label_license"));

$license = file_get_contents($CJO['HTDOCS_PATH'].'core/_license.txt');
$fields['license'] = new textAreaField('license', '');
$fields['license']->addAttribute('rows', '30');
$fields['license']->addAttribute('wrap', 'off');
$fields['license']->addAttribute('style', 'width: 745px; margin-left: 200px;');
$fields['license']->addAttribute('readonly', 'readonly');
$fields['license']->setValue($license);

$fields['confirm'] = new checkboxField('confirm', '&nbsp;',  array('style' => 'width: auto;'));
$fields['confirm']->addValidator('notEmpty', cjoI18N::translate("msg_confirm_license_notEmpty"));
$fields['confirm']->addBox(cjoI18N::translate("label_confirm_license"),1);

$fields['headline2'] = new headlineField(cjoI18N::translate("msg_setup_step2_label"));

$fields['info'] = new readOnlyField('info','', array('style'=>'margin-left: 200px;'));
$fields['info']->setContainer('div');
$fields['info']->setValue(cjoI18N::translate("msg_setup_step2_info"));

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',cjoI18N::translate("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',cjoI18N::translate("button_next_step3"), true, 'img/silk_icons/control_play.png');
$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');
//Add Fields:
$section = new cjoFormSection('', cjoI18N::translate("label_setup_".$subpage."_title"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
    cjoUrl::redirectBE(array('subpage' => 'step3', 'lang' => $lang));
}

$form->show(false);