<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.7.x
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
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

$dataset = $CJO['ADDON']['settings'][$mypage];
$dataset['PRODUCT_ADDED_MESSAGE'] = stripslashes($dataset['PRODUCT_ADDED_MESSAGE']);

//Form
$form = new cjoForm();
$form->setEditMode(true);
$form->debug = false;

//edit message displayed when product added to basket
$fields['product_added_message'] = new cjoWYMeditorField('PRODUCT_ADDED_MESSAGE',
														 cjoAddon::translate(21,'label_product_added_message'));



$fields['order_confirm_subject'] = new textField('ORDER_CONFIRM_SUBJECT',
												 cjoAddon::translate(21,'order_confirmation_subject'));
$fields['order_confirm_subject']->addValidator('notEmpty',
    										   cjoI18N::translate("msg_order_confirmation_subject"),
													false, false);
$fields['order_confirm_subject']->setNote(cjoAddon::translate(21,'note_mail_subject'));

// edit text of the order confirmation mail
$fields['order_confirm_mail'] = new textAreaField('ORDER_CONFIRM_MAIL',
												  cjoAddon::translate(21,'label_order_confirm_mail'),
											   	  array('rows' => '10'));
$fields['order_confirm_mail']->setHelp(cjoAddon::translate(21,'shop_mail_wildcards'));



$fields['order_send_subject'] = new textField('ORDER_SEND_SUBJECT',
											  cjoAddon::translate(21,'products_send_subject'));
$fields['order_send_subject']->addValidator('notEmpty',
    										cjoI18N::translate("msg_products_send_subject"),
											false, false);
$fields['order_send_subject']->setNote(cjoAddon::translate(21,'note_mail_subject'));

// edit text of the products delivered mail
$fields['order_send_mail'] = new textAreaField('ORDER_SEND_MAIL',
											   cjoAddon::translate(21,'label_order_send_mail'),
											   array('rows' => '10'));
$fields['order_send_mail']->setHelp(cjoAddon::translate(21,'shop_mail_wildcards'));

// edit text of the products delivered mail
$fields['order_send_mail'] = new textAreaField('ORDER_SEND_MAIL',
                                               cjoAddon::translate(21,'label_order_send_mail'),
                                               array('rows' => '10'));
$fields['order_send_mail']->setHelp(cjoAddon::translate(21,'shop_mail_wildcards'));

$fields['update_button'] = new buttonField();
$fields['update_button']->addButton('cjoform_update_button',cjoI18N::translate("button_update"), true, 'img/silk_icons/tick.png');
$fields['update_button']->setButtonAttributes('cjoform_update_button', 'id="cjoform_update_button1"');

//Add Fields
$section = new cjoFormSection($dataset, cjoAddon::translate(21,'label_lang_setup'), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
    
	$config_file = $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];

	if (!cjoFile::isWritable($config_file)){
		cjoMessage::addError(cjoI18N::translate("msg_data_not_saved"));
		$form->valid_master = false;
	}
	else {

		$config_data = file_get_contents($config_file);

		foreach($_POST as $key=>$value){
			$pattern = "!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['".$key."'\].?\=.?)[^;]*!";
			$config_data = preg_replace($pattern,"\\1\"".$value."\"",$config_data);
		}

		if (cjoGenerate::replaceFileContents($config_file, $config_data)){
			cjoUrl::redirectBE(array('msg'=>'msg_data_saved'));
			exit;
		}
		else{
			cjoMessage::addError(cjoI18N::translate("msg_data_not_saved"));
			cjoMessage::addError(cjoI18N::translate("msg_file_no_chmod",
			                     cjoFile::absPath($config_file)));
		}
	}
}

$form->show(false);
