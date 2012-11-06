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

class cjoEventCalendar {

    private static $mypage = 'event_calendar';
    
    public static function generateEventList($tmplfile='') {
    
        global $CJO, $results_lenght;
    
        if ($CJO['CONTEJO']) return false;
    
        if (!is_readable($tmplfile)) {
             $tmplfile =  $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/list.default.'.$CJO['TMPL_FILE_TYPE'];
        }
    
        $enabled_fields = $CJO['ADDON']['settings'][self::$mypage]['enabled_fields'];
    
        $filter_data = self::readFilterData();  
          
        $qry = array();
    
        $qry['WHERE'][] = "status = '1'";
        $qry['WHERE'][] = "clang = '".$CJO['CUR_CLANG']."'";
    
        if (cjoAssistance::inMultival('online_from_to', $enabled_fields)) {
        	$qry['WHERE'][] = "online_from < '".time()."' AND online_to > '".time()."'";
        }
        else {
            $qry['WHERE'][] = "(start_date >= '".($filter_data['datefrom']-86400)."' OR ".
                          	  "(end_date  >= '".$filter_data['datefrom']."'))";
            if ($filter_data['dateto']) {
                $qry['WHERE'][] = "start_date <= '".$filter_data['dateto']."'";
            }
        }
    
        $columns = cjoSql::getFieldnames(TBL_16_EVENTS);
    
        foreach (cjoAssistance::toArray($filter_data['select']) as $key=>$val) {
            if ($key == "" || $val == "" || !in_array($key, $columns)) continue;
        	$qry['WHERE'][] = $key." LIKE '%".$val."%'";
        }
    
        $qry['ORDER'] = " ORDER BY ".$filter_data['order_by']." ".$filter_data['order_dir'];
    
        $sql->flush();
        $qry = "SELECT * FROM
                    ".TBL_16_EVENTS."
                WHERE
                	".implode(" AND \r\n", $qry['WHERE'])."
                    ".$qry['ORDER'];
    
        $results = $sql->getArray($qry);
    
        // URSPRÜNGLICHE LÄNGE DES RESULTS-ARRAY
        $results_lenght = count($results);
    
        $html_tpl = new cjoHtmlTemplate($tmplfile, false);
    
        if (is_array($results)) {
    
            $set['pagination']['xpage'] 	 	 = !cjo_request('xpage', 'bool') ? 0 : cjo_request('xpage', 'int');
            $set['pagination']['xpage_query'] 	 = array('xpage' => $set['pagination']['xpage']);
            $set['pagination']['elm_per_page']   = $CJO['ADDON']['settings'][self::$mypage]['elements_per_page'];
            $set['pagination']['links_per_page'] = 5;
            $set['pagination']['start'] 		 = $set['pagination']['xpage'] * $set['pagination']['elm_per_page'];
            $set['pagination']['end'] 			 = $set['pagination']['elm_per_page'];
            $set['pagination']['show'] 	         = ($set['pagination']['elm_per_page'] != '' && $results_lenght > $set['pagination']['elm_per_page']);
    
            // RESULTS-ARRAY AUF AKTUELLEN PAGINATION-AUSSCHNITT 'BESCHNEIDEN'
            $results = array_slice($results, $set['pagination']['start'] , $set['pagination']['end']);
    
            // AUSGABE DER PAGE-PAGINATION
            $pagination = ($set['pagination']['show'])
                ? cjoOutput::getPagePagination(
                                 $set['pagination']['xpage'],
                                 $set['pagination']['elm_per_page'],
                                 $set['pagination']['links_per_page'],
                                 $results_lenght,
                                 $set['pagination']['query_array'])
                : '';
    
            $list = array();
            foreach ($results as $num => $result) {
    
                $list['number'][$num] = $set['pagination']['elm_per_page']*$set['pagination']['xpage']+$num+1;
    
                foreach ($result as $key => $val ) {
    
                    if ($val == '') continue;
                    $list[$key][$num]  = $val;
                    preg_match('/\d+$/', $key, $i);
    
    
                    $i = $i[0];
                    if ($i >= 1 && $i <= 10) $key = 'attribute';
    
                    switch ($key) {
    
                        case 'start_date':
                        case 'end_date':
                            $list[$key][$num] = strftime($CJO['ADDON']['settings'][self::$mypage]['date_output_format'], $val);
                            break;
    
                        case 'file':
                            $list['thumbnail'][$num] = OOMedia::toThumbnail($val, '', array ('crop_num'=>$CJO['ADDON']['settings'][self::$mypage]['list_crop_num']));
                            break;
    
                        case 'article_id':
                            if (cjo_contains_multival('article', $enabled_fields)) {
                                $article = OOArticle::getArticleById($val, $result['clang']);
                                if(OOArticle::isValid($article)) {
                                    $list['link_url'][$num] = $article->getUrl();
                                    $list['link'][$num] = $article->toLink();
                                }
                            }
                            break;
    
                        case 'attribute':
    
                            $list['attribute_title'.$i][$num] = $CJO['ADDON']['settings'][$mypage]['attribute_title'.$i];
    
                            switch ($CJO['ADDON']['settings'][self::$mypage]['attribute_typ'.$i]) {
    
                                case "select":
                                    $attribute_values = preg_replace('/\r\n|\r/', "\n", $CJO['ADDON']['settings'][self::$mypage]['attribute_values'.$i]);
                                    $attribute_values = cjo_to_array($attribute_values, "\n");
                                    $list['attribute'.$i][$num] = $val;
                                    $attribute_key = array_search($val,$attribute_values);
                                    if ($attribute_key !== false)
                                    $list['attribute_key'.$i][$num] = ($attribute_key+1);
                                    break;
    
                                case "textarea":
                                    $list['attribute'.$i][$num] = nl2br(stripslashes(trim($val)));
                                    break;
    
                                case "datepicker":
                                    $list['attribute'.$i][$num] = strftime($CJO['ADDON']['settings'][self::$mypage]['attribute_date_format'.$i], $val);
                                    break;
    
                                case "media":
                                    $list['media'.$i][$num] = OOMedia::toThumbnail($val, '', array ($CJO['ADDON']['settings'][self::$mypage]['attribute_crop_num'.$i]));
                                    break;
    
                                case "article":
                                    $article = OOArticle::getArticleById($val,$result['clang']);
                                    $list['attribute'.$i][$num] = $val;
                                    $list['link_url'.$i][$num] = $article->getUrl();
                                    $list['link'.$i][$num] = $article->toLink();
                                    break;
    
                                 default:
                                    $list['attribute'.$i][$num] = $val;
                            }
                            break;
                    }
                }
            }
            $html_tpl->fillTemplateArray('RESULTS', $list);
        }
        else {
            $no_data_text = $CJO['ADDON']['settings'][self::$mypage]['no_data_text'];
        }
    
        $html_tpl->fillTemplate('TEMPLATE', array(
                                'FILTER'				=> cjoEventCalendar::generateEventFilter($filter_data),
                                'NO_DATA'				=> $no_data_text,
                                'PAGINATION' 			=> $pagination
                                ));
    
        return $html_tpl->render(false);
    }
    
    public static function generateEventFilter($filter_data) {
    
        global $CJO, $article_id;
    
        $html_tpl = new cjoHtmlTemplate($CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/filter.default.'.$CJO['TMPL_FILE_TYPE'], false);
    
        $form_selects      = array();
        $search_key_sel    = new cjoSelect();
        $has_search_fields = (bool) $CJO['ADDON']['settings'][self::$mypage]['search_fields'];        
        $has_select_fields = (bool) $CJO['ADDON']['settings'][self::$mypage]['select_fields'];        

        if (!$has_search_fields && !$has_select_fields) return false;
        
        if ($has_search_fields) { 
            
            $search_key_sel->setName('event_calendar[search_key]');
            $search_key_sel->setId('ec_search_key');
            $search_key_sel->setSize(1);
            $search_key_sel->addOption("","");
            $search_key_sel->setSelected($filter_data['search_key']);
        
            foreach(cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['search_fields']) as $field) {
        
                preg_match('/\d+$/', $field, $i);
                $i = $i[0];
                if ($i >= 1 && $i <= 10) {
                    $search_key_sel->addOption($CJO['ADDON']['settings'][self::$mypage]['attribute_title'.$i], $field);
                } else {
                    $search_key_sel->addOption('[translate_16:'.$field.']',$field);
                }
            }
        }
    
        if ($has_select_fields) {
        
            foreach (cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['select_fields']) as $key=>$field) {
        
                    preg_match('/\d+$/', $field, $i);
                    $i = $i[0];
        
                    if ($i >= 1 && $i <= 10) {
                        $form_selects['element_label'][] = $CJO['ADDON']['settings'][self::$mypage]['attribute_title'.$i];
                    } else {
                        $form_selects['element_label'][]  = $field;
                    }
                    $field_sel = new cjoSelect();
                    $field_sel->setName('event_calendar[select]['.$field.']');
                    $field_sel->setId('ec_filter_'.$field);
                    $field_sel->setSize(1);
                    $field_sel->addOption("","");
                    $field_sel->addSqlOptions("SELECT DISTINCT ".$field." AS value1, ".$field." AS value FROM ".TBL_16_EVENTS." WHERE ".$field." != '' ORDER BY ".$field." ASC");
                    $option_string = print_r($field_sel->options,true);
                    if (!empty($filter_data['select'][$field]) && (
                        empty($option_string) ||
                        @strpos($option_string, $filter_data['select'][$field]) === false)) {
                        $field_sel->addOption($filter_data['select'][$field],$filter_data['select'][$field]);
                    }
                    $field_sel->setSelected($filter_data['select'][$field]);
                    $form_selects['element_select_out'][] = $field_sel->get();
            }
            
            $html_tpl->fillTemplateArray('FORM_SELECTS', $form_selects);
        }
            
        $strftime_format = $CJO['ADDON']['settings'][self::$mypage]['date_input_format'];
        $dateto_available = cjoAssistance::inMultival('dateto', $CJO['ADDON']['settings'][self::$mypage]['enabled_fields']);
    
        $html_tpl->fillTemplate('TEMPLATE', array(
                                    'URL'					 => cjoRewrite::getUrl($article_id),
                        			'DATE_INPUT_ENABLED'     => $CJO['ADDON']['settings'][self::$mypage]['date_input_enabled'],
                        	        'DATEFROM'               => @strftime($strftime_format, $filter_data['datefrom']),
                                    'DATETO_AVAILABLE'       => !empty($CJO['ADDON']['settings'][self::$mypage]['search_fields']),
                        	        'DATETO'                 => (!empty($filter_data['dateto'])
                                                                    ? @strftime($strftime_format, $filter_data['dateto'])
                                                                    : ''),
                                    'SEARCH_AVAILABLE'       => !empty($CJO['ADDON']['settings'][self::$mypage]['search_fields']),
                                    'SEARCH'			     => $filter_data['search'],
                        	        'SEARCH_KEY_OUT'         => $search_key_sel->get(),
                                    'COOKIE_ENABLED_CHECKED' => cjoAssistance::setChecked($filter_data['save_cookie'], array(1)),
                        		    'COOKIE_ENABLED'         => $CJO['ADDON']['settings'][self::$mypage]['cookie_enabled'],
                        			'FORM_LEGEND_TEXT'       => '[translate_16: form_legend_text]',
                        	        'DATEFROM_TEXT'          => '[translate_16: datefrom_text]',
                        			'DATETO_TEXT'            => '[translate_16: dateto_text]',
                        			'SEARCH_TEXT'            => '[translate_16: search_text]',
                        	        'SEARCH_KEY_TEXT'        => '[translate_16: search_key_text]',
                        		    'COOKIE_ENABLED_TEXT'    => '[translate_16: cookie_enabled_text]',
                        			'SUBMIT_BUTTON_TEXT'     => '[translate_16: submit_button_text]'
                        			));
     //  cjo_Debug($_SERVER);
       return $html_tpl->render(false);
    
    }    
   
    private static function readFilterData() {
    
        global $CJO;    
        
        if (cjo_post(self::$mypage, 'bool')) {
            //Session schreiben    
            $filter_data = cjo_post(self::$mypage, 'array', array());
    
            if (!empty($CJO['ADDON']['settings'][self::$mypage]['cookie_enabled']) &&
                $filter_data['save_cookie']) {
                self::writeCookie(self::$mypage, $filter_data, (86400*7));
            } else {
                self::writeCookie(self::$mypage,'', -86400); // Cookie Save löschen
            }
        }
        elseif (!empty($CJO['ADDON']['settings'][self::$mypage]['cookie_enabled'])) {
            
            $temp = self::readCookie(self::$mypage);
            if (!empty($temp)) $filter_data = $temp;
        }
    
        if (empty($filter_data)) {
            $filter_data = cjo_session(self::$mypage, 'array', array());
        }
    
        $today = getdate();
        $filter_data['datefrom'] = strtotime($filter_data['datefrom']);
    
        if ($filter_data['datefrom'] < $today[0]) {
            $filter_data['datefrom'] = $today[0];
        }
    
        if (!empty($filter_data['dateto'])) {
            $filter_data['dateto'] = strtotime($filter_data['dateto']);
            if ($filter_data['dateto'] < $filter_data['datefrom']) {
                $filter_data['dateto'] = $filter_data['datefrom'];
            }
        }
    
        if (!empty($filter_data['search_key']) && !empty($filter_data['search'])) {
        	$filter_data['select'][$filter_data['search_key']] = $filter_data['search'];
        }
    
        if (empty($filter_data['search']))      unset($filter_data['search_key']);
        if (empty($filter_data['order_by']))    $filter_data['order_by'] = 'start_date';
    
        $filter_data = self::mergeFilterData($filter_data);
    
        cjo_set_session(self::$mypage, $filter_data);
    
        return $filter_data;
    }    
    
    
    private static function mergeFilterData($filter_data) {
    
        global $CJO;
    
        if (empty($_GET)) return $filter_data;
    
        $available_keys = array_keys($filter_data);
    
        foreach ($_GET as $key=>$val) {
    
            if (in_array($key, $available_keys)) {
                $filter_data[$key] = (strpos($key,'date') === false)
                                  ? cjoAssistance::cleanInput($val)
                                  : strtotime(cjoAssistance::cleanInput($val));
            }
            if (cjoAssistance::inMultival($key, $CJO['ADDON']['settings'][self::$mypage]['select_fields'])) {
                $filter_data['select'][$key] = cjoAssistance::cleanInput($val);
            }
        }
    
        if (!cjo_get('search_key', 'bool') && !cjo_get('search', 'bool')) {
            $filter_data['search_key'] = cjo_get('search_key', 'string');
            $filter_data['search'] = cjo_get('search', 'string');
        	$filter_data['select'][$filter_data['search_key']] = $filter_data['search'];
        }
        
        return $filter_data;
    }
    
    
    private static function readCookie($cookie) {
         $array = get_magic_quotes_gpc() ? unserialize(stripslashes($_COOKIE[$cookie])) : unserialize($_COOKIE[$cookie]);
         return cjoAssistance::cleanInput($array);
    }
    
    private static function writeCookie($cookie, $array, $duration = 86400) {
         setcookie($cookie, serialize($array), time()+$duration);
    }
    
    public static function replaceVars($params) {
    
    	global $CJO, $article_id;
    
    	$content = $params['subject'];
    
    	if (strpos($content,'EC_EVENT_LIST[]') !== false) {
    		$content = str_replace('EC_EVENT_LIST[]', self::generateEventList($article_id), $content);
    	}
    	$content = str_replace('EC_EVENT_LIST[]', '', $content);
    
    	return $content;
    }       

    public static function updateEvent($id, $mode, $clang) {
    
    	global $CJO, $I18N_16;
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT status FROM ".TBL_16_EVENTS." WHERE id='".$id."' AND clang='".$clang."'");
    
        if ($sql->getRows() == 0) {
            cjoMessage::addError($I18N_16->msg("msg_no_such_event"));
        	return  false;
        }
        
        $sql->flush();
        
        if ($mode == 'delete') {
            $qry = 'DELETE FROM '.TBL_16_EVENTS.' WHERE id='.$id;
            return $sql->statusQuery($qry, $I18N_16->msg("msg_event_deleted"));
    	}
    
    	$new_val = ($sql->getValue('status') == 1) ? 0 : 1;

    	$update = $sql;
    	$update->setTable(TBL_16_EVENTS);
    	$update->setWhere("id='".$id."' AND clang='".$clang."'");
    	$update->setValue('status', $new_val);
    	$state = $update->update($I18N_16->msg('msg_event_status_updated'));
        
        cjoExtension::registerExtensionPoint('EVENT_CALENDAR_UPDATE_EVENT', array ('id' => $id));
        return $state;
    }
    
    public static function copyConfig($params) {

    	global $CJO, $I18N;

    	$file = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/0.clang.inc.php';
    	$dest = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/'.$params['id'].'.clang.inc.php';

    	if (file_exists($file)) {
    		if (!copy($file, $dest)) {
    			cjoMessage::addError($I18N->msg("err_config_file_copy", $dest));
    		}
    		else {
    		   @chmod($dest, $CJO['FILEPERM']);
    		}
    	}
    }
    
    public static function prepareDataset($params) {
        $params = $params['subject'];

        $start_time = cjoAssistance::correctTimestampOnDay($params['start_date'],$params['start_time']);
        $end_time   = cjoAssistance::correctTimestampOnDay($params['end_date'],$params['end_time']);

        $params['start_time'] = $params['start_time'] + $params['start_time']-$start_time;
        $params['end_time'] = $params['end_time'] + $params['end_time']-$end_time;
        
        return $params;
    }
}
