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

if (!$CJO['CONTEJO']) return false;

class cjoInstall {

	private $page;
	private $page_path;
	private $config_path;
	private $setup_path;
	private $sql;

    public  $error          = '';
  	public  $install_ids    = array();
    public  $has_components = false;
	public  $components     = array('actions'   => false,
        							'modules'   => false,
        							'html'      => false,
        							'templates' => false,
        							'articles'  => false,
        							'settings'  => false);

    public function __construct($page){

        global $CJO;

        $this->page        = $page;
        $this->page_path   = $CJO['ADDON_PATH'].'/'.$page;
        $this->setup_path  = $CJO['ADDON_PATH'].'/'.$page.'/setup';
        $this->config_path = $CJO['ADDON_CONFIG_PATH'].'/'.$page;
        $this->sql         = new cjoSql();
    }

    public function installResource($debug = false) {

        global $CJO;

        if (!cjoAssistance::isReadable($this->config_path) ||
            !cjoAssistance::isWritable($this->config_path)) return false;

        foreach(glob($this->setup_path.'/*') as $filepath) {

            if (!is_dir($filepath)) continue;
            $dir = pathinfo($filepath, PATHINFO_BASENAME);
            if ($this->components[$dir] !== false) continue;
            $this->components[$dir] = true;
            $this->has_components = true;
        }

        $install_sql = $this->setup_path.'/install.sql';

        if (cjoMessage::hasErrors()) return false;

        if ($this->has_components) {

            foreach($this->components as $key => $component) {

                if ($component !== true) continue;

                switch ($key) {
                      case 'actions'  : $this->installActions();      break;
                      case 'modules'  : $this->installModules();      break;
                      case 'html'     : $this->installHtml('input');
                                        $this->installHtml('output'); break;
                      case 'templates': $this->installTemplates();    break;
                      case 'articles' : $this->installArticles();     break;
                      case 'settings' : $this->installSettings();     break;
                }
            }
        }

        if (file_exists($install_sql)) {
            if (!cjoAssistance::isReadable($install_sql)) return false;
    	    if (!self::installDump($install_sql, $debug)) return false;
        }

	    $ext_name = 'ADDON_INSTALLED_'.strtoupper($this->page);

	    cjoExtension::registerExtensionPoint($ext_name, array("addon"          => $this->page,
                                                              "addon_path"     => $this->page_path,
                                                              "config_path"    => $this->config_path,
                                                              "setup_path"     => $this->setup_path,
                                                              "install_ids"    => $this->install_ids,
                                                              "has_components" => $this->has_components,
            												  "components"     => $this->components));
	    return true;
    }

    public function uninstallResource($debug = false) {

        global $CJO;

	    $un_install_sql = $this->setup_path.'/uninstall.sql';

        if (file_exists($un_install_sql)) {
            if (!cjoAssistance::isReadable($un_install_sql)) return false;
    	    if (!self::installDump($un_install_sql, $debug)) return false;
        }

        $ext_name = 'ADDON_UNINSTALLED_'.strtoupper($this->page);

	    cjoExtension::registerExtensionPoint($ext_name, array("addon"          => $this->page,
                                                              "addon_path"     => $this->page_path,
                                                              "config_path"    => $this->config_path));
        return true;
    }

	public function installActions(){

        $settings = $this->setup_path.'/actions/settings.php';

        if (!file_exists($settings) || !cjoAssistance::isReadable($settings)) return false;

	    include_once $settings;

	    if (empty($actions)) return false;
	    
	    $this->actions = & $actions;	    

	    $path = $this->setup_path.'/actions';
	    $db_actions = $this->sql->getArray("SELECT * FROM ".TBL_ACTIONS." ORDER BY id");

        foreach(glob($path.'/*.action.php') as $filepath) {

            $filename = pathinfo($filepath, PATHINFO_BASENAME);
        	$id = str_replace('.action.php', '', $filename);

        	if (empty($actions[$id])) continue;

        	$this->install_ids['actions'][$id] = false;
        	$action = file_get_contents($filepath);
        	$action_md5 = md5($action);

        	foreach($db_actions as $db_action) {
        	    if ($action_md5 == md5($db_action["action"]) &&
        	        $actions[$id]['prepost'] == $db_action['prepost'] &&
        	        $actions[$id]['sadd'] == $db_action['sadd'] &&
        	        $actions[$id]['sedit'] == $db_action['sedit'] &&
        	        $actions[$id]['sdelete'] == $db_action['sdelete']) {
        	        $this->install_ids['actions'][$id] = $db_action['id'];
        	        break;
        	    }
        	}

            if ($this->install_ids['actions'][$id] === false) {
                $this->sql->flush();
            	$this->sql->setTable(TBL_ACTIONS);
            	$this->sql->setValue("name", $actions[$id]['name']);
            	$this->sql->setValue("action",$action);
            	$this->sql->setValue("prepost", $actions[$id]['prepost']);
            	$this->sql->setValue("sadd", $actions[$id]['sadd']);
            	$this->sql->setValue("sedit", $actions[$id]['sedit']);
            	$this->sql->setValue("sdelete", $actions[$id]['sdelete']);
            	$this->sql->Insert();

            	if ($this->sql->getError()){
            		cjoMessage::addError($this->sql->getError());
            	}
            	else {
            	    $this->install_ids['actions'][$id] = $this->sql->getLastId();
            	}
            }
        }
	}

	public function installModules(){

        $settings = $this->setup_path.'/modules/settings.php';

        if (!file_exists($settings) || !cjoAssistance::isReadable($settings)) return false;

	    include_once $settings;

	    if (empty($modules)) return false;
	    
	    $this->modules = & $modules;

	    $path = $this->setup_path.'/modules';
	    $this->sql->flush();
	    $db_modules = $this->sql->getArray("SELECT * FROM ".TBL_MODULES." ORDER BY id");

        foreach(glob($path.'/*.input.php') as $filepath){

        	$id = preg_replace('#(.*/)(\S+)(\.input\.php)#', '\2', $filepath);

        	if (empty($modules[$id]) || !file_exists($path.'/'.$id.'.output.php')) continue;

        	$this->install_ids['modules'][$id] = false;
        	$input      = file_get_contents($path.'/'.$id.'.input.php');

        	$input_md5  = md5(preg_replace('/\s/', '', $input));
        	$output     = file_get_contents($path.'/'.$id.'.output.php');
        	$output_md5 = md5(preg_replace('/\s/', '', $output));

        	foreach($db_modules as $db_module) {

        	    if ($input_md5 == md5(preg_replace('/\s/', '', $db_module["input"])) &&
        	        $output_md5 == md5(preg_replace('/\s/', '', $db_module["output"]))) {
        	        $this->install_ids['modules'][$id] = $db_module['id'];
        	        break;
        	    }
        	}

            if ($this->install_ids['modules'][$id] !== false)
                continue;

        	if (strlen($id) == strlen((int)$id)) {
        	    $this->install_ids['modules'][$id] = $id;

        	    foreach($this->install_ids['modules'] as $module_id) {
        	        if ($module_id == $id ) {
        	            $this->install_ids['modules'][$id] = false;
        	            break;
        	        }
        	    }
                if ($this->install_ids['modules'][$id] != false) {
            	    foreach($db_modules as $db_module) {
            	        if ($db_module['id'] == $id ) {
            	            $this->install_ids['modules'][$id] = false;
            	            break;
            	        }
            	    }
                }
        	}

        	$this->sql->flush();
        	$this->sql->setTable(TBL_MODULES);
			if (empty($this->install_ids['modules'][$id])) {
			    $this->install_ids['modules'][$id] = $this->sql->setNewId("id");
			} else {
                $this->sql->setValue("id", $this->install_ids['modules'][$id]);
			}

            $this->sql->setValue("templates", (int) $modules[$id]['templates']);
        	$this->sql->setValue("name", $modules[$id]['name']);
        	$this->sql->setValue("input",$input);
        	$this->sql->setValue("output",$output);
        	$this->sql->addGlobalCreateFields();
        	$this->sql->addGlobalUpdateFields();
        	$this->sql->Insert();

            cjoAssistance::updatePrio(TBL_MODULES, $this->install_ids['modules'][$id], time());
            cjoMessage::removeLastSuccess();

        	if ($this->sql->getError()) {
        		cjoMessage::addError($this->sql->getError());
        		continue;
        	}

		    foreach(cjoAssistance::toArray($modules[$id]['actions']) as $action_id){

		        $this->sql->flush();
    			$this->sql->setQuery("SELECT id FROM ".TBL_MODULES_ACTIONS." WHERE module_id ='".$this->install_ids['modules'][$id]."' AND action_id='".$this->install_ids['actions'][$action_id]."'");

    			if ($this->sql->getRows() == 0) {
    				$this->sql->flush();
    				$this->sql->setTable(TBL_MODULES_ACTIONS);
    				$this->sql->setValue("module_id", $this->install_ids['modules'][$id]);
    				$this->sql->setValue("action_id", $this->install_ids['actions'][$action_id]);
    				$this->sql->Insert();
    			}
		    }
    	}
	}

	public function installHtml($type){

	    global $CJO;

	    $path = $this->setup_path.'/html';
	    $ext  = $CJO['TMPL_FILE_TYPE'];
	    $dir  = $CJO['ADDON']['settings']['developer']['edit_path'].'/'.$ext.'/'.$type;

	    foreach(glob($path.'/'.$type.'/*.'.$ext) as $filepath) {

	        $filename = pathinfo($filepath, PATHINFO_BASENAME);
	        if (!preg_match('#^(\S+)\.\S+?\.'.$ext.'#', $filename, $matches)) continue;

            $name = $matches[0];            
	        $id   = $matches[1];

	        
	        if ($this->install_ids['modules'][$id]) {
	            $name = explode('.', $name);
	            $name[0] = cjo_specialchars($this->modules[$id]['name']);
	            $name = $this->install_ids['modules'][$id].'.'.implode('.', $name);          
	        }
	        
	        $dest = $dir.'/'.$name;
	        cjoAssistance::copyFile($filepath, $dest);
	    }
	}

    public function installTemplates(){

        $settings = $this->setup_path.'/templates/settings.php';

        if (!file_exists($settings) || !cjoAssistance::isReadable($settings)) return false;

	    include_once $settings;

	    if (empty($templates)) return false;

	    $this->templates = & $templates;	 	    
	    
	    $path = $this->setup_path.'/templates';
	    $this->sql->flush();
	    $db_templates = $this->sql->getArray("SELECT * FROM ".TBL_TEMPLATES." ORDER BY id");

        foreach(glob($path.'/*.template.php') as $filepath){

        	$id = preg_replace('#(.*/)(\S+)(\.template\.php)#', '\2', $filepath);

        	if (empty($templates[$id])) continue;

        	$this->install_ids['templates'][$id] = false;
        	$template = file_get_contents($path.'/'.$id.'.template.php');

        	$template_md5 = md5(preg_replace('/\s|action_after_validation_\d+/','',$template));

        	foreach($db_templates as $db_template) {
                $db_template_md5 = md5(preg_replace('/\s|action_after_validation_\d+/','',$db_template["content"]));
        	    if ($template_md5 == $db_template_md5) {
        	        $this->install_ids['templates'][$id] = $db_template['id'];
        	        break;
        	    }
        	}

            if ($this->install_ids['templates'][$id] !== false) continue;

            $templates[$id]['content'] = $template;

            $this->install_ids['templates'][$id] = cjoTemplate::addTemplate($templates[$id]);
    	}
	}

	public function installArticles(){

        $path = $this->setup_path.'/articles';

        if (!cjoAssistance::isReadable($path)) return false;

        foreach(glob($path.'/*.article.php') as $filepath){
            if (!cjoAssistance::isReadable($filepath)) continue;

            $id = preg_replace('#(.*/)(\S+)(\.article\.php)#', '\2', $filepath);

            $article = array();
            include_once $filepath;

            if (empty($article)) continue;
            if (!$article['cat_group']) $article['cat_group'] = 999;
            $this->validateTemplateId($article);
            $this->install_ids['articles'][$id] = cjoArticle::addArticle($article);

            if (cjoMessage::hasErrors() || !is_array($article['slices'])) continue;

            foreach($article['slices'] as $slice) {

                $slice['article_id'] = $this->install_ids['articles'][$id];

                if ($this->validateModultypId($slice)) {
                    $new_slice = array();
                    foreach($slice as $key=>$value){
                        $new_slice[$key] = $value;
                    }
                    $this->install_ids['slices'][$id] = cjoSlice::addSlice($new_slice);
                }
            }
        }
	}

	private function validateTemplateId($article){

	    global $I18N;

	    $template_id= $this->install_ids['templates'][$article['template_id']];
	    if (!$template_id) $template_id = $article['template_id'];

	    $sql = new cjoSql();
	    $sql->setQuery("SELECT id FROM ".TBL_TEMPLATES." WHERE id='".$template_id."'");

	    if ($sql->getRows() == 1) {
	        $article['template_id'] = $template_id;
	        return true;
	    }
		else {
	        cjoMessage::addError($I18N->msg('msg_template_not_found'), $article['template_id']);
	        return false;
	    }
	}

	private function validateModultypId($slice){

	    global $I18N;

	    $modultyp_id = $this->install_ids['modules'][$slice['modultyp_id']];
	    if (!$modultyp_id) $modultyp_id = $slice['modultyp_id'];

	    $sql = new cjoSql();
	    $sql->setQuery("SELECT id FROM ".TBL_MODULES." WHERE id='".$modultyp_id."'");

	    if ($sql->getRows() == 1) {
	        $slice['modultyp_id'] = $modultyp_id;
	        return true;
	    }
		else {
	        cjoMessage::addError($I18N->msg('msg_module_not_found'), $slice['modultyp_id']);
	        return false;
	    }
	}

	public function installSettings(){

	    $path = $this->setup_path.'/settings';

        if (!cjoAssistance::isReadable($path)) return false;

	    foreach(glob($path.'/*') as $filepath) {

	        $filename = pathinfo($filepath, PATHINFO_BASENAME);

	        $dest = $this->config_path.'/'.$filename;

	        if (is_dir($filepath)){
                cjoAssistance::copyDir($filepath, $dest);
	        } else {
	            cjoAssistance::copyFile($filepath, $dest);
	        }
	    }
	}

    public static function installDump($file, $debug = false) {

        $sql = new cjoSql();
    	$sql->debugsql = $debug;

    	foreach (self::readSqlFile($file) as $query) {
    	    $sql->flush();
    		$sql->setQuery($query);

    		if ($sql->getError() != '') {
    			cjoMessage::addError($sql->getError());
    		}
    	}
	    return cjoMessage::hasErrors() ? false : true;
    }

    public static function readSqlFile($file) {

        if (!cjoAssistance::isReadable($file)) return array();

        $ret = array ();
        $sqlsplit = '';
        $fileContent = file_get_contents($file);
        self::splitSqlFile($sqlsplit, $fileContent, '');

        if (is_array($sqlsplit)) {

            foreach ($sqlsplit as $qry) {
				preg_match_all('/(?<=%)TBL[A-Z0-9_]*(?=%)/', $qry['query'], $tbl_name);
				
                foreach($tbl_name[0] as $key => $name) {
                	if ($name != '' && defined($name)) {
    					eval('$replace_name = '.$name.';');
    					$qry['query'] = str_replace('%'.$name.'%', $replace_name, $qry['query']);
                	}
                }
                $ret[] = $qry['query'];
            }
        }
        return $ret;
    }

    public static function splitSqlFile(&$ret, $sql, $release) {

        global $CJO;

        $sql = str_replace('`cjo_', '`' . $CJO['TABLE_PREFIX'], $sql);

        // do not trim, see bug #1030644
        //$sql          = trim($sql);
        $sql          = rtrim($sql, "\n\r");
        $sql_len      = strlen($sql);
        $char         = '';
        $string_start = '';
        $in_string    = FALSE;
        $nothing      = TRUE;
        $time0        = time();

        for ($i = 0; $i < $sql_len; ++ $i) {
            $char = $sql[$i];

            // We are in a string, check for not escaped end of strings except for
            // backquotes that can't be escaped
            if ($in_string) {
                for (;;) {
                    $i = strpos($sql, $string_start, $i);
                    // No end of string found -> add the current substring to the
                    // returned array
                    if (!$i) {
                        $ret[] = $sql;
                        return TRUE;
                    }
                    // Backquotes or no backslashes before quotes: it's indeed the
                    // end of the string -> exit the loop
                    else
                        if ($string_start == '`' || $sql[$i -1] != '\\') {
                            $string_start = '';
                            $in_string = FALSE;
                            break;
                        }
                    // one or more Backslashes before the presumed end of string...
                    else {
                        // ... first checks for escaped backslashes
                        $j = 2;
                        $escaped_backslash = FALSE;
                        while ($i - $j > 0 && $sql[$i - $j] == '\\') {
                            $escaped_backslash = !$escaped_backslash;
                            $j++;
                        }
                        // ... if escaped backslashes: it's really the end of the
                        // string -> exit the loop
                        if ($escaped_backslash) {
                            $string_start = '';
                            $in_string = FALSE;
                            break;
                        }
                        // ... else loop
                        else {
                            $i++;
                        }
                    } // end if...elseif...else
                } // end for
            } // end if (in string)

            // lets skip comments (/*, -- and #)
            else
                if (($char == '-' && $sql_len > $i +2 && $sql[$i +1] == '-' && $sql[$i +2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i +1 && $sql[$i +1] == '*')) {
                    $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
                    // didn't we hit end of string?
                    if ($i === FALSE) {
                        break;
                    }
                    if ($char == '/')
                        $i++;
                }

            // We are not in a string, first check for delimiter...
            else
                if ($char == ';') {
                    // if delimiter found, add the parsed part to the returned array
                    $ret[] = array (
                        'query' => substr($sql,
                        0,
                        $i
                    ), 'empty' => $nothing);
                    $nothing = TRUE;
                    $sql = ltrim(substr($sql, min($i +1, $sql_len)));
                    $sql_len = strlen($sql);
                    if ($sql_len) {
                        $i = -1;
                    } else {
                        // The submited statement(s) end(s) here
                        return TRUE;
                    }
                } // end else if (is delimiter)

            // ... then check for start of a string,...
            else
                if (($char == '"') || ($char == '\'') || ($char == '`')) {
                    $in_string = TRUE;
                    $nothing = FALSE;
                    $string_start = $char;
                } // end else if (is start of string)

            elseif ($nothing) {
                $nothing = FALSE;
            }

            // loic1: send a fake header each 30 sec. to bypass browser timeout
            $time1 = time();
            if ($time1 >= $time0 +30) {
                $time0 = $time1;
                header('X-pmaPing: Pong');
            } // end if
        } // end for

        // add any rest to the returned array
        if (!empty ($sql) && preg_match('@[^[:space:]]+@', $sql)) {
            $ret[] = array (
                'query' => $sql,
                'empty' => $nothing
            );
        }

        return TRUE;
    }
}
