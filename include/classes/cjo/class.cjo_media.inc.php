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
 * cjoMedia class
 *
 * The cjoMedia class includes the main functionality
 * to handle media in the backend.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoMedia {

    public static function getCategoryId() {
        if (!cjoProp::get('MEDIA_CATEGORY_ID'))
            cjoProp::set('MEDIA_CATEGORY_ID', cjo_post('media_category','int', cjo_get('media_category','int', cjo_session('MEDIA_CATEGORY'))));

        return cjoProp::get('MEDIA_CATEGORY_ID');
    }

    /**
     * Regenerates the crop settings of all media.
     * @param int $crop_num the current crop number
     * @return array success messages
     * @access public
     */
    public static function resetAllMedia($crop_num = -1) {

    	$count_accepted = 0;
    	$max_script_time = get_cfg_var('max_execution_time');

    	$cur_file_id = isset($_GET['cur_file_id']) ? $_GET['cur_file_id'] : 0;

    	$sql = new cjoSql();
    	$qry = "SELECT DISTINCT *
               FROM ".TBL_FILES."
               WHERE file_id >'".$cur_file_id."'";

    	$effected_files = $sql->getArray($qry);

    	foreach (cjoAssistance::toArray($effected_files) as $file) {

    		if ($file['filename'] == '') continue;

    		$left_time = $max_script_time - cjoTime::showScriptTime();

    		if ($left_time < 10) {

    			$_GET['cur_file_id'] = $file['file_id'];

    			ob_end_clean();

                echo '<pre>'.cjoAddon::translate(8,'msg_wait_while_generating_images').'</pre>';
                echo '<script type="text/javascript">'."\r\n";
                echo 'location.reload();'."\r\n";
                echo '</script>'."\r\n";
                die();
    		}

    		if (cjoMedia::resetMedia($file, $crop_num) === false) continue;
    		$count_accepted++;
    	}

    	cjoMessage::addSuccess(cjoI18N::translate("msg_all_media_repaired",
    	                       count($effected_files),
    	                       $count_accepted));

        cjoExtension::registerExtensionPoint('SPECIALS_RESET_MEDIA');      	                               
    }

    /**
     * Regenerates the given crop setteng of the given media.
     * @param array $file
     * @param int $crop_num the current crop number
     * @return boolean
     * @access public
     */
    public static function resetMedia($file, $crop_num = -1) {

    	if (!OOMedia::isAvailable($file['filename'])) return false;
    	
        $fullpath = cjoPath::media($file['filename']);
        
        $imagesize = @getimagesize($fullpath);
        $filesize = filesize($fullpath);  
        $filetime = filemtime($fullpath); 
            
        $update = new cjoSql();
        $update->setTable(TBL_FILES);
        $update->setWhere("file_id='".$file['file_id']."'");
        
        if (OOMedia :: isImageType($file['filetype'])) {
    
        	if (cjoAddon::isActivated('image_processor')) {
        		if (resizecache :: is_conflict_memory_limit($fullpath)) {
        			return false;
        		}
        		$crop = cjoImageProcessor::initCropValues($imagesize);
        	}
    
        	if ($crop_num == -1 &&
        		$imagesize[0] == $file['width'] &&
        		$imagesize[1] == $file['height'] && (
        		$file['crop_1'] != '' ||
        		$file['crop_2'] != '' ||
        		$file['crop_3'] != '' ||
        		$file['crop_4'] != '' ||
        		$file['crop_5'] != '')){
        		return false;
        	}
    
        	if ($filesize != '')
        		$update->setValue("filesize", $filesize);
    
        	if ($crop_num == -1 || $crop_num == 1)
        		$update->setValue("crop_1", $crop[1]);
    
        	if ($crop_num == -1 || $crop_num == 2)
        		$update->setValue("crop_2", $crop[2]);
    
        	if ($crop_num == -1 || $crop_num == 3)
        		$update->setValue("crop_3", $crop[3]);
    
        	if ($crop_num == -1 || $crop_num == 4)
        		$update->setValue("crop_4", $crop[4]);
    
        	if ($crop_num == -1 || $crop_num == 5)
        		$update->setValue("crop_5", $crop[5]);
        		
        	@cjoImageProcessor::unlinkCached($file['filename']);
        }
        
    	$update->setValue("width", $imagesize[0]);
    	$update->setValue("height", $imagesize[1]);
        $update->setValue("filesize", $filesize);   
        $update->setValue("createdate", $filetime); 
    	$update->setValue("updatedate", time());
    	$update->setValue("updateuser", cjoProp::getUser()->getValue("name"));
    	return $update->Update();
    }
    
    

    /**
     * Moves media files to a different media category.
     * @param array|string $filenames
     * @param int $target_id
     * @param int $parent_id
     * @param string $delimiter
     * @return array success messages
     * @access public
     */
    public static function moveMedia($filenames, $target_id=0, $parent_id=0, $delimiter='|') {

    	if (!cjoProp::getUser()->hasMediaPerm($parent_id) ||
    		!cjoProp::getUser()->hasMediaPerm($target_id) ||
            cjoProp::getUser()->hasPerm('editContentOnly[]')) {
    		cjoMessage::addError(cjoI18N::translate('msg_no_rights'));
    		return false;
    	}
    	if ($target_id == $parent_id) {
    		cjoMessage::addError(cjoI18N::translate('msg_you_selected_the_current_category'));
    		return false;
    	}
    	if ($filenames == '') {
    		cjoMessage::addError(cjoI18N::translate('msg_no_files'));
    		return false;
    	}

    	$filenames = cjoAssistance::toArray($filenames, $delimiter);

    	foreach ($filenames as $filename) {
    		$update = new cjoSql();
    		$update->setTable(TBL_FILES);
    		$update->setWhere("filename='".$filename."' AND category_id='".$parent_id."'");
    		$update->setValue('category_id', $target_id);
    		$update->setValue("updatedate", time());
    		$update->setValue("updateuser", cjoProp::getUser()->getValue("name"));
    		$update->Update();

    		if ($update->getError() != ''){
    			cjoMessage::addError('<b>'.$filename.':</b> '.$update->getError());
    		}
    	}

    	if (cjoMessage::hasErrors()) {
    	    return false;
    	}
    	
        cjoExtension::registerExtensionPoint('MEDIA_MOVED',
                                      array ("filename" => $filename,
                                             "old_parent_id" => $parent_id,
                                             "new_parent_id" => $target_id));

    	cjoMessage::addSuccess(cjoI18N::translate('msg_all_files_moved'));
    	return true;
    }
    
    /**
     * Moves a media category and it's child categories to a different media category.
     * @param int id
     * @param int $target_id
     * @return array success messages
     * @access public
     */
    public static function moveMediaCategory($id, $target_id=0) {

    	if (!cjoProp::getUser()->hasMediaPerm($id) ||
    		!cjoProp::getUser()->hasMediaPerm($target_id) ||
            cjoProp::getUser()->hasPerm('editContentOnly[]')) {
    		cjoMessage::addError(cjoI18N::translate('msg_no_rights'));
    		return false;
    	}

    	$mediacat = OOMediaCategory::getCategoryById($id);
    	$target = OOMediaCategory::getCategoryById($target_id);
    	
    	if (!OOMediaCategory::isValid($mediacat)) {
    		cjoMessage::addError(cjoI18N::translate('msg_category_not_found'));
    		return false;
    	}
        
    	if (!OOMediaCategory::isValid($target) && $target_id != 0) {
    		cjoMessage::addError(cjoI18N::translate('msg_media_target_does_not_exist'));
    		return false;
    	}
    	
        $target_path = ($target_id > 0) ?  $target->_path.$target_id.'|' : "|";
        $target_name = ($target_id > 0) ?  $target->getName() : cjoI18N::translate('label_media_root');
    	$parent_id   = $mediacat->getParentId();
    	    	
        if ($target_id == $parent_id) {
    		cjoMessage::addError(cjoI18N::translate('msg_you_selected_the_current_category'));
    		return false;
    	}
    	
        if (strpos($target_path,'|'.$id.'|') !== false ||
    	    $parent_id == $target_id) {
    	    cjoMessage::addError(cjoI18N::translate('msg_error_move_media_cat_self', $mediacat->getName()));
    	    return false;
    	}
    	
		$update = new cjoSql();
		$update->setTable(TBL_FILE_CATEGORIES);
		$update->setValue('path', $target_path);
		$update->setValue('re_id', $target_id);
		$update->addGlobalUpdateFields();
		$update->setWhere('id="'.$id.'"');
		$update->Update(cjoI18N::translate("msg_media_cat_moved", $mediacat->getName(), $target_name));    	

    	if (cjoMessage::hasErrors()) {
    	    return false;
    	}
    	
        $curr_path = $mediacat->_path.$id."|";

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_FILE_CATEGORIES." WHERE PATH LIKE '".$curr_path."%'");

        $update = new cjoSql();
    	for ($i=0; $i < $sql->getRows(); $i++) {
    		// path aendern und speichern
    		$new_path = $target_path.$id."|".str_replace($curr_path, "", $sql->getValue("path"));

    		// make update
    		$update->flush();
    		$update->setTable(TBL_FILE_CATEGORIES);
    		$update->setWhere("id='".$sql->getValue("id")."'");
    		$update->setValue("path", $new_path);
    		$update->addGlobalUpdateFields();
    		$update->Update();
    		if($update->getError()) {
    		    cjoMessage::addError($update->getError());
    		}
    		$sql->next();
        }

        if (cjoMessage::hasErrors()) {
    	    return false;
    	}
    	
        cjoExtension::registerExtensionPoint('MEDIA_CATEGORY_MOVED',
                                      array ("id" => $id,
                                     		 "old_parent_id" => $parent_id,
    										 "new_parent_id" => $target_id));
    	return true;
    }

/**
     * Removes the a media category by the id.
     * @param int $id
     * @return array
     * @access public
     */
    public static function deleteMediaCategory($id, $recurse = true, $exclude_files = true) {

        $cat = OOMediaCategory :: getCategoryById($id);

        if (!is_object($cat)) {
            cjoMessage::addError(cjoI18N::translate("msg_category_not_found"));
            return false;
        }
        if (!cjoProp::getUser()->hasMediaPerm($id)) {
            cjoMessage::addError(cjoI18N::translate("msg_no_permissions"));
            return false;
        }
        return self::_deleteMediaCategory($cat, $recurse, $exclude_files);
    }

    /**
     * Removes the current media category and all containing media.
     * @param boolean $recurse If true child categories are included.
     * @return array
     * @access protected
     */
    protected static function _deleteMediaCategory($cat, $recurse = true, $exclude_files = true) {

        $status = true;
        
        // delete recrusive
        if ($recurse) {
            if ($cat->hasChildren()) {
                $childs = $cat->getChildren();
                foreach ($childs as $child) {
                    self::_deleteMediaCategory($child, $recurse, $exclude_files);
                }
            }
        }
        // delete all containig files
        if ($cat->hasFiles()) {
            
            if (!$exclude_files) {
                $files = $cat->getFiles();
                foreach ($files as $file) {
                    if (!$file->_delete()) {
                        $status = false;
                    }
                }
                if (!status) return false;
            }
            else {
                cjoMessage::addError(cjoI18N::translate("msg_mediacat_has_files", $cat->getName()));
                return false;
            }
        }

        $sql = new cjoSql();
        $results = $sql->getArray("SELECT * FROM ".TBL_FILE_CATEGORIES." WHERE id = ".$cat->getId()." LIMIT 1");
        $sql->flush();
        $qry = "DELETE FROM ".TBL_FILE_CATEGORIES." WHERE id = ".$cat->getId()." LIMIT 1";
        if ($sql->statusQuery($qry, cjoI18N::translate('msg_mediacat_deleted', $cat->getName()))) {
            cjoExtension::registerExtensionPoint('MEDIA_CATEGORY_DELETED', $results[0]);
            return true;
        }
        return false;
    }
    
    /**
     * Inserts or updates a media file.
     * @param int|string $file_id
     * @param string $filenames
     * @param int $media_category
     * @param array $fileinfos
     * @param string $delimiter
     * @return void
     * @access public
     */
    public static function saveMedia($file_id, $filenames='', $media_category=0, $fileinfos= array(), $delimiter='|') {

    	$message = array();

    	if (!cjoProp::getUser()->hasMediaPerm($media_category)){
    		cjoMessage::addError( cjoI18N::translate('msg_no_rights'));
    		return false;
    	}

    	$filenames = cjoAssistance::toArray($filenames, $delimiter);
    	$path = cjoPath::uploads();

    	if ($filenames == '') return false;

    	if ($file_id == 'add'){
    		$mode = 'insert';
    		$fileinfos = cjoAssistance::unserializeJquerySerialized($fileinfos);
    		$fileinfos['desc_serialized'] = serialize($fileinfos['description']);
    		$fileinfos['file_id'] = $file_id;
    		$fileinfos['title'] = $fileinfos['title'] == '' ? cjoI18N::translate('label_no_title') : $fileinfos['title'];
    	}
    	else {
    		$mode = 'update';
    		$media = OOMedia::getMediaById($file_id);
    		$filenames = (!is_array($filenames)) ? array($media->getFileName()) : $filenames;
    	}

    	foreach($filenames as $filename){

            $source = $path.'/'.$filename;
    		if (!file_exists($source) || $filename == ''){
    			cjoMessage::addError(cjoI18N::translate('msg_file_is_missing',$filename));
    			continue;
    		}
    		
    		
            $fileinfos['size'] = @getimagesize($source);
            
    		$fileinfos['filetype'] = $fileinfos['size']['mime'] 
    		                       ? $fileinfos['size']['mime'] 
    		                       : cjoMedia::detectMime($source);

    		if ($mode == 'insert'){

    			$fileinfos['filename'] = cjoMedia::generateNewMediaName($path, $filename);
    			$fileinfos['org_filename'] = $filename;
    			$fileinfos['filesize'] = filesize($source);

    			if (!@copy($path.'/'.$fileinfos['org_filename'], cjoPath::media($fileinfos['filename']))){
    				cjoMessage::addError(cjoI18N::translate('msg_file_copy_not_possible', $fileinfos['org_filename']));
    				continue;
    			}
    			@unlink($path.'/'.$filename);
    			@chmod(cjoPath::media($fileinfos['filename']), cjoProp::getFilePerm());

    			$insert = new cjoSql();
    			$insert->setTable(TBL_FILES);
    			$insert->setValue("filesize", $fileinfos['filesize']);
    			$insert->setValue("width", (int) $fileinfos['size'][0]);
    			$insert->setValue("height", (int) $fileinfos['size'][1]);
    			$insert->setValue("filename", $fileinfos['filename']);
    			$insert->setValue("originalname", $fileinfos['org_filename']);
    			$insert->setValue("title", (string) $fileinfos['title']);
    			$insert->setValue("description", $fileinfos['desc_serialized']);
    			$insert->setValue("copyright", (string) $fileinfos['copyright']);
    			$insert->setValue("category_id", $media_category);
    			$insert->setValue("filetype", $fileinfos['filetype']);
    			$insert->addGlobalCreateFields();
    			$insert->addGlobalUpdateFields();    			
    			$insert->Insert();

    			if ($insert->getError() == ''){
    				$fileinfos['file_id'] = $insert->getLastID();
    				cjoMedia::resetMedia($fileinfos);
    				cjoMessage::addSuccess(cjoI18N::translate('msg_file_saved', $fileinfos['org_filename']));
			        cjoExtension::registerExtensionPoint('MEDIA_ADDED', $fileinfos);
    			}
    			else {
    				cjoMessage::addError('<b>'.$fileinfos['org_filename'].':</b> '.$insert->getError());
    			}
    		}
    		else{

    			$fileinfos['file_id'] = $file_id;
    			$fileinfos['filename'] = $media->getFileName();
    			$fileinfos['org_filename'] = $filename;

    			if ($fileinfos['filetype'] != $media->getType()){
    				cjoMessage::addError(cjoI18N::translate('msg_different_media_type'));
    			}
    			if (!@copy($path.'/'.$fileinfos['org_filename'], cjoPath::media($fileinfos['filename']))){
    				cjoMessage::addError(cjoI18N::translate('msg_file_copy_not_possible', $fileinfos['org_filename']));
    			}
    			@unlink($path.'/'.$fileinfos['org_filename']);
    			@chmod(cjoPath::media($fileinfos['filename']), cjoProp::getFilePerm());

    			if (cjoMedia::resetMedia($fileinfos)){
    				cjoMessage::addSuccess(cjoI18N::translate('msg_file_saved', $fileinfos['org_filename']));
    				cjoExtension::registerExtensionPoint('MEDIA_UPDATED', $fileinfos);
                                                         
    			}
    			else {
    				cjoMessage::addError('<b>'.$fileinfos['org_filename'].':</b> '.$sql->getError());
    			}
    		}
    	}
    }

    /**
     * Generates a new name for each file
     * that is going to be inserted, in
     * order to avoid doubles and syntax problems.
     * @param string $path
     * @param string $filename
     * @return string the new filename
     * @access public
     */
    public static function generateNewMediaName($path, $filename){

    	$path_info = pathinfo($path.'/'.$filename);
    	$name = strtolower(substr($filename, 0, strrpos($filename, '.')));
    	$ext = strtolower(substr($filename, strrpos($filename, '.')));

    	$new_name = cjo_specialchars($name);
    	$new_name = str_replace(array('.',',',';','|','/','+','*',':',' '),'-', $name);
    	$new_name = preg_replace("/_{2,}/", "_", $new_name);

    	if (!in_array($ext, cjoProp::get('UPLOAD_EXTENSIONS'))){
    		$ext .= '.txt';
    	}

    	$new_filename = $new_name.$ext;

    	if (OOMedia::isAvailable($new_filename)) {
    		for ($i = 1; $i < 1000; $i++) {
    			$new_filename = $new_name."_".$i.$ext;
    			if (!OOMedia::isAvailable($new_filename)){
    				return $new_filename;
    			}
    		}
    	}
    	return $new_filename;
    }

    /**
     * Removes a media object by its name.
     * @param string $filename filename of the media
     * @return array success messages
     * @access public
     */
    public static function deleteByName($filename) {

        $media = OOMedia::getMediaByName($filename);

        if (!OOMedia::isValid($media)) return false;

        if (!cjoProp::getUser()->hasMediaPerm($media->getCategoryId()) ||
            cjoProp::getUser()->hasPerm('editContentOnly[]')) {
            cjoMessage::addError(cjoI18N::translate("msg_no_permissions"));
            return false;
        }

        $inuse = $media->isInUse();

        if ($inuse === false) {

            $sql = new cjoSql();
            $qry = "DELETE FROM ".TBL_FILES." WHERE file_id='".$media->getId()."' LIMIT 1";

            if (!$sql->setQuery($qry)) {
                cjoMessage::addError(cjoI18N::translate('msg_file_not_deleted', $sql->getError()));
                return false;
            }

            unlink(cjoPath::media($media->getFileName()));

            if (cjoAddon::isActivated('image_processor')) {
                cjoImageProcessor::unlinkCached($media->getFileName());
            }
            cjoMessage::addSuccess(cjoI18N::translate('msg_file_deleted', $filename));

            cjoExtension::registerExtensionPoint('MEDIA_DELETED', 
                                                 array ("id" => $media->getFileName(),
                                                        "filename" => $media->getFileName(),
                                                        "category_id" => $media->getCategoryId(),
                                                        "category_name" => $media->getCategoryName()));
                    
            return true;
        }
        elseif (is_array($inuse)) {

            $message  = cjoI18N::translate('msg_file_delete_error_1', $filename)." ";
            $message .= cjoI18N::translate('msg_file_delete_error_2')."<br/>";

            $_GET = array();

            $temp = array();

            foreach ($inuse as $value) {

                $value['article_name'] = (cjoProp::countCtypes() > 1)
                    ? $value['article_name'].' ('.cjoProp::getCtypeName($value['ctype']).')'
                    : $value['article_name'];

                $temp[] = cjoUrl::createBELink($value['article_name'],
                                            array ('page' => 'edit',
                                                   'subpage' => 'content',
                                                   'article_id' => $value['article_id'],
                                                   'mode' => 'edit',
                                                   'ctype' => $value['ctype'],
                                                   'clang' => cjoProp::getClang()),
                                            array (),
                                            'target="_blank"');
            }
            $message .= implode(' | ', $temp);

            cjoMessage::addError($message);
            return false;
        }
        return false;
    }

    /**
     * Removes a number of media objects by its names.
     * @param string|array $filenames
     * @param string $delimiter
     * @return array success messages
     * @access public
     */
    public static function deleteByNames($filenames = array(), $delimiter = '|') {

        if (!is_array($filenames)) {
            $filenames = cjoAssistance::toArray($filenames, $delimiter);
        }
        foreach ($filenames as $filename) {
            if ($filename == '') continue;
            cjoMedia::deleteByName($filename);
        }
        return true;
    }



    /**
     * Returns the description of the current media
     * depending on the language or of every language
     * as flag icons.
     * @param string $desc serialized description array
     * @param string $type possible 'alt' or 'flags'
     * @return string
     * @access public
     */
    public static function splitDescription($desc, $type = '') {

    	$output     = '';
    	$desc_array = (!is_array($desc)) ? unserialize($desc) : $desc;

    	foreach (cjoProp::getClangs() as $clang_id => $clang_name) {
    		if ($type == 'alt') {
    			return htmlspecialchars(stripslashes($desc_array[cjoProp::getClangIso($clang_id)]));
    		}
    		else if ($type == 'flags') {
    			if ($desc_array[$clang_id] != '')
    			$output .= '<img src="img/flags/'.cjoProp::getClangIso($clang_id).'.png"
    						alt="'.cjoProp::getClangIso($clang_id).'" title="'.
    						htmlspecialchars(stripslashes($desc_array[$clang_id])).'"
    						style="vertical-align: middle;" /> ';
    		}
    	}

    	return ($output != '')
    	? '<span class="media_desc">'.$output.'</span>'
    	: '<span class="media_desc">'.cjoI18N::translate('msg_pool_file_no_description').'</span>';
    }

    /**
     * Reads all files in the upload directory
     * including sub directories.
     * @param string $path
     * @return array
     * @access public
     */
    public static function getUploads($path=false){

    	if ($path === false) $path = cjoUrl::uploads();
    	
        $files = array();

        foreach(glob($path.'/*.*') as $file) {

            if (is_dir($file)) {

                $files = array_merge($files,cjoMedia::getUploads($file));
            }
            else {              
                self::removeSpecialCharsFromFilename($file, false);
                $files[] = $file ;
            }
        }

        natcasesort($files);
    	return $files;
    }
    
    /**
     * Removes special characters from uploaded files.
     * @param string $filename
     * @param bool $return_fullpath
     * @return void
     * @access private
     */
    private static function removeSpecialCharsFromFilename(&$filename, $return_fullpath = true) {
        
        $temp_array = explode('/',$filename);
        $temp_name = array_pop($temp_array);

        if ($temp_name != urlencode($temp_name)) {
            
            $new_name = str_replace(array(' ',), '-', utf8_encode($temp_name));
            $new_name = html_entity_decode($new_name);
            $new_name = cjo_specialchars($new_name);
            $new_name = preg_replace("/[^a-zA-Z\-0-9\.]/", "", $new_name);
            
            $new_path = implode('/',$temp_array).'/'.$new_name;

            @copy($filename, $new_path);
            @chmod($new_path, cjoProp::getFilePerm());
            @unlink($filename);

            $filename = $new_path;
        }
        
        if (!$return_fullpath)
            $filename = $temp_name;
    }

    /**
     * Generates the HTML for the media selection dialogs.
     *
     * @param string $filename
     * @param string $fullpath
     * @param boolean $upload
     * @param array $params
     * @return string
     * @access public
     */
    public static function getMediaContainer($filename, $fullpath=false, $upload=false, $params=array('width' => 80, 'height' => 80)){

    	$buttons    = '';
    	$attributes = '';
    	$css        = '';

    	if (!empty($params['css'])) {
    	    $css = $params['css'];
    	    unset($params['css']);
    	}

    	if (!$upload && ($filename == '' || !file_exists($fullpath))) return false;

    	if ($upload){
    		if ($fullpath == 'swfupload'){
    			$fullpath = cjoUrl::uploads($filename);
    			if (!file_exists($fullpath)) return false;
    			$css .= ' hide_me';
    		}
    		$buttons .= '<input name="delete_upload" type="image" title="'.cjoI18N::translate('label_delete').'" value="'.$filename.'" src="img/silk_icons/cross.png" />';
    	}
    	$buttons .= '<input name="preview_upload" type="image" title="'.cjoI18N::translate('label_preview').'" value="'.$filename.'" src="img/silk_icons/zoom.png" />';

    	$tbnl  = OOMedia::toThumbnail($filename, $fullpath, $params); // $file;

    	$title = (!$params['title']) ? cjoI18N::translate("label_select_file").': '.$filename : $params['title'];
    	$id	   = str_replace('.','_DOT_',$filename);

    	return sprintf('<div class="cjo_image_container%s" id="%s" title="%s">%s&nbsp;<b>%s</b><span>%s</span></div>', $css, $id, $title, $tbnl, $filename, $buttons);
    }

    public static function getCropSelection($name, $crop_id, $css='cjo_crop_select cjo_select_box') {

        OOMedia::getDefaultImageSizes();

    	if (empty($crop_id)){
    	    $crop_id = (strpos($name, 'zoom') !== false) 
    	             ? cjoProp::get('IMG_DEFAULT|zoom') 
    	             : cjoProp::get('IMG_DEFAULT|default');
    	}

        if (cjoProp::get('CROP_SELECT', false)) {
            $CROP_SELECT = new cjoSelect();
            $CROP_SELECT->setSize(1);
            $CROP_SELECT->setMultiple(false);
            $CROP_SELECT->addSqlOptions("SELECT name, id FROM ".TBL_IMG_CROP." WHERE status!=0 ORDER BY id");
            cjoProp::set('CROP_SELECT', $CROP_SELECT);
        }

        $sel = cjoProp::get('CROP_SELECT');

        $sel->setName($name);
        $sel->setStyle('class="'.$css.'"');
        $sel->addOption(cjoI18N::translate('label_use_original_size'), '-');
        $sel->setSelected($crop_id);
        return $sel->get();
    }

    public static function getBrandSelection($name, $value, $crop_id) {

    	$brand = '<input type="hidden" name="'.$name.'" value="off" />'."\r\n";

    	if (!cjoProp::get('IMAGE_LIST_BUTTON|BRAND_IMG', false)) return $brand;

    	$brand .= '<input type="checkbox" name="'.$name.'" value="on"'.
    			   cjo_input_check_checked($value, array('on')).
    			   cjo_input_check_disabled(crop_id, array('-')).' />'."\r\n".
    	          '	<label class="right">'.cjoI18N::translate('label_show_watermark').'</label>'."\r\n";

    	return $brand;
    }

    /**
     * Returns the MIME-Typ by the extension of a file.
     * @param string $fullpath
     * @param boolen $test_image
     * @return string
     * @access public
     */
    public static function detectMime($fullpath, $test_image=true) {

        if ($test_image === true && file_exists($fullpath)) {
        	$imagesize = @getimagesize($fullpath);
        	if (!empty($imagesize['mime'])){
        		return $imagesize['mime'];
        	}
        }

    	$ext = strrchr($fullpath, ".");
    	switch ($ext) {
    		case ".ez" :
    			$mime = "application/andrew-inset";
    			break;
    		case ".hqx" :
    			$mime = "application/mac-binhex40";
    			break;
    		case ".cpt" :
    			$mime = "application/mac-compactpro";
    			break;
    		case ".doc" :
    			$mime = "application/msword";
    			break;
    		case ".bin" :
    		case ".dms" :
    		case ".lha" :
    		case ".lzh" :
    		case ".exe" :
    		case ".class" :
    		case ".so" :
    		case ".dll" :
    		case ".gzip" :    
    		case ".gz" :     	
    		case ".sql" :         		    	    		    
    			$mime = "application/octet-stream";
    			break;
    		case ".oda" :
    			$mime = "application/oda";
    			break;
    		case ".pdf" :
    			$mime = "application/pdf";
    			break;
    		case ".ai" :
    		case ".eps" :
    		case ".ps" :
    			$mime = "application/postscript";
    			break;
    		case ".smi" :
    		case ".smil" :
    			$mime = "application/smil";
    			break;
    		case ".xls" :
    			$mime = "application/vnd.ms-excel";
    			break;
    		case ".ppt" :
    			$mime = "application/vnd.ms-powerpoint";
    			break;
    		case ".wbxml" :
    			$mime = "application/vnd.wap.wbxml";
    			break;
    		case ".wmlc" :
    			$mime = "application/vnd.wap.wmlc";
    			break;
    		case ".wmlsc" :
    			$mime = "application/vnd.wap.wmlscriptc";
    			break;
    		case ".bcpio" :
    			$mime = "application/x-bcpio";
    			break;
    		case ".vcd" :
    			$mime = "application/x-cdlink";
    			break;
    		case ".pgn" :
    			$mime = "application/x-chess-pgn";
    			break;
    		case ".cpio" :
    			$mime = "application/x-cpio";
    			break;
    		case ".csh" :
    			$mime = "application/x-csh";
    			break;
    		case ".dcr" :
    		case ".dir" :
    		case ".dxr" :
    			$mime = "application/x-director";
    			break;
    		case ".dvi" :
    			$mime = "application/x-dvi";
    			break;
    		case ".spl" :
    			$mime = "application/x-futuresplash";
    			break;
    		case ".gtar" :
    			$mime = "application/x-gtar";
    			break;
    		case ".hdf" :
    			$mime = "application/x-hdf";
    			break;
    		case ".js" :
    			$mime = "application/x-javascript";
    			break;
    		case ".skp" :
    		case ".skd" :
    		case ".skt" :
    		case ".skm" :
    			$mime = "application/x-koan";
    			break;
    		case ".latex" :
    			$mime = "application/x-latex";
    			break;
    		case ".nc" :
    		case ".cdf" :
    			$mime = "application/x-netcdf";
    			break;
    		case ".sh" :
    			$mime = "application/x-sh";
    			break;
    		case ".shar" :
    			$mime = "application/x-shar";
    			break;
    		case ".swf" :
    			$mime = "application/x-shockwave-flash";
    			break;
    		case ".sit" :
    			$mime = "application/x-stuffit";
    			break;
    		case ".sv4cpio" :
    			$mime = "application/x-sv4cpio";
    			break;
    		case ".sv4crc" :
    			$mime = "application/x-sv4crc";
    			break;
    		case ".tar" :
    			$mime = "application/x-tar";
    			break;
    		case ".tcl" :
    			$mime = "application/x-tcl";
    			break;
    		case ".tex" :
    			$mime = "application/x-tex";
    			break;
    		case ".texinfo" :
    		case ".texi" :
    			$mime = "application/x-texinfo";
    			break;
    		case ".t" :
    		case ".tr" :
    		case ".roff" :
    			$mime = "application/x-troff";
    			break;
    		case ".man" :
    			$mime = "application/x-troff-man";
    			break;
    		case ".me" :
    			$mime = "application/x-troff-me";
    			break;
    		case ".ms" :
    			$mime = "application/x-troff-ms";
    			break;
    		case ".ustar" :
    			$mime = "application/x-ustar";
    			break;
    		case ".src" :
    			$mime = "application/x-wais-source";
    			break;
    		case ".xhtml" :
    		case ".xht" :
    			$mime = "application/xhtml+xml";
    			break;		
    		case ".zip" :
    			$mime = "application/zip";
    			break;
    		case ".au" :
    		case ".snd" :
    			$mime = "audio/basic";
    			break;
    		case ".mid" :
    		case ".midi" :
    		case ".kar" :
    			$mime = "audio/midi";
    			break;
    		case ".mpga" :
    		case ".mp2" :
    		case ".mp3" :
    			$mime = "audio/mpeg";
    			break;
    		case ".aif" :
    		case ".aiff" :
    		case ".aifc" :
    			$mime = "audio/x-aiff";
    			break;
    		case ".m3u" :
    			$mime = "audio/x-mpegurl";
    			break;
    		case ".ram" :
    		case ".rm" :
    			$mime = "audio/x-pn-realaudio";
    			break;
    		case ".rpm" :
    			$mime = "audio/x-pn-realaudio-plugin";
    			break;
    		case ".ra" :
    			$mime = "audio/x-realaudio";
    			break;
    		case ".wav" :
    			$mime = "audio/x-wav";
    			break;
    		case ".pdb" :
    			$mime = "chemical/x-pdb";
    			break;
    		case ".xyz" :
    			$mime = "chemical/x-xyz";
    			break;
    		case ".bmp" :
    			$mime = "image/bmp";
    			break;
    		case ".gif" :
    			$mime = "image/gif";
    			break;
    		case ".ief" :
    			$mime = "image/ief";
    			break;
    		case ".jpeg" :
    		case ".jpg" :
    		case ".jpe" :
    			$mime = "image/jpeg";
    			break;
    		case ".png" :
    			$mime = "image/png";
    			break;
    		case ".tiff" :
    		case ".tif" :
    			$mime = "image/tiff";
    			break;
    		case ".djvu" :
    		case ".djv" :
    			$mime = "image/vnd.djvu";
    			break;
    		case ".wbmp" :
    			$mime = "image/vnd.wap.wbmp";
    			break;
    		case ".ras" :
    			$mime = "image/x-cmu-raster";
    			break;
    		case ".pnm" :
    			$mime = "image/x-portable-anymap";
    			break;
    		case ".pbm" :
    			$mime = "image/x-portable-bitmap";
    			break;
    		case ".pgm" :
    			$mime = "image/x-portable-graymap";
    			break;
    		case ".ppm" :
    			$mime = "image/x-portable-pixmap";
    			break;
    		case ".rgb" :
    			$mime = "image/x-rgb";
    			break;
    		case ".xbm" :
    			$mime = "image/x-xbitmap";
    			break;
    		case ".xpm" :
    			$mime = "image/x-xpixmap";
    			break;
    		case ".xwd" :
    			$mime = "image/x-xwindowdump";
    			break;
    		case ".igs" :
    		case ".iges" :
    			$mime = "model/iges";
    			break;
    		case ".msh" :
    		case ".mesh" :
    		case ".silo" :
    			$mime = "model/mesh";
    			break;
    		case ".wrl" :
    		case ".vrml" :
    			$mime = "model/vrml";
    			break;
    		case ".css" :
    			$mime = "text/css";
    			break;
    		case ".html" :
    		case ".htm" :
    			$mime = "text/html";
    			break;
    		case ".asc" :
    		case ".txt" :
    			$mime = "text/plain";
    			break;
    		case ".rtx" :
    			$mime = "text/richtext";
    			break;
    		case ".rtf" :
    			$mime = "text/rtf";
    			break;
    		case ".sgml" :
    		case ".sgm" :
    			$mime = "text/sgml";
    			break;
    		case ".tsv" :
    			$mime = "text/tab-separated-values";
    			break;
    		case ".wml" :
    			$mime = "text/vnd.wap.wml";
    			break;
    		case ".wmls" :
    			$mime = "text/vnd.wap.wmlscript";
    			break;
    		case ".etx" :
    			$mime = "text/x-setext";
    			break;
    		case ".xml" :
    		case ".xsl" :
    			$mime = "text/xml";
    			break;
    		case ".mpeg" :
    		case ".mpg" :
    		case ".mpe" :
    			$mime = "video/mpeg";
    			break;
    		case ".qt" :
    		case ".mov" :
    			$mime = "video/quicktime";
    			break;
    		case ".mxu" :
    			$mime = "video/vnd.mpegurl";
    			break;
    		case ".avi" :
    			$mime = "video/x-msvideo";
    			break;
    		case ".movie" :
    			$mime = "video/x-sgi-movie";
    			break;
    		case ".asf" :
    		case ".asx" :
    			$mime = "video/x-ms-asf";
    			break;
    		case ".wm" :
    		case ".wmv" :
    			$mime = "video/x-ms-wmv";
    			break;
    		case ".wvx" :
    			$mime = "video/x-ms-wvx";
    			break;
    		case ".flv" :
    			$mime = "video/x-flv";
    			break;
    		case ".mpeg4":
    		case ".mp4" :    			
    		case ".f4v" :
    			$mime = "video/mp4";
    			break;  
    		case ".webm" :
    			$mime = "video/webm";
    			break;  
    		case ".ogg" :    			
    		case ".ogv" :
    			$mime = "video/ogg";
    			break;      			
    		case ".ice" :
    			$mime = "x-conference/x-cooltalk";
    			break;
    		case ".docm" :
    			$mime = "application/vnd.ms-word.document.macroEnabled.12";
    			break;
    		case ".docx" :
    			$mime = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
    			break;
    		case ".dotm" :
    			$mime = "application/vnd.ms-word.template.macroEnabled.12";
    			break;
    		case ".dotx" :
    			$mime = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
    			break;
    		case ".potm" :
    			$mime = "application/vnd.ms-powerpoint.template.macroEnabled.12";
    			break;
    		case ".potx" :
    			$mime = "application/vnd.openxmlformats-officedocument.presentationml.template";
    			break;
    		case ".ppam" :
    			$mime = "application/vnd.ms-powerpoint.addin.macroEnabled.12";
    			break;
    		case ".ppsm" :
    			$mime = "application/vnd.ms-powerpoint.slideshow.macroEnabled.12";
    			break;
    		case ".ppsx" :
    			$mime = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
    			break;
    		case ".pptm" :
    			$mime = "application/vnd.ms-powerpoint.presentation.macroEnabled.12";
    			break;
    		case ".pptx" :
    			$mime = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
    			break;
    		case ".xlam" :
    			$mime = "application/vnd.ms-excel.addin.macroEnabled.12";
    			break;
    		case ".xlsb" :
    			$mime = "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
    			break;
    		case ".xlsm" :
    			$mime = "application/vnd.ms-excel.sheet.macroEnabled.12";
    			break;
    		case ".xlsx" :
    			$mime = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    			break;
    		case ".xltm" :
    			$mime = "application/vnd.ms-excel.template.macroEnabled.12";
    			break;
    		case ".xltx" :
    			$mime = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
    			break;
    		case ".odt" :
    			$mime = "application/vnd.oasis.opendocument.text";
    			break;
    		case ".ott" :
    			$mime = "application/vnd.oasis.opendocument.text-template";
    			break;
    		case ".oth" :
    			$mime = "application/vnd.oasis.opendocument.text-web";
    			break;
    		case ".odm" :
    			$mime = "application/vnd.oasis.opendocument.text-master";
    			break;
    		case ".odg" :
    			$mime = "application/vnd.oasis.opendocument.graphics";
    			break;
    		case ".otg" :
    			$mime = "application/vnd.oasis.opendocument.graphics-template";
    			break;
    		case ".odp" :
    			$mime = "application/vnd.oasis.opendocument.presentation";
    			break;
    		case ".otp" :
    			$mime = "application/vnd.oasis.opendocument.presentation-template";
    			break;
    		case ".ods" :
    			$mime = "application/vnd.oasis.opendocument.spreadsheet";
    			break;
    		case ".ots" :
    			$mime = "application/vnd.oasis.opendocument.spreadsheet-template";
    			break;
    		case ".odc" :
    			$mime = "application/vnd.oasis.opendocument.chart";
    			break;
    		case ".odf" :
    			$mime = "application/vnd.oasis.opendocument.formula";
    			break;
    		case ".odb" :
    			$mime = "application/vnd.oasis.opendocument.database";
    			break;
    		case ".odi" :
    			$mime = "application/vnd.oasis.opendocument.image";
    			break;
    		case ".oxt" :
    			$mime = "application/vnd.openofficeorg.extension";
    			break;
    		case ".sxw" :
    			$mime = "application/vnd.sun.xml.writer";
    			break;
    		case ".stw" :
    			$mime = "application/vnd.sun.xml.writer.template";
    			break;
    		case ".sxc" :
    			$mime = "application/vnd.sun.xml.calc";
    			break;
    		case ".stc" :
    			$mime = "application/vnd.sun.xml.calc.template";
    			break;
    		case ".sxd" :
    			$mime = "application/vnd.sun.xml.draw";
    			break;
    		case ".std" :
    			$mime = "application/vnd.sun.xml.draw.template";
    			break;
    		case ".sxi" :
    			$mime = "application/vnd.sun.xml.impress";
    			break;
    		case ".sti" :
    			$mime = "application/vnd.sun.xml.impress.template";
    			break;
    		case ".sxg" :
    			$mime = "application/vnd.sun.xml.writer.global";
    			break;
    		case ".sxm" :
    			$mime = "application/vnd.sun.xml.math";
    			break;
    	}
    	return $mime;
    }
}