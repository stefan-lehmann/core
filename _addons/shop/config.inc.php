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

$mypage = "shop"; // only for this file

// language settings
$i18n_lang = $CJO['CONTEJO'] ? $CJO['LANG'] : $CJO['CLANG_ISO'][$CJO['CUR_CLANG']];
$I18N_21 = new i18n($i18n_lang, $CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // create lang obj for this addon

// main settings
$CJO['ADDON']['addon_id'][$mypage]  = '21';
$CJO['ADDON']['page'][$mypage] 		= $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		= $I18N_21->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		= 'shop[]'; // permission
$CJO['ADDON']['author'][$mypage] 	= 'Matthias Schomacker, Stefan Lehmann 2010';
$CJO['ADDON']['version'][$mypage] 	= '1.0';
$CJO['ADDON']['compat'][$mypage] 	= '2.7.x';
$CJO['ADDON']['support'][$mypage] 	= 'http://contejo.com/addons/shop';

// define table constants
if (!defined('TBL_21_ATTRIBUTES'))
    define('TBL_21_ATTRIBUTES', $CJO['TABLE_PREFIX'].'21_attributes');
if (!defined('TBL_21_ATTRIBUTE_VALUES'))
    define('TBL_21_ATTRIBUTE_VALUES', $CJO['TABLE_PREFIX'].'21_attribute_values');
if (!defined('TBL_21_ATTRIBUTE_TRANSLATE'))
    define('TBL_21_ATTRIBUTE_TRANSLATE', $CJO['TABLE_PREFIX'].'21_attribute_translate');
if (!defined('TBL_21_PACKUNITS'))
    define('TBL_21_PACKUNITS', $CJO['TABLE_PREFIX'].'21_packunits');
if (!defined('TBL_21_BASKET'))
    define('TBL_21_BASKET', $CJO['TABLE_PREFIX'].'21_basket');
if (!defined('TBL_21_ORDERS'))
    define('TBL_21_ORDERS', $CJO['TABLE_PREFIX'].'21_orders');
if (!defined('TBL_21_DELIVERER'))
    define('TBL_21_DELIVERER', $CJO['TABLE_PREFIX'].'21_deliverer');
if (!defined('TBL_21_COUNTRY_ZONE'))
    define('TBL_21_COUNTRY_ZONE', $CJO['TABLE_PREFIX'].'21_country_zone');
if (!defined('TBL_21_DELIVERER_DETAILS'))
    define('TBL_21_DELIVERER_DETAILS', $CJO['TABLE_PREFIX'].'21_deliverer_details');
if (!defined('TBL_21_DELIVERER_ZONE'))
    define('TBL_21_DELIVERER_ZONE', $CJO['TABLE_PREFIX'].'21_deliverer_zone');
if (!defined('TBL_21_PAYMENT'))
	define('TBL_21_PAYMENT', $CJO['TABLE_PREFIX'].'21_payment');
if (!defined('TBL_21_DELIVERY_COSTS'))
	define('TBL_21_DELIVERY_COSTS', $CJO['TABLE_PREFIX'].'21_delivery_costs');

// define required global vars
$CJO['ADDON']['settings'][$mypage]['SETTINGS']            = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php";
$CJO['ADDON']['settings'][$mypage]['CSS']['FRONTEND']     = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/shop.css";
$CJO['ADDON']['settings'][$mypage]['CSS']['FRONTEND_IE']  = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/ie.css";
$CJO['ADDON']['settings'][$mypage]['CSS']['BACKEND']      = $CJO['ADDON_PATH']."/".$mypage."/css/backend.css";
$CJO['ADDON']['settings'][$mypage]['JS']['BACKEND']       = $CJO['ADDON_PATH']."/".$mypage."/js/backend.js";
$CJO['ADDON']['settings'][$mypage]['PAY_METHODS_PATH']    = $CJO['ADDON_PATH']."/".$mypage."/include/pay_methods";
$CJO['ADDON']['settings'][$mypage]['CLANG_CONF'] 	      = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/'.$clang.'.clang.inc.php';
$CJO['ADDON']['settings'][$mypage]['BUSINESS_TERMS']      = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/'.$clang.'.business_terms.txt';

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
include_once $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];

if ($CJO['ADDON']['settings'][$mypage]['SETUP'] == 'true') {

    if (!$CJO['CONTEJO']) {
        $CJO['ADDON']['status'][$mypage] = 0;
        return;
    }

    if ($subpage != 'basic_settings') {

        $url = cjoAssistance::createBEUrl(array('page' => $mypage, 'subpage'=>'basic_settings'));

        cjoMessage::addWarning($I18N_21->msg('msg_err_configure_basic_settings', $url));
    }
}

// include required classes
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_price.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_basket.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_delivery.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_payment.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_product.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_product_attributes.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_address.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_zone.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_checkout.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_extension.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_mail.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_output.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_export.inc.php";
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.shop_delivery_settings.inc.php";

/*********************
 *
 * CURRENCY SETTINGS
 *
 ********************/


$settings = $CJO['ADDON']['settings'][$mypage];

// get currency codes
preg_match_all('/(?<=^|\|)([^\|]*)=([^\|]*)(?=\||$)/', $settings['CURRENCY_NAMES'],
                                                       $currencies,
                                                       PREG_SET_ORDER);
 // get default currency
$default_currency_code =  $currencies[0][1];

// select a new currency if different from default
if (!$CJO['CONTEJO']) {
    $currency_name_code = cjo_post('currency_select', 'string', cjo_session($mypage.'_currency', 'string', $default_currency_code));
    cjo_set_session($mypage.'_currency', $currency_name_code);
} else {
    $currency_name_code = $default_currency_code;
}

foreach($currencies as $currency_name)
	if (in_array($currency_name_code, $currency_name))
		$currency_name = $currency_name[2];

// get currency sign
preg_match('/(?<=\|'.$currency_name_code.'\=|^'.$currency_name_code.'\=).*?(?=\||$)/',
		   $settings['CURRENCY_SIGNS'], $sign);

// get default currency sign
preg_match('/(?<=\|'.$default_currency_code.'\=|^'.$default_currency_code.'\=).*?(?=\||$)/',
		   $settings['CURRENCY_SIGNS'], $default_sign);

// get exchange ratio
preg_match('/(?<=\|'.$currency_name_code.'\=|^'.$currency_name_code.'\=).*?(?=\||$)/',
		   $settings['EXCHANGE_RATIO'], $exchange_ratio);

// get separator
preg_match('/(?<=\|'.$currency_name_code.'\=|^'.$currency_name_code.'\=).*?(?=\||$)/',
		   $settings['PRICE_SEPARATORS'], $separator);

// get default separator
preg_match('/(?<=\|'.$default_currency_code.'\=|^'.$default_currency_code.'\=).*?(?=\||$)/',
		   $settings['PRICE_SEPARATORS'], $default_separator);

// set html templates
$CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['BASKET']          = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/basket.".$CJO['TMPL_FILE_TYPE'];
$CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['CHECKOUT']        = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/checkout.".$CJO['TMPL_FILE_TYPE'];
$CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['PRODUCT_TABLE']   = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/product_table.".$CJO['TMPL_FILE_TYPE'];
$CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['BASKET_INFO']     = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/basket_info.".$CJO['TMPL_FILE_TYPE'];
$CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['SHOP_NAV']        = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/shop_nav.".$CJO['TMPL_FILE_TYPE'];
$CJO['ADDON']['settings'][$mypage]['HTML_TEMPLATE']['CURRENCY_SELECT'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/theme/currency_select.".$CJO['TMPL_FILE_TYPE'];

// set global values
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_CODE']         = $currency_name_code;
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_NAME']         = $currency_name;
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_SIGN']         = $sign[0];
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_RATIO']        = $exchange_ratio[0];
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_SEPARATOR']    = $separator[0];

// default settings
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['DEFAULT_SIGN']      = $default_sign[0];
$CJO['ADDON']['settings'][$mypage]['CURRENCY']['DEFAULT_SEPARATOR'] = $default_separator[0];

// direct to basket page if submitted
if (cjo_post('shop_goto_basket', 'bool')) {
	$basket_id = $CJO['ADDON']['settings'][$mypage]['BASKET_ARTICLE_ID'];
	cjoAssistance::redirectFE($basket_id);
}
