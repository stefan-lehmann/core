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
 * @version     2.6.0
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

$mypage    = 'shop';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');
$mode      = cjo_request('mode', 'string');

// define required variables
$currency_name = $CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_NAME'];
$separator =  $CJO['ADDON']['settings'][$mypage]['CURRENCY']['CURR_SEPARATOR'];
$pay_methods = $CJO['ADDON']['settings'][$mypage]['PAY_METHODS'];

// declare settings pages
$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage(array('orders', 'title' => $I18N_21->msg('title_orders')));
$subpages->addPage(array('products', 'title' => $I18N_21->msg('title_products')));
$subpages->addPage(array('attributes', 'title' => $I18N_21->msg('shop_attributes')));
$subpages->addPage(array('shipping', 'title' => $I18N_21->msg('title_shipping')));
$subpages->addPage(array('payment', 'title' => $I18N_21->msg('title_payment')));
$subpages->addPage(array('localisation', 'title' => $I18N_21->msg('title_localisation')));
$subpages->addPage(array('basic_settings', 'title' => $I18N_21->msg('title_basic_settings')));

// Layout-Kopf
require_once $CJO['INCLUDE_PATH'].'/layout/top.php';

cjo_insertCss(false, $CJO['ADDON']['settings'][$mypage]['CSS']['BACKEND']);

require_once $subpages->getPage();
// Layout-Footer
require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';

if ($subpage == 'orders' &&
    $subpage == 'shipping' &&
    $subpage == 'payment') return false;

$CJO['SEL_LANG']->get();
