<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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

class cjoComments {

    static $addon = 'comments';
      
    public static function changeCommentStatus($id){
    
        global $I18N_7;
        
        $sql = new cjoSql();
        $sql->setQuery("SELECT status FROM ".TBL_COMMENTS." WHERE id='".$id."' LIMIT 2");

        if ($sql->getRows() == 0) {
            cjoMessage::addError(cjoAddon::translate(7,'msg_comment_not_found'));
            return false;
        }
        
        if ($sql->getRows() == 2) {
            cjoMessage::addError(cjoAddon::translate(7,'msg_comment_not_unique'));
            return false;
        }        

        $new_status = $sql->getValue('status') > 1 ? 0 : 1; 
         
        $update = $sql;
        $update->flush();
        $update->setTable(TBL_COMMENTS);
        $update->setWhere("id='".$id."'");
        $update->setValue("status",$new_status);
        return $update->Update(cjoAddon::translate(7,"status_updated"));
    }  
    
    public static function deleteComment($id) {
    
        global $I18N_7;
        
        $sql = new cjoSql();
        $sql->setTable(TBL_COMMENTS);
        $sql->setWhere('id='.$id);
        return $sql->Delete(cjoAddon::translate(7,"msg_comment_deleted"));
    }
    
    public static function getComments($article_id = false){
    
        global $CJO;
    
        if ($article_id === false) $article_id = $CJO['ARTICLE_ID'];
        $article = OOArticle::getArticleById($article_id);

        if (!OOArticle::isValid($article) || !$article->getComments()) return false;
    
        $results_lenght = 0;
        
        // Config aus DB holen
        $sql = new cjoSql();
        $qry = "SELECT * " .
                "FROM ".TBL_COMMENTS_CONFIG." " .
                "WHERE " .
                "  (reference_article_id='".$article_id."' OR" .
                "	reference_article_id='-1') AND" .
                " 	clang='".cjoProp::getClang()."' " .
                "ORDER BY reference_article_id DESC";
        $conf = array_shift($sql->getArray($qry));

        if ($sql->getRows() == 0 || !$conf['comment_function']) return false;
    
        $form_article = new cjoArticle();
        $form_article->setArticleID($conf['form_article_id']);
        $form_article->setCLang(cjoProp::getClang());
        $form = $form_article->getArticle(-1);

        $list = self::formatComments($conf, $article_id, $results_lenght);
    
        $form = '<div class="comments_form">'.$form.'</div>';
        $list = '<div class="comments_list">'.$list.'</div>';
    
        if ($conf['list_typ'] != 'guestbook'){
    
            // TEXT FÜR KOMMENTARHEADLINE
            switch($results_lenght){
                case '':
                case 0: $headline = '[translate: comments_add_comment]'; break;
                case 1: $headline = $results_lenght.'. [translate: comments_comment]'; break;
                default: $headline = $results_lenght.'. [translate: comments_comments]'; break;
            }
    
            // FUNKTION FÜR KOMMENTARHEADLINE (show/hide oder ...' zu Artikel XY')
            if($conf['list_typ'] == 'visible'){
                $headline = '<h3 class="comments_headline">'.$headline.' [translate: comments_for] &quot;'.$article->getName().'&quot;</h3>';
            }
            else{
                $comments_hide = ' style="display:none;"';
    
                $headline = '<h3 class="comments_headline" id="show_comments"><a href="#">'.$headline.' [translate: comments_show]</a></h3>'."\r\n".
                            '<h3 class="comments_headline" id="hide_comments"'.$comments_hide.'><a href="#">'.$headline.' [translate: comments_hide]</a></h3>';
             }
            $output = $list.$form;
        }
        else {
            $output = $form.$list;
        }
    
        $comments_hide = ($conf['list_typ'] == 'hidden') ? ' style="display:none;"' : '';
    
        return $headline.'<div id="comments"'.$comments_hide.'>'.$output.'</div>';
    }

    private static function formatComments($conf, &$article_id, &$results_lenght) {
        
        global $CJO;
    
        // Ausgabe nur im Frontend
        if (cjoProp::isBackend()) return false;
    
        if($conf['filter_comments_by'] != ''){
    
            $filter_comments_by = "";
            $filter_strs = explode(',',$conf['filter_comments_by']);
    
            foreach($filter_strs as $filter_str){
                $filter_comments_by .= ($filter_comments_by == '') ? "(" : " OR ";
                $filter_comments_by .= " UPPER(message) LIKE UPPER('%".trim($filter_str)."%') ";
            }
            $filter_comments_by .= ") AND";
        }
        else {
            $filter_comments_by = "article_id = '".$article_id."' AND";
        }
    
        $sql = new cjoSql();
        $qry = "SELECT * FROM
                    ".TBL_COMMENTS."
                WHERE
                    ".$filter_comments_by."
                    status = '1' AND
                    clang = '".cjoProp::getClang()."'
                ORDER BY
                    id ".$conf['order_comments'];
    
        $results = $sql->getArray($qry);
    
        // URSPRÜNGLICHE LÄNGE DES RESULTS-ARRAY
        $results_lenght = count($results);
    
        $html_tpl = new cjoHtmlTemplate($CJO['ADDON']['settings'][self::$addon]['html_template']);
    
        if(is_array($results)){
    
            if($conf['list_typ'] == 'guestbook'){
    
                $set['pagination']['xpage']          = empty($_GET['xpage']) ? 0 : $_REQUEST['xpage'];
                $set['pagination']['xpage_query']    = array('xpage' => $set['pagination']['xpage']);
                $set['pagination']['elm_per_page']   = 20; //['elementsperpage'];
                $set['pagination']['links_per_page'] = 5;
                $set['pagination']['start']          = $set['pagination']['xpage'] * $set['pagination']['elm_per_page'];
                $set['pagination']['end']            = $set['pagination']['elm_per_page'];
                $set['pagination']['show']   = ($set['pagination']['elm_per_page'] != '' && $results_lenght > $set['pagination']['elm_per_page']);
    
                // RESULTS-ARRAY AUF AKTUELLEN PAGINATION-AUSSCHNITT 'BESCHNEIDEN'
                $results = array_slice($results, $set['pagination']['start'] , $set['pagination']['end']);
    
                // AUSGABE DER PAGE-PAGINATION
                $pagination = ($set['pagination']['show'])
                    ? page_pagination($set['pagination']['xpage'],
                                     $set['pagination']['elm_per_page'],
                                     $set['pagination']['links_per_page'],
                                     $results_lenght,
                                     $set['pagination']['query_array'])
                    : '';
            }
    
            $list = array();
            foreach ($results as $key=>$result) {
    
                $autor = self::splitLongWords($result['author'],$conf);
    
                $list['anchor'][]  = 'comment_'.$result['id'];
                $list['author'][]  = $autor;
                $list['url'][]     = '<a href="'.$result['url'].'" class="homepage" title="'.$result['url'].'">'.$result['url'].'</a>';
                $list['city'][]    = self::splitLongWords($result['city'],$conf);
                $list['country'][] = self::splitLongWords($result['country'],$conf);
                $list['created'][] = strftime($conf['date_format'], $result['created']);
                $list['message'][] = nl2br(self::splitLongWords($result['message'],$conf));
                $list['reply'][]   = nl2br(stripslashes(trim($result['reply'])));
    
                $list['author_url'][] = ($result['url'] != '')
                    ? '<a href="'.$result['url'].'" class="homepage" title="'.$result['url'].'">'.$autor.'</a>'
                    : $autor;
    
                $list['count'][] = ($conf['list_typ'] != 'guestbook')
                    ? ($key+1).'. '
                    : ($results_lenght - ($key+($set['pagination']['xpage']*$set['pagination']['elm_per_page']))).'. ';
            }
    
            $html_tpl->fillTemplateArray('RESULTS', $list);
    
    
        }
    
        $html_tpl->fillTemplate('TEMPLATE', array(
                                'PAGINATION'            => $pagination,
                                'NO_ENTRIES'            => ($results_lenght == 0 ? $conf['no_entries_text'] : ''),
                                'SHORT_COMMENTS'        => $conf['short_comments'],
                                'SHORT_COMMENTS_LENGTH' => $conf['short_comments_length']
                                ));
    
        return $html_tpl->render(false);
    }

    
    private static function splitLongWords($text, $conf){
    
        switch ($conf['oversize_replace']) {
            case 'nbsp':
                $replace = '&nbsp;'; break;
            case '-' :
                $replace = '- '; break;
            default:
                $replace = ' ';
        }
    
        $text = stripslashes($text);
    
        if($conf['oversize_length'] == 0){
            return $text;
        } else{
            return preg_replace('/(^|\s)(\S{'.$conf['oversize_length'].'})(\S)/S', '\1\2'.$replace.'\3', $text);
        }
    }    
          
    public static function detectSpam($conf, $posted) {
        
        global $CJO;
    
        $addon = 'comments';
        $is_spam = false;
    
        $blacklist = explode("\n", $conf['blacklist_0']);
    
        if ($conf['blacklist_1'] != ''){
            $blacklist_1 = explode("\n", $conf['blacklist_1']);
            $blacklist = array_merge($blacklist, $blacklist_1);
            $blacklist = array_unique($blacklist);
        }
    
        foreach($blacklist as $key => $val){
    
            $val = trim($val);
            
            if (stripos($posted['author'], $val) !== false ||
                stripos($posted['message'], $val) !== false ||
                stripos($posted['url'], $val) !== false ||
                stripos($posted['email'], $val) !== false ||
                stripos($posted['city'], $val) !== false ||
                stripos($posted['country'], $val) !== false){
                $is_spam = 'spam';
                break;
            }
        }
        require_once $CJO['ADDON']['settings'][self::$addon]['b8'] ;
    
        $sql = new cjoSql();
        
        $config = array();
        $config['host'] = $CJO['DB'][$sql->DBID]['HOST'];
        $config['user'] = $CJO['DB'][$sql->DBID]['LOGIN'];
        $config['pass'] = $CJO['DB'][$sql->DBID]['PSW'];
        $config['db']   = $CJO['DB'][$sql->DBID]['NAME'];   
        $config['tableName'] = TBL_COMMENTS_B8; 
        
        $b8  = new b8($config);
        $b8_value = 0;
        $b8_results = array();
    
        $b8_results['author']   = $b8->classify($posted['author']);
        $b8_results['message']  = $b8->classify($posted['message']);
    
        if ($b8_results['author'] !== false &&
            $b8_results['message'] !== false) {
    
            foreach ($b8_results as $value){
                $b8_value += $value/count($b8_results);
            }
            if (!$is_spam && $b8_value > $conf['b8_spam_border']){
                $is_spam = 'spam';
            }

            if ($conf['b8_autolearn']){
                if ($b8_value > $conf['b8_spam_border']){
                    $b8->learn($posted['author'], 'spam');
                    $b8->learn($posted['message'], 'spam');
                }
                if ($b8_value <= $conf['b8_spam_border']){
                    $b8->learn($posted['author'], 'ham');
                    $b8->learn($posted['message'], 'ham');
                }
            }
        }
        return $is_spam;
    }
    
    private static function logBadRequest() {
        return true;
    }
    
    /**
     * Prüft Formulversand und übergebene Variablen
     *
     *
     * @todo Fehleranzeige verbessern (die()-Aufruf ist hier nicht gut)
     *
     * @link http://www.alt-php-faq.org/local/115  How do I stop spammers using header injection with my PHP Scripts?
     * @param string/array  postvars - zu prüfende POST-Variablen
     * @param string/array  domainname - erlaubte Domainnamen (ist kein Name angegeben, wird diese Prüfung ignoriert)
     * @return bool TRUE or FALSE
     */
    public static function checkPostedValues($postvars, $domainname = false) {
        
        // wenn keine zu prüfenden POST-Variablen übergeben wurden, gibts ein FALSE zurück
        // irgend etwas sollte schon zum prüfen vorhanden sein, wenn diese Funktion aufgerufen wird
        if (!isset ($postvars) || $postvars == '') return false;
    
        // First, make sure the form was posted from a browser.
        // For basic web-forms, we don't care about anything
        // other than requests from a browser:
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            die("Forbidden - You are not authorized to view this page");
            exit;
        }
    
        // Make sure the form was indeed POST'ed:
        //  (requires your html form to use: action="post")
        if (!$_SERVER['REQUEST_METHOD'] == "POST") {
            die("Forbidden - You are not authorized to view this page");
            exit;
        }
    
        /**
        * Dies nur ein Entwicklungs-Hack.
        * Wenn kein Domainname übergeben wurde, ignoriere diese Prüfung.
        *
        * Hier muss noch eine Lüsung her, wie Domainnamen sinnvoll übergeben werden können,
        * in Bezug auf Entwicklungsumgebung/Produktivumgebung.
        */
        if ($domainname !== false) {
            // Host names from where the form is authorized
            // to be posted from:
            if (!is_array ($domainname)) {
                $authHosts = array($domainname);
            } else {
                //$authHosts = array("domain.com", "domain2.com", "domain3.com");
                $authHosts = $domainname;
            }
    
            // Where have we been posted from?
            $fromArray = parse_url(strtolower($_SERVER['HTTP_REFERER']));
            
            // Test to see if the $fromArray used www to get here.
            $wwwUsed = strpos($fromArray['host'], "www.");
            // Make sure the form was posted from an approved host name.
            if (!in_array(($wwwUsed === false ? $fromArray['host'] : substr(stristr($fromArray['host'], '.'), 1)), $authHosts)) {
                self::logBadRequest();
                header("HTTP/1.0 403 Forbidden");
                exit;
            }
        } // if ($domainname !== false)
    
        // Attempt to defend against header injections:
        $badStrings = array("Content-Type:",
                            "MIME-Version:",
                            "Content-Transfer-Encoding:",
                            "bcc:",
                            "cc:");
    
        $_postvarcheck = (!is_array ($postvars)) ? array ($postvars) : $postvars;
    
        // Loop through each POST'ed value and test if it contains
        // one of the $badStrings:
        //  foreach($_postvarcheck as $k => $v) {
        foreach($_postvarcheck as $v) {
            foreach($badStrings as $v2) {
                if (strpos($v, $v2) !== false) {
                    logBadRequest();
                    header("HTTP/1.0 403 Forbidden");
                    exit;
                }
            }
        }
    
      // Made it past spammer test, free up some memory
      // and continue rest of script:
    //  unset($k, $v, $v2, $badStrings, $authHosts, $fromArray, $wwwUsed);
      unset($v, $v2, $badStrings, $authHosts, $fromArray, $wwwUsed);
    
      // wenn alles gut ging
      return true;
    }
}