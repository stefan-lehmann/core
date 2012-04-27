<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  search
 * @version     2.6.0
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

class cjoSearch {

    public $form_name;
    public $matches;
    public $results;
    public $query;
    public $sql;
    public $html;
    public $error_message;
    public $search_string;
    public $keywords;
    public $rows;
    
    static $mypage          = 'search';
    public $min_length      = 4;
    public $relevance       = '0.5';
    public $limit           = 35;
    public $sourround_tag   = array('type'   => 'span',
                                  	'class'  => 'cjo_search_found',
                                  	'length' => 60);
    public $pagination      = array('start'  => 0,
                                    'elm_per_page' => 10,
    								'links_per_page' => 5,
                                    'show' => true,
                                    'html' => '');
    public $debug;
    private $is_bool;

    function __construct($form_name, $error_message="[translate: no_matched_results]", $debug=false) {

        global $CJO;
        
        $requested              = cjo_request($form_name, 'array', array(), true);
        
        $this->form_name        = $form_name;
        $this->results          = array();
        $this->error_message    = $error_message;
        $this->search_string    = $requested['search'];
        $this->is_bool          = $requested['stype'] == 'boolean';
        $this->debug            = $debug;
        
        if ($CJO['ADDON']['settings'][self::$mypage]['MIN_LENGTH'])
            $this->min_length = $CJO['ADDON']['settings'][self::$mypage]['MIN_LENGTH'];
            
        if ($CJO['ADDON']['settings'][self::$mypage]['RELEVANCE'])
            $this->relevance = $CJO['ADDON']['settings'][self::$mypage]['RELEVANCE'];
            
        if ($CJO['ADDON']['settings'][self::$mypage]['LIMIT_RESULTS'])
            $this->limit = $CJO['ADDON']['settings'][self::$mypage]['LIMIT_RESULTS'];        
                
        if ($CJO['ADDON']['settings'][self::$mypage]['ELEMENTS_PER_PAGE'])
            $this->pagination['elm_per_page'] = $CJO['ADDON']['settings'][self::$mypage]['ELEMENTS_PER_PAGE'];
            
        if ($CJO['ADDON']['settings'][self::$mypage]['SOURROUND_TAG_LENGTH'])
            $this->sourround_tag['length'] = $CJO['ADDON']['settings'][self::$mypage]['SOURROUND_TAG_LENGTH'];

        $this->splitKeywords();

        if ($this->is_bool) {
            if (strpos($this->search_string, ')') === false) $this->search_string = $this->search_string.')';
            if (strpos($this->search_string, '(') === false) $this->search_string = '('.$this->search_string;
        }
    }

    private function buildQuery(){

        global $CJO;

        $query = array();

        $exclude_articles = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['EXCLUDE_ARTICLES']);
        $include_modules  = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['INCLUDE_MODULES']);
        $article_values   = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['ARTICLE_VALUES']);
    	$slice_values     = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['SLICE_VALUES']);

        $this->search_string = preg_replace("/(\b[\w]*-[\w*]*-[\w*]*[^\s]|\b[\w]*-[\w*]*[^\s])/is",
        									'"$'.'1"', $this->search_string);

        // DB-Abfrage für bestimmte *ARTIKEL* ausschließen
        if(!empty($exclude_articles)) {
            foreach ($exclude_articles as $id) {
                    if ($query['excluded'] != '') $query['excluded'] .= " AND"."\r\n\t\t\t";
                    $query['excluded'] .= "a.path NOT LIKE '%|".trim($id)."|%'";
                    $query['excluded'] .= "AND"."\r\n\t\t\t";
                    $query['excluded'] .= "a.id != '".trim($id)."' ";
            }
            if ($query['excluded'] != '') $query['excluded'] = "(".$query['excluded'].") AND";
        }
        // DB-Abfrage für bestimmte *MODULE* ausschließen
        if (!empty($include_modules)) {
            foreach ($include_modules as $id) {
                if ($query['included'] != '') $query['included'] .= " OR"."\r\n\t\t\t";
                $query['included'] .= "s.modultyp_id = '".trim($id)."'";
            }
            if ($query['included'] != '') $query['included'] = "(".$query['included'].") AND";
        }

        // DB-Abfrage auf folgende Suchfelder anwenden
        $query['article_values'] = "a.".implode(", a.", $article_values);
        $query['concat_values']  = "s.".implode(", ' ', s.", $slice_values);
        $query['slice_values']   = "s.".implode(", s.", $slice_values);

        $this->query =
        		"SELECT
                    a.id, ".$query['article_values'].",
                    GROUP_CONCAT(
                        CONCAT(
                         ".$query['concat_values']."
                        )
                    ) AS content,
                    SUM(
                        MATCH
                            (".$query['slice_values'].")
                        AGAINST
                            ('".$this->search_string."')
                        +
                        MATCH
                            (".$query['article_values'].")
                        AGAINST
                            ('".$this->search_string."')
                     ) AS relevance
                FROM
                    ".TBL_ARTICLES_SLICE." s
                LEFT JOIN
                    ".TBL_ARTICLES." a
                ON
                    a.id = s.article_id
                WHERE
                    ".$query['excluded']."
                    ".$query['included']."
                    a.status = '1' AND
                    a.online_from < '".time()."' AND
                    a.online_to > '".time()."' AND
                    a.clang = '".$CJO['CUR_CLANG']."' AND
                    s.clang = '".$CJO['CUR_CLANG']."'
                GROUP BY a.id
                HAVING relevance > ".$this->relevance."
                ORDER BY relevance DESC
                LIMIT ".$this->limit;

        return $this->query;
    }

    private function buildPagination() {

        if ($this->rows <= $this->pagination['elm_per_page']) return false;

        $this->pagination['xpage'] 	 	 = !cjo_request('xpage','bool') ? 0 : cjo_request('xpage','int');
        $this->pagination['xpage_query'] = array('xpage' => $this->pagination['xpage']);
        $this->pagination['start'] 	     = $this->pagination['xpage'] * $this->pagination['elm_per_page'];
        $this->pagination['end'] 	     = $this->pagination['elm_per_page'];

        // RESULT-ARRAY AUF AKTUELLEN PAGINATION-AUSSCHNITT 'BESCHNEIDEN'
        $this->results = array_slice($this->results, $this->pagination['start'], $this->pagination['end']);

        if (!$this->pagination['show']) return false;

        // AUSGABE DER PAGE-PAGINATION
        $this->pagination['html'] = cjoOutput::getPagePagination(
                                            $this->pagination['xpage'],
                                            $this->pagination['elm_per_page'],
                                            $this->pagination['links_per_page'],
                                            $this->rows,
                                            array($this->form_name.'[search]' => $this->search_string,
                                                  $this->form_name.'[submit]' => 1));
    }

    private function splitKeywords(){
        $keywords = str_replace(array(')','(','{','}','{',']','[','/',',',':','.','?','!','+',' -','^','$','*'),' ',$this->search_string);
        $this->keywords = cjoAssistance::toArray($keywords,' ');
    }

    private function highlightResult($key){

        $value = $this->results[$key];
        $value['highlightedtext'] = '';
        $matches = array();

        $sourround_open = '<'.$this->sourround_tag['type'].' class="'.$this->sourround_tag['class'].'">';
        $sourround_close = '</'.$this->sourround_tag['type'].'>';

        foreach ($this->keywords as $keyword) {

            $pattern_keyword = str_replace('____CJO_SEARCH____', ').(', $keyword);

            if (strlen($value['content']) <= $this->sourround_tag['length']) {

                $value['highlightedtext'] = $value['content'];
            }
            else {

                $pattern = "/\s.{0,".$this->sourround_tag['length']."}".$pattern_keyword.".{0,".$this->sourround_tag['length']."}\s/im";
                preg_match_all($pattern, $value['content'], $matches);
                $matches = $matches[0];

                if (is_array($matches)) {
                    $i = 0;
                    foreach ($matches as $match) {
                        if ($i == 5) break 1;
                        $value['highlightedtext'] .=
                        	' ...'.preg_replace('/('.$keyword.')/ims',
                            $sourround_open.'$'.'1'.$sourround_close,
                            $match).'... ';
                        $i++;
                    }
                }
            }

            $keyword = str_replace('____CJO_SEARCH____', ' ', $keyword);

            $value['name'] = preg_replace("/(".$keyword.")/ims",
                                                        $sourround_open.'$'.'1'.$sourround_close,
                                                        $value['name']);

            $value['infos'] = preg_replace("/(".$keyword.")/ims",
                                                         $sourround_open.'$'.'1'.$sourround_close,
                                                         $value['infos']);

            if ($count == count($this->results)) $value['infos'] = str_replace('class="absatz"', 'class="absatz last"', $value['infos']);
        }

        if ($value['highlightedtext'] == '') {
            $pattern = '/([\S]+\s*){0,'.($this->sourround_tag['length']/3).'}/';
            preg_match($pattern, $value['content'], $matches);
            $value['highlightedtext'] = $matches[0].'... ';
        }

        $value = $this->results[$key] = $value;
    }

    public function get() {

        global $CJO;

        if (empty($this->search_string)) {
        	$this->html = '<p class="error">'.$this->error_message.'</p>';
	        return;
        }

        $this->sql = new cjoSql();
        $this->matches = $this->sql->getArray($this->buildQuery());
  

        if ($this->debug) {
            cjo_debug($this->sql,'$sql','yellow');
        }

        foreach($this->matches as $key => $result) {

            $id = $result['id'];
            // falls Offline weiter gehen
            if ($id == '' || !OOArticle::isOnline($id)) continue;

            $this->results[$id]['id']         = $id;
            $this->results[$id]['name']       = $result['name'];
            $this->results[$id]['meta-title'] = $result['title'];
            $this->results[$id]['infos']      = OOArticle::getArticleInfos($id,'on','off');
            $this->results[$id]['content']    = cjoAssistance::htmlToTxt($result['content']);
        }
        
        // ursprüngliche Länge des Result-Arrays
        $this->rows = count($this->results);
        
        if ($this->debug) {
            cjo_debug($result,'$result 1','lightblue');
        }

        $this->buildPagination();

        $count = $this->pagination['start'];

        foreach($this->results as $key=>$result) {

            $count++;

            $this->highlightResult($key);

            if ($this->debug) {
                cjo_debug($this->results[$key],'$this->results ['.$key.']','lightblue');
            }
            $this->html .= ($this->html != '') ? '<div class="absatz"></div>'."\r\n" : '';
            $this->html .= '<div class="textblock">'."\r\n";
            $this->html .= '	<h3>'.$count.'. <a href="'.cjoRewrite::getUrl($this->results[$key]['id'],$CJO['CUR_CLANG']).'">'.$this->results[$key]['name'].'</a></h3>'."\r\n";
            $this->html .= '	<p>'.$this->results[$key]['highlightedtext'].'</p>'."\r\n";
            $this->html .= '	<p><a href="'.cjoRewrite::getUrl($this->results[$key]['id'],$CJO['CUR_CLANG']).'" class="more cjo_search">'.cjoRewrite::getUrl($this->results[$key]['id'],$CJO['CUR_CLANG']).'</a></p>'."\r\n";
            $this->html .= '</div>'."\r\n";

        }

        $this->html = ($this->html != '')
            ? '<div class="absatz"><span class="left_left_70"></span></div>'.$this->html."\r\n".$this->pagination['html']."\r\n"
            : '<p class="error">'.$this->error_message.'</p>';

        return '<div class="cjo_search_results">'.$this->html.'</div>';
    }

    public function returnToFormTemplate() {
            return false;
    }

    static function addFulltextIndex() {

    	global $CJO, $I18N_13;

    	$article_values = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['ARTICLE_VALUES']);
    	$slice_values = cjoAssistance::toArray($CJO['ADDON']['settings'][self::$mypage]['SLICE_VALUES']);

    	if (!empty($slice_values)){
        	$sql = new cjoSql;
        	$qry = "ALTER TABLE ".TBL_ARTICLES_SLICE." ADD FULLTEXT search (".implode(',',$slice_values).")";
        	$sql->statusQuery($qry, $I18N_13->msg("msg_article_slice_index_added"));
    	}
    	if (!empty($article_values)){
        	$sql->flush();
        	$qry = "ALTER TABLE ".TBL_ARTICLES." ADD FULLTEXT search (".implode(',',$article_values).")";
        	$sql->statusQuery($qry, $I18N_13->msg("msg_article_index_added"));
    	}
    }

    static function removeFulltextIndex() {

    	global $CJO, $I18N_13;

    	$sql = new cjoSql();
    	$sql->statusQuery("ALTER IGNORE TABLE ".TBL_ARTICLES_SLICE." DROP INDEX `search`",
    	                   $I18N_13->msg("msg_article_slice_index_removed"));
    	$sql->flush();
    	$sql->statusQuery("ALTER IGNORE  TABLE ".TBL_ARTICLES." DROP INDEX `search`",
    	                   $I18N_13->msg("msg_article_index_removed"));
    }

    static function replaceVars($params) {

    	global $CJO, $article_id;

    	$content = $params['subject'];

    	$search_form = $CJO['ADDON']['settings'][self::$mypage]['SEARCH_ARTICLE_ID'];

    	if (!empty($search_form) &&
    	    $article_id != $search_form &&
    		strpos($content,'[[SE_SEARCH_FORM]]') !== false) {

    		$article = new cjoArticle($search_form);
            $article->setClang($CJO['CUR_CLANG']);
    		$form = preg_replace('/(?<=action\=\")[^"]*?(?=\"|#)/',
    							 cjoRewrite::getUrl($search_form),
    							 $article->getArticle() );

    	}
    	return str_replace('[[SE_SEARCH_FORM]]', $form, $content);
    }
}