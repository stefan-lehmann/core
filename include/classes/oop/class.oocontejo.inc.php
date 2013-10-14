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
 * OOContejo class
 *
 * The OOContejo class provides the main methods for the OOArticle class.
 * @package 	contejo
 * @subpackage 	core
 */
class OOContejo {

    /**
     * Constructor
     * @param array|boolean $params
     * @param int|boolean $clang
     */
    public function __construct($params = false, $clang = false) {

        global $CJO;

        if ($params !== false) {
            foreach (OOContejo :: getClassVars() as $var) {
                $class_var = '_'.$var;
                $this->$class_var = isset($params[$var]) ? $params[$var] : NULL;
            }            
        }

        $this->_slices = isset($params['slices']) ? unserialize(stripslashes($params['slices'])) : NULL;

        if ($clang === false && isset($params['clang'])) {
            $clang = $params['clang'];
        }
        
        if ($clang !== false) {
            $this->setClang($clang);
        }

        //just for compatibility
        $this->_autor = &$this->_author;
        $this->_alias = &$this->_redirect;
    }

    /**
     * Converts the generated array to OOContejo formatted array.
     * @param array $generatedArray
     * @param int $clang
     * @return array
     * @access public
     */
    public static function convertGeneratedArray($generatedArray, $clang) {

        if (empty($generatedArray)) return array();
        
        $OOContejoArray['id'] = $generatedArray['article_id'][$clang];
        $OOContejoArray['clang'] = $clang;
      
        foreach ($generatedArray as $key => $var) {
            $OOContejoArray[$key] = $var[$clang];
        }
        unset ($OOContejoArray['_article_id']);
        $OOContejoArray['set_template_id'] = $generatedArray['set_template_id'][$clang]; 

        return $OOContejoArray;
    }

    /**
     * Set language id of the current article.
     * @param int $clang
     * @access public
     */
    public function setClang($clang) {
        $this->_clang = $clang;
    }

    /**
     * Returns language id of the current article.
     * @return int
     * @access public
     */
    public function getClang() {
        return $this->_clang;
    }

    /**
     * Returns an OOContejo object value.
     * @param string $value
     * @return mixed
     * @access public
     */
    public function getValue($value) {
        if (substr($value, 0, 1) != '_') {
            $value = "_".$value;
        }
                
        if (empty($this->$value)) {
            if ($value == '_updatedate') {
                return $this->getValue('createdate');
            }
            if ($value == '_createdate') {
                return 0;
            }
            if ($value == '_image') {
                return $this->getValue('file');
            }                
        }
        return isset($this->$value) ? $this->$value : null;
    }

    /**
     * Returns a url for linking to this article.
     * @param string $params
     * @param string $hash_string
     * @return string html link code
     * @access public
     */
    public function getUrl($params = '', $hash_string = '') {
        return cjoRewrite::getUrl($this->getId(), $this->getClang(), $params, $hash_string);
    }

    /**
     * Returns the id of the current article.
     * @return int
     * @access public
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Returns the id of the parent article.
     * @return int
     * @access public
     */
    public function getParentId() {
        return $this->_re_id;
    }

    /**
     * Returns the name of the article.
     * @return string
     * @access public
     */
    public function getName() {

        global $I18N;

        if ($this->_name != '') {
            return htmlspecialchars($this->_name);
        }
        elseif ($this->_title != '') {
            return htmlspecialchars($this->_title);
        }
        else{
            return $I18N->msg("label_no_name");
        }
    }

    /**
     * Returns the name of the file
     * which is connected to this article.
     * @param string|boolean $fullpath if true path to the mediafolder is added
     * @return string
     * @access public
     */
    public function getFile($fullpath = false) {
       global $CJO;
       return ($fullpath == true) ? $CJO['MEDIAFOLDER'].'/'.$this->_file : $this->_file;
    }

    /**
     * Returns an OOMedia object of the file
     * which is connected to this article.
     * @return object
     * @access public
     */
    public function getFileMedia() {
        return OOMedia :: getMediaByName($this->_file);
    }

    /**
     * Returns the type id of the article.
     * @return string|int
     * @access public
     */
    public function getTypeId() {
        return $this->_type_id;
    }

    /**
     * Returns the description of the article.
     * @return string
     * @access public
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * Returns the keywords of the article.
     * @return string
     * @access public
     */
    public function getKeywords() {
        return $this->_keywords;
    }

    /**
     * Returns the title of the article.
     * @return string
     * @access public
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Returns the alias of the article.
     * @return string
     * @access public
     * @deprecated
     */
    public function getAlias() {
        return $this->_redirect;
    }
    
    /**
     * Returns the redirect of the article.
     * @return string
     * @access public
     */
    public function getRedirect() {
        return $this->_redirect;
    }
    

    /**
     * Returns the author of the article.
     * @return string
     * @access public
     */
    public function getAuthor() {
        return $this->_author;
    }

    /**
     * alias of getAuthor() just for compatibility
     * @return string
     * @access public
     */
    public function getAutor() {
        return $this->getAuthor();
    }

    /**
     * Returns true if the article is shown in the navigations.
     * @return boolean
     * @access public
     */
    public function isNaviItem() {
        return $this->_navi_item;
    }

    /**
     * Returns the navigation membership of the article.
     * @return string
     * @access public
     */
    public function getCatGroup()  {
        return $this->_cat_group;
    }

    /**
     * Returns true if the article can be commented.
     * @return boolean
     * @access public
     */
    public function getComments() {
        return $this->_comments;
    }

    /**
     * Returns the article priority.
     * @return int
     * @access public
     */
    public function getPriority() {
        return $this->_prior;
    }

    /**
     * Returns the article path.
     * @return string
     * @access public
     */
    public function getPath() {
        return $this->_path;
    }
    /**
     * Returns the level.
     * @return int
     * @access public
     */
    public function getLevel() {
        return count(cjoAssistance::toArray($this->getPath())) +1;
    }
    
    /**
     * Returns the template id.
     * @return int
     * @access public
     */
    public function getTemplateId() {
        return $this->hasSetTemplate() ? $this->_set_template_id : $this->_template_id;
    }

    /**
     * Returns the original template id.
     * @return int
     * @access public
     */
    public function getOrgTemplateId() {
        return $this->_template_id;
    }    
    
    /**
     * Returns true if the template has been overwritten.
     * @return bool
     * @access public
     */
    public function hasSetTemplate() {
        return $this->_set_template_id && $this->_set_template_id != $this->_template_id;
    }    
    
    /**
     * Returns true if article has a template.
     * @return boolean
     * @access public
     */
    public function hasTemplate() {
        return ($this->_template_id > 0);
    }
    
    /**
     * Returns the online from date.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public function getOnlineFromDate($format = null) {
        return OOContejo :: _getDate($this->_online_from, $format);
    }
    
    /**
     * Returns the online to date.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public function getOnlineToDate($format = null) {
        return OOContejo :: _getDate($this->_online_to, $format);
    }
    /**
     * Returns the date when the current article has been created.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public function getCreateDate($format = null) {
        return OOContejo :: _getDate($this->_createdate, $format);
    }

    /**
     * Returns the date when the current article has been updated.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public function getUpdateDate($format = null) {
        return OOContejo :: _getDate($this->_updatedate, $format);
    }

    /**
     * Returns the name of the user which created the current article.
     * @return string
     * @access public
     */
    public function getCreateUser() {
        return $this->_createuser;
    }

    /**
     * Returns the name of the user which updated the current article the last time.
     * @return string
     * @access public
     */
    public function getUpdateUser() {
        return $this->_updateuser;
    }

    /**
     * Formats a datestamp with the given format.
     * @param int $date unixtimestamp
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public static function _getDate($date, $format = null) {

        global $I18N;

        if ($format !== null) {
            if (strpos($format, '%') === false && isset($I18N)) {
                $format = $I18N->msg('dateformat');
            }
            return @strftime($format, $date);
        }
        return $date;
    }

    /**
     * Returns true if the current article is online.
     *
     * Attention: This funtion only references this article,
     * login status, online from and online to settings as
     * well as the status of all parent articles are ignored
     *
     * @return boolean
     * @access public
     */
    public function _isOnline() {
        return $this->_status == 1 ? true : false;
    }

    /**
     * Returns true if the current article is only editable for superadmins.
     * @return boolean
     * @access public
     */
    public function isAdminOnly() {
        global $CJO;
        return !$CJO['USER']->isAdmin() && $this->_admin_only == 1 ? true : false;
    }   
    
    
    /**
     * Returns a link to this article.
     * @param string $params parameter for the link (see OOContejo :: getUrl)
     * @param array|null $attributes array of atributes for the link
     * @param string|null $sorround_tag if set the link will be sorrounded with this tag
     * @param array|null $sorround_attributes  array of atributes for the sorrounding tag
     * @return string
     * @access public
     */
    public function toLink($params = '', $attributes = null, $sorround_tag = null, $sorround_attributes = null) {
        
        $link = sprintf('<a href="%s"%s title="%s">%s</a>',
                        $this->getUrl($params),
                        $this->convertAttributesToString($attributes, array('title')),
                        $attributes['title'] ? $attributes['title'] : $this->getName(),
                        $this->getName());

        if ($sorround_tag !== null && is_string($sorround_tag)) {

            $link = sprintf('<%s%s>%s</%s>',
                            $sorround_tag,
                            $this->convertAttributesToString($sorround_attributes, array('title')),
                            $link,
                            $sorround_tag);
        }
        return $link;
    }

    public static function hasValue($value, $prefixes = array()) {

        static $values = null;

        if (!$values) $values = OOContejo::getClassVars();

        foreach(array_merge(array('','_'), $prefixes) as $prefix) {
            if (in_array($prefix . $value, $values)) {
                return true;
            }
        }
        return false;
    }

    public function hasCtypeContent($ctype = -1) {
        return $this->countCtypeContent($this->_id, $this->_clang, $ctype) != 0 ? true : false;
    }
    
    public static function countCtypeContent($article_id, $clang, $ctype = -1) {

        $ctype = (int) $ctype;
        $addsql = ($ctype != -1) ? " AND ctype='".$ctype."'" : "";

        $sql = new cjoSql();
        $sql->setQuery("SELECT id FROM ".TBL_ARTICLES_SLICE." WHERE article_id ='".$article_id."' AND clang='".$clang."'".$addsql);

        return $sql->getRows();
    }

    /**
     * Returns an array containing article field names.
     * @return array
     * @access public
     */
    public static function getClassVars() {

         $vars = array();

        if (empty($vars)) {

            global $CJO;

            $file = $CJO['FOLDER_GENERATED_ARTICLES']."/".$CJO['START_ARTICLE_ID'].".0.article";

            if ($CJO['GG'] && file_exists($file)) {

                include_once($file);

                $genVars = OOContejo::convertGeneratedArray($CJO['ART'][$CJO['START_ARTICLE_ID']],0);
                unset($genVars['article_id']);
                unset($genVars['last_update_stamp']);
                foreach($genVars as $name => $value) {
                    $vars[] = $name;
                }
            } else {
                foreach(cjoSql::getFieldNames(TBL_ARTICLES) as $field) {
                    $vars[] = $field;
                }
                $vars = cjoExtension::registerExtensionPoint('CONTEJO_CLASS_VARS_GENERATED', array('subject'=>$vars));
            }
                 
            if (empty($vars)) $vars = self::getClassVars();
            
            $vars[] = 'set_template_id';

        }

        return array_unique($vars);
    }

    /**
     * Converts the attributes array to a string.
     * @param array $attributes
     * @param array $filter atribute names that have to be removed
     * @return string
     * @access public
     */
    public function convertAttributesToString($attributes, $filter = array()) {

        $attr = '';

        if (!is_array($filter)) $filter = cjoAssistance::toArray($filter);

        if ($attributes !== null && is_array($attributes)) {
            foreach ($attributes as $name => $value) {
                if (in_array($name, $filter)) continue;
                $attr .= ' '.$name.'="'.$value.'"';
            }
        }
        return $attr;
    }

    /**
     * Returns a array of all parent articles as OOContejo objects.
     * @return array
     * @access public
     */
    public function getParentTree() {

        $return = array ();

        if (!$this->_path) return $return;

        foreach (cjoAssistance::toArray($this->_path.$this->_id) as $var) {
            if (empty($var)) continue;
            $article = OOArticle::getArticleById($var, $this->_clang);
            if (OOArticle::isValid($article)) $return[$var] = $article;
        }
        return $return;
    }

    /**
     * Returns true if the current article has children.
     * @return boolean
     * @access public
     */
    public function isStartPage() {
        return $this->_startpage;
    }

    /**
     * Returns true if this article is the startpage for the entire site.
     * @return boolean
     * @access public
     */
    public function isSiteStartArticle() {
        global $CJO;
        return $this->_id == $CJO['START_ARTICLE_ID'];
    }

    /**
     * Returns a string representation of this object
     * for debugging purposes.
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->_id.", ".$this->_name.", ". ($this->_isOnline() ? "online" : "offline").'"'."<br/>\r\n";
    }
}