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
 * Template Objekt.
 * Zust�ndig f�r die Verarbeitung eines Templates
 *
 * @package redaxo4
 * @version svn:$Id: class.cjo_template.inc.php 1335 2011-07-31 10:32:23Z s_lehmann $
 */

class cjoTemplate {

    var $id;

    function __construct ($template_id = 0) {
        $this->setId($template_id);
    }

    public static function addTemplate($template) {

       global $CJO, $I18N;

        if (!is_array($template)) {
            return false;
        }

        if (!$CJO['CONTEJO']) {
            cjoMessage::addError($I18N->msg("msg_no_permissions"));
            return false;
        }

        $sql = new cjoSql();
        $sql->setTable(TBL_TEMPLATES);
    	$sql->setValue("name", $template['name']);
    	$sql->setValue("active", $template['active']);
    	$sql->addGlobalCreateFields();
    	$sql->addGlobalUpdateFields();
    	$sql->Insert();

        if ($sql->getError()){
    		cjoMessage::addError($sql->getError());
    		return false;
        }

	    $id = $sql->getLastId();

        cjoAssistance::updatePrio(TBL_TEMPLATES, $id, time());

		$template['content'] = preg_replace('/(action_after_validation)_(\d+)/','\1_'.$id, $template['content']);

		$sql->flush();
		$sql->setTable(TBL_TEMPLATES);
		$sql->setWhere("id='".$id."'");
		$sql->setValue("content", $template['content']);

		if (!$sql->Update($I18N->msg("msg_template_added", $template['name']))) return false;

		cjoGenerate::putFileContents($CJO['FOLDER_GENERATED_TEMPLATES']."/".$id.'.template', $template['content']);
		return $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int) $id;
    }

    public function getFile()
    {
        if ($this->getId() < 1) return false;
        $tempalte_file = $this->getFilePath($this->getId());
        if (!$tempalte_file) return false;
        if (!file_exists($tempalte_file)) {

            // Generated Datei erzeugen
            if (!$this->generate()) {
                trigger_error('Unable to generate Template with id "'. $this->getId() . '"', E_USER_ERROR);
                return false;
            }
        }
        return $tempalte_file;
    }

    public function getFilePath($template_id) {
        if ($template_id < 1) return false;
        return cjoTemplate::getTemplatesDir().'/'.$template_id .'.template';
    }

    public function getTemplatesDir() {
        global $CJO;
        return $CJO['FOLDER_GENERATED_TEMPLATES'];
    }

    public function getTemplate($article_id = false) {
        $tempalte_file = $this->getFile();
        if (!$tempalte_file) return false;
        $content = @file_get_contents($tempalte_file);
        return $this->replaceTemplateVars($content, $article_id, $this->getId());
    }
    
    public function executeTemplate($article_id = false) {
        $content = $this->getTemplate($article_id);
        if (strpos($content, '<?') === false) {
            echo $content; 
        }
        else {
            eval('?>'.$content);
        }
    }

    public function replaceTemplateVars($content, $article_id = false) {
        global $CJO;
        foreach($CJO['VARIABLES'] as $var){
            $content = $var->getTemplate($content, $article_id);
        }
        return $content;
    }

    public static function getCtypes($template_id) {

        global $CJO;
        
        $ctypes = array();
        
        $sql = new cjoSql();
        $qry = "SELECT ctypes FROM ".TBL_TEMPLATES." WHERE id='".$template_id."' LIMIT 1";
    	$results = $sql->getArray($qry);
    	
    	if (!empty($results[0])) {
        	foreach(cjoAssistance::toArray($results[0]['ctypes']) as $ctype_id) {
        	    if (isset($CJO['CTYPE'][$ctype_id])) {
        	        $ctypes[] = $ctype_id;
        	    }
        	}
    	}
    	return $ctypes;
    }

    public static function hasCtype($template_id, $ctype) {

        $ctype = (int) $ctype;

        foreach(self::getCtypes($template_id) as $id) {
    	    if ($ctype == $id) return true;
    	}
    	return false;
    }

    public function generate() {
        global $CJO;
        if ($this->getId() < 1) return false;
        require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_generate.inc.php";
        return cjoGenerate::generateTemplates($this->getId());
    }

    public function deleteCache() {
        global $CJO;
        if($this->id < 1) return false;
        $tempalte_file = $this->getFilePath($this->getId());
        return @unlink($tempalte_file);
    }
}