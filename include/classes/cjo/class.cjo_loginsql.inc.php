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
 * cjoLoginSQL class
 *
 * The cjoLoginSQL class provides methods for validating permissions of the user object.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoLoginSQL extends cjoSql {

    /**
     * permission to access media categories
     * @var array
     * @access public
     */
    public $media_perm;

    /**
     * Validates all types of permissions by matching the property with the content
     * of field "rights".
     * @param string $perm must of the common form, eg. media[], media[this], clang[0]
     * @return boolean
     * @access public
     */
    public function hasPerm($perm='') {

        if ($this->isAdmin()) return ($perm == 'editContentOnly[]') ? false : true;

        preg_match_all('/^\S*(?=\[)|(?<=\[)\S*(?=\]$)/', $perm, $matches);

        $perm_value = $matches[0][0];
        $perm_key = $matches[0][1];

        switch ($perm_value) {
            case 'media'  : return $this->hasMediaPerm($perm_key);
            case 'clang'  : return $this->hasClangPerm($perm_key);
            case 'csw'	  : return $this->hasCatPermWrite($perm_key,true);
            case 'csr' 	  : return $this->hasCatPermRead($perm_key);
            case 'module' : return $this->hasModulPerm($perm_key);
        }

        return ($this->isValueOf("rights",$perm) ||
                $this->isValueOf("rights",$perm_value.'[]'));
    }

    /**
     * Validates if write or read rights are available for the given article.
     * @param int $article_id
     * @param string $type  must "csw" for write access and "csr" for read access
     * @param bool $admin_only
     * @return boolean
     * @access public
     */
    public function hasCatPerm($article_id, $type, $admin_only=false) {

        global $CJO;

        $article = OOArticle::getArticleById($article_id, $CJO['CUR_CLANG']);

        if ($this->isAdmin()) {
            if (OOArticle::isvalid($article) && $article->isLocked()){ 
                return $type == 'csr' ? true : false;
            }
            return true;
        }

        if (($admin_only && $article->isAdminOnly()) || $article->isLocked()) return $type == 'csr' ? true : false;
        
        if ($this->isValueOf("rights", $type."[0]")) return true;
        
        if ($type == 'csr') {
            return ($article_id != '' && $this->isValueOf("rights",$type."[".$article_id."]"));
        }
        
        $path = cjoAssistance::toArray($article->_path.$article_id);
        krsort($path);

        foreach ($path as $cat_id) {
            if ($this->isValueOf("rights",$type."[".$cat_id."]")) return true;
        }
        return false;
    }

    /**
     * Calls hasCatPerm method for testing write access.
     * @param int $article_id
     * @param bool $admin_only
     * @return boolean
     * @access public
     */
    public function hasCatPermWrite($article_id, $admin_only=false) {
        return $this->hasCatPerm($article_id, 'csw', $admin_only);
    }

    /**
     * Calls hasCatPermRead method for testing read access.
     * @param int $article_id
     * @return boolean
     * @access public
     */
    public function hasCatPermRead($article_id) {
        if (!$this->hasCatPerm($article_id, 'csr')) {
            return $this->hasCatPermWrite($article_id);
        }
        return true;
    }

    /**
     * Validates access to the modultyp.
     * @param int $modul_id
     * @return boolean
     * @access public
     */
    public function hasModulPerm($modul_id) {
        return ($this->isAdmin() || $this->isValueOf("rights", "module[0]") || $this->isValueOf("rights", "module[".$modul_id."]"));
    }
    
    /**
     * Validates access to the template.
     * @param int $template_id
     * @return boolean
     * @access public
     */
    public function hasTemplatePerm($template_id) {
        return ($this->isAdmin() || $this->isValueOf("rights", "template[0]") || $this->isValueOf("rights", "template[".$template_id."]"));
    }
    
    /**
     * Validates access to the ctype.
     * @param int $ctype_id
     * @return boolean
     * @access public
     */
    public function hasCtypePerm($ctype_id) {
        return ($this->isAdmin() || $this->isValueOf("rights", "ctype[all]") || $this->isValueOf("rights", "ctype[".$ctype_id."]"));
    }
        
    /**
     * Validates access to the given language version of the website.
     * @param string|int|boolean $clang
     * @return boolean
     * @access public
     */
    public function hasClangPerm($clang = false) {

        global $CJO;

        if ($clang === false || $clang == 'this')
        $clang = $CJO ['CUR_CLANG'];

        if ($this->isAdmin() ||
        $this->isValueOf("rights","clang[".$clang."]"))
        return true;

        return false;
    }

    /**
     * Returns permission to the given media category.
     * @param string|int|bool $category
     * @return boolean
     * @access public
     */
    public function hasMediaPerm($category = false) {

        global $media_category;

        if ($this->isAdmin()) return true;

        if ($category === false || $category == 'this')
            $category = $media_category;

        if ($this->media_perm[$category] == '')
            $this->validateMediaPerm($category);

        return $this->media_perm[$category];
    }

    /**
     * Validates access to the given media category.
     * @param int $category
     * @return boolean
     * @access public
     */
    public function validateMediaPerm($category) {

        if ($this->isAdmin() ||
            $this->isValueOf("rights","media[0]") ||
            $this->isValueOf("rights","media[".$category."]")) {

            $this->media_perm[$category] = true;
            return;
        }

        $mediacat = OOMediaCategory::getCategoryById($category);

        if (!OOMediaCategory::isValid($mediacat)) {
            $this->media_perm[$category] = false;
            return;
        }
    
        $path = explode('|',OOMediaCategory::getPath($category));

        foreach ($path as $parent) {
            if ($parent != '' &&
                $this->isValueOf("rights","media[".$parent."]")) {
                $this->media_perm[$category] = true;
                return;
            }
        }
        $this->media_perm[$category] = false;
        return;
    }

    /**
     * Returns the permission of setting articles
     * login settings (if the feature is enabled).
     * @return boolean
     * @access public
     */
    public function hasLoginPerm() {
        global $CJO;
        return ($CJO['LOGIN_ENABLED'] && ($this->isAdmin() || $this->hasPerm('setloginArticle[]')));
    }

    /**
     * Returns the permission of setting the online
     * time for articles (if the feature is enabled).
     * @return boolean
     * @access public
     */
    public function hasOnlineFromToPerm() {
        global $CJO;
        return ($CJO['ONLINE_FROM_TO_ENABLED'] && ($this->isAdmin() || !$this->hasPerm("editContentOnly[]")));
    }

    /**
     * Returns true if the user is a super admin.
     * @return bool
     * @access public
     */
    public function isAdmin() {
        return $this->isValueOf("rights","admin[]");
    }

    /**
     * Validates permission to access an addon.
     * @param string $name name of the addon
     * @param bool $strict if false super admin has always permission
     * @return boolean
     * @access public
     */
    public function hasAddonPerm($name, $strict = false) {

        global $CJO;

        if ((!$strict && empty($CJO['ADDON']['perm'][$name])) ||
        $this->isAdmin()) return true;

        foreach(cjoAssistance::toArray($CJO['ADDON']['perm'][$name]) as $perm) {
            if (empty($perm)) continue;
            if ($this->hasPerm($perm) == true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Basic method for validating a permission in current sql object.
     * @param string $field
     * @param string $perm
     * @return boolean
     * @access public
     */
    public function isValueOf($field, $perm) {

        if ($perm == "") {
            return true;
        }
        else {
            return ($field == "rights")
                ? strpos($this->getValue($field), "#".$perm) !== false
                : strpos($this->getValue($field), $perm) !== false;
        }
    }
}
