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

class cjoTemplate {

    private $id;

    function __construct ($template_id = 0) {
        $this->setId($template_id);
    }

    public static function addTemplate($template) {

        if (!is_array($template)) {
            return false;
        }

        if (!cjoProp::isBackend()) {
            cjoMessage::addError(cjoI18N::translate("msg_no_permissions"));
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

		if (!$sql->Update(cjoI18N::translate("msg_template_added", $template['name']))) return false;

		cjoGenerate::putFileContents(cjoPath::generated('templates', $id.'.template'), $template['content']);
		return $id;
    }

    public static function getContent($template_id) {
        return file_get_contents(cjoPath::generated('templates', $template_id.'.template'));
    }

    public static function getGeneratedPath($template_id) {
        return file_get_contents(cjoPath::generated('templates', $template_id.'.template'));
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int) $id;
    }

    public function getFile() {
        
        if ($this->getId() < 1) return false;
        $tempalte_file = $this->getFilePath($this->getId());
        if (!$tempalte_file) return false;
        if (!file_exists($tempalte_file)) {

            // Generated Datei erzeugen
            if (!$this->generate()) {
                throw new cjoException('Unable to generate Template with id "'. $this->getId() . '"', E_USER_ERROR);
                return false;
            }
        }
        return $tempalte_file;
    }

    public function getFilePath($template_id) {
        if ($template_id < 1) return false;
        return cjoPath::generated('templates', $template_id.'.template');
    }

    public function getTemplate($article_id = false) {
        $tempalte_file = $this->getFile();
        if (!$tempalte_file) return false;
        $content = file_get_contents($tempalte_file);
        
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

        foreach(cjoProp::get('VARIABLES') as $var){
            $content = $var->getTemplate($content, $article_id);
        }
        return $content;
    }

    public static function getCtypes($template_id) {
        
        $ctypes = array();
        
        $sql = new cjoSql();
        $qry = "SELECT ctypes FROM ".TBL_TEMPLATES." WHERE id='".$template_id."' LIMIT 1";
    	$results = $sql->getArray($qry);
    	
    	if (!empty($results[0])) {
        	foreach(cjoAssistance::toArray($results[0]['ctypes']) as $ctype_id) {
        	    if (cjoProp::getCtypeName($ctype_id)) {
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
        if ($this->getId() < 1) return false;
        return cjoGenerate::generateTemplates($this->getId());
    }

    public function deleteCache() {
        if($this->id < 1) return false;
        $tempalte_file = $this->getFilePath($this->getId());
        return @unlink($tempalte_file);
    }
    
    public static function deleteTemplate($id) {
        if ($id == '1') {
            cjoMessage::addError(cjoI18N::translate("msg_cant_delete_default_template"));
        }
        elseif ($id != '') {
    
            $sql = new cjoSql();
            $qry = "SELECT DISTINCT
                    a.id AS id,
                        a.clang AS clang,
                        a.name AS name,
                        t.name AS template_name
                   FROM ".TBL_ARTICLES." a
                   LEFT JOIN ".TBL_TEMPLATES." t
                   ON a.template_id = t.id
                   WHERE a.template_id='".$id."'";
            $results = $sql->getArray($qry);
    
            $temp = array();
            foreach ($results as $result) {
                    $temp[] = cjoUrl::createBELink(
                                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
                                                array('page' => 'edit',
                                                      'subpage' => 'settings',
                                                      'function' => '',
                                                      'oid' => '',
                                                      'article_id' => $result['id'],
                                                      'clang' => $result['clang']));
            }
    
            if (!empty($temp))
                cjoMessage::addError(cjoI18N::translate("msg_cant_delete_template_in_use",
                                                $results[0]['template_name']).'<br />'.implode(' | ',$temp));
    
            if (!cjoMessage::hasErrors()) {
                $sql->flush();  
                $results = $sql->getArray("SELECT * FROM ".TBL_TEMPLATES." WHERE id='".$id."'");
                $sql->flush();
                if ($sql->statusQuery("DELETE FROM ".TBL_TEMPLATES." WHERE id='".$id."'",
                                  cjoI18N::translate("msg_template_deleted"))) {
                    cjoAssistance::updatePrio(TBL_TEMPLATES);
                    cjoExtension::registerExtensionPoint('TEMPLATE_DELETED', $results[0]);
                }
            }
        }
    }    
}