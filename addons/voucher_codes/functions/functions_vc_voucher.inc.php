<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  voucher_codes
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

function vc_delete_voucher($code){

    global $I18N_17;

    $sql = new cjoSql();
    $qry = "DELETE FROM ".TBL_17_VOUCHER." WHERE code='".$code."'";
    return $sql->statusQuery($qry, cjoAddon::translate(17,"msg_code_deleted"));
}

function vc_import_codes(){

    global $CJO, $I18N_17;

    $message = array();
    $message_temp = array();
    $statistic = 0;

    preg_match_all('/^.*$/m', $_POST['codes'], $csv_data);

    if (!is_array($csv_data[0])){
        cjoMessage::addError(cjoAddon::translate(17,'msg_err_codes_notEmpty'));
        return false;
    }
    
    $total = count($csv_data[0]);
    $sql = new cjoSql();
    $insert = new cjoSql();

    foreach(cjoAssistance::toArray($csv_data[0]) as $code){

        $code = trim($code);

        if (empty($code)) continue;

        $sql->flush();
        $qry = "SELECT * FROM ".TBL_17_VOUCHER." WHERE code = '".$code."'";
        $sql->setQuery($qry);

        if ($sql->getRows() == 0){

            $insert->flush();
            $insert->setTable(TBL_17_VOUCHER);
            $insert->setValue("code",$code);

            if ($insert->insert()) {
                $statistic++;
            }
            else {
                cjoMessage::addError($insert->getError());
            }
        }
    }

    if ($statistic > 0) {
        cjoMessage::addSuccess(cjoAddon::translate(17,"msg_import_done", $statistic, ($total-$statistic)));
        return true;        
    }
    
    cjoMessage::addError(cjoAddon::translate(17,"msg_import_failed"));
    return false;
}

function vc_template_get_defaults() {
    
    global $CJO;
     
    $default_values = array();
    $equal_values   = array();

    $sql = new cjoSql();
    $qry = "SELECT code FROM ".TBL_17_VOUCHER." WHERE event_id < 1";
    $sql->setQuery($qry);
    for ($i = 0; $i < $sql->getRows(); $i++){
        $equal_values['code'] .= '|'.$sql->getValue('code');
        $sql->next();
    }
    
    $default_values['event'] = cjo_request('event', 'int');
    
    return array($equal_values, $default_values); 
}


function vc_encrypt_email($email){
    // das Suchmuster mit Delimiter und Modifer (falls vorhanden)
    $pattern = '/([A-Z0-9\._%\+\-]*)@([0-9a-z\-]*)([a-z\.]{2,})/i';

    preg_match($pattern, $email, $matches);
    $length = strlen($matches[2]);

    return $matches[1].'@'.preg_replace('/\S/','*',$matches[2]).$matches[3];
}

function vc_export_redemptions($event_id, $filename = '	redemption_list') {

    global $CJO, $I18N_17, $I18N;

    $addon = 'voucher_codes';
    $break = "----------------------------------------------------------------------------------------------------------"."\r\n";

    $sql = new cjoSql();
    $qry = "SELECT * FROM ".TBL_16_EVENTS." WHERE id = '".$event_id."'";
    $event = $sql->getArray($qry);

    $date_string = strftime('%Y-%m-%d', $event[0]['start_date']);

    $sql->flush();
    $qry = "SELECT name, firstname, email FROM ".TBL_17_VOUCHER." WHERE event_id='".$event_id."' ORDER BY name, firstname ASC";
    $data = $sql->getArray($qry);


    $output  = $event[0]['title'].' '.strftime('%d.%m.%Y', $event[0]['start_date'])."\r\n\r\n";
    $output .= $break;
    foreach(cjoAssistance::toArray($data) as $line) {

        if (!is_array($line)) continue;
        $output .= str_replace(array("\r\n","\r","\n"),
        					   ' ',
                               $line['name'].', '.$line['firstname']."\t\t".vc_encrypt_email($line['email']))."\r\n";
        $output .= $break;
    }

    ob_end_clean();

    $filename .= ".txt";
    header("Content-type: plain/text; charset=".cjoI18N::translate("htmlcharset"));
    header("Content-Disposition: attachment; filename=$filename");
    echo $output;
    exit;
}
