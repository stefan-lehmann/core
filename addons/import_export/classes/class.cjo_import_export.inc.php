<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  import_export
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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

class cjoImportExport {

    private static $addon = 'import_export';

    function __construct(){
        return false;
    }

    /**
     * Importiert den SQL Dump $filename in die Datenbank
     *
     * @param string Pfad + Dateinamen zur SQL-Datei
     *
     * @return array Gibt ein Assoc. Array zurück.
     *               'state' => boolean (Status ob fehler aufgetreten sind)
     *               'message' => Evtl. Status/Fehlermeldung
     */
    public static function importSqlFile($filename, $replace_cjo = false) {

    	if ($filename == '' || !file_exists($filename)) {
    		cjoMessage::addError(cjoAddon::translate(3,'err_no_import_file_chosen_or_wrong_version'));
    		return false;
    	}
    	
        $add = new cjoSql();
        
    	$conts = file_get_contents($filename);
  
    	// Versionsstempel prüfen
    	// ## CONTEJO Database Dump Version x.x
    	$cjo_version = strpos($conts, "## CONTEJO Database Dump Version ".cjoProp::get('VERSION'));

    	if ($cjo_version === false) {
    		cjoMessage::addError(cjoAddon::translate(3,"err_no_valid_import_file")."<br/>
    							 <b>## CONTEJO Database Dump Version ".cjoProp::get('VERSION')."</b>");
    		return false;
    	}
    	else {
    		// Versionsstempel entfernen
    		$conts = trim(preg_replace("/## CONTEJO Database Dump Version ".cjoProp::get('VERSION').".*$/", "", $conts));
    	}

    	// Prefix prüfen
    	// ## Prefix cjo_
    	$cjo_prefix = strpos($conts, "## Prefix ". cjoProp::getTablePrefix());
        
    	$conts = str_replace("%CUR_DATABASE%", cjoProp::get('DB|'.$add->DBID.'|NAME'), $conts);
    	
    	if ($replace_cjo) {
    		$conts = trim(str_replace("## Prefix cjo_", "", $conts));
    		$conts = str_replace("TABLE cjo_","TABLE ".cjoProp::getTablePrefix(),$conts);
    		$conts = str_replace("INTO cjo_","INTO ".cjoProp::getTablePrefix(),$conts);
    		$conts = str_replace("EXISTS cjo_","EXISTS ".cjoProp::getTablePrefix(),$conts);
    	}
    	elseif ($cjo_prefix === false) {
    		cjoMessage::addError(cjoAddon::translate(3,"err_no_valid_import_file").".
    							 [## Prefix ". cjoProp::getTablePrefix() ."] does not
    							 match config in master.inc.php");
    		return false;
    	}
    	else {
    		// Prefix entfernen
    		$conts = trim(str_replace("## Prefix ". cjoProp::getTablePrefix(), "", $conts));
    	}

    	// Ordner /generated komplett leeren
    	cjoAssistance::deleteDir(cjoPath::generated('articles'),false);
    	cjoAssistance::deleteDir(cjoPath::generated('templates'),false);

    	// Datei aufteilen
    	$lines = explode("\n", $conts);

    	//$add->debugsql = 1;
    	foreach ($lines as $line) {
    		$line = trim($line,"\r;");
    	    if(empty($line) || substr($line,0,1) == '#') continue;
    		if (!$add->setDirectQuery($line)) {
    		    cjoMessage::addError($add->getError());
    		}
    		$add->flush();
    	}
    	if (cjoMessage::hasErrors()) return false;
    	
    	cjoMessage::addSuccess(cjoAddon::translate(3,"msg_database_imported").". ".
    	                       cjoAddon::translate(3,"msg_entry_count", count($lines)));

    	// CLANG Array aktualisieren
    	cjoProp::remove('CLANG');
        $lang = array();
    	$sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS);
    	for ($i = 0; $i < $sql->getRows(); $i++) {
    		$lang[$sql->getValue("id")] = $sql->getValue("name");
    		$sql->next();
    	}
        cjoProp::set('CLANG',$lang);

    	// prüfen, ob eine user tabelle angelegt wurde
    	$result = $sql->getArray('SHOW TABLES');
    	$user_table_found = false;
    	foreach ($result as $row) {
    		if (in_array(TBL_USER, cjoAssistance::toArray($row))) {
    			$user_table_found = true;
    			break;
    		}
    	}

    	if (!$user_table_found) {

    		$create_user_table = '
    	      CREATE TABLE IF NOT EXISTS `cjo_user` (
    	      `user_id` int(11) NOT NULL auto_increment,
    	      `name` varchar(255) NOT NULL,
    	      `description` text NOT NULL,
    	      `login` varchar(50) NOT NULL,
    	      `psw` varchar(50) NOT NULL,
    	      `status` varchar(5) NOT NULL,
    	      `rights` text collate utf8_unicode_ci NOT NULL,
    	      `login_tries` tinyint(4) NOT NULL default 0,
    	      `lasttrydate` int(11) NOT NULL default 0,
    	      `session_id` varchar(255) NOT NULL,
    	      `createuser` varchar(255) collate NOT NULL,
    	      `updateuser` varchar(255) collate NOT NULL,
    	      `createdate` int(11) NOT NULL default 0,
    	      `updatedate` int(11) NOT NULL default 0,
    	      PRIMARY KEY  (`user_id`)
    	    ) ENGINE=MyISAM;';
    		if(!$add->setQuery($create_user_table)) {
    			cjoMessage::addError($add->getError());
    		}
    	}

    	// generated neu erstellen, wenn kein Fehler aufgetreten ist
        cjoGenerate::generateAll();

    	//  EXTENSION POINT
    	cjoExtension::registerExtensionPoint('SQL_IMPORTED');

    	return true;
    }

    /**
     * Importiert das Tar-Archiv $filename in den Ordner /files
     *
     * @param string Pfad + Dateinamen zum Tar-Archiv
     *
     * @return array Gibt ein Assoc. Array zurück.
     *               'state' => boolean (Status ob fehler aufgetreten sind)
     *               'message' => Evtl. Status/Fehlermeldung
     */
    public static function importTarFile($filename) {

    	if ($filename == '') {
    		cjoMessage::addError(cjoAddon::translate(3,"err_no_import_file_chosen"));
    		return false;
    	}

    	// Ordner /files komplett leeren
//    	if (file_exists($CJO['MEDIAFOLDER'])) {
//    		@unlink ($CJO['MEDIAFOLDER'].'_old');
//    		if (!rename($CJO['MEDIAFOLDER'], $CJO['MEDIAFOLDER'].'_old')) {
//    			cjoMessage::addError(cjoAddon::translate(3,"err_import_failed_mediafolder_not_deletable",$CJO['MEDIAFOLDER']));
//    			return false;
//    		}
//    	}

    	$tar = new tar;
    	$tar->openTAR($filename);
    	if (!$tar->extractTar()) {

    		$message_temp = cjoAddon::translate(3,"err_while_extracting")."<br/>";

    		if (count($tar->message) > 0) {
    			$message_temp .= cjoAddon::translate(3,"err_create_dirs_manually")."<br/>";
    			reset($tar->message);

    			for ($fol = 0; $fol < count($tar->message); $fol++) {
    				$message_temp .= cjoFile::absPath(str_replace("'", "", key($tar->message)))."<br/>";
    				next($tar->message);
    			}
    		}
    		cjoMessage::addError($message_temp);
    		return false;
    	}

    	cjoMessage::addSuccess(cjoAddon::translate(3,"msg_file_imported")."<br/>");
    	return true;
    }

    /**
     * Erstellt einen SQL Dump, der die aktuellen Datebankstruktur darstellt
     * @return string SQL Dump der Datenbank
     */
    public static function generateSqlExport($export_tables=false) {
    	
        $dump = '';
                
        $sql  = new cjoSql();
        $cont = new cjoSql();

    	$header  = "## CONTEJO Database Dump Version ".cjoProp::get('VERSION')." [.".cjoProp::get('RELEASE')."]\r\n";
    	$header .= "## Prefix ". cjoProp::getTablePrefix() ."\r\n\r\n";
    
        $exec_place_holder = '[___'.md5($header).'___]';

        $header .= "## User           ". cjoProp::getUser()->getValue("name") ."[".cjoProp::getUser()->getValue("login")."]\r\n";    	
        $header .= "## Origin-DB      ". cjo_server('REMOTE_ADDR','string') ."\r\n";    	
        $header .= "## Server         ". cjo_server('SERVER_NAME','string') ."\r\n";    
        $header .= "## Date           ". strftime(cjoI18N::translate('dateformat_sort')) ."\r\n";        
        $header .= "## Execution-Time ". $exec_place_holder."\r\n\r\n";
    	$header .= "## TABLES INCLUDED\r\n";    
        
        $tables_in_db = cjoSql::showTables();
        
        if (!is_array($export_tables)) {
            $export_tables = $tables_in_db;
        }

        foreach($export_tables as $key=>$export_table) {
            if (!in_array($export_table,$tables_in_db)) {
                unset($export_tables[$key]);
                cjoMessage::addWarning(cjoAddon::translate(3,'err_table_not_found', $export_table));
                $header .= "## [ERR] ".$export_table ."\r\n";                    
            }
            else {
                $header .= "## [OK]  `".$export_table ."`\r\n";
            }
        }
        $header .= "\r\n## ---------------------------------------------------------------------------------------------------------------------- \r\n"; 
    	
    	foreach($export_tables as $export_table) {
    	    
			$sql->flush();
			$sql->setQuery("LOCK TABLE `".$export_table."` READ"); 
			$columns = $sql->getArray("SHOW FULL COLUMNS FROM `".$export_table."`");
			$query = "DROP TABLE IF EXISTS `".$export_table."`;\nCREATE TABLE `".$export_table."` (";
			$key = array ();
            $temp = array();
			// Spalten auswerten
			foreach ($columns as $column) {
			    
				$colname   = '`'.$column['Field'].'`';
				$coltype   = $column['Type'];
				$colnull   = ($column['Null'] == 'YES') ? "NULL" : "NOT NULL";
				$collation = (!empty($column['Collation'])) ? 'COLLATE '.$column['Collation'] : '';
                $colextra  = $column['Extra'];
                
				// Default Werte
				if (!empty($column['Default'])) {
					 (strpos($coltype, 'varchar') !== false) ? "DEFAULT '".$sql->getValue("Default")."' " : "DEFAULT ".$sql->getValue("Default")." ";
				}
				else {
					$coldef = "";
				}

				if (strtoupper($column['Key']) == 'PRI') {
					$key[] = $colname;
					$colnull = "NOT NULL";
				}

				$temp[] = $colname.' '.$coltype.' '.$collation.' '.$colnull.' '.$coldef.' '.$colextra;
			}
            $query .= implode(', ',$temp);
			// Primürschlüssel Auswerten
			if (count($key) > 0) {
				$temp = array();
				for ($k = 0, reset($key); $k < count($key); $k++, next($key)) { // <-- yeah super for schleife, rock 'em hard :)
					$temp[] = current($key);
				}
				$query .= ", PRIMARY KEY(".implode(',',$temp).")";
			}
			$query .= ") ENGINE = MyISAM;";
			$dump .= "\r\n".$query."\r\n";

			// Inhalte der Tabelle Auswerten
			$cont->flush();
			$cont->setQuery("SELECT * FROM ".$export_table);

			if ($cont->getRows() > 0) {
			    $dump .= "LOCK TABLES `".$export_table."` WRITE;"."\r\n";
			
    			for ($j = 0; $j < $cont->getRows(); $j++, $cont->next()) {
    				$temp = array();
    				foreach ($columns as $column) {
    					$con = $cont->getValue($column['Field']);
    					$temp[] = (is_numeric($con)) ? "'".$con."'" : "'".addslashes($con)."'";
    				}
    				$query = "INSERT INTO `".$export_table."` VALUES (".implode(',',$temp).");";
    				$dump .= str_replace(array ("\r\n","\n"), '\r\n', $query)."\n";
    			}
                $dump .= "UNLOCK TABLES;"."\r\n";    		
			}
		}
        $sql->setQuery('UNLOCK TABLES'); 
    	// Versionsstempel hinzufügen
    	$dump = str_replace("\r", "", $dump);

    	$header = str_replace($exec_place_holder, (intval((cjoTime::getCurrentTime() - cjoProp::get('SCRIPT_START_TIME')) * 1000) / 1000).' sec', $header);

    	return $header . $dump;
    }

    /**
     * Exportiert alle Ordner $folders aus dem Verzeichnis /files
     *
     * @param array Array von Ordnernamen, die exportiert werden sollen
     * @param string Pfad + Dateiname, wo das Tar File erstellt werden soll
     *
     * @access public
     * @return string Inhalt des Tar-Archives als String
     */
    public static function generateTarExport($folders, $filename, $ext = '.tar.gz') {

    	$tar = new tar;
    	foreach ($folders as $key => $item) {
    		cjoImportExport::addFolderToTar($tar, cjoPath::frontend(), $key);
    	}

    	$content = $tar->toTarOutput($filename.$ext, true);
    	return $content;
    }

    /**
     * Fügt einem Tar-Archiv ein Ordner von Dateien hinzu
     * @access protected
     */
    private static function addFolderToTar(&$tar, $path, $dir) {

    	$handle = opendir($path.$dir);
    	$array_indx = 0;
    	#$tar->addFile($path.$dir."/",TRUE);
    	while (false !== ($file = readdir($handle))) {
    		$dir_array[$array_indx] = $file;
    		$array_indx++;
    	}

    	foreach ($dir_array as $n) {
    		if (($n != '.') && ($n != '..') && ($n != '.svn')) {
    			if (is_dir($path.$dir."/".$n)) {
    				cjoImportExport::addFolderToTar($tar, $path.$dir."/", $n);
    			}
    			if (!is_dir($path.$dir."/".$n)) {
    				$tar->addFile($path.$dir."/".$n, true);
    			}
    		}
    	}
    }


    /**
     * Returns the content of the given folder
     *
     * @param $dir Path to the folder
     * @return Array Content of the folder or false on error
     */
     public static function readFolder($dir) {

        if (!is_dir($dir)) {
            throw new cjoException('Folder "'.$dir.'" is not available or not a directory');
            return false;
        }
        $hdl = opendir($dir);
        $folder = array ();
        while (false !== ($file = readdir($hdl))) {
            $folder[] = $file;
        }
        return $folder;
    }

    /**
     * Returns the content of the given folder.
     * The content will be filtered with the given $fileprefix
     *
     * @param $dir Path to the folder
     * @param $fileprefix Fileprefix to filter
     * @return Array Filtered-content of the folder or false on error
     */
    public static function readFilteredFolder($dir, $fileprefix) {
        $filtered = array ();
        $folder = cjoImportExport::readFolder($dir);
        if (!$folder) return false;
        foreach ($folder as $file) {
            if (endsWith($file, $fileprefix)) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }

    /**
     * Returns the files of the given folder.
     *
     * @param $dir Path to the folder
     * @return Array Files of the folder or false on error
     */
    public static function readFolderFiles($dir) {
        $folder = cjoImportExport::readFolder($dir);
        $files = array ();
        if (!$folder) return false;
        foreach ($folder as $file) {
            if (is_file($dir.'/'.$file)) {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * Returns the subfolders of the given folder
     *
     * @param $dir Path to the folder
     * @param $ignore_dots True if the system-folders ".", ".." and ".svn" should be ignored
     * @return Array Subfolders of the folder or false on error
     */
    public static function readSubFolders($dir, $ignore_dots = true) {
        $folder = cjoImportExport::readFolder($dir);
        $folders = array ();
        if (!$folder) return false;
        foreach ($folder as $file) {
            if ($ignore_dots && ($file == '.' || $file == '..' || $file == '.svn')) {
                continue;
            }
            if (is_dir($dir.'/'.$file)) {
                $folders[] = $file;
            }
        }
        return $folders;
    }

    public static function getImportDir() {
        return cjoPath::addonAssets(self::$addon);
    }

    public static function readImportFolder($fileprefix) {

    	$dir = cjoImportExport::getImportDir();
        $temp = cjoImportExport::readFilteredFolder(cjoImportExport::getImportDir(), $fileprefix);

        $folders = array();

        foreach($temp as $file) {
    	    $time = filemtime( $dir.'/'.$file);
    	    $folders[$time] = $file;
        }
        ksort($folders);

    	return $folders;
    }
}