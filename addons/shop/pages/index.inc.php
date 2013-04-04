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

$addon    = 'shop';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');
$mode      = cjo_request('mode', 'string');

// define required variables
$currency_name = cjoAddon::getParameter('CURRENCY|CURR_NAME', $addon);
$separator     = cjoAddon::getParameter('CURRENCY|CURR_SEPARATOR', $addon);
$pay_methods   = cjoAddon::getParameter('PAY_METHODS', $addon);

cjoSubPages::addPages( array(
                        array('orders', 
                              'title' => cjoAddon::translate(21,'title_orders')),
                        array('products', 
                              'title' => cjoAddon::translate(21,'title_products')),
                        array('attributes', 
                              'title' => cjoAddon::translate(21,'shop_attributes')),
                        array('shipping', 
                              'title' => cjoAddon::translate(21,'title_shipping')),
                        array('payment', 
                              'title' => cjoAddon::translate(21,'title_payment')),
                        array('localisation', 
                              'title' => cjoAddon::translate(21,'title_localisation')),
                        array('basic_settings', 
                              'title' => cjoAddon::translate(21,'title_basic_settings'))
                     ));
                     
cjo_insertCss(false, cjoUrl::addon($addon, cjoAddon::getParameter('CSS_BACKEND', $addon)));
cjo_insertJs(false, cjoUrl::addon($addon, cjoAddon::getParameter('JS_BACKEND', $addon)));

require_once cjoSubPages::getPagePath();
// Layout-Footer

if ($subpage == 'orders' &&
    $subpage == 'shipping' &&
    $subpage == 'payment') return false;

cjoSelectLang::get();
