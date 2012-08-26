<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
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

class cjoVarWYMeditor extends cjoVars {

    public function getACRequestValues($CJO_ACTION) {

        $values = cjo_request('WYMEDITOR', 'array');
        for ($i = 1; $i <= 19; $i++) {
            if (isset($values[$i])){
                $values[$i] = $this->processHtmlTables($values[$i]);
                $values[$i] = $this->cleanupInputs($values[$i]);
                $values[$i] = stripslashes($values[$i]);
                $CJO_ACTION['WYM'][$i] = $values[$i];
            }
            else {
                $CJO_ACTION['WYM'][$i] = '';
            }                
        }
        return $CJO_ACTION;
    }

    public function setACValues(& $sql, $CJO_ACTION, $escape = false) {

        global $CJO;

        for ($i = 1; $i <= 19; $i++) {
            if($CJO_ACTION['WYM'][$i])
            $this->setValue($sql, 'value'.$i, $CJO_ACTION['WYM'][$i], $escape);
        }
    }

    /**
     * @see cjo_var::handleDefaultParam
     */
    public function handleDefaultParam($varname, $args, $name, $value) {

        switch($name) {
            case 'height' :
            case 'width' :
            case 'hidetools' :
            case 'showtools' :
                $args[$name] = (string) $value;
                break;
        }
        return parent::handleDefaultParam($varname, $args, $name, $value);
    }

    public function getBEInput(& $sql, $content) {
        $content = $this->matchWYMeditor($sql, $content);
        return $content;
    }

    /**
     * MediaButton für die Eingabe
     */
    private function matchWYMeditor(& $sql, $content) {

        $vars = array ('CJO_WYMEDITOR');

        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
            foreach ($matches as $match) {

                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);

                if ($id <= 19 && $id > 0) {
                    $replace = $this->getWYMeditor($id, $this->getValue($sql, 'value'.$id), $args);
                    $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
                    $content = str_replace($var.'['.$param_str.']', $replace, $content);
                }
            }
        }
        return $content;
    }

    private function getWYMeditor($id, $value, $args = array()) {

        global $CJO, $I18N;

        $wymeditor = new WYMeditor();
        $wymeditor->height = 400;

        $class_vars = get_class_vars(get_class($wymeditor));

        foreach($args as $aname => $avalue) {
            if (!in_array($aname, $class_vars)) continue;
            $wymeditor->$aname = $avalue;
        }

        $wymeditor->id = $id;
        $wymeditor->content = $value;

        $toolsitems = array();

        foreach($wymeditor->toolsItems as $key => $tool) {
            $toolsitems[strtolower($tool['name'])] = $key;
        }

        if (!empty($args['hidetools'])) {
        	foreach(cjoAssistance::toArray($args['hidetools'],',') as $hide){

        	    $hide = trim($hide);
        	    $hide = strtolower($hide);

                if (isset($toolsitems[$hide]))
        	        unset($wymeditor->toolsItems[$toolsitems[$hide]]);
        	}
        }

        if (!empty($args['showtools'])) {

            $new_toolsitems = array();

        	foreach(cjoAssistance::toArray($args['showtools'],',') as $show){

        	    $show = trim($show);
        	    $show = strtolower($show);

                if (isset($toolsitems[$show]))
                    $new_toolsitems[] = $wymeditor->toolsItems[$toolsitems[$show]];
        	}
        	$wymeditor->toolsItems = $new_toolsitems;
        }

        return $wymeditor->render(false);
    }

    private function processHtmlTables($content) {

    	$placeholder = '<!-- CJO_REPLACE_TABLE -->';

    	$pattern['table']   = '/<table([^>]*)>(.*?)<\/table>/ims';
    	$pattern['caption'] = '/<caption[^>]*>.*?<\/caption>/ims';
    	$pattern['tr'] 	    = '/<tr([^>]*)>(.*?)<\/tr>/ims';
    	$pattern['td'] 	    = '/<(th|td)([^>]*)>(.*?)<\/\1>/ims';

    	$table_array = $this->readHtmlTables($content, $pattern);

    	return $this->writeHtmlTables($content, $table_array, $pattern['table'], $placeholder);
    }



    private function readHtmlTables($content, $pattern) {

    	$matches = array();

    	preg_match_all($pattern['table'], $content, $tables);

    	foreach ($tables[2] as $key1=>$val) {

    		$tables['cont'][$key1] = $val;
    		$matches[$key1]['attr'] = $this->readTableAttr($tables[1][$key1]);

    		preg_match($pattern['caption'], $val, $caption);
    		$matches[$key1]['caption'] = $caption[0];

    		preg_match_all($pattern['tr'], $val, $rows[$key1]);
    		$row_counts = array();
    		$row_counts_temp = array();

    		foreach ($rows[$key1][2] as $key2=>$val) {

    			$matches[$key1]['tr'][$key2]['attr'] = $this->readTableAttr($rows[$key1][1][$key2]);

    			preg_match_all($pattern['td'], $val, $cols[$key1][$key2]);

    			foreach ($cols[$key1][$key2][3] as $key3=>$val) {

    				$matches[$key1]['tr'][$key2]['td'][$key3]['attr'] = $this->readTableAttr($cols[$key1][$key2][2][$key3]);
    				$matches[$key1]['tr'][$key2]['td'][$key3]['cont'] = $val;

    				if ($matches[$key1]['tr'][$key2]['td'][$key3]['attr']['colspan']){
    				    $row_counts_temp[$key2] += $matches[$key1]['tr'][$key2]['td'][$key3]['attr']['colspan'];
    				} else {
    				    $row_counts_temp[$key2]++;
    				}

    				if ($matches[$key1]['tr'][$key2]['td'][$key3]['attr']['rowspan']){
    				    $row_counts_temp[$key2+1]++;
    				}
    			}
    			$row_counts[$key2] = $row_counts_temp[$key2];
    		}

    		asort($row_counts);
            $max_count = array_pop($row_counts);

    		foreach (cjoAssistance::toArray($row_counts) as $key2=>$row_count) {
    		    $diff_count = $max_count - $row_count;
                if ($diff_count > 0) {
                    for($i=0; $i<$diff_count; $i++){
                        $matches[$key1]['tr'][$key2]['td'][$row_count+$i]['cont'] = '';
                    }
                }

    		}

    	}
    	return $matches;
    }

    private function writeHtmlTables($content, $matches, $pattern, $placeholder) {

    	global $article_id, $slice_id;

    	$output = array();
    	$thead = '';
    	$tbody = '';
    	$content = preg_replace($pattern, $placeholder, $content);

    	foreach ($matches as $key1=>$table) {

    		$rows_out = array();
    		$rowspan = array();
    		$head_done = false;
    		$all = ($table['tr'][1]['td']) ? count($table['tr'][1]['td']) : count($table['tr'][1]['td']);

    		foreach (cjoAssistance::toArray($table['tr']) as $key2=>$row) {

    			$cells_out = array();
    			$num = 0;
    			$ct = (is_array($row['th'])) ? 'th' : 'td';



    			foreach (cjoAssistance::toArray($row[$ct]) as $key => $col) {

    			    if (!$row[$ct][$key]) continue;

    				$num++;
    				if ($rowspan[$num]-- > 1) $num++;

    				if (empty($rows_out['th'])) {
    				    $row['th'] = $row[$ct];
    				    $ct = 'th';

    				    $cur_key = $key + 1;

        				while(!empty($row[$ct][$cur_key])) {

        				    if (trim($row[$ct][$cur_key]['cont']) == '') {
        				        $col['attr']['colspan'] += (empty($col['attr']['colspan'])) ? 2 : 1;
        				        unset($row['th'][$cur_key]);
        				        unset($row['td'][$cur_key]);
        				    } else {
        				        break;
        				    }
        				    $cur_key++;
        				}
    				}

    				if ($col['attr']['rowspan']) $rowspan[$num] = $col['attr']['rowspan'];

    				if ($col['attr']['colspan']) {
    				    $set = array('num'=>$num, 'all'=>$all - $col['attr']['colspan']+1, 'type'=>$ct);
    				    $num += $col['attr']['colspan']-1;
    				} else {
    				    $set = array('num'=>$num, 'all'=>$all, 'type'=>$ct);
    				}
    				$attributes = $this->writeTableAttr($col['attr'],$set);

    				$cells_out[$ct] .= sprintf("\r\n\t\t".'<%s%s>'."\r\n\t\t\t".'%s'."\r\n\t\t".'</%s>',
    				                      $ct,
    									  $attributes,
    									  $this->cleanCellContent($col['cont']),
    									  $ct);
    			}

    			if (!$cells_out[$ct]) continue;

    			$set = array('num'=>($key2+1), 'all'=>count($table['tr']), 'type'=>'tr');
    			$attributes = $this->writeTableAttr($row['attr'],$set);

    			$rows_out[$ct] .= sprintf("\r\n\t".'<tr%s>%s'."\r\n\t".'</tr>',
    							  $attributes,
    							  $cells_out[$ct]);
    		}

    		if (!empty($rows_out['th'])) {
    			$thead = "\r\n".'<thead>'.$rows_out['th']."\r\n".'</thead>';
    		}

    		if (!empty($rows_out['td'])) {
    			$tbody = "\r\n".'<tbody>'.$rows_out['td']."\r\n".'</tbody>';
    		}

    		if (!empty($thead) || !empty($rows_out['td'])) {

    			$set = array('num'=>($key1+1), 'all'=>count($matches), 'type'=>'table');

    			$attributes = $this->writeTableAttr($table['attr'],$set);
    			$id 		= 'table_'.$article_id.'_'.$slice_id.'_'.($key1+1);
    			$caption  	= $table['caption'] != '' ? "\r\n".$table['caption'] : '';

    			$output[$key1] .= sprintf("\r\n".'<table id="%s" %s border="0">%s%s%s'."\r\n".'</table>'."\r\n",
    									 $id,
    									 $attributes,
    									 $caption,
    									 $thead,
    									 $tbody);
    		}
    		else{
    			$output[$key1] = '';
    		}
    		$content = preg_replace('/'.$placeholder.'/', $output[$key1], $content, 1);
    	}
    	return $content;
    }

    private function readTableAttr($value) {

    	if (empty($value)) return false;

    	$attributes = array();
    	preg_match_all('/([a-z]+)="(.*?)"/i', stripslashes(trim($value)), $matches);
    	foreach ($matches[1] as $key=>$val) {
    		if (empty($val))continue;
    			$attributes[$val] = $matches[2][$key];
    	}
    	return $attributes;
    }

    private function writeTableAttr($attributes, $set=array()) {

    	if (!is_array($attributes)) $attributes = array();

    	if (!isset($set['num']))  	 $set['num'] = 0;
    	if (!isset($set['type'])) 	 $set['type'] = '_';
    	if (!isset($set['allowed'])) $set['allowed'] = array('colspan', 'rowspan', 'class', 'title', 'summary');

    	$type = 'cjo_'.$set['type'];
    	$class['first'] = 'first';
    	$class['last']  = 'last';
    	$class['curr']  = $type.'_'.$set['num'];
    	$class['odd']   = $type.'_odd';
    	$class['even']  = $type.'_even';

    	$attributes['class']  = preg_replace('/cjo_(th|td|tr|table)_\S+|last|first/i', '', $attributes['class']);
    	$attributes['class'] .= ' '.$class['curr'];

    	$attributes['class'] .= ($set['type'] != 'tr' && $set['num'] == 1) ? ' '.$class['first'] : '';
    	$attributes['class'] .= ($set['type'] == 'tr' && $set['num'] == 2) ? ' '.$class['first'] : '';
    	$attributes['class'] .= ($set['num'] == $set['all']) ? ' '.$class['last'] : '';
    	$attributes['class'] .= ($set['num'] % 2) ? ' '.$class['odd'] : ' '.$class['even'];

    	$attributes['class'] = preg_replace('/\s{1,}/',' ', $attributes['class']);

    	$out = '';
    	foreach ($attributes as $attr_name => $attr_value) {
    		if (!in_array($attr_name, $set['allowed'])) continue;
    		$out .= ' '.$attr_name.'="'.trim($attr_value).'"';
    	}
    	return $out;
    }

    private function cleanCellContent($cell) {
    	$cell = trim($cell);
    	$cell = preg_replace('/<(h[1-6]|p|pre|div|address|blockquote)\b[^>]*>(.*?)<\/\1>/i', '\2<br/>', $cell);
    	return preg_replace('/<br\/>$/i', '', $cell);
    }
}