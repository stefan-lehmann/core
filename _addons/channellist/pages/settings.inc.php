<?php
/**
 * This file is part of CONTEJO ADDON - CHANNEL LIST
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_channel_list
 * @subpackage 	pages
 * @version   	SVN: $Id: settings.inc.php 1084 2010-11-24 12:37:42Z s_lehmann $
 *
 * @author 		Stefan Lehmann <sl@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */
//Form
$form = new cjoForm();
$form->setEditMode(false);
//$form->debug = true;

$fields['tv_sprite_big'] = new cjoMediaButtonField('tv_sprite_big', $I18N_23->msg('label_tv_sprite_big'), array('preview' => array('disabled' => false)));
$fields['tv_sprite_big']->addValidator('notEmpty', $I18N_23->msg("msg_tv_sprite_big_empty"));

$fields['tv_sprite_small'] = new cjoMediaButtonField('tv_sprite_small', $I18N_23->msg('label_tv_sprite_small'), array('preview' => array('disabled' => false)));
$fields['tv_sprite_small']->addValidator('notEmpty', $I18N_23->msg("msg_tv_sprite_small_empty"));

$fields['radio_sprite_big'] = new cjoMediaButtonField('radio_sprite_big', $I18N_23->msg('label_radio_sprite_big'), array('preview' => array('disabled' => false)));
$fields['radio_sprite_big']->addValidator('notEmpty', $I18N_23->msg("msg_radio_sprite_big_empty"));

$fields['radio_sprite_small'] = new cjoMediaButtonField('radio_sprite_small', $I18N_23->msg('label_radio_sprite_small'), array('preview' => array('disabled' => false)));
$fields['radio_sprite_small']->addValidator('notEmpty', $I18N_23->msg("msg_radio_sprite_small_empty"));

$fields['sprite_packages'] = new cjoMediaButtonField('sprite_packages', $I18N_23->msg('label_sprite_packages'), array('preview' => array('disabled' => false)));
$fields['sprite_packages']->addValidator('notEmpty', $I18N_23->msg("msg_sprite_packages_empty"));

$fields['player'] = new cjoMediaButtonField('player', $I18N_23->msg('label_player'), array('preview' => array('disabled' => false)));
$fields['player']->addValidator('notEmpty', $I18N_23->msg("msg_player_empty"));

$fields['offset_x'] = new textField('offset_x', $I18N_23->msg('label_offset_x'));
$fields['offset_x']->addValidator('notEmpty', $I18N_23->msg("msg_offset_x_empty"));
$fields['offset_x']->addValidator('isNumber', $I18N_23->msg("msg_offset_x_number"));
$fields['offset_x']->addAttribute('style', 'width: 40px');
$fields['offset_x']->setNote('Pixel');

$fields['offset_y'] = new textField('offset_y', $I18N_23->msg('label_offset_y'));
$fields['offset_y']->addValidator('notEmpty', $I18N_23->msg("msg_offset_y_empty"));
$fields['offset_y']->addValidator('isNumber', $I18N_23->msg("msg_offset_y_number"));
$fields['offset_y']->addAttribute('style', 'width: 40px');
$fields['offset_y']->setNote('Pixel');

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_update_button', $I18N->msg('button_update'), true, 'img/silk_icons/tick.png');
$fields['button']->needFullColumn(true);


//Add Fields
$section = new cjoFormSection($CJO['ADDON']['settings'][$mypage], $I18N_23->msg("label_basic_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->show(false);

if ($form->validate()) {

	if (!cjoMessage::hasErrors()) {
    	if (!cjoGenerate::updateSettingsFile($CJO['ADDON']['settings'][$mypage]['settings'])) {
    		cjoMessage::addError($I18N->msg('msg_data_not_saved'));
    	}
	}
}


