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
 * OOMediaCategory class
 *
 * The OOMediaCategory class is an object wrapper over the database table media_category.
 * @package 	contejo
 * @subpackage 	core
 */
class OOMediaCategory {

    /**
     * id of the current media category
     * @var int
     * @access public
     */
    public $_id = "";

	/**
	 * id of the parent media category
	 * @var int
     * @access public
     */
    public $_parent_id = "";

	/**
	 * name of the current media category
	 * @var string
     * @access public
     */
    public $_name = "";

	/**
     * path of the current media category
	 * @var string
     * @access public
     */
    public $_path = "";

	/**
	 * update date of the current media category
	 * @var int
     * @access public
     */
    public $_updatedate = "";

	/**
	 * create date of the current media category
	 * @var int
     * @access public
     */
    public $_createdate = "";

	/**
	 * update user of the current media category
	 * @var string
     * @access public
     */
    public $_updateuser = "";

	/**
	 * create user of the current media category
	 * @var string
     * @access public
     */
    public $_createuser = "";

    /**
     * child categories of the current media category
     * @var array|null
     * @access public
     */
    public $_children = null;

	/**
	 * media of the current media category
	 * @var array|null
     * @access public
     */
    public $_files = null;

	/**
	 * Constructor
	 * @access protected
	 * @param int|null $id
	 * @return OOMediaCategory
	 */
	protected function __construct($id = null) {
		$this->getCategoryById($id);
	}

    /**
     * Returns an OOMediaCategory object based on the id.
     * @param int $id
     * @return object|null
     * @access public
     */
	public static function getCategoryById($id) {

		$id = (int) $id;
		if (!is_numeric($id) || $id == 0) return null;

		$sql = new cjoSql();
		$qry = "SELECT * FROM ".TBL_FILE_CATEGORIES." WHERE id = '".$id."' LIMIT 1";
		$result = $sql->getArray($qry);

		if (empty($result[0])) return null;
		
        $result = $result[0];
        
		$cat = new OOMediaCategory();
		$cat->_id         = $result['id'];
		$cat->_parent_id  = $result['re_id'];
		$cat->_name       = $result['name'];
		$cat->_path       = $result['path'];
		$cat->_hide       = $result['hide'];
		$cat->_createdate = $result['createdate'];
		$cat->_updatedate = $result['updatedate'];
		$cat->_createuser = $result['createuser'];
		$cat->_updateuser = $result['updateuser'];
		$cat->_children   = null;
		$cat->_files      = null;

		return $cat;
	}

	/**
	 * Return an array of OOMediaCategory objects that have
	 * no parent categories.
	 * @return array
	 * @access public
	 */
	public static function getRootCategories() {

		$sql = new cjoSql();
		$qry = "SELECT id FROM ".TBL_FILE_CATEGORIES." WHERE re_id = 0 ORDER by name";
		$result = $sql->getArray($qry);

		$rootCats = array ();
		if (is_array($result)) {
			foreach ($result as $line) {
				$rootCats[] = OOMediaCategory :: getCategoryById($line['id']);
			}
		}
		return $rootCats;
	}

    /**
     * Returns an OOMediaCategory object based on the name.
     * @param string $name
     * @return object
     * @access public
     */
	public static function getCategoryByName($name) {

		$sql = new cjoSql();
		$qry = "SELECT id FROM ".TBL_FILE_CATEGORIES." WHERE name = '".$name."'";
		$result = $sql->getArray($qry);

		$media = array ();
		if (is_array($result)) {
			foreach ($result as $line) {
				$media[] = OOMediaCategory :: getCategoryById($line['id']);
			}
		}
		return $media;
	}

    /**
     * Returns the id of the current media category.
     * @return int
     * @access public
     */
	public function getId() {
		return $this->_id;
	}

    /**
     * Returns the id of the parent media category.
     * @return int
     * @access public
     */
	public function getParentId() {
		return $this->_parent_id;
	}

	/**
	 * Returns the parent media category as a OOMediaCategory object.
	 * @return object
	 * @access public
	 */
	public function getParent() {
		return OOMediaCategory :: getCategoryById($this->getParentId());
	}

	/**
     * Returns the name of the current media category.
     * @return string
     * @access public
     */
	public function getName() {
		return $this->_name;
	}

	/**
     * Returns the path of the current media category.
	 * @param $id int|bool 
	 * @return string
	 */
	public static function getPath($id) {
	    
        $mediacat = OOMediaCategory :: getCategoryById($id);
        return (OOMediaCategory::isValid($mediacat)) ? $mediacat->_path : $id;
	}

    /**
     * Returns the date when the current media category has been created.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
	public function getCreateDate($format = null) {
		return OOContejo :: _getDate($this->_createdate, $format);
	}

    /**
     * Returns the date when the current media category has been updated.
     * @see OOContejo::_getDate()
     * @param mixed $format
     * @return string|int
     * @access public
     */
	public function getUpdateDate($format = null) {
        return OOContejo :: _getDate($this->_updatedate, $format);
	}

    /**
     * Returns the name of the user which created the current media category.
     * @return string
     * @access public
     */
    public function getCreateUser() {
        return $this->_createuser;
    }

    /**
     * Returns the name of the user which updated the current media category the last time.
     * @return string
     * @access public
     */
    public function getUpdateUser() {
        return $this->_updateuser;
    }

    /**
     * Returns an array of sub categories as OOMediaCategory objects.
     * @return array
     * @access public
     */
	public function getChildren() {

		if ($this->_children === null) {

			$this->_children = array();

			$sql = new cjoSql();
			$qry = "SELECT id FROM ".TBL_FILE_CATEGORIES." WHERE re_id = ".$this->getId()." ORDER BY name";
			$result = $sql->getArray($qry);

			if (is_array($result)) {
				foreach ($result as $row) {
					$id = $row['id'];
					$this->_children[] = OOMediaCategory :: getCategoryById($id);
				}
			}
		}
		return $this->_children;
	}

	/**
	 * Returns the number of sub categories.
	 * @return int
     * @access public
     */
	public function countChildren() {
		return count($this->getChildren());
	}

	/**
     * Returns an array of OOMedia object which are
     * located in the given media category.
     * @param int $id id of the media category (default is 0 = root)
     * @param string $order_by setting for sorting the output
     * @param bool $sort_desc true if sort direction is desc / false if sort direction is asc
     * @return array
     * @access public
     */
	public static function getFilesOfCategory($id=0, $order_by=false, $sort_desc=false) {

		$files = array ();
		
        $qry_add  = $order_by ? ' ORDER BY '.$order_by : '';
        $qry_add .= ($qry_add &$sort_desc == true) ? ' DESC' : '';        
        
		$sql = new cjoSql();
		$qry = "SELECT file_id FROM ".TBL_FILES." WHERE category_id = '".$id."'".$qry_add;
		$result = $sql->getArray($qry);

		if (is_array($result)) {
			foreach ($result as $line) {
				$files[] = OOMedia :: getMediaById($line['file_id']);
			}
		}
		return $files;
	}

	/**
     * Returns an array of OOMedia object which are
     * located in the current media category.
	 * @param int $id default is root
	 * @return array
     * @access public
     */
	public function getFiles() {

		if ($this->_files === null) {

			$this->_files = array ();

			$sql = new cjoSql();
			$qry = "SELECT file_id FROM ".TBL_FILES." WHERE category_id = ".$this->getId();
			$result = $sql->getArray($qry);

			if (is_array($result)) {
				foreach ($result as $line) {
					$this->_files[] = OOMedia :: getMediaById($line['file_id']);
				}
			}
		}
		return $this->_files;
	}

	/**
	 * Returns the number of files.
	 * @return int
     * @access public
     */
	public function countFiles() {
		return count($this->getFiles());
	}

	/**
	 * Generates the path of a media category.
	 * @param $id id of the media category
	 * @param $is_re_id  id of the parent media category
	 * @return string
     * @access public
     */
	public static function generateMediaCategoryPath($id, $is_re_id = false) {

		global $CJO;

		$cat = OOMediaCategory :: getCategoryById($id);
		$re_id = (!$is_re_id) ? $cat->_re_id : $id;

		$path = '|';

		if (!empty($re_id)) {
			$path = '|'.$re_id.$path;
			$path = OOMediaCategory :: generateMediaCategoryPath($re_id).$path;
		}
		return str_replace('||','|', $path);
	}

    /**
     * Returns true if the given var is a valid OOMediaCategory object.
     * @param object $mediaCat
     * @return boolean
     * @access public
     */
	public static function isValid($mediacat) {
		return is_object($mediacat) && is_a($mediacat, 'oomediacategory');
	}

    /**
     * Returns true if the current media category has a parent media category.
     * @return boolean
     * @access public
     */
	public function hasParent() {
		return $this->getParentId() != 0;
	}

    /**
     * Returns true if the current media category has children.
     * @return boolean
     * @access public
     */
	public function hasChildren() {
		return count($this->getChildren()) > 0;
	}

	/**
     * Returns true if the current media category contains media.
     * @return boolean
     * @access public
     */
	public function hasFiles() {
		return count($this->getFiles()) > 0;
	}

//    /**
//     * Removes the a media category by the id.
//	 * @param int $id
//	 * @return array
//     * @access public
//     */
//	public static function deleteCategory($id, $recurse = true, $exclude_files = true) {
//
//		global $CJO, $I18N;
//
//		$cat = OOMediaCategory :: getCategoryById($id);
//
//		if (!is_object($cat)) {
//			cjoMessage::addError($I18N->msg("msg_category_not_found"));
//			return false;
//		}
//		if (!$CJO['USER']->hasMediaPerm($id)) {
//			cjoMessage::addError($I18N->msg("msg_no_permissions"));
//			return false;
//		}
//		return $cat->_delete($recurse, $exclude_files);
//	}
//
//    /**
//     * Removes the current media category and all containing media.
//	 * @param boolean $recurse If true child categories are included.
//	 * @return array
//     * @access protected
//     */
//	protected function _delete($recurse = true, $exclude_files = true) {
//
//		global $I18N;
//
//		$status = true;
//		
//		// delete recrusive
//		if ($recurse) {
//			if ($this->hasChildren()) {
//				$childs = $this->getChildren();
//				foreach ($childs as $child) {
//					$child->_delete($recurse,$exclude_files);
//				}
//			}
//		}
//		// delete all containig files
//		if ($this->hasFiles()) {
//		    
//		    if (!$exclude_files) {
//    			$files = $this->getFiles();
//    			foreach ($files as $file) {
//    				if (!$file->_delete()) {
//    				    $status = false;
//    				}
//    			}
//    			if (!status) return false;
//		    }
//		    else {
//		        cjoMessage::addError($I18N->msg("msg_mediacat_has_files", $this->getName()));
//		        return false;
//		    }
//		}
//
//		$sql = new cjoSql();
//		$results = $sql->getArray("SELECT * FROM ".TBL_FILE_CATEGORIES." WHERE id = ".$this->getId()." LIMIT 1");
//		$sql->flush();
//		$qry = "DELETE FROM ".TBL_FILE_CATEGORIES." WHERE id = ".$this->getId()." LIMIT 1";
//		if ($sql->statusQuery($qry, $I18N->msg('msg_mediacat_deleted', $this->getName()))) {
//    		cjoExtension::registerExtensionPoint('MEDIA_CATEGORY_UPDATED', $results[0]);
//    		return true;
//		}
//		return false;
//	}

	/**
     * Returns a string representation of this object
     * for debugging purposes.
     * @return string
     * @access public
     */
	public function __toString() {
		return 'OOMediaCategory, "'.$this->getId().'", "'.$this->getName().'"'."<br/>\r\n";
	}
}