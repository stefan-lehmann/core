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
$hidden['prev_subpage']->setValue('step3');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
$fields['headline1']->setValue(cjoI18N::translate("label_common_settings"));

$fields['server'] = new textField('SERVER', cjoI18N::translate("label_server"));
$fields['server']->setDefault($CJO['SERVER']);

$fields['servername'] = new textField('SERVERNAME', cjoI18N::translate("label_servername"));
$fields['servername']->setDefault($CJO['SERVERNAME']);

$fields['error_email'] = new textField('ERROR_EMAIL', cjoI18N::translate("label_error_email"));
$fields['error_email']->setDefault($CJO['ERROR_EMAIL']);
$fields['error_email']->addValidator('isEmail', cjoI18N::translate("msg_no_vaild_email"));

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',cjoI18N::translate("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',cjoI18N::translate("button_next_step5"), true, 'img/silk_icons/control_play.png');
$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');

//Add Fields:
$section = new cjoFormSection('', cjoI18N::translate("label_setup_".$subpage."_title"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
	$data = file_get_contents($CJO['FILE_CONFIG_MASTER']);
	if ($data != '') {
		$data = preg_replace('/^(\$CJO\[\'SERVER\'\]\s*=\s*")(.*)(".*?)$/imx', '$1'.cjo_post('SERVER', 'string').'$3', $data);
		$data = preg_replace('/^(\$CJO\[\'SERVERNAME\'\]\s*=\s*")(.*)(".*?)$/imx', '$1'.cjo_post('SERVERNAME', 'string').'$3', $data);
		$data = preg_replace('/^(\$CJO\[\'ERROR_EMAIL\'\]\s*=\s*")(.*)(".*?)$/imx', '$1'.cjo_post('ERROR_EMAIL', 'string').'$3', $data);
		$data = preg_replace('/^(\$CJO\[\'LANG\'\]\s*=\s*")(.*)(".*?)$/imx', '$1'.cjo_post('lang', 'string').'$3', $data);
		$data = preg_replace('/^(\$CJO\[\'INSTNAME\'\]\s*=\s*")(.*)(".*?)$/imx', '$1'.'cjo'.date("YmdHis").'$3', $data);

		if (!cjoGenerate::putFileContents($CJO['FILE_CONFIG_MASTER'], $data)) {
		    cjoMessage::removeLastError();
			cjoMessage::addError(cjoI18N::translate("msg_config_master_no_perm", $CJO['FILE_CONFIG_MASTER']));
		}
	}
	else {
	    cjoMessage::addError(cjoI18N::translate("msg_config_master_does_not_exist"));
	}

	if (!cjoMessage::hasErrors()) {
	    cjoUrl::redirectBE(array('subpage' => 'step5', 'lang' => $lang));
	}
}

$form->show(false);