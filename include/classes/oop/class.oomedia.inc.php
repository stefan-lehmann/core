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
 * OOMedia class
 *
 * The OOMedia class is an object wrapper over the database table files.
 * @package     contejo
 * @subpackage  core
 */
class OOMedia {

    /**
     * id of the current media
     * @var int
     * @access public
     */
    public $_id = "";

    /**
     * id of the parent file (FOR FUTURE USE!)
     * @var int
     * @access public
     */
    public $_parent_id = "";

    /**
     * id of the media category
     * @var int
     * @access public
     */
    public $_cat_id = "";

    /**
     * name of the media category
     * @var string
     * @access public
     */
    public $_cat_name = "";

    /**
     * current media category as a OOMediaCategory object
     * @var object
     * @access public
     */
    public $_cat = "";

    /**
     * name  of the current media
     * @var string
     * @access public
     */
    public $_name = "";

    /**
     * original name  of the current media
     * @var string
     * @access public
     */
    public $_orgname = "";

    /**
     * mime-type of the current media
     * @var string
     * @access public
     */
    public $_type = "";

    /**
     * filesize of the current media
     * @var string
     * @access public
     */
    public $_size = "";

    /**
     * width of the current media in pixel
     * @var string
     * @access public
     */
    public $_width = "";

    /**
     * height of the current media in pixel
     * @var string
     * @access public
     */
    public $_height = "";

    /**
     * title of the current media
     * @var string
     * @access public
     */
    public $_title = "";

    /**
     * descriptions of the current media depending on languages
     * @var array
     * @access public
     */
    public $_description = "";

    /**
     * copyright owner of the current media
     * @var string
     * @access public
     */
    public $_copyright = "";

    /**
     * update date of the current media
     * @var int
     * @access public
     */
    public $_updatedate = "";

    /**
     * create date of the current media
     * @var int
     * @access public
     */
    public $_createdate = "";

    /**
     * update user of the current media
     * @var string
     * @access public
     */
    public $_updateuser = "";

    /**
     * create user of the current media
     * @var string
     * @access public
     */
    public $_createuser = "";

    /**
     * cropping values
     * @var array
     * @access public
     */
    public $_crop = "";

    /**
     * error file in use
     * @var bool
     * @access public
     */
    public $_error_file = false;

    /**
     * Constructor
     * @param int|null $id
     * @return OOMedia
     * @access protected
     */
    protected function __construct($id=null) {
        $this->getMediaById($id);
    }

    /**
     * Returns an OOMedia object based on the id.
     * @param int $id
     * @return object|null
     * @access public
     */
    public static function getMediaById($id) {

        global $CJO;

        $id = (int) $id;
        if (empty($id) || !is_numeric($id)) return null;

        $qry = "SELECT 
                    fl.file_id AS `file_id`,
                    fl.re_file_id AS `re_file_id`,
                    fl.category_id AS `category_id`,
                    ct.name AS `catname`,
                    fl.filename AS `filename`,
                    fl.originalname AS `originalname`,
                    fl.filetype AS `filetype`,
                    fl.filesize AS `filesize`,
                    fl.width AS `width`,
                    fl.height AS `height`,
                    fl.title AS `title`,
                    fl.description AS `description`,
                    fl.copyright AS `copyright`,
                    fl.updatedate AS `updatedate`,
                    fl.updateuser AS `updateuser`,
                    fl.createdate AS `createdate`,
                    fl.createuser AS `createuser`,
                    fl.crop_1 AS `crop_1`,
                    fl.crop_2 AS `crop_2`,
                    fl.crop_3 AS `crop_3`,
                    fl.crop_4 AS `crop_4`,
                    fl.crop_5 AS `crop_5`
                FROM
                  ".TBL_FILES." fl
                LEFT JOIN
                  ".TBL_FILE_CATEGORIES." ct
                ON
                  fl.category_id = ct.id
                WHERE
                  fl.file_id = '".$id."'
                LIMIT 1";

        $sql = new cjoSql();
        $result = $sql->getArray($qry);

        if (count($result) == 0) {
            return self::getErrorFile();
        }

        $result = $result[0];

        $media = new OOMedia();
        $media->_id          = $result['file_id'];
        $media->_parent_id   = $result['re_file_id'];
        $media->_cat_id      = $result['category_id'];
        $media->_cat_name    = $result['catname'];
        $media->_name        = $result['filename'];
        $media->_orgname     = $result['originalname'];
        $media->_type        = $result['filetype'];
        $media->_size        = $result['filesize'];
        $media->_width       = $result['width'];
        $media->_height      = $result['height'];
        $media->_title       = $result['title'];
        $media->_description = $result['description'];
        $media->_copyright   = $result['copyright'];
        $media->_updatedate  = (int) $result['updatedate'];
        $media->_updateuser  = $result['updateuser'];
        $media->_createdate  = (int) $result['createdate'];
        $media->_createuser  =  $result['createuser'];
        $media->_crop[1]     = $result['crop_1'];
        $media->_crop[2]     = $result['crop_2'];
        $media->_crop[3]     = $result['crop_3'];
        $media->_crop[4]     = $result['crop_4'];
        $media->_crop[5]     = $result['crop_5'];

        return $media;
    }

    /**
     * Returns an OOMedia object of the error image.
     * @param string $filename
     * @return object
     * @access public
     */
    public static function getErrorFile($filename=false) {

        global $CJO, $I18N;

        $error_filename = $CJO['ADDON']['settings']['image_processor']['error_img'];
        $fullpath       = $CJO['MEDIAFOLDER'].'/'.$error_filename;

        if (!file_exists($fullpath)) return null;

        $imagesize = @getimagesize($fullpath);

		    $crop      = imageProcessor_initCropValues($imagesize);

        $media             = new OOMedia();
        $media->_id        = 0;
        $media->_parent_id = 0;
        $media->_cat_id    = 0;
        $media->_cat_name  = '';

        $media->_name      = $error_filename;
        $media->_orgname   = '';
        $media->_type      = '';
        $media->_size      = '';

        $media->_width     = $imagesize[0];
        $media->_height    = $imagesize[1];
        $media->_title     = '';

        foreach($CJO['CLANG'] as $clang_id=>$name) {
            $desc[$clang_id] = ($filename) ? $I18N->msg('msg_file_not_found', $filename) : '&nbsp;';
        }

        $media->_description = serialize($desc);
        $media->_copyright   = '';

        $media->_updatedate  = '';
        $media->_updateuser  = '';

        $media->_createdate  = '';
        $media->_createuser  = '';

        $media->_crop[1]     = $crop[1];
        $media->_crop[2]     = $crop[2];
        $media->_crop[3]     = $crop[3];
        $media->_crop[4]     = $crop[4];
        $media->_crop[5]     = $crop[5];

        $media->_error_file  = true;

        return $media;
    }

    /**
     * Returns an OOMedia object based on the file name.
     * @param string $filename
     * @return object
     * @access public
     */
    public static function getMediaByName($filename) {

        $sql = new cjoSql();
        $sql->setQuery("SELECT file_id FROM ".TBL_FILES." WHERE filename = '".$filename."' LIMIT 1");
        $file_id = $sql->getValue('file_id');
        return ($file_id != '') ? self::getMediaById($file_id) : self::getErrorFile($filename) ;
    }
    
    /**
     * Returns a set of OOMedia objects based on the file name.
     * @param string $file
     * @param string|bool $extension limit the output to a specific extension
     * @return object
     * @access public
     */
    public static function getMediaSetByName($filename, $extension=false) {

        $sql = new cjoSql();
        $qry = "SELECT file_id, filename FROM ".TBL_FILES." WHERE filename LIKE '".pathinfo($filename,PATHINFO_FILENAME).".".$extension."%'";
        $result = $sql->getArray($qry);
        $media = array ();

        if ($extension !== false) {
            return self::getMediaById($result[0]['file_id']);
        }
        
        if (is_array($result)) {
            foreach ($result as $row) {
                $extension = self::getExtension($row['filename']);
                $temp = self::getMediaById($row['file_id']);
                
                if (self::isImageType($temp->getType()) && !isset($media['jpg'])) {
                    $media['image'] = $temp;
                }
                
                if (self::isVideoType($temp->getType(), true)) {
                    if (!isset($media['flv']) && !isset($media['mp4'])) {
                        $media['video'] = $temp;
                    }
                    if (!isset($media['mp4'])) {
                        $media['video'] = $temp;
                    }
                }
                $media[$extension] = $temp;
            }
        }
        return $media;
    }

    /**
     * Returns an OOMedia object based on the file name.
     * this method is equivalent to getMediaByName()
     * @deprecated
     * @param string $filename
     * @return object
     * @access public
     */
    public static function getMediaByFileName($filename) {
        return self::getMediaByName($filename);
    }

    /**
     * Returns an array of OOMedia objects that are matching to a given file extension.
     * @example OOMedia::getMediaByExtension('css');
     * @example OOMedia::getMediaByExtension('gif');
     * @param string $filename
     * @return object
     * @access public
     */
    public static function getMediaByExtension($extension) {

        $sql = new cjoSql();
        $qry = "SELECT file_id
                FROM ".TBL_FILES."
                WHERE SUBSTRING(filename,LOCATE( '.',filename)+1) = '".$extension."'";
        $result = $sql->getArray($qry);
        $media = array ();

        if (is_array($result)) {
            foreach ($result as $row) {
                $media[] = self::getMediaById($row['file_id']);
            }
        }

        return $media;
    }

    /**
     * Returns the id of the current media.
     * @return int
     * @access public
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Returns media category of the current media as an OOMediaCategory object.
     * @return object
     * @access public
     */
    public function getCategory() {

        if ($this->_cat === null) {
            $this->_cat = OOMediaCategory::getCategoryById($this->getCategoryId());
        }
        return $this->_cat;
    }

    /**
     * Returns the category name of the current media.
     * @return int
     * @access public
     */
    public function getCategoryName() {
        return $this->_cat_name;
    }

    /**
     * Returns the id of the current media.
     * @return int
     * @access public
     */
    public function getCategoryId() {
        return $this->_cat_id;
    }

    /**
     * Returns the parent id of the current media.
     * @return int
     * @access public
     */
    public function getParentId() {
        return $this->_parent_id;
    }

    /**
     * Returns true if the current media has a parent object.
     * @return boolean
     * @access public
     */
    public function hasParent() {
        return $this->getParentId() != 0;
    }

    /**
     * Returns the title id of the current media.
     * @return string
     * @access public
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Returns the description of the current media depending on the language.
     * @param boolean|int $clang
     * @param int $specialchars
     * @return string
     * @access public
     */
    public function getDescription($clang=false, $specialchars = true) {

        global $CJO, $I18N;

        if ($clang === false)  $clang = $CJO['CUR_CLANG'];
        $desc_array = (!is_array($this->_description)) ? unserialize(stripslashes($this->_description)) : $this->_description;
        $description = trim(stripslashes($desc_array[$clang]));

        if (!$description) {
            return ($CJO['CONTEJO']) ? $I18N->msg('label_no_media_description') : false;
        }

        $description = preg_replace('/(?![\(\s])"(?=[\w|\d])|„/', '„', $description);
        $description = preg_replace('/(?!\w)"(?=[\)|\W|\D|.$])|(?!\w)"$|"|“|”/','“', $description);

        if ($specialchars) {
            return htmlspecialchars($description, ENT_QUOTES, 'UTF-8', false);
        }
        else {
            return $description;
        }
    }

    /**
     * Returns the copyright owner of the current media.
     *
     * @param boolean $full
     * @return string
     */
    public function getCopyright($full=false) {

        global $CJO;
        
        if ($full !== true){
            return $this->_copyright;
        }

        $copyright = empty($this->_copyright) ? $CJO['SERVERNAME'] : $this->_copyright;
        $copyright = (strpos($copyright, '|') > strlen($copyright)-3) ? substr($copyright, 0, -2) : $copyright;
        $copyright = str_replace(' ', '_', $copyright);
        $copyright = str_replace('|', '--', $copyright);

        return $copyright;
    }

    /**
     * Returns the file name of the current media.
     * @return string
     * @access public
     */
    public function getFileName() {
        return $this->_name;
    }

    /**
     * Returns the original file name of the current media
     * @return string
     * @access public
     */
    public function getOrgFileName() {
        return $this->_orgname;
    }

    /**
     * Returns the complete path of the current media.
     * @return string
     * @access public
     */
    public function getFullPath() {
        global $CJO;
        return $CJO['MEDIAFOLDER'].'/'.$this->getFileName();
    }

    /**
     * Returns the width of the current media in pixel.
     * @return int
     * @access public
     */
    public function getWidth() {
        return $this->_width;
    }

    /**
     * Returns the height of the current media in pixel.
     * @return int
     * @access public
     */
    public function getHeight() {
        return $this->_height;
    }

    /**
     * Returns the mime type of the current media.
     * @return string
     * @access public
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * Returns the file size of the current media.
     * @return string
     * @access public
     */
    public function getSize() {

        global $CJO;

        if (empty($this->_size) && file_exists($this->getFullPath())) {
            $this->_size = filesize($this->getFullPath());

            if (!empty($this->_size)) {
                $update = new cjoSql();
                $update->setTable(TBL_FILES);
                $update->setWhere("file_id='".$this->_id."'");
                $update->setValue('filesize',$this->_size);
                $update->Update();
            }
        }
        return $this->_size;
    }
    
    /**
     * Returns a formated file size.
     * If no size is given the size of the current media will be used.
     * @param int $size
     * @return string
     * @access public
     */
    public function _getFormattedSize() {
        return self::getFormattedSize($this->getSize());
    }
    
    /**
     * Returns a formated file size.
     * If no size is given the size of the current media will be used.
     * @param int $size
     * @return string
     * @access public
     */
    public static function getFormattedSize($size) {

        $kb = 1024;         // kB
        $mb = 1024 * $kb;   // MB
        $gb = 1024 * $mb;   // GB
        $tb = 1024 * $gb;   // TB

        if ($size < $kb)
            return $size.' Bytes';
        else if ($size < $mb)
            return round($size/$kb,2).' kB';
        else if ($size < $gb)
            return round($size/$mb,2).' MB';
        else if ($size < $tb)
            return round($size/$gb,2).' GB';
        else
            return round($size/$tb,2).' TB';
    }

    /**
     * Returns the date when the current media has been created.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public function getCreateDate($format=null) {
        return OOContejo :: _getDate($this->_createdate, $format);
    }

    /**
     * Returns the date when the current media has been updated.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
    public function getUpdateDate($format=null) {
        return OOContejo :: _getDate($this->_updatedate, $format);
    }

    /**
     * Returns the name of the user which updated the current media the last time.
     * @return string
     * @access public
     */
    public function getUpdateUser() {
        return $this->_updateuser;
    }

    /**
     * Returns the name of the user which created the current media.
     * @return string
     * @access public
     */
    public function getCreateUser() {
        return $this->_createuser;
    }

    /**
     * Returns an array with the crop values of the current media.
     * @param int $index array index (1-5)
     * @return string
     * @access public
     */
    public function getCropData($index) {
        return cjoAssistance::toArray($this->_crop[$index]);
    }

    /**
     * returns either an image in original size or an icon of the current media
     * @param string $filename 
     * @param array $params
     * @return string html-code to display a media as an image or an icon
     * @example  $media = Media::getMediaById(5);
     *          echo $media->toImage(array('width'=>100, 'height'=> 200, 'title' => 'my picture');
     * @access public
     */
    public static function toImage($filename, $params = array ()) {

        global $CJO;

        $fullpath   = $CJO['MEDIAFOLDER']."/".$filename;         
        $media_obj = self::getMediaByName($filename);
        $description = '';  
        $attributes = '';

        if (!self::isValid($media_obj)) {
            if ($strict) return false;
            
            $media_obj = self::getErrorFile();
            if (!self::isValid($media_obj) && !$media_obj->_error_file) return false;  
            $filename  = $media_obj->getFileName();
        }
        
        // Is the media an image?
        if (!self::isImage($filename)) {
            return self::toIcon($filename, $fullpath, $params);
        }

        if (empty($params['alt'])) {
            $attributes .= ' alt="'. $media_obj->getDescription() .'"';
        }

        if (empty($params['title'])) {
            if (($desc = $media_obj->getDescription()) != '') {
                $attributes .= ' title="'. $desc .'"';
            }
        }

        foreach (cjoAssistance::toArray($params) as $name => $value) {
            $attributes .= ' '. $name.'="'.$value.'"';
        }

        return sprintf('<img src="%s"%s />', $fullpath, $attributes);
    }


    public static function toResizedImage($filename, $params=array(), $replace_folder=false, $return_array=false, $strict=true) {

        global $CJO, $I18N;

        $image   = array();
        $search  = array();
        $replace = array();
        $media_obj = self::getMediaByName($filename);
        $description = '';  

        $params['get_src'] = false;

        if (!self::isValid($media_obj)) {
            if ($strict) return false;
            
            $media_obj = self::getErrorFile();
            if (!self::isValid($media_obj) && !$media_obj->_error_file) return false;  
            $filename  = $media_obj->getFileName();
        }
        
        if (!isset($CJO['IMAGE_LIST_BUTTON']['DESCRIPTION']) || 
            $CJO['IMAGE_LIST_BUTTON']['DESCRIPTION']) {
                
            switch ($params['des']['settings']) {
                case '-': $description = ''; break;
                case '2':
                case  2 : $description = $params['des'][2]; break;
                default : $description = $media_obj->getDescription();
            }
        }

        $short_description = cjoAssistance::truncateString($description,50);

        $pram['img']['style'] = !empty($params['stl'][0]) ? $params['stl'][0] : NULL;
        $pram['img']['class'] = !empty($params['css'][0]) ? $params['css'][0] : NULL;

        if (empty($params['img']['title'])) $params['img']['title']  = $short_description;

        $image = $media_obj->_toThumbnail($params['img']);
        preg_match('/(?<=src=\")[^\"]*(?=\")/i', $image, $array);
        $image_src = $array[0];

        $size = @getimagesize($image_src);

        $replace  = array('copyright'   => $media_obj->getCopyright(true),
                          'title'       => $description != '' ? $description : $media_obj->getTitle(),
                          'image'       => $image,
                          'description' => $description,
                          'width'       => $size[0].'px');

        if ($replace_folder) {
            $replace[$CJO['MEDIAFOLDER'].'/'] = 'CJO_MEDIAFOLDER/';
        }

        $output = '%image%%description%';

        switch($params['fun']['settings']) {
            case 1:
                $temp = !empty($params['fun']['zoom']) ? $params['fun']['zoom'] : $params['zoom'];
                $output = $media_obj->getImageboxLink($temp);
                break;
            case 2:
                $output = $media_obj->getFlashboxLink($params['fun']['swf']['name'], $params['fun']['swf']);
                break;
            case 4:
                if (!isset($params['fun']['int']['clang'])) $params['fun']['int']['clang'] = false;
                $output = $media_obj->getImageIntLink($params['fun']['int']['id'], $params['fun']['int']['clang']);
                break;
            case 5:
                $output = $media_obj->getImageExtLink($params['fun']['ext']);
                break;
            case 6:
                //compatibility
                if (!empty($params['fun']['flv']) && empty($params['fun']['video'])) {
                    $params['fun']['video'] = & $params['fun']['flv'];
                }
                $output = cjoHtml5Video::getVideoLink($params['fun']['video']['name'], $params['fun']['video']);
                if ($output == false) $output = '%image%%description%';
                break;
        }

        $output = '<p style="width: %width%">'."\r\n".$output."\r\n".'</p>';

        if ($return_array === true) {
            return $replace;
        }

        foreach(array_keys($replace) as $key) {
            if ($key != $CJO['MEDIAFOLDER'].'/') {
                $search[] = '%'.$key.'%';
            }
            else {
                $search[] = $key;
            }
        }
        $search[] = 'name=""';
        return str_replace($search, $replace, $output);
    }

    private function getImageboxLink($params=array()) {

        global $CJO;

        if (!$CJO['IMAGE_LIST_BUTTON']['IMAGEBOX'])  return '%image%%description%';
        
        $params['get_src'] = 1;
        $url = $this->_toThumbnail($params);
        $group = !empty($params['grp']) ? $params['grp'] : 'group_'.$CJO['ARTICLE_ID'];

        return '<a rel="imagebox-'.$group.'" href="'.$url.'" name="%copyright%"
                    title="%title%" class="imagelink zoom">%image%<span></span>%description%</a>'."\r\n";
    }

    private function getFlashboxLink($flash_file, $params=array()) {

        global $CJO;

        $flash_file = $CJO['MEDIAFOLDER']."/".$flash_file;

        if (!file_exists($flash_file)) return '%image%%description%';

        $flash_size = getimagesize($flash_file);
        $group  = ($params['grp'] != '') ? $params['grp'] : 'group_'.$CJO['ARTICLE_ID'];
        $width  = ($params['width'] != '') ? $params['width'] : $flash_size[0];
        $height = ($params['height'] != '') ? $params['height'] : $flash_size[1];
        $prams  = ($params['prams'] != '') ? '&amp;'.str_replace('&', '&amp;',$params['prams']) : '';
        $url    = $flash_file.'?w='.$width.'&amp;h='.$height.$prams;

        return '<a rel="flashbox-'.$group.'" href="'.$url.'" name="%copyright%"
                    title="%title%" class="imagelink zoom flash">%image%<span></span>
                    %description%</a>'."\r\n";
    }

    private function getImageIntLink($article_id, $clang=false) {

        global $CJO;

        $article = OOArticle::getArticleById($article_id, $clang);

        if (!OOArticle::isValid($article)) return '%image%%description%';

        return '<a href="'.cjoRewrite::getUrl($article_id, $article->getClang()).'"
                    title="'.$article->getName().'" class="imagelink ">%image%%description%</a>'."\r\n";

    }

    private function getImageExtLink($url) {

        if (empty($url)) return '%image%%description%';

        return '<a href="'.$url.'"
               title="%title%" class="imagelink">%image%
               %description%</a>'."\r\n";
    }

    /**
     * Returns a link to the the current media.
     * @param string $attributes
     * @return string
     * @access public
     */
    public function toLink($attributes = '') {
        return sprintf('<a href="%s" title="%s"%s>%s</a>', $this->getFullPath(), $this->getDescription(), $attributes, $this->getFileName());
    }

     /**
     * Returns either a resized image or an icon.
     * @param array $params
     * @return string html-code to display a media as an image or an icon
     * @example  $media = Media::getMediaById(5);
     *           echo $media->_toThumbnail(array('width'=>100, 'height'=> 200, 'crop_num' => '2'));
     * @access public
     */
    public function _toThumbnail($params=array('width'=>80, 'height'=>80)) {
        return self::toThumbnail($this->getFileName(), '', $params);
    }
    
    /**
     * Returns either a resized image or an icon.
     * @param string $filename
     * @param string|boolean $fullpath
     * @param array $params
     * @return string html-code to display a media as an image or an icon
     * @example  echo Media::toThumbnail(test.jpg, '../img/test/', array('width'=>800, 'height'=> 600));
     * @access public
     */
    public static function toThumbnail($filename, $fullpath=false, $params=array('width'=>80, 'height'=>80)) {

        global $CJO, $I18N;

        self::getDefaultImageSizes();

        $media_obj  = self::getMediaByName($filename);
        $resize     = true;
        $attributes = '';
        
        if (empty($fullpath) || !file_exists($fullpath)) {
            $fullpath = $CJO['MEDIAFOLDER'].'/'.$filename;
        }

        if (!file_exists($fullpath) || !is_file($fullpath)) {
            $media_obj = self::getErrorFile();
            if (!self::isValid($media_obj) && !$media_obj->_error_file) return false;       
            $fullpath  = $media_obj->getFullPath();
            $filename  = $media_obj->getFileName();
        }

        if (!empty($params['crop_num']) && $params['crop_num'] != '-') {

            $width = $CJO['IMG_DEFAULT'][$params['crop_num']]['width'];
            $height = $CJO['IMG_DEFAULT'][$params['crop_num']]['height'];

            $crop_data['img'] = $media_obj->getCropData($params['crop_num']);
            $shadow_crop_nums = $CJO['ADDON']['settings']['image_processor']['shadow']['shadow_crop_nums'];
            $shadow           = in_array($params['crop_num'], $shadow_crop_nums) || !empty($params['shadow']);
            $brand_on_off     = (isset($params['brand']) && $params['brand'] == 'on') ? 'brand_on_off = 1' : 'brand_on_off = 0';
        }
        else {
            $width = $params['width'];
            $height = $params['height'];
            if (!empty($params['crop_auto'])) {
                $imagesize        = $media_obj->isErrorFile() ? getimagesize($fullpath) : array($media_obj->getWidth(),$media_obj->getHeight());
                $crop_data['img'] = imageProcessor_calculateCrop($imagesize, $params);
            } else {
                $crop_data['img'] = array(1=>null, 2=>null, 3=>null, 4=>null);
            }
            $shadow = !empty($params['shadow'])? $params['shadow'] : NULL;
            $brand_on_off = 'brand_on_off = 0';
        }

        unset($params['crop_num']);
        unset($params['width']);
        unset($params['height']);
        unset($params['brand']);

        if (empty($params['title']) && $media_obj->getCopyright()) {
            $params['title'] = $media_obj->getCopyright();
        }
        
        if (empty($params['alt'])) {
            $params['alt'] = $media_obj->getTitle() != $I18N->msg('label_no_title') ? $media_obj->getTitle() : '';
        }

        foreach (cjoAssistance::toArray($params) as $tag => $value) {
            if (!in_array($tag, array('class','style','rel','id','alt','title'))) continue;
            $attributes .= cjoAssistance::htmlToTxt(' '.$tag.'="'.$value.'"');
        }

        if ($CJO['ADDON']['status']['image_processor'] == 1) {
            if (!class_exists("resizecache")) {
                require_once $CJO['ADDON_PATH'].'/image_processor/classes/class.resizecache.inc.php';
            }
            $resize = !resizecache::is_conflict_memory_limit($fullpath);
        }
        $size = @getimagesize($fullpath);

        if (file_exists($fullpath) &&
            self::isResizeImage($filename) &&
            $size != false) {
            if ($resize) {
                $img = imageProcessor_getImg($filename,
                                             $width,
                                             $height,
                                             $resize=null,
                                             $aspectratio=true,
                                             $brand_on_off,
                                             $brandimg=false,
                                             $jpg_quality=null,
                                             $crop_data["img"][1],
                                             $crop_data["img"][2],
                                             $crop_data["img"][3],
                                             $crop_data["img"][4],
                                             $shadow,
                                             $fullpath );
                                             

                return !empty($params['get_src']) ? $img : '<img src="'.$img.'"'.$attributes.' />';
            }
            else {
                if ($size[0] > $size[1] && $size[0] > $width) {
                    $attributes .= ' width="'.$width.'"' ;
                }
                else if ($size[0] < $size[1] && $size[1] > $params['y']) {
                    $attributes .= ' height="'.$height.'" ';
                }

                return !empty($params['get_src']) ? '' : '<span class="warning">&nbsp;</span><img src="'.$fullpath.'"'.$attributes.' />';
            }
        }
        return !empty($params['get_src']) ? '' : self::toIcon($filename, $fullpath, $params);
    }

    /**
     * Returns an icon of the current media depending on the mime typ.
     * @param string $filename
     * @param string|boolean $fullpath
     * @param array $params
     * @param string $icon_path
     * @return string html-code to display a media as an icon
     * @access public
     */
    public static function toIcon($filename, $fullpath=false, $params=array(), $icon_path=false) {

        global $CJO, $I18N;

        if (empty($fullpath))
            $fullpath = $CJO['MEDIAFOLDER'].'/'.$filename;
            
        $attributes = '';
        
        if (!$icon_path) $icon_path = $CJO['HTDOCS_PATH'].'contejo/img/mime_icons';

        foreach (cjoAssistance::toArray($params) as $name => $value) {
            if ($name == 'width' || $name == 'height') continue;
            $attributes .= ' '. $name.'="'.$value.'"';
        }

        if (!file_exists($fullpath) || !is_file($fullpath))
            return '<img src="'.$icon_path.'/error.png" alt="'.$I18N->msg('msg_file_not_found', $fullpath).'" title="'.$I18N->msg('msg_file_not_found', $fullpath).'" />';

        $path_info = pathinfo($fullpath);
        $ext = strtolower($path_info["extension"]);

        if (self::isDocType($ext) && file_exists($icon_path.'/'.$ext.'.png')) {
            return '<img src="'.$icon_path.'/'.$ext.'.png"'.$attributes.' alt="" />';
        }
        return '<img src="'.$icon_path.'/default.png"'.$attributes.' alt="" />';
    }

    /**
     * Returns the current media with corresponding html tags.
     * @param string $filename 
     * @param string $attributes
     * @return string html-code to display a media as an icon
     * @access public
     */
    public static function toHTML($filename, $attributes='') {

        global $CJO;
        
        $fullpath = $CJO['MEDIAFOLDER'].'/'.$filename;

        switch (self::getExtension($filename)) {
            case 'jpg' :
            case 'jpeg':
            case 'png' :
            case 'gif' :
            case 'bmp' : return self::toImage($filename, $attributes);
            case 'js'  : return sprintf('<script type="text/javascript" src="%s"%s></script>', $filename, $attributes);
            case 'css' : return sprintf('<link href="%s" rel="stylesheet" type="text/css"%s>', $filename, $attributes);
            default    : return '<!-- Sorry, no html-equivalent available for '.$filename.' (Mime-Type: "'.cjoMedia::detectMime($fullpath).'") -->';
        }
    }

    /**
     * Returns true if the given var is a valid OOMedia object.
     * @param object $media
     * @return boolean
     * @access public
     */
    public static function isValid($media) {
        return is_object($media) && is_a($media, 'oomedia') && ($media->_id > 0 || !$media->_error_file);
    }

    /**
     * Returns true, if the current file was not found and has been replaced by the error file.
     * @return boolean
     * @access public
     */
    public function isErrorFile() {
        return $this->_error_file;
    }

    /**
     * Returns true if the current or given media is
     * connected in an article or an article slice.
     * @return boolean
     * @access public
     */
    public function isInUse() {

        global $I18N;

        // check if file is in an article slice
        $filename  = $this->getFileName();

        $search = "a.file LIKE '".$filename."' \r\n";
        for ($i = 1; $i <= 20; $i++) {
            $search .= "OR s.value".$i." LIKE '%".$filename."%' \r\n";
            if ($i > 10) continue;
            $search .= "OR s.file".$i."='".$filename."' \r\n";
            $search .= "OR s.filelist".$i." LIKE '%|".$filename."|%' \r\n";
        }

        $sql = new cjoSql();
        $qry = "SELECT DISTINCT
              IFNULL(a.name,'".$I18N->msg("label_no_name")."') AS article_name,
                  a.id AS article_id,
                  s.ctype AS ctype
                FROM ".TBL_ARTICLES." a
                LEFT JOIN ".TBL_ARTICLES_SLICE." s ON
                  s.article_id=a.id
                WHERE ".$search;

        $results = $sql->getArray($qry);

        return (!empty($results)) ? $results : false;
    }

    /**
     * Returns the extension.
     * @return string
     * @access public
     */
    public function _getExtension() {
        return self::getExtension($this->getFileName());
    }
    
    /**
     * Returns the extension.
     * @param string $filename
     * @return string
     * @access public
     */
    public static function getExtension($filename) {
        return substr(strrchr($filename, "."), 1);
    }

    /**
     * Returns the supported file extensions.
     * @return array
     * @access public
     */
    public static function getDocTypes() {
        global $CJO;
        return $CJO['UPLOAD_EXTENSIONS'];
    }

    /**
     * Writes the default image sizes in to a global array.
     * @return array
     * @access public
     */
    public static function getDefaultImageSizes() {

        global $CJO;

        if (isset($CJO['img_default']) && is_array($CJO['img_default'])) return;

        $img_default = array();
        $img_default['default'] = 0;
        $total_alt = 0;

        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_IMG_CROP." WHERE status!='0' ORDER BY id";
        $sql->setQuery($qry);

        for($i = 1; $i <= $sql->getRows(); $i++) {

            $id     = $sql->getValue('id');
            $status = $sql->getValue('status');
            $width  = $sql->getValue('width');
            $height = $sql->getValue('height');
            $total  = $width * $height;

            $img_default[$id]           = array();
            $img_default[$id]['width']  = $width;
            $img_default[$id]['height'] = $height;

            if ($status == '-1') {
                $img_default['default'] = $id;
            }

            if ($total > $total_alt &&
                $img_default['default'] != $id) {
                $img_default['zoom'] = $id;
                $total_alt = $total;
            }
            $sql->next();
        }

        ksort ($img_default);

        $CJO['IMG_DEFAULT'] = $img_default;
    }

    /**
     * Checks if the given file extension is a supported extension.
     * @param string $type
     * @return boolean
     * @access public
     */
    public static function isDocType($type) {
        return in_array('.'.$type, self::getDocTypes());
    }

    /**
     * returns the image mime typs
     * @return array
     */
    public static function getImageTypes() {
        return array ('image/gif','image/jpg','image/jpeg','image/png','image/pjpeg','image/bmp');
    }

    /**
     * Checks if the given mime type is an image.
     * @param string $mime
     * @return boolean
     * @access public
     */
    public static function isImageType($mime) {
        return in_array($mime, self::getImageTypes());
    }
    
    /**
     * Returns true if a file is an image.
     * @param string $filename
     * @return boolean
     * @access public
     */
    public static function isImage($filename) {
        return self::isImageType(cjoMedia::detectMime($filename));
    }    
    
    /**
     * Returns the mime typs of all resizeable image types.
     * @return array
     * @access public
     */
    public static function getResizeTypes() {
        return array ('image/gif','image/jpg','image/jpeg','image/pjpeg','image/png');
    }

    /**
     * Checks if the given mime type is a resizeable image.
     * @param string $mime
     * @return boolean
     * @access public
     */
    public static function isResizeType($mime) {
        return in_array($mime, self::getResizeTypes());
    }
    
    /**
     * Returns true if a file is an image.
     * @param string $filename
     * @return boolean
     * @access public
     */
    public static function isResizeImage($filename) {
        return self::isResizeType(cjoMedia::detectMime($filename));
    }  

    /**
     * Returns true if a file is a HTML5 video type.
     * @param string $filename
     * @return boolean
     * @access public
     */
    public static function getHtml5VideoType($filename) {
        $mime = cjoMedia::detectMime($filename);
        return self::isVideoType($mime);
    }       
    
    /**
     * Returns true if a file is a video.
     * @param string $filename
     * @return boolean
     * @access public
     */
    public static function isVideo($filename, $html5=false) {
        $mime = cjoMedia::detectMime($filename);
        return self::isVideoType($mime, $html5);
    }
    
    /**
     * Checks if the given mime type is a video.
     * @param string $mime
     * @return boolean
     * @access public
     */
    public static function isVideoType($mime, $html5=false) {

        if (!$html5) {
            return (strpos('video/', $mime) === true) ? true : false;
        } else {
            return in_array($mime, self::getVideoTypes());
        }
    }
        
    /**
     * Returns the mime typs of all supported video types.
     * @return array
     * @access public
     */
    public static function getVideoTypes() {
        return array('video/mp4','video/x-flv','video/webm','video/ogg');
    }    
    
    /**
     * Converts the current :: object to a string.
     * @return string
     * @access public
     */
    public function __toString() {
        return 'OOMedia, "'.$this->getId().'", "'.$this->getName().'"'."<br/>\n";
    }
}