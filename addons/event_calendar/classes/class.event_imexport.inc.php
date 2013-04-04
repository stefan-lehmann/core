<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  event_calendar
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

class cjoEventImportExport {

    public static function import() {
    
        global $CJO, $I18N_16, $CJO_USER, $clang;
    
        $addon = 'event_calendar';
    
        $message_temp = array();
        $statistic = 0;
    
        $import_file	 = $_FILES['userfile']['tmp_name'];
        $charset	     = cjo_post('charset', 'string');
        $divider	     = stripslashes(cjo_post('divider', 'string'));
        $limit_start	 = stripslashes(cjo_post('limit_start', 'int'));
        $limit_number    = stripslashes(cjo_post('limit_number', 'int'));
    
        $csv_file = ($charset == 'iso')
            ? utf8_encode(file_get_contents($import_file))
            : file_get_contents($import_file);
    
    
        preg_match_all('/^.*$/m', $csv_file, $csv_data);
               
        // leere Datei
        if (!is_array($csv_data[0])){
            cjoMessage::addError(cjoAddon::translate(16,'err_empty_csv_document'));
            return false;
        }
        else {
            $columns = array_shift($csv_data[0]);
            $columns = explode($divider,$columns);
        }

        $csv_data[0] = array_slice($csv_data[0], $limit_start-1, ($limit_number > 0 ? $limit_number : count($csv_data[0])));
        $total = count($csv_data[0]);


        // Falsches Trennzeichen
        if (!preg_match('/'.$divider.'(?=([^"]*"[^"]*")*(?![^"]*"))/', $csv_file)){
            cjoMessage::addError(cjoAddon::translate(16,'err_wrong_delimiter'));
            return false;
        }
    
        $insert = new cjoSql();

        foreach ($csv_data[0] as $data) {
    
            $data = explode($divider,$data);
            $insert->flush();
            $insert->setTable(TBL_16_EVENTS);
    
            foreach ($columns as $key=>$col) {
    
              $col = self::prepareValues($col);
              $val = self::prepareValues($data[$key]);
    
                    preg_match('/\d+$/', $col, $i);
                    $i = $i[0];
                    if ($i >= 1 && $i <= 10) $col = 'attribute';
    
                switch($col){
                    case 'file':
                    case 'article_id':
                        if (empty($val)) continue;
                        $insert->setValue($col,$val);
                        break;
    
                    case 'start_date':
                        $insert->setValue($col,strtotime($val));
                        break;
    
                    case 'end_date':
                        if (empty($val)) continue;
                        $insert->setValue($col,strtotime($val));
                        break;
    
                    case 'end_date':
                        if (empty($val)) continue;
                        $insert->setValue($col,strtotime($val));
                        break;
    
                    case 'status':
                        if ($val=='') $val=1;
                        $insert->setValue($col,$val);
                        break;
    
                    case 'online_from':
                        $insert->setValue($col, (empty($val) ? time() : strtotime($val)));
                        break;
    
                    case 'online_to':
                        $insert->setValue($col, (empty($val) ? mktime(0, 0, 0, 1, 1, 2020) : strtotime($val)));
                        break;
    
                    case 'description':
                    case 'short_description':
                            if (!empty($val) &&
                                strpos($val,'<p>') === false &&
                                strpos($val,'<\p>') === false) {
                                $val = '<p>'.$val.'</p>';
                            }
                            $insert->setValue($col, $val);
                            break;
                            
                    case 'attribute':
    
                        switch($CJO['ADDON']['settings'][$addon]['attribute_typ'.$i]) {
    
                            case 'datepicker': $insert->setValue($col.$i, strtotime($val));
    
                            case 'wymeditor':
                                if (!empty($val) &&
                                    strpos($val,'<p>') === false &&
                                    strpos($val,'<\p>') === false) {
                                    $val = '<p>'.$val.'</p>';
                                }
                                $insert->setValue($col.$i, $val);
                                break;
    
                            default: $insert->setValue($col.$i,$val);
                         }
                         break;
    
                    default: $insert->setValue($col,$val);
                }
            }
    
            $insert->addGlobalCreateFields(cjoProp::getUser()->getValue("name").' (Import)');
            $insert->addGlobalUpdateFields($CJO_USER->getValue("name").' (Import)');
            if ($insert->Insert()){
                $statistic++;
            }
            else {
                cjoMessage::addError($insert->getError());
            }
    
        }
        if ($statistic > 0) {
            cjoMessage::addSuccess(cjoAddon::translate(16,"msg_import_done", $statistic, ($total-$statistic)));
        }
        else {
            cjoMessage::addError(cjoAddon::translate(16,"msg_import_failed"));
        }
    }

    function export($separator=";") {
    
        global $CJO, $I18N_16, $I18N, $clang;
    
        $addon = 'event_calendar';
    
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_16_EVENTS." WHERE clang='".$clang."' ORDER BY start_date";
        $data = $sql->getArray($qry);
    
        $ignore = array('id',
        				'clang',
        				'createdate',
        				'updatedate',
        				'createuser',
        				'updateuser');
    
        $output = "";
        foreach(cjoAssistance::toArray($data) as $line) {
    
            if (!is_array($line)) continue;
    
            foreach($line as $key=>$val) {
    
                preg_match('/\d+$/', $key, $i);
                $i = $i[0];
    
                if ($key == 'attribute'.$i &&
                    empty($CJO['ADDON']['settings'][$addon]['attribute_typ'.$i])) {
                        unset($line[$key]);
                }
                elseif (strpos($key, '_date') !== false ||
                        strpos($key, 'online_') !== false ||
                       ($key == 'attribute'.$i &&
                        strpos($CJO['ADDON']['settings'][$addon]['attribute_typ'.$i], 'date') !== false)) {
    
                    $line[$key] = (!empty($val))
                                ? strftime ($CJO['ADDON']['settings'][$addon]['date_input_format'], $val)
                                : '';
                }
                else {
                    $line[$key] = $val;
                }
                if (in_array($key, $ignore)) unset($line[$key]);
            }
            if (empty($output)) {
                $output .= str_replace(array("\r\n","\r","\n"), ' ',implode($separator,array_keys($line)))."\r\n";
            }
            $output .= str_replace(array("\r\n","\r","\n"), ' ',implode($separator,$line))."\r\n";
        }
    
        ob_end_clean();
        $date_string = date("Y").'-'.date("m").'-'.date("d");
        $filename = "events_".$date_string."___".$CJO['CLANG'][$clang].".csv";
        header("Content-type: plain/text; charset=".cjoI18N::translate("htmlcharset"));
        header("Content-Disposition: attachment; filename=$filename");
        echo $output;
        exit;
    }


    private static function prepareValues($value){
    
        $value = trim($value);
        $value = preg_replace('/"(.*)"/', '\1', $value );
    
        return $value;
    }
}