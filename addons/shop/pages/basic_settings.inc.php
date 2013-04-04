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

// get file path of business terms
$dataset = $CJO['ADDON']['settings'][$mypage];
$dataset['business_terms'] = is_readable($CJO['ADDON']['settings'][$mypage]['BUSINESS_TERMS'])
						   ? file_get_contents($CJO['ADDON']['settings'][$mypage]['BUSINESS_TERMS'])
						   : '';

//create formular
$form = new cjoForm();
$form->setEditMode(true);

$themes = cjoAssistance::parseDir($CJO['ADDON_PATH']."/".$mypage."/themes", array(), false, 2, 1, '/.*/i', '', '');

// edit home country
$fields['country'] = new selectField('COUNTRY', cjoAddon::translate(21,'shop_country'));
$fields['country']->addAttribute('size', '1');
$fields['country']->addValidator('notEmpty', cjoI18N::translate("msg_country_notEmpty"),
								 false, false);
// add select options

foreach(cjo_get_country_codes() as $key => $country) {
    if (empty($key)) continue;
    $fields['country']->addOption($country, $key);
}

// new area
$fields['shop_theme'] = new selectField('SHOP_THEME', cjoAddon::translate(21,'shop_themes'));
$fields['shop_theme']->addAttribute('size', 1);
$fields['shop_theme']->setHelp(cjoAddon::translate(21,"help_shop_theme", $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/themes'));

foreach(cjoAssistance::toArray($themes) as $name=>$theme) {
	$fields['shop_theme']->addOption($name, trim($name));
}

$fields['attribute_format'] = new selectField('ATTRIBUTE_FORMAT', cjoAddon::translate(21,'shop_product_attribut_format'));
$fields['attribute_format']->setMultiple(false);
$fields['attribute_format']->addAttribute('size', '1', true);
$fields['attribute_format']->addOption(cjoAddon::translate(21,'shop_product_attribut_format_0'), "0");
$fields['attribute_format']->addOption(cjoAddon::translate(21,'shop_product_attribut_format_1'), "1");
$fields['attribute_format']->addOption(cjoAddon::translate(21,'shop_product_attribut_format_2'), "2");

$fields['delivery_method'] = new selectField('DELIVERY_METHOD', cjoAddon::translate(21,'shop_delivery_calculation'));
$fields['delivery_method']->addAttribute('size', '1');
$fields['delivery_method']->setMultiple(false);
$fields['delivery_method']->addOption(cjoAddon::translate(21,'shop_order_value'), "0");
$fields['delivery_method']->addOption(cjoAddon::translate(21,'shop_packing_units'), "1");
$fields['delivery_method']->setHelp(cjoAddon::translate(21,'shop_help_delivery_method'));

$fields['adress2_enabled'] = new checkboxField('ADRESS2_ENABLED', '&nbsp;');
$fields['adress2_enabled']->setUncheckedValue();
$fields['adress2_enabled']->addBox(cjoAddon::translate(21,'shop_enable_different_supply_address'), 1);

// edit mail account for order confirmation mail
$qry = "SELECT CONCAT(from_name,' &lt;',from_email,'&gt;') AS name, id FROM ".TBL_20_MAIL_SETTINGS." ORDER BY id";
$fields['php_mailer_account'] = new selectField('PHP_MAILER_ACCOUNT', cjoAddon::translate(21,'shop_php_mailer_account'));
$fields['php_mailer_account']->addSqlOptions($qry);
$fields['php_mailer_account']->setMultiple(false);
$fields['php_mailer_account']->addAttribute('size', '1', true);
$fields['php_mailer_account']->addValidator('notEmpty', cjoAddon::translate(21,"msg_no_php_mailer_account"));

// edit currency names
$fields['shop_owner_email'] = new textField('SHOP_OWNER_EMAIL', cjoAddon::translate(21,'shop_owner_email'));
$fields['shop_owner_email']->addValidator('isEmail', cjoI18N::translate("msg_shop_owner_email_notEmpty"),false, false);

// edit currency names
$fields['currency_names'] = new textField('CURRENCY_NAMES', cjoAddon::translate(21,'shop_currency_names'));
$fields['currency_names']->addValidator('notEmpty', cjoI18N::translate("msg_currency_names_notEmpty"),false, false);
$fields['currency_names']->setHelp(cjoAddon::translate(21,'msg_currency_names_format'));

// edit currency signs
$fields['currency_signs'] = new textField('CURRENCY_SIGNS', cjoAddon::translate(21,'shop_currency_signs'));
$fields['currency_signs']->addValidator('notEmpty', cjoI18N::translate("msg_currency_signs_notEmpty"),false, false);
$fields['currency_signs']->setHelp(cjoAddon::translate(21,'note_currency_signs_format'));

// edit currencies
$fields['exchange_ratio'] = new textField('EXCHANGE_RATIO', cjoAddon::translate(21,'shop_exchange_ratio'));
$fields['exchange_ratio']->addValidator('notEmpty', cjoI18N::translate("msg_exchange_ratio_notEmpty"),false, false);
$fields['exchange_ratio']->setHelp(cjoAddon::translate(21,'note_exchange_ratio_format'));

// edit separators
$fields['price_separators'] = new textField('PRICE_SEPARATORS', cjoAddon::translate(21,'shop_separators'));
$fields['price_separators']->addValidator('notEmpty',cjoI18N::translate("msg_separators_notEmpty"),false, false);
$fields['price_separators']->setHelp(cjoAddon::translate(21,'note_separators_format'));

// edit mail account for order confirmation mail
$fields['shop_modul_id'] = new selectField('SHOP_MODUL_ID',cjoAddon::translate(21,'shop_modul_id'));
$fields['shop_modul_id']->addOption('', '');
$fields['shop_modul_id']->addSqlOptions("SELECT CONCAT(name, ' (ID=', id,')') AS name, id FROM ".TBL_MODULES);
$fields['shop_modul_id']->setMultiple(false);
$fields['shop_modul_id']->addAttribute('size', '1', true);
$fields['shop_modul_id']->addValidator('notEmpty', cjoI18N::translate("msg_shop_modul_id_notEmpty"),false, false);

// edit currencies
$fields['shop_use_https'] = new textField('HTTPS', cjoAddon::translate(21,'shop_use_https'));
$fields['shop_use_https']->addValidator('isRegExp', cjoI18N::translate("msg_no_valid_https"), array('expression' => '!^https?://[\w-]+\.[\w-]+(\S+)?$!i'), false);

$fields['shop_use_https']->setHelp(cjoAddon::translate(21,'note_use_https'));

//headline for the links
$fields['headline4'] = new headlineField(cjoAddon::translate(21,'shop_be_article_links'), true);

// link to basket page
$fields['delivery_article_id'] = new cjoLinkButtonField('DELIVERY_ARTICLE_ID',cjoAddon::translate(21,'shop_delivery_id'));
$fields['delivery_article_id']->addValidator('notEmptyOrNull',cjoAddon::translate(21,"msg_delivery_id_notEmpty"),false, false);

// link to basket page
$fields['basket_article_id'] = new cjoLinkButtonField('BASKET_ARTICLE_ID',cjoAddon::translate(21,'shop_basket_id'));
$fields['basket_article_id']->addValidator('notEmptyOrNull',cjoAddon::translate(21,"msg_basket_id_notEmpty"),false, false);

// link to checkout page
$fields['checkout_article_id'] = new cjoLinkButtonField('CHECKOUT_ARTICLE_ID',cjoAddon::translate(21,'shop_checkout_id'));
$fields['checkout_article_id']->addValidator('notEmptyOrNull', cjoAddon::translate(21,"msg_checkout_id_notEmpty"),false, false);

// link to page after ordering
$fields['post_order_article_id'] = new cjoLinkButtonField('POST_ORDER_ARTICLE_ID',cjoAddon::translate(21,'shop_post_order_id'));


$fields['post_order_article_id']->addValidator('notEmptyOrNull',cjoAddon::translate(21,"msg_post_order_id_notEmpty"),false, false);

// headline for business terms
$fields['headline5'] = new headlineField(cjoAddon::translate(21,'shop_edit_business_terms'), true);

// textarea for editing business terms
$fields['business_terms'] = new textAreaField('business_terms', cjoAddon::translate(21,'shop_business_terms'));
$fields['business_terms']->addAttribute('rows', 20);
$fields['business_terms']->addAttribute('cols', 200);
$fields['business_terms']->addAttribute('style', 'width: 720px');
$fields['business_terms']->addValidator('notEmpty',cjoAddon::translate(21,"msg_business_terms_notEmpty"),false, false);

$fields['update_button'] = new buttonField();
$fields['update_button']->addButton('cjoform_update_button',cjoI18N::translate("button_update"), true, 'img/silk_icons/tick.png');
$fields['update_button']->setButtonAttributes('cjoform_update_button', 'id="cjoform_update_button1"');

// build form
$section = new cjoFormSection($dataset, cjoAddon::translate(21,'shop_edit_basic_settings'), array ());
$section->addFields($fields);
$form->addSection($section);
$form->show(false);

if ($form->validate()) {
        
	$theme = cjo_post('SHOP_THEME', 'string', 'default');

	if ($CJO['ADDON']['settings'][$mypage]['SHOP_THEME'] != $theme) {

    	$theme_dir   = $CJO['ADDON_PATH']."/".$mypage."/themes/".$theme;
    	$theme_dest  = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme";
        $date_string = strftime('%Y-%m-%d_%H-%M-%S',time());

    	if (file_exists($theme_dest) && !rename($theme_dest, $theme_dest.'_state_'.$date_string)) {
    		cjoMessage::addError(cjoAddon::translate(21,"err_create_theme_dir", $theme_dest));
    	}
    	if (!@mkdir($theme_dest, cjoProp::getDirPerm())) {
    		cjoMessage::addError(cjoAddon::translate(21,"err_create_theme_dir", $theme_dest));
    	}
    	if (!cjoMessage::hasErrors() && @file_exists($theme_dir)) {
    		if (!cjoFile::copyDir($theme_dir, $theme_dest)) {
    			cjoMessage::addError(cjoAddon::translate(21,"err_copy_theme", $theme_dir, $theme_dest));
    		}
    	}
	}

    $_POST['SETUP'] = 'false';
	
	cjoGenerate::updateSettingsFile($CJO['ADDON']['settings'][$mypage]['SETTINGS']);

	if (!cjoGenerate::putFileContents($CJO['ADDON']['settings'][$mypage]['BUSINESS_TERMS'], cjo_post('business_terms', 'string'))){
		cjoMessage::addError(cjoAddon::translate(21,"err_write_business_terms"));
	}

	if (!cjoMessage::hasErrors()) cjoMessage::flushWarnings();
}
