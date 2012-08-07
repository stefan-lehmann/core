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

if (!$CJO['CONTEJO']) return false;

/**
 * cjoSelectArticle class
 *
 * The cjoSelectArticle class creates a selectbox object
 * that represents the article structure. At the same time
 * it provides access to the articles, depending on the
 * user permissions.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoSelectArticle extends cjoSelect {

    /**
     * caching file of the sel_article object
     * @var string
     */
    private $cache_file;

    /**
     * Constructor.
     * @return void
     */
    function __construct($article_id = false, $clang = false) {

        global $CJO, $I18N;

        if (!$CJO['CONTEJO'] || !$CJO['USER']) return false;

        parent :: __construct();

        if ($article_id === false) $article_id = 0;
        if ($clang === false) $clang = $CJO['CUR_CLANG'];

        $this->cache_file = $CJO['FOLDER_GENERATED_ARTICLES']."/".
                            $CJO['USER']->getValue('user_id').".".$clang.".aspath";

        $article = OOArticle::getArticleById($article_id, $clang);
        $selected_path = (OOArticle::isValid($article)) ? $article->_path : $article_id;

        if (version_compare(PHP_VERSION, '5.3.0') >= 0 &&
            $this->readChachedSelArticle($clang)) {
            $CJO['SEL_ARTICLE']->resetSelected();
            $CJO['SEL_ARTICLE']->resetSelectedPath();
            $CJO['SEL_ARTICLE']->resetDisabled();
            $CJO['SEL_ARTICLE']->setSelected($article_id);
            $CJO['SEL_ARTICLE']->setSelectedPath($selected_path);
            return  true;
        }

        $this->setName("custom_select");
        $this->setStyle('class="custom_select"');
        $this->setStyle("width: 928px");
        $this->showRoot($I18N->msg('label_article_root'), 'root');
        $this->setSize(1);
        $this->setSelected($article_id);
        $this->setSelectedPath($selected_path);

        $query = "SELECT
            name, id AS value, id, re_id,
            IF(startpage=1,'folder','file') as title
          FROM
            ".TBL_ARTICLES."
          WHERE
            clang='".$clang."'
          ORDER BY prior, id";

        if ($this->addSqlOptions($query, false)) {
            $CJO['SEL_ARTICLE'] = $this;
            $this->writeCachedSelArticle();
        }
    }

    /**
     * Adds the results of a mysql request as a set of options to the selectbox.
     * @param string $query sql query
     * @param booelan $sqldebug if true the request is executed in debug mode
     * @return booelan|string on success returns true, if a sql error occurs the error message is returned
     */
    public function addSqlOptions($query, $sqldebug = false) {

        global $CJO;

        $sql = new cjoSql();
        $result = $sql->getArray($query, PDO::FETCH_NUM);

        if ($sql->getError() != '') return $sql->getError();

        if ($sqldebug) {
            cjo_debug($result,'ADD_SQLOPTIONS','lightgreen');
        }

        if (count($result[0]) < 2 && count($result[0]) > 5) {
            return false;
        }

        foreach ($result as $value) {
            
            $article = OOArticle::getArticleById($value[1]);                
            if (!OOArticle::isValid($article))  continue;
                
            if ($CJO['USER']->hasCatPermWrite($value[1],true)) {
                if ($article->isLocked()) {
                    $value[4] .= '_by_user';
                }
                elseif ($article->isAdminOnly()) {
                    $value[4] .= '_admin';
                }
            }     
            elseif ($CJO['USER']->hasCatPermRead($value[1])) {
                
                if ($article->isLocked()) {
                    $value[4] .= '_by_user';
                }
                elseif ($article->isAdminOnly()) {
                    $value[4] .= '_admin';
                }
                else {
                    $value[4] .= '_locked';
                }
            }
            else {
                if ($CJO['USER']->hasCatPermWrite($value[1])) {
                    $value[4] = 'file_admin';
                }
                else {
                    $value[4] = 'file_locked';
                }
            }

            if (count($result[0]) == 4 || count($result[0]) == 5) {
                $this->addOption($value[0], $value[1], $value[2], $value[3], $value[4]);

                if ($value[4] != '') {
                    $this->addTitles($value[4], $value[2]);
                }
            }
            elseif (count($result[0]) == 2) {
                $this->addOption($value[0], $value[1]);
            }
            elseif (count($result[0]) == 1) {
                $this->addOption($value[0], $value[0]);
            }
        }
        return true;
    }

    /**
     * Returns the cached sel_article object.
     * @return object
     */
    private function readChachedSelArticle() {

        global $CJO;

        if (!file_exists($this->cache_file) || filemtime($this->cache_file) < (time()-600)) return false;
        include($this->cache_file);
        if (!is_object($CJO['SEL_ARTICLE']) || empty($CJO['SEL_ARTICLE'])) return false;
       return true;
    }

    /**
     * Writes the current sel_article object into the cache file.
     * @return void
     */
    private function writeCachedSelArticle() {

        global $CJO;

        $new_content = '<?php'."\r\n".
                       'global $CJO;'."\r\n".
                       '$CJO[\'SEL_ARTICLE\'] = unserialize(stripslashes(\''.addslashes(serialize($CJO['SEL_ARTICLE'])).'\'));'."\r\n".
                       '?>';

        if (cjoAssistance::isWritable($CJO['FOLDER_GENERATED_ARTICLES'])) {
            cjoGenerate::putFileContents($this->cache_file, $new_content);
        }
    }
    
    /**
     * Writes the current sel_article object into the cache file.
     * @return void
     */
    public static function deleteCachedSelArticle() {
        global $CJO;
        foreach(cjoAssistance::toArray(glob($CJO['FOLDER_GENERATED_ARTICLES']."/*.aspath")) as $file) {
            unlink($file);
        }
    }

    /**
     * Writes the generated $CJO['SEL_ARTICLE'] object.
     * @param boolean $render
     * @return void|string
     */
    public function get($render=false) {

        global $CJO;

        $subpage = cjo_request('subpage', 'string');
        $clang   = cjo_request('clang', 'cjo-clang-id');
        $ctype   = cjo_request('ctype', 'cjo-ctype-id');

        $s  = $CJO['SEL_ARTICLE']->_get();

        if ($this->getSelectId() == 'custom_select') {

            $s .= '<script type="text/javascript">'."\r\n".
                  '	/* <![CDATA[ */'."\r\n".
                  '	$(function() {'."\r\n".
                  '		$(\'#custom_select\').selectpath({'."\r\n".
                  '			action : {root   			: "location.href=\'index.php?page=edit&clang='.$clang.'&ctype='.$ctype.'\'",'."\r\n".
                  '					  folder 			: "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id",'."\r\n".
                  '					  file   		    : "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id",'."\r\n".
                  '                   \'file admin\'    : "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id",'."\r\n".
                  '                   \'file by_user\'  : "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id",'."\r\n".
                  '					  \'folder locked\' : "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id",'."\r\n".
                  '                   \'folder admin\'  : "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id",'."\r\n".
                  '                   \'folder by_user\': "location.href=\'index.php?page=edit&subpage='.$subpage.'&clang='.$clang.'&ctype='.$ctype.'&article_id=\'+id"'."\r\n".                
                  '					  },'."\r\n".
                  '			types  : {root	 		 : \'root\','."\r\n".
                  '					  folder 		 : \'folder\','."\r\n".
                  '					  file	 		 : \'file\','."\r\n".
                  '					  folder_locked  : \'folder locked\','."\r\n".
                  '					  file_locked 	 : \'locked\','."\r\n".
                  '                   folder_admin   : \'folder admin\','."\r\n".
                  '                   file_admin     : \'file admin\','."\r\n".          
                  '                   folder_by_user : \'folder by_user\','."\r\n".
                  '                   file_by_user   : \'file by_user\''."\r\n".                     
                  '					  }'."\r\n".
                  '		});'."\r\n".
                  '	});'."\r\n".
                  '/* ]]> */'."\r\n".
                  '</script>'."\r\n";
        }

        if ($render == true) {
            echo '<div id="cjo_cat_path" class="cjo_article_path">'.$s.'</div>';
        } else {
            return $s;
        }
    }

    public function _get() {
        return parent::get();
    }
}