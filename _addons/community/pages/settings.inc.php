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

//Form
$form = new cjoForm($mypage.'_'.$subpage.'_clang_form');
$form->setEditMode(true);
$form->debug = false;

//Fields
$fields['php_mailer_account'] = new selectField('PHP_MAILER_ACCOUNT', $I18N_10->msg('label_php_mailer_account'));
$fields['php_mailer_account']->addSqlOptions("SELECT CONCAT(from_name,' &lt;',from_email,'&gt;') As name, id FROM ".TBL_20_MAIL_SETTINGS);
$fields['php_mailer_account']->setMultiple(false);
$fields['php_mailer_account']->addAttribute('size', '1', true);
$fields['php_mailer_account']->addValidator('notEmpty', $I18N_10->msg("msg_php_mailer_account"));
$fields['php_mailer_account']->setNote(cjoAssistance::createBELink('<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />', 
                                                                    array('page'=>'phpmailer')));

$fields['subject'] = new textField('SUBJECT', $I18N_10->msg('label_subject'));
$fields['subject']->addValidator('notEmpty', $I18N_10->msg("msg_empty_subject"));

$fields['gender_types'] = new textField('GENDER_TYPES', $I18N_10->msg('label_gender_types'));
$fields['gender_types']->addValidator('notEmpty', $I18N_10->msg("msg_empty_gender_types"));
$fields['gender_types']->setHelp($I18N_10->msg("note_gender_types"));

$fields['verify_registration'] = new checkboxField('VERIFY_REGISTRATION', $I18N_10->msg('label_security_option'),  array('style' => 'width: auto;'));
$fields['verify_registration']->setUncheckedValue();
$fields['verify_registration']->addBox($I18N_10->msg('label_verify_registration'), '1');

// Benachrichtigungsvorlagen ----------------------------------------------------------------------------------------------------------------------------

$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
$fields['headline1']->setValue($I18N_10->msg('label_text_templates'));

$fields['activation_text'] = new textAreaField('ACTIVATION_MSG', $I18N_10->msg('label_activation_text'));
$fields['activation_text']->addValidator('notEmpty', $I18N_10->msg("msg_empty_activation_text"));
$fields['activation_text']->addAttribute('rows', '12');
$fields['activation_text']->setNote($I18N_10->msg("note_placeholders"));

$fields['update_text'] = new textAreaField('SEND_PASSWORD_MSG', $I18N_10->msg('label_update_text'));
$fields['update_text']->addValidator('notEmpty', $I18N_10->msg("msg_empty_update_text"));
$fields['update_text']->addAttribute('rows', '12');
$fields['update_text']->setNote($I18N_10->msg("note_placeholders"));

$fields['update_button'] = new buttonField();
$fields['update_button']->addButton('cjoform_update_button',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');
$fields['update_button']->setButtonAttributes('cjoform_update_button', 'id="cjoform_update_button1"');

//Add Fields
$section = new cjoFormSection('', $I18N_10->msg('label_lang_setup'), array ());
$section->dataset = $CJO['ADDON']['settings'][$mypage];

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
	$config_file = $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];

	if (cjoGenerate::updateSettingsFile($config_file)) {
	    cjoAssistance::redirectBE(array('msg' => 'msg_data_saved'));
	}
	else {
		$form->valid_master = false;
		cjoMessage::addError($I18N->msg("msg_data_not_saved"));
		cjoMessage::addError($I18N->msg("msg_file_no_chmod",
		                     cjoAssistance::absPath($config_file)));
	}
}
$form->show(false);


//Form
$form = new cjoForm($mypage.'_'.$subpage.'_form_articles_form');
$form->setEditMode(true);
$form->debug = false;

$fields['nl_signin_article'] = new cjoLinkButtonField('NL_SIGNIN', $I18N_10->msg('label_nl_signin_article'));
$fields['nl_signin_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_nl_signin_article_notEmpty"),false, false);

$fields['nl_signout_article'] = new cjoLinkButtonField('NL_SIGNOUT', $I18N_10->msg('label_nl_signout_article'));
$fields['nl_signout_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_nl_signout_article_notEmpty"),false, false);

$fields['nl_confirm_article'] = new cjoLinkButtonField('NL_CONFIRM', $I18N_10->msg('label_nl_confirm_article'));
$fields['nl_confirm_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_nl_confirm_article_notEmpty"),false, false);

$fields['login_form_article'] = new cjoLinkButtonField('LOGIN_FORM', $I18N_10->msg('label_login_form_article'));
$fields['login_form_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_login_form_article_notEmpty"),false, false);

$fields['logout_article'] = new cjoLinkButtonField('LOGOUT', $I18N_10->msg('label_logout_article'));
$fields['logout_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_logout_article_notEmpty"),false, false);

$fields['register_user_article'] = new cjoLinkButtonField('REGISTER_USER', $I18N_10->msg('label_register_user_article'));
$fields['register_user_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_register_user_article_notEmpty"),false, false);

$fields['activate_user_article'] = new cjoLinkButtonField('ACTIVATE_USER', $I18N_10->msg('label_activate_user_article'));
$fields['activate_user_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_activate_user_article_notEmpty"),false, false);

$fields['manage_account_article'] = new cjoLinkButtonField('MANAGE_ACCOUNT', $I18N_10->msg('label_manage_account'));
$fields['manage_account_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_manage_account_notEmpty"),false, false);

$fields['send_password_article'] = new cjoLinkButtonField('SEND_PASSWORD', $I18N_10->msg('label_send_password_article'));
$fields['send_password_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_send_password_article_notEmpty"),false, false);

$fields['safe_download_article'] = new cjoLinkButtonField('SAFE_DOWNLOAD', $I18N_10->msg('label_safe_download_article'));
$fields['safe_download_article']->addValidator('notEmptyOrNull',$I18N_10->msg("msg_safe_download_article_notEmpty"),false, false);

$fields['update_button'] = new buttonField();
$fields['update_button']->addButton('cjoform_update_button',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');
$fields['update_button']->setButtonAttributes('cjoform_update_button', 'id="cjoform_update_button3"');

//Add Fields
$section = new cjoFormSection('', $I18N_10->msg("label_community_form_articles"), array());
$section->dataset = $CJO['ADDON']['settings'][$mypage];

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show(false);

if ($form->validate()) {
    
	$config_file = $CJO['ADDON']['settings'][$mypage]['SETTINGS'];

    $_POST['SETUP'] = 'false';

	if (cjoGenerate::updateSettingsFile($config_file)) {
	    cjoAssistance::redirectBE(array('msg' => 'msg_data_saved'));
	}
	else {
		$form->valid_master = false;
		cjoMessage::addError($I18N->msg("msg_data_not_saved"));
		cjoMessage::addError($I18N->msg("msg_file_no_chmod",
		                     cjoAssistance::absPath($config_file)));
	}
}


