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

$send_type = cjo_post('SEND_TYPE', 'string', $CJO['ADDON']['settings'][$mypage]['SEND_TYPE']);


//Form
$form = new cjoForm();
$form->setEditMode(true);
$form->debug = false;

//Fields
$fields['headline1'] = new headlineField('1. '. cjoAddon::translate(10,'label_mail_data'));

$fields['subject'] = new textField('GL_SUBJECT', cjoAddon::translate(10,'label_subject'));
$fields['subject']->addValidator('notEmpty', cjoAddon::translate(10,"err_empty_subject"));

$fields['mail_account'] = new selectField('MAIL_ACCOUNT', cjoAddon::translate(10,'label_php_mailer_account'));
$fields['mail_account']->addSqlOptions("SELECT CONCAT(from_name,' &lt;',from_email,'&gt;') AS name, id FROM ".TBL_20_MAIL_SETTINGS);
$fields['mail_account']->setMultiple(false);
$fields['mail_account']->addAttribute('size', '1', true);
$fields['mail_account']->addValidator('notEmpty', cjoAddon::translate(10,"msg_php_mailer_account"));

$fields['send_type'] = new radioField('SEND_TYPE', cjoAddon::translate(10,'label_send_type'),  array('style' => 'width: auto;'));
$fields['send_type']->addRadio(cjoAddon::translate(10,'label_send_html'), 'html');
$fields['send_type']->addRadio(cjoAddon::translate(10,'label_send_text'), 'text');
$fields['send_type']->setValue($send_type);
$fields['send_type']->addColAttribute('class', 'send_type', 'join');
$fields['send_type']->activateSave(false);

$fields['defaultletter'] = new cjoLinkButtonField('DEFAULTLETTER', cjoAddon::translate(10,'label_defaultletter'));
$fields['defaultletter']->addColAttribute('class', 'defaultletter', 'join');
$fields['defaultletter']->addValidator('notEmpty', cjoAddon::translate(10,"msg_empty_defaultletter"));

$fields['text'] = new textAreaField('TEXT', cjoAddon::translate(10,'label_nl_text'));
$fields['text']->addAttribute('rows', '12');
$fields['text']->setDefault("\r\n\r\n".$CJO['ADDON']['settings'][$mypage]['NL_TEXT']);
$fields['text']->addColAttribute('class', 'nl_text', 'join');
if ($send_type != 'text') {
	$fields['text']->addColAttribute('class', 'hide_me nl_text', 'join');
}

$qry = "SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_TEMPLATES." ORDER BY prior";
$fields['template'] = new selectField('TEMPLATE', cjoAddon::translate(10,'label_groupletter_template'));
$fields['template']->addSQLOptions($qry);
$fields['template']->addAttribute('size', '1');
$fields['template']->addValidator('notEmpty', cjoAddon::translate(10,"msg_empty_groupletter_template"));

// Empfängergruppen auswählen  ----------------------------------------------------------------------------------------------------------------------------

$fields['headline2'] = new headlineField('2. '. cjoAddon::translate(10,'label_select_groups'));


$sel_group = cjoCommunityGroups::getSelectGroups($oid);
$sel_group->resetSelected();

if (cjo_post('cjo_form_name','string') == $form->getName()) {
	$group_ids = cjo_post('groups','array');
}
else {
	$group_ids = $CJO['ADDON']['settings'][$mypage]['DEFAULT_GROUPS'];
}
foreach (cjoAssistance::toArray($group_ids) as $val) {
	$sel_group->setSelected($val);
}

$fields['groups'] = new readOnlyField('groups[]', cjoAddon::translate(10,'label_groups'));
$fields['groups']->setValue($sel_group->get());
$fields['groups']->addValidator('notEmpty', cjoAddon::translate(10,'err_notEmpty_groups'));

$fields['atonce'] = new textField('ATONCE', cjoAddon::translate(10,"label_atonce"), array('maxlength' => 4));
$fields['atonce']->setNote(cjoAddon::translate(10,"note_atonce"));
$fields['atonce']->addValidator('notEmpty', cjoAddon::translate(10,"msg_isRange_label_atonce"), false, false);
$fields['atonce']->addValidator('isRange', cjoAddon::translate(10,"msg_isRange_label_atonce"), array('low' => '1', 'high' => '5000'), false);
$fields['atonce']->addAttribute('style', 'width: 80px;');

$fields['save_default'] = new simpleButtonField('button_save_default', '&nbsp;',  array('style' => 'width: auto; margin-right: 390px; float: right;'));
$fields['save_default']->setValue(cjoAddon::translate(10,'button_save_default'));
$fields['save_default']->activateSave(false);

// Testmail versenden  ----------------------------------------------------------------------------------------------------------------------------

$fields['headline3'] = new headlineField('3. '. cjoAddon::translate(10,'label_send_testmail'));

$fields['test_gender'] = new selectField('TEST_GENDER', cjoAddon::translate(10,'label_gender'));
$fields['test_gender']->addAttribute('size', '1');
$fields['test_gender']->addAttribute('style', 'width: 130px;');
$fields['test_gender']->setDefault($CJO['ADDON']['settings'][$mypage]['TEST_GENDER']);
$fields['test_gender']->addOption('', '');

preg_match_all('/(?<=^|\|)([^\|]*)=([^\|]*)(?=\||$)/',
               $CJO['ADDON']['settings'][$mypage]['GENDER_TYPES'],
               $gender_types,
               PREG_SET_ORDER);

foreach($gender_types as $gender_type) {
	$fields['test_gender']->addOption($gender_type[2], $gender_type[1]);
}

$fields['test_firstname'] = new textField('TEST_FIRSTNAME', cjoAddon::translate(10,'label_firstname'));
$fields['test_firstname']->setDefault($CJO['ADDON']['settings'][$mypage]['TEST_FIRSTNAME']);

$fields['test_name'] = new textField('TEST_NAME', cjoAddon::translate(10,'label_name'));
$fields['test_name']->setDefault($CJO['ADDON']['settings'][$mypage]['TEST_NAME']);

$fields['test_email'] = new textField('TEST_EMAIL', cjoAddon::translate(10,'label_email'));
$fields['test_email']->addValidator('isEmail', cjoAddon::translate(10,'msg_no_valid_test_email'), true, false);
$fields['test_email']->setDefault($CJO['ADDON']['settings'][$mypage]['TEST_EMAIL']);

$fields['send_testmail'] = new simpleButtonField('button_send_testmail', '&nbsp;',  array('style' => 'width: auto; margin-right: 390px; float: right;'));
$fields['send_testmail']->setValue(cjoAddon::translate(10,'button_send_testmail'));
$fields['send_testmail']->activateSave(false);

// Hilfetext  ----------------------------------------------------------------------------------------------------------------------------

$fields['headline4'] = new headlineField(cjoAddon::translate(10,'label_help'));

$explain = 	cjoAddon::translate(10,"text_explain_cycle1").
			cjoAddon::translate(10,"text_explain_cycle2").
			cjoAddon::translate(10,"text_explain_cycle3").
			cjoAddon::translate(10,"text_explain_cycle4").
			cjoAddon::translate(10,"text_explain_cycle5").
			cjoAddon::translate(10,"text_explain_cycle6");

$fields['explain'] = new readOnlyField('', '', array('style'=>'display: block; padding:20px;'));
$fields['explain']->setValue($explain);

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_prepare_button',cjoAddon::translate(10,"button_prepare"), true, 'img/silk_icons/arrow_refresh.png');

//Add Fields
$section = new cjoFormSection('', cjoAddon::translate(10,'label_prepare_groupletter'), array ());
$section->dataset = $CJO['ADDON']['settings'][$mypage];

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
            
if ($form->validate()) {

	$preferences = $_POST;
	$preferences['DEFAULT_GROUPS'] = implode('|',cjo_post('groups','array'));

	if (cjo_post('button_send_testmail','bool')) {

		$error = array();
		if (!cjo_post('TEST_GENDER', 'bool')) {
			cjoMessage::addError(cjoAddon::translate(10,'err_not_empty_test_gender'));
			$fields['test_gender']->addAttribute('class', 'invalid');
		}
		if (!cjo_post('TEST_FIRSTNAME', 'bool')) {
			cjoMessage::addError(cjoAddon::translate(10,'err_not_empty_test_firstname'));
			$fields['test_firstname']->addAttribute('class', 'invalid');
		}
		if (!cjo_post('TEST_NAME', 'bool')) {
			cjoMessage::addError(cjoAddon::translate(10,'err_not_empty_test_name'));
			$fields['test_name']->addAttribute('class', 'invalid');
		}
		if (!cjo_post('TEST_EMAIL', 'bool')) {
			cjoMessage::addError(cjoAddon::translate(10,'err_not_empty_email'));
			$fields['test_email']->addAttribute('class', 'invalid');
		}	
		if (cjo_post('SEND_TYPE','string') != 'text' && !OOArticle::isOnline(cjo_post('DEFAULTLETTER','cjo-article-id'))) {
            cjoMessage::addError(cjoAddon::translate(10,'label_defaultletter_offline'));
            $fields['defaultletter']->addAttribute('class', 'invalid');
        }
		
		if (!cjoMessage::hasErrors()) {

		    $test_recipient = array();
		    $test_recipient['email']      = cjo_post('TEST_EMAIL', 'string');
		    $test_recipient['gender']     = cjo_post('TEST_GENDER', 'string');
            $test_recipient['name']       = cjo_post('TEST_NAME', 'string');
            $test_recipient['firstname']  = cjo_post('TEST_FIRSTNAME', 'string');
            $test_recipient['clang']      = $clang;
            $test_recipient['user_id']    = 0;
            $test_recipient['sendgrp']    = 0;

            $test_preferences = array();
            $test_preferences['subject']       = cjo_post('GL_SUBJECT', 'string');
            $test_preferences['article_id']    = cjo_post('SEND_TYPE','string') == 'text' ? -1 : cjo_post('DEFAULTLETTER','cjo-article-id');
            $test_preferences['send_type']     = cjo_post('SEND_TYPE','string');
            $test_preferences['mail_account']  = cjo_post('MAIL_ACCOUNT','int');
            $test_preferences['template']      = cjo_post('TEMPLATE','int');
            $test_preferences['group_ids']     = '';

		    $groupletter->setPreferences($test_preferences);

		    if ($preferences['SEND_TYPE'] == 'text') {
	            $groupletter->setBodyText($preferences['TEXT']);
		    }
	        else {
	            $html = $groupletter->getArticle($preferences['DEFAULTLETTER'], cjoProp::getClang(), $preferences['TEMPLATE']);
    	        $groupletter->setBodyHtml($html);
	        }
	        $groupletter->setRecipient($test_recipient);

	        if ($groupletter->sendGroupletter()) {
	            cjoMessage::addSuccess(cjoAddon::translate(10,'msg_test_send_success', $preferences['TEST_EMAIL']));
		    }
	        else {
	            cjoMessage::addError($groupletter->mail_error);
	        }
		}
	}
	elseif (cjo_post('button_save_default', 'bool')) {

			$config_file = $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
			$lang_conf = $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];			

        	if (!cjoFile::isWritable($config_file)) {
        	    $error = cjoMessage::removeLastError();
        		cjoMessage::addError(cjoI18N::translate("msg_data_not_saved"));
        		cjoMessage::addError($error);
				$form->valid_master = false;
			}
			else {
			    
			    $_POST['DEFAULT_GROUPS'] = @implode('|',$_POST['groups']);

				if (cjoGenerate::updateSettingsFile($config_file) &&
			        cjoGenerate::updateSettingsFile($lang_conf)) {
					cjoUrl::redirectBE(array('msg'=>'msg_data_saved'));
				}
				else{
					$form->valid_master = false;
					cjoMessage::addError(cjoI18N::translate("msg_data_not_saved"));
					cjoMessage::addError(cjoI18N::translate("msg_file_no_chmod", cjo_absPath($config_file)));
				}
			}
	}
	elseif (cjo_post('cjoform_prepare_button','bool')) {
		if ($groupletter->prepareGroupLetter($preferences)) {
			cjoUrl::redirectBE(array('ok'=>''));
		}
	}
}
$form->show(false);
?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function() {

		$(".send_type :radio").click(function() {
			cm_toggle_send_type();
		});

		function cm_toggle_send_type() {

			$(".send_type :radio").each(function() {

				if ($(this).is(':checked')) {

					var show, hide;

					show = ($(this).val() == 'html') ? '.defaultletter' : '.nl_text';
					hide = ($(this).val() == 'html') ? '.nl_text' : '.defaultletter';

					$(hide)
						.slideUp('fast')
						.addClass('.hide_me')
						.prev('.hr').hide();

					$(show)
						.slideDown('fast')
						.removeClass('.hide_me')
						.prev('.hr').show();
				}
			});
		}
		cm_toggle_send_type();

		if ($('.defaultletter').find('.invalid').length > 0) {
			$('.defaultletter')
				.slideDown('fast')
				.removeClass('.hide_me')
				.prev('.hr').show();
		}
	});

/* ]]> */
</script>