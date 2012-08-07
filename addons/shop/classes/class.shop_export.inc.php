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


class cjoShopExport {

    /**
	 * Exports all orders to a CSV-file
	 * @param string $separator
	 * @return void
	 * @access public
	 */
	public static function exportOrders($separator = ";") {

        global $CJO, $I18N, $I18N_21;

        $sql = new cjoSql();
        $orders = $sql->getArray("SELECT * FROM ".TBL_21_ORDERS);
        $content = implode($separator,array_keys($orders[0]))."\r\n";

        foreach($orders as $order) {

            if (!is_array($order)) continue;

            foreach($order as $key=>$value){

                switch($key) {
                    case 'address1':        $addr = new cjoShopAddress($value);
                                            $value = $addr->out();
                                            break;
                    case 'address2':        $addr = new cjoShopSupplyAddress($value);
                                            $value = $addr->out();
                                            break;

                    case 'total_price':
                    case 'delivery_cost':
                    case 'pay_costs':       $value = cjoShopPrice::toCurrency($value);
                                            break;


                    case 'pay_data':        $pay_object = cjoShopPayMethod::getPayObject($order['pay_method'], $value);
                                            $value = get_class($pay_object) != 'cjoShopEmptyPayMethod' ? $pay_object->out() : '';
                                            $order['pay_method'] = cjoShopPayMethod::getName($order['pay_method']);

                                            break;

                    case 'birth_date':
                    case 'createdate':
                    case 'updateuser':      $value = strftime($I18N->msg('dateformat'), (int) $value);
                                            break;

                    case 'products':        $value = cjoShopProduct::productsOut($value);
                                            $value = "\r\n".implode("----------------------------\r\n", $value);
                                            break;

                }

                if(strpos($value, "\n") !== false ||
                   strpos($value, ",") !== false ||
                   strpos($value, $separator) !== false) {
                    $value = '"'.$value.'"';
                }
                $order[$key] = $value;
            }

            $content .= implode($separator,$order)."\r\n";
        }

        $date_string = date("Y").'-'.date("m").'-'.date("d");
        $filename = $CJO['SERVER']."_orders_".$date_string.".csv";
        header("Content-Disposition: attachment; filename=".$filename);

        $CJO['USE_LAST_MODIFIED'] = 'false';
        $CJO['USE_ETAG']          = 'false';
        $CJO['USE_GZIP']          = 'false';
        $CJO['USE_MD5']           = 'false';

        cjoClientCache::sendContent($content, time(), false, 'attachment', true);

	} // end function exportOrders

}
