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
 * cjoAssistance class
 *
 * The cjoAssistance class contains assisting methods.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoAssistance {

    /**
     * Convertes a relative path to an absolute path.
     * @param string $rel_path the relative path
     * @return string
     * @access public
     */
    public static function absPath($rel_path) {

        $path = realpath('.');
        $stack = explode(DIRECTORY_SEPARATOR, $path);

        foreach (explode('/', $rel_path) as $dir) {
            if ($dir == '.') {
                continue;
            }

            if ($dir == '..') {
                array_pop($stack);
            } else {
                array_push($stack, $dir);
            }
        }
        return implode('/', $stack);
    }

    /**
     * Tells whether a file exists and is readable.
     * If not adds an error message.
     * @param string $filename
     * @return boolean
     * @access public
     */
    public static function isReadable($filename) {

        global $I18N;

        if (!is_readable($filename)) {
            cjoMessage::addError($I18N->msg('msg_file_not_found',
            cjoAssistance::absPath($filename)));
            return  false;
        }
        return true;
    }


    /**
     * Tells whether a file exists and is writeable.
     * If not adds an error message.
     * @param string $filename
     * @return boolean
     * @access public
     */
    public static function isWritable($filename) {

        global $I18N;

        if (@is_dir($filename)) {
            if (!@is_writable($filename."/.")) {
                cjoMessage::addError($I18N->msg("msg_folder_no_chmod",
                cjoAssistance::absPath($filename)));
                return false;
            }
        }
        elseif (@is_file($filename)) {
            if (!@is_writable($filename)) {
                cjoMessage::addError($I18N->msg("msg_file_no_chmod",
                cjoAssistance::absPath($filename)));
                return false;
            }
        } else {
            cjoMessage::addError($I18N->msg("msg_not_existing",
            cjoAssistance::absPath($filename)));
            return false;
        }
        return true;
    }

    public static function validateLinks(){

    	global $CJO, $I18N;
        $articles = array();
        $slices   = array();
        $clangs   = array();
        $ctypes   = array();
        $names    = array();
        $sql      = new cjoSql();

        for($j=1; $j<=10; $j++) {

    		$qry = "SELECT
    			sl.article_id AS article_id,
    			(SELECT name FROM ".TBL_ARTICLES." WHERE id=sl.article_id AND clang=sl.clang) AS name,
    			sl.id AS slice_id,
    			sl.clang AS clang_id,
    			sl.ctype AS ctype_id
    		  FROM
    		   	".TBL_ARTICLES_SLICE." sl
    	      LEFT JOIN
    	       	".TBL_ARTICLES." ar
    	      ON
    	       	sl.link".$j."=ar.id
    		  WHERE
    			sl.link".$j.">0 AND ar.id IS NULL";

    	    $sql->flush();
    		$results = $sql->getArray($qry);

    		foreach($results as $result) {
    			$articles[$result["article_id"]] = 1;
    			$slices[$result["article_id"]] = $result["slice_id"];
    			$clangs[$result["article_id"]] = $result["clang_id"];
    		    $ctypes[$result["article_id"]] = $result["ctype_id"];
    		    $names[$result["article_id"]] = $result["name"];
    		}
        }

        for($j=1; $j<=19; $j++) {

    	    $sql->flush();
    		$qry = "SELECT
        				sl.article_id AS article_id,
        				sl.id AS slice_id,
        				(SELECT name FROM ".TBL_ARTICLES." WHERE id=sl.article_id AND clang=sl.clang) AS name,
        				sl.value".$j." AS value,
        				sl.clang AS clang_id,
        				sl.ctype AS ctype_id
            		FROM
            		   	".TBL_ARTICLES_SLICE." sl
            		WHERE
            			REPLACE(sl.value".$j." REGEXP 'contejo://[0-9]{1,}', 'contejo://', '') > 0";

    		$results = $sql->getArray($qry);

            foreach($results as $result) {
                
                if (!empty($result)) continue;
                
                preg_match_all("/(contejo:)\/\/([0-9]*)(\.([0-9]*))*/im", $result['value'], $matches, PREG_SET_ORDER);

                foreach($matches as $match){
                    if ($match[2]) {
                        $sql->flush();
                        $sql->setQuery("SELECT id FROM ".TBL_ARTICLES." WHERE id='".$match[2]."'");
                        if ($sql->getRows() > 0) continue;
            			$articles[$result["article_id"]] = 1;
            			$slices[$result["article_id"]] = $result["slice_id"];
            			$clangs[$result["article_id"]] = $result["clang_id"];
            		    $ctypes[$result["article_id"]] = $result["ctype_id"];
    		            $names[$result["article_id"]] = $result["name"];
                    }
                }
    		}
        }

    	$temp = array();
    	foreach($articles as $id => $val){
    		$temp[] = cjoAssistance::createBELink(
			                    '<b>'.$names[$id].'</b> (ID='.$id.')',
    							array('page'=> 'edit',
    								  'subpage' => 'content',
    								  'function'=>'edit',
    								  'mode'=>'edit',
    								  'article_id' => $id,
    								  'slice_id'=>$slices[$id],
    								  'clang'=>$clangs[$id],
    								  'ctype'=>$ctypes[$id],
    								  '#'=> 'slice'.$slices[$id]),
    							array(),
                                'title="'.$I18N->msg("button_edit").'"');
    	}
    	
    	if (empty($articles)){
    		cjoMessage::addSuccess($I18N->msg("msg_links_ok"));
    	}
    	else{
    	   cjoMessage::addError($I18N->msg("msg_links_not_ok")."<br/> ".implode(' | ',$temp));
    	}
    	
        cjoExtension::registerExtensionPoint('SPECIALS_LINKS_VALIDATED');
    }

    /**
     * Makes a copy of a file. If the destination file already exists,
     * it will be renamed.
     * @param string $source path to the source file
     * @param string $dest destination path
     * @params bool $backup If destination file already exists Create backup or overwrite it
     * @return boolean
     * @access public
     */
    public static function copyFile($source, $dest, $backup = true) {

        global $CJO, $I18N;

        $dest_path = pathinfo($dest, PATHINFO_DIRNAME);

        if (!self::isReadable($source) ||
            !self::isWritable($dest_path)) return false;

        if ($backup && file_exists($dest)) {
            if (!rename($dest, $dest.'_'.date('Y-m-d_H-i-s'))) {
                cjoMessage::addError($I18N->msg('msg_err_copy_file', $source, $dest));
                return false;
            }
        }
        if (!copy($source, $dest)) {
    		cjoMessage::addError($I18N->msg('msg_err_copy_file', $source, $dest));
    		return false;
    	}

    	@chmod($dest,$CJO['FILEPERM']);
    	return true;
    }

    /**
     * Makes a copy of a directory including subdirectories.
     * @param string $source path to the source file
     * @param string $dest destination path
     * @param string $overwrite overwrite existing files
     * @param int $offset offset count for the possibilty that it somehow miscounts the files
     * @param bool $verbose
     * @return string
     */
    public static function copyDir($srcdir, $dstdir, $overwrite=false, $offset = '', $verbose = false) {

    	// A function to copy files from one directory to another one, including subdirectories and
    	// nonexisting or newer files. Function returns number of files copied.
    	// This function is PHP implementation of Windows xcopy  A:\dir1\* B:\dir2 /D /E /F /H /R /Y
    	// Syntaxis: [$returnstring =] dircopy($sourcedirectory, $destinationdirectory [, $offset] [, $verbose]);
    	// Example: $num = dircopy('A:\dir1', 'B:\dir2', 1);

    	// Original by SkyEye.  Remake by AngelKiha.
    	// Linux compatibility by marajax.
    	// ([danbrown AT php DOT net): *NIX-compatibility noted by Belandi.]
    	// Offset count added for the possibilty that it somehow miscounts your files.  This is NOT required.
    	// Remake returns an explodable string with comma differentiables, in the order of:
    	// Number copied files, Number of files which failed to copy, Total size (in bytes) of the copied files,
    	// and the files which fail to copy.  Example: 5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
    	// If you feel adventurous, or have an error reporting system that can log the failed copy files, they can be
    	// exploded using the | differentiable, after exploding the result string.
    	//
    	global $CJO, $I18N;

    	if (!isset($offset)) $offset=0;
    	$num = 0;
    	$fail = 0;
    	$sizetotal = 0;
    	$fifail = '';
    	if (!is_dir($dstdir)) mkdir($dstdir,$CJO['FILEPERM']);
    	if ($curdir = opendir($srcdir)) {
    		while($file = readdir($curdir)) {
    			if ($file != '.' && $file != '..' && $file != '.svn') {
    				$srcfile = $srcdir . '/' . $file;    # added by marajax
    				$dstfile = $dstdir . '/' . $file;    # added by marajax
    				if (is_file($srcfile)) {
    					if (is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
    					if ($overwrite || $ow > 0) {
    						if ($verbose) echo "Copying '$srcfile' to '$dstfile'...<br />";
    						if (copy($srcfile, $dstfile)) {
    							touch($dstfile, filemtime($srcfile)); $num++;
    							chmod($dstfile, $CJO['FILEPERM']);    # added by marajax
    							$sizetotal = ($sizetotal + filesize($dstfile));
    							if ($verbose) echo "OK\n";
    						}
    						else {
    							cjoMessage::addError($I18N->msg('msg_err_copy_file', $srcfile, $dstfile));
    							$fail++;
    							$fifail = $fifail.$srcfile.'|';
    						}
    					}
    				}
    				else if (is_dir($srcfile)) {
    					$res = explode(',',$ret);
    					$ret = self::copyDir($srcfile, $dstfile, $verbose); # added by patrick
    					$mod = explode(',',$ret);
    					$imp = array($res[0] + $mod[0],$mod[1] + $res[1],$mod[2] + $res[2],$mod[3].$res[3]);
    					$ret = implode(',',$imp);
    				}
    			}
    		}
    		closedir($curdir);
    	}
    	$red = explode(',',$ret);
    	$ret = ($num + $red[0]).','.(($fail-$offset) + $red[1]).','.($sizetotal + $red[2]).','.$fifail.$red[3];
    	return $ret;
    }


    /**
     * Deletes a file or a directory recursively.
     * @param string $file
     * @param boolean $delete_folders if true deletes directories too
     * @param array $exclude filenames to exclude from delete
     * @return boolean
     * @access public
     */
    public static function deleteDir($file, $delete_folders = false, $exclude = array()) {

        $state = true;

        if (!file_exists($file)) return false;

        if (@is_dir($file)) {

            $handle = opendir($file);
            if (!$handle) return false;

            while ($filename = readdir($handle)) {
                if (in_array($filename, array('.','..','.svn','.gitignore')) || (
                    !empty($exclude) &&
                    in_array(OOMedia::getExtension($filename), $exclude))) continue;

                if (($state = cjoAssistance::deleteDir($file."/".$filename, $delete_folders)) !== true) {
                    // Schleife abbrechen, dir_hanlde schließen und danach erst false zurückgeben
                    break;
                }
            }
            closedir($handle);

            if ($state !== true) return false;

            // remove folders to?
            if ($delete_folders) {
                if (!@rmdir($file)) return false;
            }
        } else {
            // delete file
            if (!@unlink($file)) return false;
        }
        return true;
    }

    /**
     * Resets the containers $hidden, $fields, $cols and $datasets.
     * @return void
     * @access public
     */
    public static function resetAfcVars() {

        global $hidden, $fields, $cols, $dataset;

        $hidden 	= array();
        $fields 	= array();
        $cols 		= array();
    }

    /**
     * Unserializes a string of parameters serialized by jQuery.
     * @param string $values
     * @return array
     * @access public
     */
    public static function unserializeJquerySerialized($values) {

        $output = array();
        
        if (is_array($values)) return $values;

        foreach (cjoAssistance::toArray($values,"&") as $value) {

            $value = urldecode("$value");

            //split nach dem ersten Auftreten eines "=" damit der Text nicht falsch getrennt wird
            $value = preg_split('/=/', $value, 2, PREG_SPLIT_DELIM_CAPTURE);

            if (strpos($value[0], '[') === false) {
                $output[$value[0]] = $value[1];
            }
            else {
                $keys = array();
                $key[1] = substr($value[0], 0, strpos($value[0], '['));
                $key[2] = substr($value[0], strpos($value[0], '['));
                $key[2] = stripslashes(str_replace('"', "'", $key[2]));

                $call = "$"."output['".$key[1]."']".$key[2]." = '".$value[1]."';";
                eval($call);
            }
        }
        return $output;
    }

    /**
     * Universal method to update priority settings in a database table.
     *
     * @param string $table table of the database
     * @param identifier $id usually the id of the element
     * @param int $newprio the new priority
     * @param string $col identifier column name
     * @param int $parent_col column name for parent id
     * @return boolean
     * @access public
     */
    public static function updatePrio($table, $id=false, $newprio= 1000000000000, $col = 'id', $parent_col = false) {

        global $CJO, $I18N;
        
        $sql        = new cjoSql();
        $update     = new cjoSql();
        $columns    = cjoSql::getFieldNames($table);        
        $qry_add    = array();
        $upd_add    = '';
        $qry_where1 = '';
        $qry_where2 = '';

        if ($parent_col !== false && $id != false) {
            $sql->flush();
            $sql->setQuery("SELECT * FROM ".$table." WHERE ".$col."='".$id."'");
            $parent_col_id = $sql->getValue($parent_col);
            $qry_add[] = $parent_col."='".$parent_col_id."'";
        }
        if (in_array("clang", $columns)) {
            $qry_add[] = "clang='".$CJO['CUR_CLANG']."'";
            $upd_add = " AND clang='".$CJO['CUR_CLANG']."'";
        }
        if (!empty($qry_add)) {
            $qry_add = "WHERE ".implode(" AND ", $qry_add);
        }

        $qry_where1 = (in_array("cat_group", $columns) && $parent_col_id == 0) ? " cat_group," : "";
        $qry_where2 = (in_array("updatedate", $columns)) ? ", updatedate" : ", ".$col;

        if ($id) {
            $update->flush();
            $update->setTable($table);
            $update->setWhere($col."='".$id."'".$upd_add);
            $update->setValue("prior",$newprio);
            if (in_array("updatedate", $columns))
                $update->setValue("updatedate",time());
            $update->Update();

            if ($update->getError() != '') {
                cjoMessage::addError($I18N->msg('msg_prio_not_updated', $update->getError()));
                return false;
            }
        }

        $sql->flush();
        $sql->setQuery("SELECT ".$col." FROM ".$table." ".$qry_add." ORDER BY".$qry_where1." prior".$qry_where2." DESC");

        for ($i = 1; $i <= $sql->getRows(); $i++) {
            
            
                    
            $update->flush();
            $update->setTable($table);
            $update->setWhere($col."='".$sql->getValue($col)."'".$upd_add);
            $update->setValue("prior", $i);
            $update->Update();

            if ($update->getError() != '') {
                cjoMessage::addError($I18N->msg('msg_prio_not_updated', $update->getError()));
            }
            else {
                if ($table == TBL_ARTICLES) {
                    cjoExtension::registerExtensionPoint('ARTICLE_UPDATED', array ('ACTION' => 'PRIOR_UPDATED',
                                                                                   $col => $sql->getValue($col),
                                                                                   $parent_col => $parent_col_id,
                                                                                   "prior" => $i ));
                }
                else {
                    cjoExtension::registerExtensionPoint('PRIOR_UPDATED', array ("table" => $table,
                                                                                 $col => $sql->getValue($col),
                                                                                 $parent_col => $parent_col_id,
                                                                                 "prior" => $i ));
                }
            }
            $sql->next();
        }

        if (cjoMessage::hasErrors()) {
            return false;
        }

        cjoMessage::addSuccess($I18N->msg('msg_prio_updated'));
        return true;
    }

    /**
     * Converts a variable into an array.
     * @param mixed $value
     * @param string $delimiter
     * @return array
     * @access public
     */
    public static function toArray($value, $delimiter = '|') {

        if (is_object($value)) {
            return get_object_vars($value);
        }
        elseif (!is_array($value)) {
            return array_values(array_diff(explode($delimiter,$value), array('')));
        }
        else {
            return $value;
        }
    }

    /**
     * Dumps information about a variable.
     * @param mixed $variable
     * @param string $name
     * @param string $color
     * @param boolean $specialchars
     * @return void
     * @access public
     */
    public static function debug($variable, $name = '', $color = 'pink') {

        preg_match_all('/^.*$/m', print_r($variable, true), $lines);
        
        foreach($lines[0] as $key=>$line) {
            $lines[0][$key] = '<li style="color:#999"><div style="color:#000">'.htmlspecialchars($line).'</div></li>';
        }
        
        $out =  '<pre style="font-size: 12px; color:#000;background:'.$color.';padding:1em; '.
        		'z-index: 1000; overflow: visible;">'.
            	'<h3>'.$name.'</h3>'."\r\n".
                '<ol>'."\r\n".
                implode('',$lines[0])."\r\n".
                '</ol>'."\r\n".
          		'</pre><br/>';

        echo $out;
    }

    /**
     * Checks if a value exists in a multivalue string.
     * @param int|string $value
     * @param string $multivalues (eg. 'value1|value2|value3')
     * @param string $delimiter
     * @return boolean
     * @access public
     */
    public static function inMultival($value, $multivalues, $delimiter='|') {
        if (in_array($delimiter, array('|'))) $delimiter = '\\'.$delimiter;
        return preg_match('/(?<=^|'.$delimiter.')'.$value.'(?='.$delimiter.'|$)/',$multivalues);
    }

    /**
     * Cleans posted data.
     * @param mixed $data
     * @return mixed
     * @access public
     */
    public static function cleanInput($data) {

        if (is_array($data)) {
            $output = array();
            foreach ($data as $key => $value) {
                $output[$key] = cjoAssistance::cleanInput($value);
            }
        } else {

            $to_escape = array(';'   => '&#x3B;',
                               '#'   => '&#x23;',
                               '&'   => '&#x26;',
                               '\\'  => '&#x5C;',
                               '--'  => '&#45;&#45;',
                               '- -' => '&#45; &#45;',
                               '"'   => '&#x22;',
                               '$'   => '&#x24;',
                               '%'   => '&#x25;',
                               "'"   => '&#x27;',
                               '('   => '&#x28;',
                               ')'   => '&#x29;',
                               '/'   => '&#x2F;', 
                               '<'   => '&#x3C;',
                               '>'   => '&#x3E;',
                               '['   => '&#x5B;',
                               ']'   => '&#x5D;',
                               '`'   => '&#x60;',
                               '{'   => '&#x7B;',
                               '}'   => '&#x7D;',
                               '}'   => '&#x7D;',
                               '&#x26;#x23;' => '&#x23;',
                               '&#x26;&#x23;x3B;' => '&#x3B;');
            
            $output = trim($data);
            $output = strip_tags($output);
            $output = str_replace(array_keys($to_escape),$to_escape,$output);
            $output = preg_replace('#-\s+-#i',"",$output);
        }
        return $output;
    }

    /**
     * Checks if a value exists in an array.
     * On success checkes the checkbox or radiobutton.
     *
     * @param mixed $value
     * @param array $values
     * @param boolean $bol
     * @return string
     * @access public
     */
    public static function setChecked($value, $values=array(), $bool=true) {
        if ($bool == true) {
            return in_array($value, $values) ? ' checked="checked"': '';
        }
        else {
            return !in_array($value, $values) ? ' checked="checked"': '';
        }
    }

    /**
     * Checks if a value exists in an array.
     * On success disables the inputfield
     * @param mixed $value
     * @param array $values
     * @param boolean $bol
     * @return string
     * @access public
     */
    public static function setDisabled($value, $values=array(), $bool=true) {

        if (!is_array($values)) return false;

        if ($bool== true) {
            return in_array($value, $values) ? ' disabled="disabled"': '';
        }
        else {
            return !in_array($value, $values) ? ' disabled="disabled"': '';
        }
    }

    public static function redirectBE($params=array()) {

        if (is_array($params)){
            $url = cjoAssistance::createBEUrl($params);
        }
        return self::redirect($url);
    }

    /**
     * Generates a link.
     * @param string $link_text
     * @param array $local_params
     * @param array $global_params
     * @param string $link_tags
     * @return string
     * @access public
     */
    public static function createBELink($link_text, $local_params = array (), $global_params = array (), $link_tags = '') {

        if (count($local_params) == 0 || $link_text == '') return $link_text;

        if (!empty ($link_tags)) $link_tags = ' '.$link_tags;

        return sprintf('<a href="%s"%s>%s</a>', cjoAssistance::createBEUrl($local_params,
                       $global_params),
                       $link_tags,
                       $link_text);
    }

    /**
     * Generates an url.
     * @param array $local_params
     * @param array $global_params
     * @param string $ampersand
     * @return string
     * @access public
     */
    public static function createBEUrl($local_params = array (), $global_params = array (), $ampersand='&') {

        if (count($local_params) == 0) return $value;
        if (count($global_params) == 0) $global_params = cjo_a22_getDefaultGlobalParams();

        $_params      = array_merge($global_params, $local_params);
        $query_string = '?';
        $hash         = '';

        foreach ($_params as $_name => $_value){
            if ($_value == '' && $_value != '0') continue;
            if ($_name != '#'){
                if (strpos($query_string, $ampersand.$_name.'=') === false){
                    $query_string .= (strpos($query_string, '?') === false) ? '?' : $ampersand;
                    $query_string .= $_name.'='.$_value;
                }
                else {
                    $query_string = preg_replace('/('.$_name.')=(.*?(?=['.$ampersand.'|#|\s]))/','$1='.$_value, $query_string);
                }
            }
            else {
                $hash = $_name.$_value;
            }
        }

        $query_string = str_replace(array('?'.$ampersand, ' '),array('?', ''),$query_string);

        if (!empty($tags)) $tags = ' '. $tags;

        return 'index.php'.$query_string.$hash;
    }

    /**
     * Trennt einen String an Leerzeichen auf.
     * Dabei wird beachtet, dass Strings in " zusammengehören
     */
    public static function splitString($string) {

        $spacer = '###CJO-SPACER###';
        $result = array();

        $string = preg_replace('/\s{1,}/Ux', ' ', $string);
        $string = str_replace(array('„','“'), '"', $string);
        // Strings mit Quotes heraussuchen
        $pattern = '/(["\'])(.*)\1/U';
        preg_match_all($pattern, $string, $matches);
        $quoted = isset ($matches[2]) ? $matches[2] : array();

        // Strings mit Quotes maskieren
        $string = preg_replace($pattern, $spacer, $string);

        // ----------- z.b. 4 "av c" 'de f' ghi
        if (strpos($string, '=') === false) {

            $parts = explode(' ', $string);
            foreach ($parts as $part) {

                if (empty ($part)) continue;

                if ($part == $spacer) {
                    $result[] = array_shift($quoted);
                }
                else {
                    $result[] = $part;
                }
            }
        }
        // ------------ z.b. a=4 b="av c" y='de f' z=ghi
        else {

            $parts = explode(' ', $string);
            foreach ($parts as $part) {
                if (empty($part))  continue;

                $variable = explode('=', $part);

                if (empty ($variable[0]) ||
                    trim($variable[1]) == '') continue;

                $var_name = $variable[0];
                $var_value = $variable[1];

                if ($var_value == $spacer) {
                    $var_value = array_shift($quoted);
                }

                $result[$var_name] = $var_value;
            }
        }
        return $result;
    }

	public static function convertToFlags($iso_codes) {

		global $CJO;

		$flags = '';
		$path = "./img/flags/";

		$country_codes = cjo_get_country_codes();
		unset($country_codes[0]);

		$iso_codes = cjoAssistance::toArray($iso_codes);
		$iso_codes = array_unique($iso_codes);
		$temp = $iso_codes;
		$val1 = array_shift($temp);

        if (empty($country_codes[strtoupper($val1)])) {
            if (!is_array($iso_codes)) return false;
            $key1 = array_shift(array_keys($iso_codes));
            if (!empty($country_codes[strtoupper($key1)])) {
                $iso_codes = array_keys($iso_codes);
            }
            else {
                return false;
            }
        }

        foreach(cjoAssistance::toArray($iso_codes) as $val) {
		    $flags .= '<img src="'.$path.strtolower($val).'.png" alt="'.$val.'" title="'.$country_codes[strtoupper($val)].'" /> ';
		}
		return $flags;
	}


    /**
     * Returns a string with backslashes before characters that
     * need to be quoted in database queries etc.
     * @param string $string
     * @return string
     * @access public
     */
    public static function addSlashes($string) {

        $string = str_replace("\\", "\\\\", $string);
        $string = str_replace("\"", "\\\"", $string);
        return $string;
    }

    /**
     * Converts HTML-Code into plain text.
     * @param string $value
     * @param string $replace
     * @return string
     * @access public
     */
    public static function htmlToTxt($value, $replace=' ') {

        $search = array('/<script[^>]*?>.*?<\/script>/si',   // Strip out javascript
                        '/<style[^>]*?>.*?<\/style>/siU',    // Strip style tags properly
                        '/<\?((?!\?>).)*\?>/s',             // Strip out HTML tags
                        '/<[a-z ]*?\/>/is',             // Strip out HTML tags
                        '/<![\s\S]*?--[ \t\n\r]*>/'         // Strip multi-line comments including CDATA
        );

        return strip_tags(preg_replace($search, $replace, $value));
    }

    /**
     * Truncates as string.
     * @param string $string
     * @param int $maxlen length of the truncated string
     * @return string
     * @access public
     */
    public static function truncateString($string, $maxlen) {

        if (strlen($string)<=$maxlen) return $string;

        $parts= explode("\n", wordwrap($string, $maxlen, "\n"));
        return $parts[0].'...';
    }
    
    /**
     * Changes the name of a key in an array keeping the original position of the value. 
     * Works recrusive.
     * @param string $original_key original key that has to be replaced
     * @param string $new_key new key that replaces the original one
     * @param array $array
     * @return array 
     * @access public
     */
    public static function changeKeyInArray($original_key, $new_key, &$array) {
        $return = array();
        foreach ($array as $key => $value ) {
            $key = $key === $orig ? $new : $key;
            $return[$key] = ( is_array($value) ? self::changeKeyInArray($orig, $new, $value) : $value);
        }
        return $return;
    }
    
    /**
     * Detects the language of the client.
     * @param array $allowed allowed languages
     * @param string $default default language in case that detection fails
     * @param string|null $var language variable if null $_SERVER['HTTP_ACCEPT_LANGUAGE'] will bee used
     * @param bool $strict in strict mode 
     * @return string 
     * @access public
     */
    public static function getBrowserLang($allowed, $default, $var = null, $strict = true) {

         // $_SERVER['HTTP_ACCEPT_LANGUAGE'] verwenden, wenn keine Sprachvariable mitgegeben wurde
         if ($var === null) $var = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

         // Wurde irgendwelche Information mitgeschickt?
         // Nein? => Standardsprache zurückgeben
         if (empty($var)) return array_search($default, $allowed);

         // Den Header auftrennen
         $accepted_languages = preg_split('/,\s*/', $var);

         // Die Standardwerte einstellen
         $current_lang = $default;
         $current_q = 0;

         // Nun alle mitgegebenen Sprachen abarbeiten
         foreach ($accepted_languages as $key => $accepted_language) {
             // Alle Infos über diese Sprache rausholen
             $res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
                                '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);

             // war die Syntax gültig?
             // Nein? Dann ignorieren
             if (!$res) continue;

             // Sprachcode holen und dann sofort in die Einzelteile trennen
             $lang_code = explode ('-', $matches[1]);

             // Wurde eine Qualität mitgegeben?
             // Nein? Kompabilitätsmodus: Qualit�ät 1 annehmen
             $lang_quality = (isset($matches[2])) ? (float)$matches[2] : 1.0;

             // Bis der Sprachcode leer ist...
             while (count ($lang_code)) {
                     // mal sehen, ob der Sprachcode angeboten wird
                     foreach ($allowed as $key => $language) {
                         if (strpos(strtolower (join ('-', $lang_code)), $language) !== false){
                                 // Qualität anschauen
                                 if ($lang_quality > $current_q || !isset($current_q)) {
                                         // diese Sprache verwenden
                                         $current_lang = $key;
                                         $current_q = $lang_quality;
                                         // Hier die innere while-Schleife verlassen
                                         break;
                                 }
                         }
                     }
                     // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
                     // innere While-Schleife aufbrechen
                     if ($strict) break;

                     // den rechtesten Teil des Sprachcodes abschneiden
                     array_pop($lang_code);
             }
         }
         // die gefundene Sprache zurückgeben
         return $current_lang;
    }
    /**
    * Parses trough an directory and returns the containing
    * files and directories with their containing files as
    * a directory tree. Works recursive.
    *
    * @param $dir (string), default=false - the directory name
    * @param $tree (array(string)), default=empty array - the directory tree
    * @param $only_files (bool), default=true - displays only files if true
    * @param $limit (int), default=2 - the maximum depth of tree level
    * @param $level (int), default=1 - the tree starting level
    * @param $pattern (string), default=starts with a point - constructs the tree key for filename
    * @param $nbsp (string), default=&nbsp; - set a new line for every entry, tormatting matter
    * @param $arrow (string), default=&rarr - the directory level symbol
    */
    public static function parseDir($dir=false, $tree = array(), $only_files=true, $limit=2, $level=1, $pattern = '/.*/i', $nbsp='&nbsp;|', $arrow='&rarr;'){

    	global $CJO;
    	if (empty($dir)) return array();

    	$nbsp .= ($level == 1) ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|';

    	$handle = opendir($dir);
    	while (false!==($item = readdir($handle)))
    	{
    		if ($item == '.' || $item == '..' || $item == '.svn')  continue;

    		if (is_dir($dir.'/'.$item) &&
    			$level < $limit){

    			if ($only_files == false &&
    				preg_match($pattern, $item, $name)){
    				$name = $nbsp . $arrow . $name[0];
    				$tree[$name] = $dir.'/'.$item;
    			}
    			$tree = cjoAssistance::parseDir($dir.'/'.$item, $tree, $only_files, $limit, ($level+1), $pattern, $nbsp);
    		}
    		elseif ($only_files == true && preg_match($pattern, $item, $name)){
    			$name = $nbsp . $arrow . $name[0];
    			$tree[$name] = $dir.'/'.$item;
    		}
    	}
    	closedir($handle);
    	return $tree;
    }

    public static function redirectFE($article_id = 0, $clang = false, $params = '', $hash_string = '') {
        return self::redirect(cjoRewrite::getUrl($article_id, $clang, $params, $hash_string));
    }
    
    public static function redirect($url) {
        if (empty($url)) return false;
        // Alle OBs schließen
        while (ob_get_level() > 0){ ob_end_clean(); };

        header ('HTTP/1.1 301 Moved Permanently');
        header('Location: '.str_replace('&amp;','&',$url));
        exit();
    }
    
    public static function redirectAchor($params=false) {

        global $CJO;
        
        if (!is_array($params)) {
            parse_str(cjo_server('QUERY_STRING', 'string'), $params);
        }
        if (empty($params['cjo_anchor'])) return false;
        
        $anchor = $params['cjo_anchor'];
        $params['cjo_anchor'] = null;

        cjoAssistance::redirectFE($CJO['ARTICLE_ID'], $CJO['CUR_CLANG'], $params, $anchor);
    }    

    /**
     * Sets the startpage value from every article
     * in tbl_article that has no children to 0.
     *
     * @return bool - error or success
     */
    public static function repairStartPage(){

    	global $I18N;

    	// get articles that need to be repaired
    	$tbl = TBL_ARTICLES;
    	$sql = new cjoSql();
    	$qry = "SELECT
    				a.id AS id
    			FROM "
    				.$tbl." a
    			WHERE (	SELECT
    						COUNT(*)
    					FROM "
    						.$tbl." b
    					WHERE
    						a.id=b.re_id
    				   ) < 1
    			AND
    				a.startpage=1";

    	$results = $sql->getArray($qry);

    	$error = $sql->getError();
    	if ($error != '') {
    		cjoMessage::addError($I18N->msg('msg_start_page_not_repaired', $error));
    		return false;
    	}

    	if (empty($results)) {
    		cjoMessage::addSuccess($I18N->msg('msg_start_page_repaired'));
    		cjoExtension::registerExtensionPoint('SPECIALS_STARTPAGE_REPAIRED');
    		return true;
    	}

    	// build 'or'-string for update-query
    	$ors = array();
    	foreach($results as $result){
    		$ors[] = " id='".$result['id']."'";
    	}
    	$or= implode(" OR ", $ors);
    	$sql->setTable($tbl);
    	$sql->setValue('startpage', 1);
    	$sql->setWhere($or);
    	if ($sql->Update($I18N->msg('msg_start_page_repaired'))) {
            cjoExtension::registerExtensionPoint('SPECIALS_STARTPAGE_REPAIRED');
            return true;
    	}
    	return false;
    }
    
    /**
     * find files matching a pattern
     * using PHP "glob" function and recursion
     *
     * @return array containing all pattern-matched files
     *
     * @param string $dir     - directory to start with
     * @param string $pattern - pattern to glob for
     */
    public static function rglob($dir, $pattern='*'){
        
        if (empty($dir) || !self::isReadable($dir)) return array(); 
        
        // escape any character in a string that might be used to trick
        // a shell command into executing arbitrary commands
        $dir = @escapeshellcmd($dir);
        // get a list of all matching files in the current directory
        $files = glob("$dir/$pattern");

        // find a list of all directories in the current directory
        // directories beginning with a dot are also included
        foreach (glob("$dir/{.[^.]*,*}", GLOB_BRACE|GLOB_ONLYDIR) as $sub_dir){
            $arr   = self::rglob($sub_dir, $pattern);  // resursive call
            $files = array_merge($files, $arr); // merge array with files from subdirectory
        }
        // return all found files
        return $files;
    }
}