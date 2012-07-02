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
 * @version     2.6.0
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

/**
 * OONavigation class
 *
 * The OONavigation class is a object handler for creating all types of navigations.
 * @package 	contejo
 * @subpackage 	core
 */
class OONavigation {

    /**
     * OOArticle object of the current article
     * @var object
     */
    public $article;

    /**
     * Id of the current article
     * @var int
     */
    public $_article_id;

    /**
     * current hierarchical level
     * @var int
     */
    public $_level = 1;

    /**
     * max hierarchical level
     * @var int
     */
    public $_level_depth;

    /**
     * start level
     * @var int
     */
    public $_start_level = 1;

    /**
     * current language id
     * @var int
     */
    public $_clang;

    /**
     * all parent articles as OOArticle object
     * @var array
     */
    public $_parent_tree = array();

    /**
     * structure with all navigation names
     * @var array
     */
    public $_structure = array();

    /**
     * the generated navigations with the
     * name of the navigation as key
     * @var array
     */
    public $_navis = array();

    /**
     * the catgroupid of the currently processed article
     * @var int
     */
    public $_curr_nav_id;

    /**
     * if false only the start level is visible
     * @var boolean
     */
    public $_show_sub_levels = false;

    /**
     * if true inactive elements are not displayed
     * @var boolean
     */
    public $_active_only = true;

    /**
     * if true offline articles and their children
     * are not displayed
     * @var boolean
     */
    public $_online_only = true;

    /**
     * if true an id parameter is added to
     * the link of the current article
     * @var string
     */
    public $_link_active_id = true;

    /**
     * id parameter for the link of the
     * current article
     * @var string
     */
    public $_curr_attr = 'cur_nav_item';

    /**
     * if true the navigation structure is ignored
     * all generated elements are added to navigation 'default'
     *
     * @var bool
     */
    public $_disable_navi_names = false;
    
    /**
     * if set overrides the default id selector value
     *
     * @var string
     */
    public $_set_id = '';
    /**
     * Constructor
     *
     * @param int|boolean $article_id id of the current article
     * @param int|boolean $clang id of the current language
     */
    public function __construct($article_id = false, $clang = false) {

        global $CJO;
        $this->_article_id  = (!empty($article_id)) ? $article_id : cjo_request('article_id', 'cjo-article-id', $CJO['START_ARTICLE_ID']);
        $this->_clang       = ($clang !== false) ? $clang : $CJO['CUR_CLANG'];
        $this->article      = OOArticle::getArticleById($this->_article_id);
        $this->_parent_tree = $this->article->getParentTree();
        $this->_curr_nav_id = $this->article->getCatGroup();
        $this->getNaviStructure();
    }

    /**
     * Generates a set of navigations from the article structure.
     * The navigation types and membership of an article is defined
     * by CONTEJO's catgroups settings.
     * @param array $set settings for parsing the contejo article structure
     * @return void
     */
    public function genereateNavis($set = array()) {

        global $CJO;

        if (!is_array($set)) $set = array();

        /**
         * Set settings default values
         */
        if (empty($set['_article_id'])) 	$set['_article_id'] = 0;
        if (empty($set['_level'])) 			$set['_level'] = $this->_level;
        if (empty($set['_level_depth'])) 	$set['_level_depth'] = $this->_start_level + $this->_level_depth;
        if (empty($set['_curr_nav_id'])) 	$set['_curr_nav_id'] = $this->_curr_nav_id;
        if (empty($set['_set_id']))         $set['_set_id'] = $this->_set_id;

        if ($this->_level_depth && $set['_level'] >= $set['_level_depth']) return false;

        if (!isset($set['_openul'][$set['_level']]) || 
            !is_bool($set['_openul'][$set['_level']])) $set['_openul'][$set['_level']] = false;

        if ($set['_curr_nav_id'] != '' && !$this->_disable_navi_names) {
            if (isset($this->_structure[$set['_curr_nav_id']][$set['_level']])) {
                $set['_curr_navi_name'] = $this->_structure[$set['_curr_nav_id']][$set['_level']];
            }
            /**
             * no separate navigation, add as sublevel of the parent navigation
             */
            else {
                $_parent_level = $set['_level']-1;
                while ($_parent_level > 1 &&
                       !isset($this->_structure[$set['_curr_nav_id']][$_parent_level])) {
                    $_parent_level--;
                }
                $set['_curr_navi_name'] = $this->_structure[$set['_curr_nav_id']][$_parent_level];
            }
        }
        else {
            if ($set['_curr_navi_name'] == '') $set['_curr_navi_name'] = 'default';
        }

        foreach ($this->getArticles($set) as $article) {
            
            $css_for_ul = '';
            $id_for_li  = '';
            $css_for_li = '';
            $id_for_a   = '';
            $css_for_a  = '';

            /**
             * check the real online status and the visibility in the navigation
             */
            if (!$article->_isOnline() || !$article->_navi_item) continue;

            /**
             * get the belonging to navigation in the navigation structure
             */
            if ($set['_level'] == 1 && !$this->_disable_navi_names) {
                $set['_curr_nav_id'] = $article->getCatGroup();
                if (!isset($this->_structure[$set['_curr_nav_id']])) continue;

                $set['_prev_navi_name'] = $set['_curr_navi_name'];
                $set['_curr_navi_name'] = $this->_structure[$set['_curr_nav_id']][1];

                if ($set['_prev_navi_name'] != $set['_curr_navi_name'] &&
                    !empty($this->_navis[$set['_prev_navi_name']])) {

                    $this->_navis[$set['_prev_navi_name']] .= "\r\n".'</ul>';
                    $set['_openul'][$set['_level']] = false;
                }
            }

            $active = ($article->_re_id == 0 || in_array($article->_re_id, array_keys($this->_parent_tree))) ? true : false;
            $show = ((!$this->_active_only || ($this->_active_only && $active)) && $this->_start_level <= $set['_level']) ? true : false;

            if ($show) {

                if (!$set['_openul'][$set['_level']]) {
                    
                    $id_value = !empty($set['_set_id']) ? $set['_set_id'] :  $set['_prev_navi_name'];
                    
                    
                    $css_for_ul = $set['_article_id'] > 0 ? ' class="childof_'.$id_value.'_'.$set['_article_id'].'"' : '';
                    
                    if (!isset($this->_navis[$set['_curr_navi_name']])) $this->_navis[$set['_curr_navi_name']] = '';
                    
                    $this->_navis[$set['_curr_navi_name']] .= "\r\n".'<ul'.$css_for_ul.'>'; /* opening ul */
                    $css_for_li = ' class="first"'; /* first class for the first list-element */
                    $set['_openul'][$set['_level']] = true;
                }

                $id_value = !empty($set['_set_id']) ? $set['_set_id'] :  $set['_curr_navi_name'];
                
                $id_for_li = ' id="'.$id_value.'_'.$article->getId().'"';
                
                $tree_keys = array_keys($this->_parent_tree);
                if ($this->_link_active_id && array_pop($tree_keys) == $article->getId()) {
                    $id_for_a = ' id="'.$this->_curr_attr.'"';
                }

                $css_for_a = ' class="tmpl'.$article->getTemplateId().'"';

                if (in_array($article->getId(),array_keys($this->_parent_tree))) {

                    $css_for_a = str_replace('class="', 'class="current ', $css_for_a);

                    if (!empty($css_for_li)) {
                        $css_for_li = str_replace('class="', 'class="current ', $css_for_li);
                    }
                    else {
                        $css_for_li = $css_for_a;
                    }
                }
                
                if ($article->getRedirect()) {
                    if (preg_match('/\D/',$article->getRedirect())) {
                        $css_for_a = str_replace('class="', 'class="ext_redirect ', $css_for_a);
                    }
                    elseif (is_numeric($article->getRedirect())) {
                        $css_for_a = str_replace('class="', 'class="int_redirect ', $css_for_a);
                    }
                }
                
                if (!$this->_link_active_id && $current_id != '') {

                    $this->_navis[$set['_curr_navi_name']] .= sprintf("\r\n\t".'<li%s%s><span%s%s>%s</span>',
                                                                      $id_for_li,
                                                                      $css_for_li,
                                                                      $id_for_a,
                                                                      $css_for_a,
                                                                      $article->getName());
                }
                else {
                    $this->_navis[$set['_curr_navi_name']] .= sprintf("\r\n\t".'<li%s%s><a href="%s"%s%s title="%s">%s</a>',
                                                                      $id_for_li,
                                                                      $css_for_li,
                                                                      $article->getUrl(),
                                                                      $id_for_a,
                                                                      $css_for_a,
                                                                      $article->getName(),
                                                                      $article->getName());
                }          
            }

            if ($this->_show_sub_levels || $active) {
                /* change settings for recrusion */
                $newset = $set;
                $newset['_article_id'] = $article->getId();
                $newset['_level']	   = $set['_level']+1;
                $this->genereateNavis($newset);
            }

            if ($show)
            $this->_navis[$set['_curr_navi_name']] .= '</li>';

        }

        if ($set['_openul'][$set['_level']]) $this->_navis[$set['_curr_navi_name']] .= "\r\n".'</ul>';

    }

   /**
    * Returns generated navigation by its name.
    * @param string|boolean $name name of the navigation
    * @return string
    */
    public function getNavi($name=false) {

        if ($name === false) $name = 'default';
        if (isset($this->_navis[$name])) {
            return $this->_navis[$name];
        }
        return;
    }

    /**
     * Generates the menu for language selection.
     *
     * @param int|boolean $text_length if true the language name is truncated by this value (eg. false = deutsch, 2 = de, 3 = deu)
     * @param string $navi_name name of the menu (default is 'lang')
     * @return void
     */
    public function genereateLangNavi($text_length = false, $navi_name = 'lang') {

        global $CJO;

        if (count($CJO['CLANG']) < 2) return false;

        $output = '';

        foreach ($CJO['CLANG'] as $key => $name) {

            $first_css  = ($output == '') ? ' class="first"' : '';
            $current    = ($key == $this->_clang) ? ' current' : '';
            $short_name = $text_length ? self::truncateLinkText($name,$text_length) : $name;

            $output .= sprintf("\r\n\t".'<li%s><a href="%s" title="%s" class="%s %s %s%s">%s</a></li>',
                               $first_css,
                               cjoRewrite::getUrl($this->_article_id,$key),
                               $name,
                               strtolower($CJO['CLANG_ISO'][$key]),
                               cjo_specialchars($name),
                               $text_length ? ' '.cjo_specialchars($short_name) : '',
                               $current,
                               $short_name);
        }

        $this->_navis[$navi_name]  = "\r\n".'<ul class="lang_nav">'.$output."\r\n".'</ul> ';
    }

    /**
     * Generate a breadcrumb navigation.
     *
     * @param string $prefix_text text that marks the breadcrumbs (eg. "You are here: ")
     * @param string $root_name name of the start page
     * @return void
     */
    public function genereateBreadCrumbs($prefix_text = '', $root_name = 'Home') {

        global $CJO;

        $end_span  = false;
        $count     = $this->_parent_tree;

        if (empty($this->_parent_tree)) return '<!-- Sorry no breadcrumb available! -->';

        $this->_navis['breadcrumbs'] = '<div id="breadcrumb">'."\n\r".
                                       '	<strong>'.$prefix_text.'</strong>'."\n\r".
                                	   '	<a href="'.cjoRewrite::getUrl($CJO['START_ARTICLE_ID']).'">'.$root_name.'</a>'."\n\r";

        foreach($this->_parent_tree as $id => $article) {

            $count--;

            if ($article->getId() == $CJO['START_ARTICLE_ID']) continue;
            if (!$article->isNaviItem()) continue;

            if ($article->getId() != $this->_article_id) {
                $this->_navis['breadcrumbs'] .= '	<a href="'.$article->getURL().'">'.$article->getName().'</a>'."\n\r";
            }
            else {
                $this->_navis['breadcrumbs'] .= '	<span>'.$article->getName().'</span>'."\n\r";
            }
        }

        $this->_navis['breadcrumbs'] .= '</div>'."\n\r";
    }

    /**
     * Generates a pevious and next article navigation within a category.
     * @param string $parent_link_text text for the link to parent article
     * @param bool $loop if true the navigation loops
     * @param bool $link_child if true linkt to first child of the current article is displayed
     * @param bool|int $text_length if not false links will be truncated to the given length
     * @return void
     */
    public function linkPrefNextArticles($parent_link_text = 'default', $loop = true, $link_child = false, $text_length = false) {

        global $CJO;

        $results = array();
        $count   = 0;
        $left    = '<div class="pagination_left">&nbsp;</div>';
        $right   = '<div class="pagination_right">&nbsp;</div>';
        $middle  = '';

        if ($this->article->_re_id == 0) return false;

        $parent   = OOArticle::getArticleById($this->article->_re_id, $this->_clang);
        $children = OOArticle::getArticlesOfCategory($this->article->_id, true, $this->_clang);
        $articles = $parent->getChildren();
        
        while($parent->getRedirect() && $parent->_re_id != 0) {
           $parent = OOArticle::getArticleById($parent->_re_id, $this->_clang);
        }

        foreach (cjoAssistance::toArray($articles) as $art) {
            if ($art->_isOnline(false,false)) {
                if ($this->_article_id == $art->getId()) {
                    $curr = $count;
                }
                $results[] = $art;
                $count++;
            }
        }

        $total = count($results)-1;

        if ($total >= 1) {

            $next = $results[($curr+1)];
            $prev = $results[($curr-1)];

            if ($curr == 0) {
                $prev = $loop ? $results[$total] : null;
            }
            elseif ($curr == $total) {
                $next = $loop ? $results[0] : null;
            }

            if ($prev != null) {
                $name = $prev->getName();                   
                $prev->_name = self::truncateLinkText($name,$text_length, true);   
                $left = $prev->toLink(null, array('class'=>'prev page_link',  'title' => '[translate: label_back_to] '.$name), 
                					  'div', array('class'=>'pagination_left'));
            }

            if ($next != null) {
                $name = $next->getName();                  
                $next->_name = self::truncateLinkText($name,$text_length, true);                    
                $right = $next->toLink(null, array('class'=>'next page_link', 'title' => '[translate: label_next_to] '.$name), 
                					   'div', array('class'=>'pagination_right'));                
            }
        }

        if ($parent_link_text != 'default' && $parent_link_text != '') {
            $parent->_name = $parent_link_text;
        }

        $name = $parent->getName();            
        $parent->_name = self::truncateLinkText($name,$text_length, true);                  
                
        $middle = $parent->toLink(null, array('class'=>'up page_link','title' => '[translate: label_up_to] '.$name));

        if ($link_child && OOArticle::isValid($children[0])) {
            
            $child = $children[0];
            $name = $child->getName(); 
            $child->_name = self::truncateLinkText($name, $text_length, true);   
            $middle .= '<br/>'.$child->toLink(null, array('class'=>'down page_link', 'title' => '[translate: label_down_to] '.$name));     
        }                         

        $middle = '<div class="pagination_middle">'.$middle.'</div>';
                                  
        $this->_navis['prevnext'] = '<div class="pagination prevnext">'.$left.$middle.$right.'</div>'."\r\n";
       
    }
    
/**
     * Generates a page of pages navigation within a category.
     * @param string $text "page x of  y pages" text
     * @param bool $loop if true the navigation loops
     * @return void
     */
    public function linkPageOfPages($text = false, $loop = true) {

        global $CJO;

        $results = array();
        $count   = 0;
        $left    = '';
        $right   = '';
        $middle  = '';

        if ($this->article->_re_id == 0) return false;

        $articles = OOArticle::getArticlesOfCategory($this->article->_re_id, true, $this->_clang);

        foreach (cjoAssistance::toArray($articles) as $art) {
            if ($art->_isOnline(false,false)) {
                if ($this->_article_id == $art->getId()) {
                    $curr = $count;
                }
                $results[] = $art;
                $count++;
            }
        }

        $total = count($results)-1;

        if ($total >= 1) {

            $next = $results[($curr+1)];
            $prev = $results[($curr-1)];

            if ($curr == 0) {
                $prev = $loop ? $results[$total] : null;
            }
            elseif ($curr == $total) {
                $next = $loop ? $results[0] : null;
            }
            
            if ($prev != null) {
                $prev->_name = '[translate: label_back_to] '.$prev->_name;
                $left = $prev->toLink('', array('class'=>'page_link prev'), 'div', array('class'=>'pagination_left'));
            }

            if ($next != null) {
                $next->_name = '[translate: label_next_to] '.$next->_name;
                $right = $next->toLink('', array('class'=>'page_link next'), 'div', array('class'=>'pagination_right'));
            }

            $middle = (!$text) ? '[translate: label_page] %s [translate: label_of] %s' : $text;
            
            $middle = sprintf('<div class="pagination_middle">'.$middle.'</div>', $curr+1 ,$total+1);

            $this->_navis['pageofpages'] = '<div class="pagination">'.$left.$middle.$right.'</div>'."\r\n";
        }
    }

    /**
     * Generates a back to list link.
     * @param string $parent_link_text text for the link to parent article
     * @param int $reference name of the global var that handles the id of the parent article
     * @param array $params link parameter
     * @return void
     */
    public function linkBackToList($parent_link_text = 'default', $reference = 'ref', $params = array()) {

        $this->_navis['backtolist'] = '';

        $reference_id = cjo_request('ref', 'cjo-article-id', 0);
        $xpage        = cjo_request('xpage', 'int', 0);

        if ($reference_id > 0) {

            $list_article = OOArticle::getArticleById($reference_id, $this->_clang);

            if (!OOArticle::isValid($list_article)) return false;

            if ($xpage > 0) {
                $params['xpage'] = $xpage;
            }

            if (!empty($parent_link_text) && $parent_link_text != 'default') {
                $list_article->_name = $parent_link_text;
            }
            $this->_navis['backtolist'] = $list_article->toLink($params, array('class'=>'cjo_back_to_list'));
        }
    }

    /**
     * Generates a standard anchor link text.
     * @param string $string
     * @param int $length
     * @return string
     * @access public
     */
    public static function getAnchorLinkText($string, $length=20) {
        $string = urlencode($string);         
        $string = cjo_specialchars($string);
        $string = str_replace(array('!','?','.',':',';','&',',','+'), '',$string);
        $string = substr($string, 0, $length);    

        return $string;
    }

    /**
     * Returns true if the given var is a valid OONavigation object.
     * @param object $nav
     * @return boolean
     * @access public
     */
    public static function isValid($nav) {
        return is_object($nav) && is_a($nav, 'oonavigation');
    }

    /**
     * Returns a set of articles.
     * @param array $set settings for parsing the contejo article structure
     * @return array
     */
    private function getArticles($set) {

        if ($set['_article_id'] == 0) {
            $articles = OOArticle::getRootArticles($this->_online_only, $this->_clang);
        }
        else {
            $articles = OOArticle::getArticlesOfCategory($set['_article_id'], $this->_online_only, $this->_clang);
        }

        return (is_array($articles) && !empty($articles)) ? $articles : array();
    }

    /**
     * Reads settings of the navigation structure from the database.
     * @return void
     */
    private function getNaviStructure() {

        global $CJO;

        if (!empty($this->_navis)) return false;

        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_ARTICLES_CAT_GROUPS." ORDER BY group_id");

        for ($i=0;$i<$sql->getRows();$i++) {

            $array = explode('|',str_replace(' ', '_', '|'.$sql->getValue("group_structure")));
            unset($array[0]);
            $this->_structure[$sql->getValue("group_id")] = $array;

            $sql->next();
        }
    }
    
    /**
     * Truncates a linktext
     * @param string $string
     * @param int $text_length 
     * @param bool $prefix
     * @return string
     */
    private static function truncateLinkText($string, $text_length, $prefix = false) {

       $prefix = $prefix && strlen($string) > $text_length ? '...' : '';
       return ($text_length) ? substr($string, 0, (int) $text_length).$prefix : $string;
    }
}