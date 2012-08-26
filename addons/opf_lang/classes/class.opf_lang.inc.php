<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  languagefilter
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

class cjoOpfLang {

    public static function translate($content)
    {
    	global $CJO;

    	if (is_array($content) &&
    	    isset($content['subject']))
    	    $content = $content['subject'];

    	$search = array();
    	$replace = array();

    	if (!preg_match('/\[translate\:([\d|\w|\s].*?)\]/', $content)) return $content;

    	$sql = new cjoSql();
    	$qry = "SELECT replacename, name FROM ".TBL_OPF_LANG." WHERE clang='".$CJO['CUR_CLANG']."'";
    	$result = $sql->getArray($qry);

    	foreach($result as $value) {
    	    $search[] = $value['replacename'];
    	    $replace[] = $value['name'];
    	}

    	$content = str_replace($search, $replace, $content);

    	return $content;
    }


    public static function addClang($params) {

    	global $CJO;
    	$new_id = $params['id'];

    	$results = array();
    	$replaces = array();

    	$sql = new cjoSql();
    	$qry = "SELECT * FROM ".TBL_OPF_LANG." WHERE clang='0' OR clang='".$new_id."' ORDER BY replacename";
    	$results = $sql->getArray($qry);

    	foreach ($results as $result){
    		$replaces[$result['replacename']][$result['clang']] = $result;
    	}

    	foreach ($replaces as $replacename=>$replace){

    		if(empty($replace[$new_id])){
    			// neue Relpace-Variablen hinzufügen
    			$insert = new cjoSql();
    			$insert->setTable(TBL_OPF_LANG);
    			$insert->setValue("name",$replace[0]['name']);
    			$insert->setValue("replacename",$replacename);
    			$insert->setValue("clang",$new_id);
    			$insert->setValue("status",$replace[0]['status']);
    			$insert->insert();
    		}
    	}
    }

    function updateLangVars($table){

        global $CJO, $I18N;

        $old_replace_names = array();
        $old_replace_status = array();
        $check_tables = array();
        $content = '';

        //Tabelen in denen nicht nach Variablen gesucht werden soll
        $not_tables = array(TBL_ACTIONS,
                            TBL_ARTICLES_CAT_GROUPS,
                            TBL_ARTICLES_TYPE,
                            TBL_CLANGS,
                            TBL_FILES,
                            TBL_FILE_CATEGORIES,
                            TBL_IMG_CROP,
                            TBL_MODULES_ACTIONS,
                            TBL_MODULES,
                            TBL_TEMPLATES,
                            TBL_USER,
                            TBL_OPF_LANG);

        cjoOpfLang::getDir($CJO['FRONTPAGE_PATH'], $content);
        cjoOpfLang::getDir($CJO['ADDON_PATH'], $content);

        // Auslesen der alten Relpace-Variablen
        $sql = new cjoSql();
        $qry = "SELECT replacename, status FROM ".$table." WHERE clang='0'";
        $sql->setQuery($qry);

        for ($i=0;$i<$sql->getRows();$i++){
            $replace_name = $sql->getValue('replacename');
            $status = $sql->getValue('status');

            $old_replace_names[$replace_name] = $replace_name;
            $old_replace_status[$replace_name] = $status;
            $sql->next();
        }

        // Auslesen der Tabellen aus der aktuellen Datenbank
        $sql->flush();
        $sql->setQuery("SHOW TABLES");

        for ($i=0;$i<$sql->getRows();$i++){
            $curr_table_name = $sql->getValue('Tables_in_'.$sql->connection['db_name']);

              // Array schreiben mit Tabellen in denen gesucht werden soll
            if (!in_array($curr_table_name, $not_tables)) {
                  $check_tables[] = $curr_table_name;
            }
            $sql->next();
        }

        // Einlesen aller Spaltennamen aus den betroffenen Tabellen
        foreach ($check_tables as $curr_table){
            $sql->flush();
            $qry = "SHOW COLUMNS FROM ".$curr_table." WHERE Type LIKE 'varchar%' OR Type LIKE 'text%'";
            $sql->setQuery($qry);
            $columns = $sql->getArray();


            // Einlesen der Tabelleninhalte
            $sql->flush();

            $where = "";
            // Erstellen der WHERE-Klausel zur Beschränkung auf betroffene Spalten
            foreach($columns as $column){
                $where .= ($where == "") ? "WHERE\r\n" : " OR\r\n";
                $where .= $column['Field']." LIKE '%[translate: %' ";
            }

            $qry = "SELECT * FROM ".$curr_table." ".$where;
            $sql->setQuery($qry);

            if($sql->getRows() != 0){
                $temp_results = $sql->getArray();
                for ($i=0;$i<$sql->getRows();$i++){
                    $content .= implode(" ", $temp_results[$i]);
                }
            }
        }

        // Ausfiltern aller vorhandener Relpace-Variablen via RegEx
        preg_match_all('/\[translate\:([\d|\w|\s].*?)\]/', $content, $temp, PREG_SET_ORDER);


        $matches = array();
        foreach($temp as $key => $val) {
            if (!empty($I18N->text[$val[1]])) continue;
            $matches[$val[1]] = $val[0];
        }

        //Updaten der Relpace-Variablen
        foreach($matches as $name => $replacename) {

            $update = new cjoSql();
            $insert = new cjoSql();

            if (strpos($I18N->msg($name), '[translate:') === false) continue;

            if (empty($old_replace_names[$replacename])) {

                foreach ($CJO['CLANG'] as $key=>$val) {
                    // neue Relpace-Variablen hinzufügen
                    $insert->setTable($table);
                    $insert->setValue("name",$name);
                    $insert->setValue("replacename",$replacename);
                    $insert->setValue("clang",$key);
                    $insert->Insert();
                    $insert->flush();
                }
            }
            elseif (empty($old_replace_status[$replacename])) {
                // Status vorhandener Relpace-Variablen von 0 auf 1 setzen
                $update->setTable($table);
                $update->setWhere("replacename LIKE '".$replacename."'");
                $update->setValue("status","1");
                $update->Update();
                $update->flush();
            }
            // Löschen der bereits verarbeiteten Replace-Variablen aus dem Array
            unset($old_replace_names[$replacename]);
        }

        // Status nicht verwendeter Relpace-Variablen auf 0 setzen
        $where = "";
        foreach($old_replace_names as $replace_name) {
            $where .= ($where == "") ? "" : " OR\r\n";
            $where .= "replacename LIKE '".$replace_name."' ";
        }
        if ($where != '') {
            $update = new cjoSql();
            $update->setTable($table);
            $update->setWhere($where);
            $update->setValue("status","0");
            $update->Update();
        }
    }

    public static function getDir($dir, &$content) {

        global $CJO;
        $tmpl_file_type = $CJO['TMPL_FILE_TYPE'];
        if(substr($dir, -1) == '/') $dir = substr($dir, 0, -1);
        while($filenames = glob($dir . '/*')) {
            $dir .= '/*';
            foreach ($filenames as $filename) {
                if (preg_match('/^.*\.(php|'.$tmpl_file_type.')+$/i',$filename) &&
                    !preg_match('/^.*input\.(php|'.$tmpl_file_type.')+$/i',$filename)) {
                    $content .= file_get_contents($filename);
                }
            }
        }
    }
}