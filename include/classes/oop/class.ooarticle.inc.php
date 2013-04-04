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

/**
 * OOArticle class
 *
 * The OOArticle class is an object wrapper over the database table cjo_articel_slice.
 * @package 	contejo
 * @subpackage 	core
 */
class OOArticle extends OOContejo {

    /**
     * Returns an OOContejo object of an article based on the id.
     * @param int $article_id
     * @param int $clang
     * @return object
     * @access public
     */
    public static function getArticleById($article_id, $clang = false) {

        global $CJO;

       // $article_id = (int) $article_id;

        if ($clang === false) $clang = cjoProp::get('CUR_CLANG');
        
        if (empty($article_id)) return new OOArticle(OOContejo :: convertGeneratedArray(array(), $clang));

        $article_path = cjoPath::generated('articles', $article_id.'.'.$clang.'.article');

        if (!file_exists($article_path)) {
            cjoGenerate::generateArticle($article_id, false, $clang);
        }
              
        if (empty($CJO['ART'][$article_id]['article_id'][$clang])) {
            if (!file_exists($article_path)) return $article_path;
            include_once ($article_path);
        }

        return new OOArticle(OOContejo :: convertGeneratedArray($CJO['ART'][$article_id], $clang));
    }

    /**
     * Returns a list of articles which names match the
     * search string. For now the search string can be either
     * a simple name or a string containing SQL search placeholders
     * that you would insert into a 'LIKE '%...%' statement.
     * @param string $article_name
     * @param boolean $ignore_offlines If true ignore elements with status 0
     * @param boolean|int $clang
     * @param boolean|int $categories
     * @return array
     * @access public
     */
    public static function searchArticlesByName($article_name, $ignore_offlines = false, $clang = false, $categories = false) {

        global $CJO;

        if ($clang === false) $clang = cjoProp::getClang();
        $offline = $ignore_offlines ? " AND status = 1 " : "";
        $cats = '';
        if (is_array($categories)) {
            $cats = " AND re_id IN (".implode(',', $categories).") ";
        }
        elseif (is_string($categories)) {
            $cats = " AND re_id = $categories ";
        }
        elseif ($categories === true) {
            $cats = " AND startpage = 1 ";
        }

        $artlist = array ();
        $sql = new cjoSql();
        $sql->setQuery("SELECT ".implode(',', OOContejo :: getClassVars())." FROM ".TBL_ARTICLES." WHERE NAME LIKE '$article_name' AND clang='$clang' $offline $cats");

        for ($i = 0; $i < $sql->getRows(); $i ++) {
            foreach (OOContejo :: getClassVars() as $var) {
                $article_data[$var] = $sql->getValue($var);
            }
            $artlist[] = new OOArticle($article_data, $clang);
            $sql->next();
        }
        return $artlist;
    }

    /**
     * Returns an array of articles as OOContejo objects which have a certain type.
     * @param int $article_type_id
     * @param boolean $ignore_offlines If true ignore elements with status 0
     * @param boolean|int $clang
     * @return array
     * @access public
     */
    public static function searchArticlesByType($article_type_id, $ignore_offlines = false, $clang = false) {

        global $CJO;

        if ($clang === false) $clang = cjoProp::getClang();
        $offline = $ignore_offlines ? " AND status = 1 " : "";
        $artlist = array ();

        $sql = new cjoSql();
        $sql->setQuery("SELECT ".implode(',', OOContejo :: getClassVars())." FROM ".TBL_ARTICLES." WHERE type_id = '$article_type_id' AND clang='$clang' $offline");

        for ($i = 0; $i < $sql->getRows(); $i++) {
            foreach (OOContejo :: getClassVars() as $var) {
                $article_data[$var] = $sql->getValue($var);
            }
            $artlist[] = new OOArticle($article_data, $clang);
            $sql->next();
        }
        return $artlist;
    }

    /**
     * Returns the site wide start article as OOContejo object.
     * @param boolean|int $clang
     * @return object
     * @access public
     */
    public static function getSiteStartArticle($clang = false) {

        global $CJO;

        if ($clang === false) $clang = cjoProp::getClang();
        return OOArticle :: getArticleById(cjoProp::get('START_ARTICLE_ID'), $clang);
    }

    /**
     * returns the parent article as OOContejo object
     * @param boolean|int $re_id
     * @param boolean|int $clang
     * @return object
     * @access public
     */
    public function getParentArticle($re_id = false, $clang = false) {

        global $CJO;

        if ($clang === false) $clang = cjoProp::getClang();
        if ($re_id === false) $re_id = $this->getParentId();
        return OOArticle :: getArticleById($re_id, $clang);
    }

    /**
     * Returns an array of sub articles as OOContejo objects.
     * @param boolean $ignore_offlines
     * @param boolean|int $clang
     * @return array
     * @access public
     */
    public function getChildren($ignore_offlines = false, $clang = false) {
        return OOArticle :: getArticlesOfCategory($this->getId(), $ignore_offlines, $clang);
    }

    /**
     * Returns an array of sub articles as OOContejo objects.
     * @param int $re_id
     * @param boolean $ignore_offlines If true ignore elements with status 0
     * @param boolean|int $clang
     * @return array
     * @access public
     */
    public static function getArticlesOfCategory($re_id, $ignore_offlines = false, $clang = false) {

        global $CJO;

        if ($clang === false) $clang = cjoProp::getClang();

        $articlelist = cjoPath::generated('articles', $re_id.'.'.$clang.'.alist');
        $artlist = array();

        if ($ignore_offlines && $re_id > 0 && !OOArticle::isOnline($re_id)) return $artlist;
        
        if (!file_exists($articlelist)) {
            $re_id = (int) $re_id;
            cjoGenerate::generateArticle($re_id, false, $clang);
        }

        if (file_exists($articlelist)) {

            include ($articlelist);

            if (isset($CJO['RE_ID'][$re_id]) && 
                is_array($CJO['RE_ID'][$re_id])) {

                foreach ($CJO['RE_ID'][$re_id] as $var) {

                    $article = OOArticle :: getArticleById($var, $clang);
                    
                    if (!OOArticle::isValid($article)) continue;
                    
                    if ($ignore_offlines) {
                        if ($article->_isOnline(false)) {
                            $artlist[] = $article;
                        }
                    }
                    else{
                        $artlist[] = $article;
                    }
                }
            }
        }
        return $artlist;
    }

    /**
	 * Returns an array of OOArticle objects that have
	 * children and no parent OOArticle objects.
     * @param boolean $ignore_offlines If true ignore elements with status 0
     * @param boolean|int $clang
     * @return unknown
     */
    public static function getRootCategories($ignore_offlines = false, $clang = false) {

		global $CJO;

		if ($clang === false) $clang = $GLOBALS['CJO']['CUR_CLANG'];
		return OOArticle :: getArticlesOfCategory(0, $ignore_offlines, $clang);
	}

    /**
     * Returns the hierachical path of an article.
     * @param int $id
     * @param boolean $is_re_id
     * @param int|boolean $clang
     * @return string
     * @access public
     */
    public static function getArticlePath($id, $is_re_id = false, $clang = false) {

        global $CJO;

        if ($clang === false) $clang = cjoProp::getClang();

        $article = OOArticle :: getArticleById($id, $clang);
        $re_id = ($is_re_id === false) ? $article->_re_id : $id;

        $path = '|';

        if (!empty($re_id)) {
            $path = '|'.$re_id.$path;
            $path = OOArticle :: getArticlePath($re_id, false, $clang, false).$path;
        }

        return str_replace('||','|', $path);
    }

    /**
     * Returns an array of top-level articles as OOContejo objects.
     * @param boolean $ignore_offlines If true ignore elements with status 0
     * @param in|boolean $clang
     * @return array
     * @access public
     */
    public static function getRootArticles($ignore_offlines = false, $clang = false) {
        global $CJO;
        if ($clang === false) $clang = cjoProp::getClang();
        return OOArticle :: getArticlesOfCategory(0, $ignore_offlines, $clang);
    }

    /**
     * Returns true if the current article is currently edited by an other user
     * @return boolean
     */
    public function isLocked() {        
        global $CJO;
        if (!cjoProp::isBackend() || !class_exists('cjoLog')) return false;
        return cjoLog::isArticleLockedByUser($this->getId());
    }
    
    /**
     * Validates the real online status considerating online from
     * and online to values, type id matchings, online status and
     * if set true, the real online status of parent articles.
     *
     * @param boolean $check_tree if true the method tests the whole tree
     * @param boolean $redirect
     * @return boolean|void
     */
    public function _isOnline($check_tree=true, $redirect=false, $clang=false) {
        return self::isOnline($this->getId(), $check_tree, $redirect, $clang);
    }
    
    /**
     * Validates the real online status considerating online from
     * and online to values, type id matchings, online status and
     * if set true, the real online status of parent articles.
     *
     * @param int|boolean $article_id
     * @param boolean $check_tree if true the method tests the whole tree
     * @param boolean $redirect
     * @return boolean|void
     */
    public static function isOnline($article_id=false, $check_tree=true, $redirect=false, $clang=false) {

    	global $CJO;

        $article = false;

    	if ($article_id == cjoProp::get('NOTFOUND_ARTICLE_ID') ||
    		$article_id == cjoProp::get('START_ARTICLE_ID')) {
    		return true;
    	}

    	if ($article_id) {

    		$article = OOArticle::getArticleById($article_id, $clang);
    		if (!OOArticle::isValid($article)) return false;
    		
    		if ($article->isOffline() ||
    			!$article->isOnlineTime()) {
    			return false;
    		}
    		else if ($check_tree) {
                $tree = $article->getParentTree();
				foreach ($tree as $article) {
					if ($article->isOffline() ||
                        !$article->isOnlineTime()) {
						return false;
					}
				}
    		}
    		
    	    if (!$article->checkTypeId()) {
    			return false;
    		}
    		else if ($check_tree) {
				foreach ($tree as $article) {
					if (!$article->checkTypeId()) {
						return false;
					}
				}
    		}    		
    	}
    	else {
    		return false;
    	}

        return true;
    }

    /**
     * Returns true if the current article has been marked as offline.
     *
     * Attention: This funtion only references this article,
     * login status, online from and online to settings as
     * well as the status of all parent articles are ignored
     *
     * @return boolean
     * @access public
     */
    public function isOffline() {
        return $this->_status != 1 ? true : false;
    }
    
    /**
     * Returns true if the current articles online offline period is present.
     *
     * @return boolean
     * @access public
     */   
    public function isOnlineTime() {

        global $CJO;
            
        if (!cjoProp::get('ONLINE_FROM_TO_ENABLED')) return true;
            
        if ($this->_online_from < time() && 
            $this->_online_to > time()) return true;
            
        return false;
    }    
    
    /**
     * Checks if the type id of an article matches the permission of a user.
     * @param mixed $type_id
     * @return boolean
     */
    public function checkTypeId($type_id=false) {

    	global $CJO;

    	if (cjoProp::isBackend() || cjoProp::getUser()) return true;

    	if ($type_id === false) $type_id = $this->getTypeId();

    	switch($type_id) {
    		case 1:		    return true;
    		case 'out':     return ($CJO['USER']['ID']) ? false : true;
    		case 'in':      return ($CJO['USER']['ID']) ? true : false;
    		case 'contejo': return cjoLogin::isBackendLogin();
    	}

    	if (!$CJO['USER']['ID']) return false;

    	$check = array_intersect($CJO['ATYPES'][$type_id], $CJO['USER']['GROUPS']);

    	if (!empty($check)) return true;
    	return false;
    }

    /**
     * Returns true if the given var is a valid OOArticle object.
     * @param object $article
     * @return boolean
     * @access public
     */
    public static function isValid($article) {
        return is_object($article) && is_a($article, 'ooarticle');
    }

    /**
     * Returns true if an article has teaser property set.
     * @return boolean
     * @access public
     */
    public function isTeaser() {
        return ($this->_teaser == 1) ? true : false;
    }

    public static function getArticleInfos($article_id, $CJO_EXT_VALUE) {

    	global $CJO;

    	if (!isset($CJO_EXT_VALUE['show_margin']) || $CJO_EXT_VALUE['show_margin'] != 'on') return false;

    	$article = OOArticle::getArticleById($article_id);
    	if (!OOArticle::isValid($article)) return false;

    	if (cjoAddon::isAvailable('comments') &&
    	    $article->getComments()) {
    		$qry = "SELECT count(*) rowCount FROM ".TBL_COMMENTS." WHERE status='1' AND article_id='".$article_id."' AND clang=".cjoProp::getClang();
    		$sql = new cjoSql();
    		$sql->setQuery($qry);

    		$rowCount = $sql->getValue('rowCount');
    		if($rowCount == 1) {
    			$comments = '<span class="item">'.$rowCount.'&nbsp;'.(($rowCount == 1) ? '[translate: label_comment]' : '[translate: label_comments]').'</span>';
    		}
    	}

    	if (isset($CJO_EXT_VALUE['show_infos']) && $CJO_EXT_VALUE['show_infos'] == 'on') {
    		switch (trim($article->getValue("author"))) {
    			case '-1': $author = ''; break;
    			case ''  : $author = '<span class="item first">[translate: label_author] '.trim($article->getValue("createuser")).'</span>'; break;
    			default  : $author = '<span class="item first">[translate: label_author] '.trim($article->getValue("author")).'</span>';
    		}
    		$update = '<span class="item">[translate: label_article_state] '.strftime(cjoI18N::translate('setlocal_short_date'), $article->getValue('updatedate')).'</span>';
    	}

    	$left  = !empty($author) ? $author : '';
    	
    	if (!empty($comments)) {
    	    $left .=  empty($author)  ? str_replace('class="item"','class="item first"',$comments) : $comments;
    	}
    	if (!empty($update)) {
    	    $left .= (empty($author) && empty($comments)) ? str_replace('class="item"','class="item first"',$update) : $update;
    	}
    	$right = '		<span><a href="#to_top" title="[translate: top_of_page]" class="anchor_top">[translate: top_of_page]</a></span>'."\r\n";

    	$infos_out  = '<div class="absatz">'."\r\n";
    	$infos_out .= 	(trim($left) != '') ?  '<span class="left_left_70">'.$left.'</span>'."\r\n" : '';
    	$infos_out .= 	($CJO_EXT_VALUE['show_to_top'] == 'on') ?  '<span class="right_right">'.$right.'</span>'."\r\n" : '';
    	$infos_out .= '</div>'."\r\n";

    	return $infos_out;
    }


    public static function getArticleInfosBE($article, $ctype = 0) {

    	global $CJO, $I18N;

    	if (!OOArticle::isValid($article)) return false;

    	$clang = cjo_request('clang', 'cjo-clang-id');

    	$article_id     = $article->getId();
    	$datetimeformat = cjoI18N::translate('datetimeformat');
    	$dateformat     = cjoI18N::translate('dateformat');
        $local_params   = array('clang' => $clang, 'ctype' => $ctype, 'mode' => 'edit');
        $global_params1 = array('page'=>'edit', 'subpage'=>'content', 'article_id' => $article->getValue("id"));
        $global_params2 = array('page'=>'edit', 'subpage'=>'structure', 'article_id' => $article->getValue("re_id"));
        $ampersand      = '&amp;';

    	$icon = ($article->isStartpage()) ? 'pages_white.png' : 'page_white.png';
    	$headline1 = '<img src="./img/silk_icons/'.$icon.'" alt="" /> <b>'.$article->getValue("name").'</b> (ID='.$article->getValue("id").')';

    	$edit_button = new buttonField();
    	$edit_button->addButton('slice_edit_button', cjoI18N::translate('label_edit_now'), true, 'img/silk_icons/page_white_edit.png');
    	$edit_button->setButtonAttributes('slice_edit_button',
    									  'onclick="cjo.openShortPopup(\''.cjoUrl::createBEUrl($local_params, $global_params1, $ampersand).'\'); return false;"');


    	$icons 					 = array();
    	$icons['navi_item'] 	 = ($article->getValue('navi_item') == 1) ? 'chart_organisation.png' : 'chart_organisation_off.png';
    	$icons['online_from_to'] = ($article->getValue('online_from') < time() && $article->getValue('online_to') > time()) ? 'calendar_view_day.png' : 'calendar_view_day_off.png';
    	$icons['status'] 		 = ($article->getValue('status') == 1) ? 'eye.png' : 'eye_off.png';
    	$icons['teaser'] 		 = ($article->getValue('teaser') == 1) ? 'star.png' : 'star_off.png';

    	if (!cjoProp::get('ONLINE_FROM_TO_ENABLED')) unset($icons['online_from_to']);

        $headline2 = '';
    	foreach ($icons  as $icon) {
    		$headline2 .= ' <img src="img/silk_icons/'.$icon.'" alt="" /> ';
    	}

    	$headline2 = '<a href="'.cjoUrl::createBEUrl($local_params, $global_params2, $ampersand).'" class="structure_settings">'."\r\n".
    				 '	'.$headline2."\r\n".
    				 '</a>';

    	$info = '<div class="article_info" id="article_info_'.$article_id.'">
    				<p>
    					<span class="label">'.cjoI18N::translate('label_from_to').': </span>
    					<span class="text">
    						<b>'.strftime($dateformat, $article->getValue("online_from")).'</b>
    						--
    						<b>'.strftime($dateformat, $article->getValue("online_to")).'</b>
    					</span>
    				</p>
    				 <p>
    					<span class="label">'.cjoI18N::translate('label_author').': </span>
    					<span class="text">'.$article->getValue("author").'</span>
    				</p>
    				<p>
    					<span class="label">'.cjoI18N::translate('label_title').': </span>
    					<span class="text">'.$article->getValue("title").'</span>
    				</p>
    				<p>
    					<span class="label">'.cjoI18N::translate('label_meta_image').': </span>
    					<span class="text">'.$article->getValue("file").'</span>
    				</p>
    				<p>
    					<span class="label">'.cjoI18N::translate('label_keywords').': </span>
    					<span class="text">'.$article->getValue("keywords").'</span>
    				</p>
    				<p>
    					<span class="label">'.cjoI18N::translate('label_description').': </span>
    					<span class="text">'.$article->getValue("description").'</span>
    				</p>
    				<p>
    					<span class="label">'.cjoI18N::translate('label_createdate').': </span>
    					<span class="text">'.cjoI18N::translate('msg_article_info_date_user', strftime($datetimeformat, $article->getValue("createdate")),$article->getValue("createuser")).'</span>
    				</p>
    				<p>
    					<span class="label">'.cjoI18N::translate('label_updatedate').': </span>
    					<span class="text">'.cjoI18N::translate('msg_article_info_date_user', strftime($datetimeformat, $article->getValue("updatedate")),$article->getValue("updateuser")).'</span>
    				</p>
    				<p>
    				'.$edit_button->_get().'
    				</p>
    			</div>';

    	return '<div class="info_icons">'."\r\n".
    			'	<h5>'."\r\n".
    			'	'.$headline2."\r\n".
    			'	'.$headline1."\r\n".
    			'	</h5>'."\r\n".
    			'	'.$info."\r\n".
    			'</div>'."\r\n";
    }
}