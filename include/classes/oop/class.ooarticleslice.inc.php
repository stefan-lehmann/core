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
 * OOArticleSlice class
 *
 * The OOArticleSlice class is an object wrapper over the database table cjo_articel_slice.
 * Together with OOArticle it provides an object oriented
 * Framework for accessing vital parts of a CONTEJO driven website.
 * This framework can be used in Modules, Templates and PHP-Slices!
 * @package 	contejo
 * @subpackage 	core
 */
class OOArticleSlice {

    /**
     * id of the current slice
     * @var int
     * @access public
     */
    public $_id;

    /**
     * id of the previous slice
     * @var int
     * @access public
     */
    public $_re_article_slice_id;

    /**
     * value array (value1-value20)
     * @var array
     * @access public
     */
    public $_value = array();

    /**
     * files array (file1-file10)
     * @var array
     * @access public
     */
    public $_file = array();

    /**
     * link array (link1-link10)
     * @var array
     * @access public
     */
    public $_link = array();

    /**
     * filelist array (filelist1-filelist10)
     * @var array
     * @access public
     */
    public $_filelist = array();

    /**
     * php content
     * @var string
     * @access public
     */
    public $_php;

    /**
     * html content
     * @var string
     * @access public
     */
    public $_html;

    /**
     * article id of the current slice
     * @var int
     * @access public
     */
    public $_article_id;

    /**
     * modultyp id of the current slice
     * @var int
     * @access public
     */
    public $_modultyp_id;

    /**
     * clang id of the current slice
     * @var int
     * @access public
     */
    public $_clang;

    /**
     * Constructor
     *
     * @param int $id
     * @param int $re_article_slice_id
     * @param mixed $value1
     * @param mixed $value2
     * @param mixed $value3
     * @param mixed $value4
     * @param mixed $value5
     * @param mixed $value6
     * @param mixed $value7
     * @param mixed $value8
     * @param mixed $value9
     * @param mixed $value10
     * @param mixed $value11
     * @param mixed $value12
     * @param mixed $value13
     * @param mixed $value14
     * @param mixed $value15
     * @param mixed $value16
     * @param mixed $value17
     * @param mixed $value18
     * @param mixed $value19
     * @param mixed $value20
     * @param mixed $file1
     * @param mixed $file2
     * @param mixed $file3
     * @param mixed $file4
     * @param mixed $file5
     * @param mixed $file6
     * @param mixed $file7
     * @param mixed $file8
     * @param mixed $file9
     * @param mixed $file10
     * @param mixed $link1
     * @param mixed $link2
     * @param mixed $link3
     * @param mixed $link4
     * @param mixed $link5
     * @param mixed $link6
     * @param mixed $link7
     * @param mixed $link8
     * @param mixed $link9
     * @param mixed $link10
     * @param mixed $filelist1
     * @param mixed $filelist2
     * @param mixed $filelist3
     * @param mixed $filelist4
     * @param mixed $filelist5
     * @param mixed $filelist6
     * @param mixed $filelist7
     * @param mixed $filelist8
     * @param mixed $filelist9
     * @param mixed $filelist10
     * @param string $php
     * @param string $html
     * @param int $article_id
     * @param int $modultyp_id
     * @param int $createdate
     * @param int $updatedate
     * @param int $clang
     * @param int $ctype
     * @return OOArticleSlice
     * @access public
     */
    public function __construct($id, $re_article_slice_id,
    $value1, $value2, $value3, $value4, $value5, $value6, $value7, $value8, $value9, $value10,
    $value11, $value12, $value13, $value14, $value15, $value16, $value17, $value18, $value19, $value20,
    $file1, $file2, $file3, $file4, $file5, $file6, $file7, $file8, $file9, $file10,
    $link1, $link2, $link3, $link4, $link5, $link6, $link7, $link8, $link9, $link10,
    $filelist1, $filelist2, $filelist3, $filelist4, $filelist5, $filelist6, $filelist7,
    $filelist8, $filelist9, $filelist10, $php, $html, $article_id, $modultyp_id,
    $createdate, $updatedate, $clang, $ctype)
    {
        $this->_id = $id;
        $this->_re_article_slice_id = $re_article_slice_id;
        $this->_value[1] = $value1;
        $this->_value[2] = $value2;
        $this->_value[3] = $value3;
        $this->_value[4] = $value4;
        $this->_value[5] = $value5;
        $this->_value[6] = $value6;
        $this->_value[7] = $value7;
        $this->_value[8] = $value8;
        $this->_value[9] = $value9;
        $this->_value[10] = $value10;
        $this->_value[11] = $value11;
        $this->_value[12] = $value12;
        $this->_value[13] = $value13;
        $this->_value[14] = $value14;
        $this->_value[15] = $value15;
        $this->_value[16] = $value16;
        $this->_value[17] = $value17;
        $this->_value[18] = $value18;
        $this->_value[19] = $value19;
        $this->_value[20] = $value20;
        $this->_file[1] = $file1;
        $this->_file[2] = $file2;
        $this->_file[3] = $file3;
        $this->_file[4] = $file4;
        $this->_file[5] = $file5;
        $this->_file[6] = $file6;
        $this->_file[7] = $file7;
        $this->_file[8] = $file8;
        $this->_file[9] = $file9;
        $this->_file[10] = $file10;
        $this->_link[1] = $link1;
        $this->_link[2] = $link2;
        $this->_link[3] = $link3;
        $this->_link[4] = $link4;
        $this->_link[5] = $link5;
        $this->_link[6] = $link6;
        $this->_link[7] = $link7;
        $this->_link[8] = $link8;
        $this->_link[9] = $link9;
        $this->_link[10] = $link10;
        $this->_filelist[1] = $filelist1;
        $this->_filelist[2] = $filelist2;
        $this->_filelist[3] = $filelist3;
        $this->_filelist[4] = $filelist4;
        $this->_filelist[5] = $filelist5;
        $this->_filelist[6] = $filelist6;
        $this->_filelist[7] = $filelist7;
        $this->_filelist[8] = $filelist8;
        $this->_filelist[9] = $filelist9;
        $this->_filelist[10] = $filelist10;
        $this->_php = $php;
        $this->_html = $html;
        $this->_article_id = $article_id;
        $this->_modultyp_id = $modultyp_id;
        $this->_createdate = $createdate;
        $this->_updatedate = $updatedate;
        $this->_clang = $clang;
        $this->_ctype = $ctype;
    }

    /**
     * Returns an OOArticleSlice object by its id.
     * @param int $id
     * @param int|boolean $clang
     * @return object|null
     * @access public
     */
    public static function getArticleSliceById($id, $clang = false) {

        global $CJO;

        $addsql = ($clang === false)? "" : " AND clang = '".$clang."'";

        $sql = new cjoSql();
        $query = "SELECT * FROM ".TBL_ARTICLES_SLICE." WHERE id = '".$id."'".$addsql;
        $sql->setQuery($query);

        if ($sql->getRows() == 1) {
            return new OOArticleSlice($sql->getValue("id"),
                                      $sql->getValue("re_article_slice_id"),
                                      $sql->getValue("value1"),
                                      $sql->getValue("value2"),
                                      $sql->getValue("value3"),
                                      $sql->getValue("value4"),
                                      $sql->getValue("value5"),
                                      $sql->getValue("value6"),
                                      $sql->getValue("value7"),
                                      $sql->getValue("value8"),
                                      $sql->getValue("value9"),
                                      $sql->getValue("value10"),
                                      $sql->getValue("value11"),
                                      $sql->getValue("value12"),
                                      $sql->getValue("value13"),
                                      $sql->getValue("value14"),
                                      $sql->getValue("value15"),
                                      $sql->getValue("value16"),
                                      $sql->getValue("value17"),
                                      $sql->getValue("value18"),
                                      $sql->getValue("value19"),
                                      $sql->getValue("value20"),
                                      $sql->getValue("file1"),
                                      $sql->getValue("file2"),
                                      $sql->getValue("file3"),
                                      $sql->getValue("file4"),
                                      $sql->getValue("file5"),
                                      $sql->getValue("file6"),
                                      $sql->getValue("file7"),
                                      $sql->getValue("file8"),
                                      $sql->getValue("file9"),
                                      $sql->getValue("file10"),
                                      $sql->getValue("link1"),
                                      $sql->getValue("link2"),
                                      $sql->getValue("link3"),
                                      $sql->getValue("link4"),
                                      $sql->getValue("link5"),
                                      $sql->getValue("link6"),
                                      $sql->getValue("link7"),
                                      $sql->getValue("link8"),
                                      $sql->getValue("link9"),
                                      $sql->getValue("link10"),
                                      $sql->getValue("filelist1"),
                                      $sql->getValue("filelist2"),
                                      $sql->getValue("filelist3"),
                                      $sql->getValue("filelist4"),
                                      $sql->getValue("filelist5"),
                                      $sql->getValue("filelist6"),
                                      $sql->getValue("filelist7"),
                                      $sql->getValue("filelist8"),
                                      $sql->getValue("filelist9"),
                                      $sql->getValue("filelist10"),
                                      $sql->getValue("php"),
                                      $sql->getValue("html"),
                                      $sql->getValue("article_id"),
                                      $sql->getValue("modultyp_id"),
                                      $sql->getValue("createdate"),
                                      $sql->getValue("updatedate"),
                                      $sql->getValue("clang"),
                                      $sql->getValue("ctype"));
        }
        return null;
    }

    /**
     * Returns the first slice for an article as an OOArticleSlice object.
     * This can then be used to iterate over all the
     * slices in the order as they appear using the
     * getNextSlice() method.
     * @param int $article_id
     * @param int|boolean $clang
     * @return object|null
     * @access public
     */
    public static function getFirstSliceForArticle($article_id, $clang = false) {

        global $CJO;

        if ($clang === false) $clang = $CJO['CUR_CLANG'];

        $sql = new cjoSql();
        $query = "SELECT * FROM ".TBL_ARTICLES_SLICE." WHERE article_id = '".$article_id."' AND re_article_slice_id = '0' AND clang = '".$clang."'";
        $sql->setQuery($query);

        if ($sql->getRows() == 1) {
            return new OOArticleSlice($sql->getValue("id"),
            $sql->getValue("re_article_slice_id"),
            $sql->getValue("value1"),
            $sql->getValue("value2"),
            $sql->getValue("value3"),
            $sql->getValue("value4"),
            $sql->getValue("value5"),
            $sql->getValue("value6"),
            $sql->getValue("value7"),
            $sql->getValue("value8"),
            $sql->getValue("value9"),
            $sql->getValue("value10"),
            $sql->getValue("value11"),
            $sql->getValue("value12"),
            $sql->getValue("value13"),
            $sql->getValue("value14"),
            $sql->getValue("value15"),
            $sql->getValue("value16"),
            $sql->getValue("value17"),
            $sql->getValue("value18"),
            $sql->getValue("value19"),
            $sql->getValue("value20"),
            $sql->getValue("file1"),
            $sql->getValue("file2"),
            $sql->getValue("file3"),
            $sql->getValue("file4"),
            $sql->getValue("file5"),
            $sql->getValue("file6"),
            $sql->getValue("file7"),
            $sql->getValue("file8"),
            $sql->getValue("file9"),
            $sql->getValue("file10"),
            $sql->getValue("link1"),
            $sql->getValue("link2"),
            $sql->getValue("link3"),
            $sql->getValue("link4"),
            $sql->getValue("link5"),
            $sql->getValue("link6"),
            $sql->getValue("link7"),
            $sql->getValue("link8"),
            $sql->getValue("link9"),
            $sql->getValue("link10"),
            $sql->getValue("filelist1"),
            $sql->getValue("filelist2"),
            $sql->getValue("filelist3"),
            $sql->getValue("filelist4"),
            $sql->getValue("filelist5"),
            $sql->getValue("filelist6"),
            $sql->getValue("filelist7"),
            $sql->getValue("filelist8"),
            $sql->getValue("filelist9"),
            $sql->getValue("filelist10"),
            $sql->getValue("php"),
            $sql->getValue("html"),
            $sql->getValue("article_id"),
            $sql->getValue("modultyp_id"),
            $sql->getValue("createdate"),
            $sql->getValue("updatedate"),
            $sql->getValue("clang"),
            $sql->getValue("ctype"));
        }
        return null;
    }

    /**
     * Returns all slices for an article that have a
     * certain module type as an array of OOArticleSlice objects.
     * @param int $article_id
     * @param int $moduletype_id
     * @param int|boolean $clang
     * @return array()
     * @access public
     */
    public static function getSlicesForArticleOfType($article_id, $moduletype_id, $clang = false) {

        global $CJO;

        if ($clang === false) $clang = $CJO['CUR_CLANG'];

        $sql = new cjoSql();
        $query = "SELECT * FROM ".TBL_ARTICLES_SLICE." WHERE article_id = ".$article_id." AND clang = ".$clang." AND modultyp_id = ".$moduletype_id;
        $sql->setQuery($query);

        $slices = array ();
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $slices[] = new OOArticleSlice($sql->getValue("id"),
            $sql->getValue("re_article_slice_id"),
            $sql->getValue("value1"),
            $sql->getValue("value2"),
            $sql->getValue("value3"),
            $sql->getValue("value4"),
            $sql->getValue("value5"),
            $sql->getValue("value6"),
            $sql->getValue("value7"),
            $sql->getValue("value8"),
            $sql->getValue("value9"),
            $sql->getValue("value10"),
            $sql->getValue("value11"),
            $sql->getValue("value12"),
            $sql->getValue("value13"),
            $sql->getValue("value14"),
            $sql->getValue("value15"),
            $sql->getValue("value16"),
            $sql->getValue("value17"),
            $sql->getValue("value18"),
            $sql->getValue("value19"),
            $sql->getValue("value20"),
            $sql->getValue("file1"),
            $sql->getValue("file2"),
            $sql->getValue("file3"),
            $sql->getValue("file4"),
            $sql->getValue("file5"),
            $sql->getValue("file6"),
            $sql->getValue("file7"),
            $sql->getValue("file8"),
            $sql->getValue("file9"),
            $sql->getValue("file10"),
            $sql->getValue("link1"),
            $sql->getValue("link2"),
            $sql->getValue("link3"),
            $sql->getValue("link4"),
            $sql->getValue("link5"),
            $sql->getValue("link6"),
            $sql->getValue("link7"),
            $sql->getValue("link8"),
            $sql->getValue("link9"),
            $sql->getValue("link10"),
            $sql->getValue("filelist1"),
            $sql->getValue("filelist2"),
            $sql->getValue("filelist3"),
            $sql->getValue("filelist4"),
            $sql->getValue("filelist5"),
            $sql->getValue("filelist6"),
            $sql->getValue("filelist7"),
            $sql->getValue("filelist8"),
            $sql->getValue("filelist9"),
            $sql->getValue("filelist10"),
            $sql->getValue("php"),
            $sql->getValue("html"),
            $sql->getValue("article_id"),
            $sql->getValue("modultyp_id"),
            $sql->getValue("createdate"),
            $sql->getValue("updatedate"),
            $sql->getValue("clang"),
            $sql->getValue("ctype"));
            $sql->next();
        }
        return $slices;
    }

    /**
     * Returns the next slice of the current slice as an OOArticleSlice object.
     * @return object|null
     * @access public
     */
    public function getNextSlice() {
        global $CJO;

        $sql = new cjoSql();
        $query = "SELECT * FROM ".TBL_ARTICLES_SLICE." WHERE re_article_slice_id = '".$this->_id."' AND clang = '".$this->_clang."'";

        $sql->setQuery($query);
        if ($sql->getRows() == 1) {
            return new OOArticleSlice($sql->getValue("id"),
            $sql->getValue("re_article_slice_id"),
            $sql->getValue("value1"),
            $sql->getValue("value2"),
            $sql->getValue("value3"),
            $sql->getValue("value4"),
            $sql->getValue("value5"),
            $sql->getValue("value6"),
            $sql->getValue("value7"),
            $sql->getValue("value8"),
            $sql->getValue("value9"),
            $sql->getValue("value10"),
            $sql->getValue("value11"),
            $sql->getValue("value12"),
            $sql->getValue("value13"),
            $sql->getValue("value14"),
            $sql->getValue("value15"),
            $sql->getValue("value16"),
            $sql->getValue("value17"),
            $sql->getValue("value18"),
            $sql->getValue("value19"),
            $sql->getValue("value20"),
            $sql->getValue("file1"),
            $sql->getValue("file2"),
            $sql->getValue("file3"),
            $sql->getValue("file4"),
            $sql->getValue("file5"),
            $sql->getValue("file6"),
            $sql->getValue("file7"),
            $sql->getValue("file8"),
            $sql->getValue("file9"),
            $sql->getValue("file10"),
            $sql->getValue("link1"),
            $sql->getValue("link2"),
            $sql->getValue("link3"),
            $sql->getValue("link4"),
            $sql->getValue("link5"),
            $sql->getValue("link6"),
            $sql->getValue("link7"),
            $sql->getValue("link8"),
            $sql->getValue("link9"),
            $sql->getValue("link10"),
            $sql->getValue("filelist1"),
            $sql->getValue("filelist2"),
            $sql->getValue("filelist3"),
            $sql->getValue("filelist4"),
            $sql->getValue("filelist5"),
            $sql->getValue("filelist6"),
            $sql->getValue("filelist7"),
            $sql->getValue("filelist8"),
            $sql->getValue("filelist9"),
            $sql->getValue("filelist10"),
            $sql->getValue("php"),
            $sql->getValue("html"),
            $sql->getValue("article_id"),
            $sql->getValue("modultyp_id"),
            $sql->getValue("createdate"),
            $sql->getValue("updatedate"),
            $sql->getValue("clang"),
            $sql->getValue("ctype"));
        }
        return null;
    }

    /**
     * Returns the previous slice of the current slice as an OOArticleSlice object.
     * @return object|null
     * @access public
     */
    public function getPreviousSlice() {
        return OOArticleSlice :: getArticleSliceById($this->_re_article_slice_id);
    }

    /**
     * Returns an OOArticle object of the given slice id.
     * @param int $id
     * @param int|boolean $clang
     * @return object|null
     * @access public
     */
    public static function getArticleBySliceId($id, $clang = false) {
        $slice = self::getArticleSliceById($id);
        return (self::isValid($slice)) ? $slice->getArticle() : null;
    }


    /**
     * Returns the article of the current slice as an OOArticle object.
     * @return object|null
     * @access public
     */
    public function getArticle() {
        return OOArticle :: getArticleById($this->getArticleId());
    }

    /**
     * Returns the article id of the current slice.
     * @return int
     * @access public
     */
    public function getArticleId() {
        return $this->_article_id;
    }
    
    /**
     * Returns the clang id of the current slice.
     * @return int
     * @access public
     */
    public function getClang() {
        return $this->_clang;
    }
    
    /**
     * Returns the ctype id of the current slice.
     * @return int
     * @access public
     */
    public function getCtype() {
        return $this->_ctype;
    }
    
    /**
     * Returns the modultyp id of the current slice.
     * @return int
     * @access public
     */
    public function getModulTyp() {
        return $this->_modultyp_id;
    }

    /**
     * Returns the id of the current slice.
     * @return unknown
     * @access public
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Returns the value from the value array of the current slice.
     * @param int|string $index
     * @return mixed
     * @access public
     */
    public function getValue($index) {
        
        if (!isset($this->_value[$index]) && preg_match('/(\D+)(\d+)/',$index, $matches)) {
            $index = $matches[2];
        }
        return isset($this->_value[$index]) ? $this->_value[$index] : null;
    }

    /**
     * Returns the url of the current slices article.
     * @return string
     * @access public
     */
    public function getUrl() {
        return cjoRewrite::getUrl($this->getArticleId());
    }

    /**
     * Returns a article id stored in the link array of the current slice.
     * @param int $index
     * @return string
     * @access public
     */
    public function getLink($index) {
        return $this->_link[$index];
    }

    /**
     * Returns an array of files stored in the file list array of the current slice.
     * @param int $index
     * @param boolean $strict
     * @return string
     * @access public
     */
    public function getFilelist($index, $strict = false) {

        global $CJO;
        $files_temp = explode(",",$this->_filelist[$index]);
        if ($strict) {
            foreach($files_temp as $file){
                if (file_exists($CJO['MEDIAFOLDER'].'/'.$file))
                   $files[] = $file;
            }
        }
        else {
            $files = $files_temp;
        }
        return $files;
    }

    /**
     * Returns a random file from the file list array of the current slice.
     * @param int $index
     * @return string
     * @access public
     */
    public function getRandomFileFromList($index) {

        $files = $this->getFilelist($index, true);

        if(count($files) > 1){
            shuffle($files);
            $randIndex = mt_rand( 0, count( $files) -1);
            return $files[$randIndex];
        }
        else {
            return $files[0];
        }
    }

    /**
     * Returns a link generated from a article id stored in the link array of the current slice.
     * @param int $index
     * @return string
     * @access public
     */
    public function getLinkUrl($index) {
        return cjoRewrite::getUrl($this->getLink($index));
    }

    /**
     * Returns a file stored in the file array of the current slice.
     * @param int $index
     * @return string
     * @access public
     */
    public function getFile($index) {
        return $this->_file[$index];
    }


    /**
     * Returns a file with added path stored in the files array of the current slice.
     * @param int $index
     * @return string
     * @access public
     */
    public function getFileUrl($index) {
        global $CJO;
        return $CJO['MEDIAFOLDER']."/".$this->getFile($index);
    }

    /**
     * Returns the content of the html field.
     * @return string
     * @access public
     */
    public function getHtml() {
        return $this->_html;
    }

    /**
     * Returns the content of the php field.
     * @return string
     * @access public
     */
    function getPhp() {
        return $this->_php;
    }

    /**
     * Unserializes a given value.
     * @param string $value
     * @return array|null
     * @access public
     */
    public static function getExtValue($value) {

        if (empty($value)) return array();
        
        $CJO_EXT_VALUE = unserialize(htmlspecialchars_decode(stripslashes($value)));

        if (!is_array($CJO_EXT_VALUE))
            $CJO_EXT_VALUE = unserialize(htmlspecialchars_decode(utf8_decode(stripslashes($value))));            

        return $CJO_EXT_VALUE;
    }

    /**
     * Returns true if the given var is a valid OOArticleSlice object.
     * @param object $slice
     * @return boolean
     * @access public
     */
    public static function isValid($slice) {
        return is_object($slice) && is_a($slice, 'ooarticleslice');
    }

	/**
     * Returns a string representation of this object
     * for debugging purposes.
     * @return string
     * @access public
     */
	public function __toString() {
		return 'OOArticleSlice, "'.$this->getId().'", "'.$this->getArticleId().'", "'.$this->getModulTyp().'"'."<br/>\r\n";
	}
}