<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  developer
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

class liveEdit {

    public $livePath;
    public $ModulePath = "modules/";
    public $ModuleInputExtension = ".input.php";
    public $ModuleOutputExtension = ".output.php";
    public $TemplatePath = "templates/";
    public $TemplateExtension = ".template.php";

    public function liveEdit(){

        global $CJO;

        $this->livePath = $CJO['ADDON']['settings']['developer']['edit_path'].'/';
    }

    public function getModuleFiles(){

        if ($handle = opendir($this->livePath.$this->ModulePath)) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('/^\./', $file)) {
                    if (strstr($file,$this->ModuleInputExtension)){
                        $id = str_replace($this->ModuleInputExtension,"",$file);
                        $moduleFiles[] = $id;
                    }
                }
            }
            closedir($handle);
            return $moduleFiles;
        }
    }

    public function getTemplateFiles(){

        if ($handle = opendir($this->livePath.$this->TemplatePath)) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('/^\./', $file)) {
                    $id = str_replace($this->TemplateExtension,"",$file);
                    $templateFiles[] = $id;
                }
            }
            closedir($handle);
            return $templateFiles;
        }
    }

    public function getModulesFormDB(){

        global $CJO;

        $sql = new cjoSql();
        $qry = "SELECT id, output, input FROM ".TBL_MODULES." ORDER BY id";
        $dbResult = $sql->getArray($qry);

        if (is_array($dbResult)){
            foreach($dbResult as $var){
                $modules[$var['id']]['input'] = $var['input'];
                $modules[$var['id']]['output'] = $var['output'];
            }
            return $modules;
        }
    }

    public function getTemplatesFormDB(){

        global $CJO;
        $db = new cjoSql();

        $sql = "SELECT id, content FROM ".TBL_TEMPLATES;
        $dbResult = $db->getArray($sql);

        if (is_array($dbResult)){
            foreach($dbResult as $var){
                $templates[$var['id']] = $var['content'];
            }
            return $templates;
        }
    }

    public function writeModuleFiles($overwrite=false){

        $modulesFiles = $this->getModuleFiles();
        $modulesDB = $this->getModulesFormDB();

        if (is_array($modulesDB)){
            foreach($modulesDB as $key=>$var){
                if (!@in_array($key,$modulesFiles) || $overwrite==true){
                    $inputFilename = $this->livePath.$this->ModulePath.$key.$this->ModuleInputExtension;
                    $outputFilename = $this->livePath.$this->ModulePath.$key.$this->ModuleOutputExtension;
                    $this->writeFile($inputFilename,$var['input']);
                    $this->writeFile($outputFilename,$var['output']);
                    //print "wrote $outputFilename<br>";
                }

            }
        }
    }

    public function writeTemplateFiles($overwrite=false){

        $templateFiles = $this->getTemplateFiles();
        $templatesDB = $this->getTemplatesFormDB();

        if(is_array($templatesDB)){
            foreach($templatesDB as $key=>$var){

                if(!@in_array($key,$templateFiles) || $overwrite==true){
                    $templateFilename = $this->livePath.$this->TemplatePath.$key.$this->TemplateExtension;
                    $this->writeFile($templateFilename,$var);
                    //print "wrote $templateFilename<br>";
                }
            }
        }
    }

    public function removeTemplateFiles(){

        $templateFiles = $this->getTemplateFiles();

        if (is_array($templateFiles)){
            foreach($templateFiles as $template_id){
                unlink($this->livePath.$this->TemplatePath.$template_id.$this->TemplateExtension);
            }
        }
    }

    public function setConfigValue($filename,$valuename,$value){

        $content = file_get_contents($filename);
        $content = preg_replace("/\[$valuename\] = .*;/imsU","[$valuename] = \"".$value."\";",$content);
        $this->writeFile($filename,$content);
    }

    public function writeFile($filename,$content){

        global $CJO;
        file_put_contents($filename, $content);
        @chmod($filename,$CJO['FILEPERM']);
    }

    public function syncModules(){

        global $CJO;

        $run_update = false;

        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_MODULES." ORDER BY id";
        $db_moduls = $sql->getArray($qry);

        if (is_array($db_moduls)){
            foreach($db_moduls as $db_module){
                $db_moduls_by_id[$db_module['id']] = $db_module;
            }
        }
        
        $regenerate = '';
        $module_files = $this->getModuleFiles();

        if (is_array($module_files)) {
            foreach($module_files as $module_id){

                $input_path = $this->livePath.$this->ModulePath.$module_id.$this->ModuleInputExtension;
                $output_path = $this->livePath.$this->ModulePath.$module_id.$this->ModuleOutputExtension;

                if (file_exists($input_path) && file_exists($output_path)){
                    
                    
                    $input_modified = filemtime($input_path);
                    $output_modified = filemtime($output_path);

                    $file_modified = ($input_modified < $output_modified) ? $output_modified : $input_modified;
                    $db_module_modified = $db_moduls_by_id[$module_id]['updatedate'];

                    if ($db_module_modified == $file_modified) continue;

                    $update = new cjoSql();
                    $update->setTable(TBL_MODULES);
                    $update->setWhere("id='".$module_id."'");

                    if ($db_module_modified < $input_modified){
                        $input =  file_get_contents($input_path);
                        @touch($input_path, $file_modified);
                        $update->setValue("input", $input);
                        $update->setValue("updatedate",$file_modified);
                        $run_update = true;
                    }

                    if ($db_module_modified < $output_modified){
                        $output =  file_get_contents($output_path);
                        @touch($input_path, $file_modified);
                        $update->setValue("output", $output);
                        $update->setValue("updatedate",$file_modified);
                        $run_update = true;
                    }
                    
                    if ($run_update){
                        //$update->debugsql = true;    
                        $update->Update();

                        if ($update->getRows()!=0){
                            $regenerate .= $module_id."__";
                            //print "generated db".$module_id."<br>";
                        }
                    }

                    if ($db_module_modified > $file_modified || empty($db_moduls_by_id[$module_id])){
                        unlink($input_path);
                        unlink($output_path);
                    }
                }
                $this->writeModuleFiles();
            }
        }

        if ($CJO['CONTEJO'] && $CJO['USER'] && self::md5ModulLayouts()){
            $this->regenerateArticlesByModultypId($module_files);
        }
        return $regenerate;
    }

    public function syncTemplates(){

        global $CJO;
        $run_update = false;

        $sql = new cjoSql();
        $qry = "SELECT id, name, updatedate FROM ".TBL_TEMPLATES." ORDER BY prior";
        $db_templates = $sql->getArray($qry);

        if (is_array($db_templates)){
            foreach($db_templates as $db_template){
                $db_template_by_id[$db_template['id']] = $db_template;
            }
        }

        $template_files = $this->getTemplateFiles();

        if (is_array($template_files)){
            foreach($template_files as $template_id){

                $template_path = $this->livePath.$this->TemplatePath.$template_id.$this->TemplateExtension;

                if (file_exists($template_path)){

                    $template_modified = filemtime($template_path);
                    $db_template_modified = $db_template_by_id[$template_id]['updatedate'];

                    if (empty($db_template_by_id[$template_id])){
                        @unlink($template_path);
                        continue;
                    }


                    if ($db_template_modified == $template_modified ) continue;

                    if ($db_template_modified < $template_modified){

                        $content =  file_get_contents($template_path);

                        $update = new cjoSql();
                        $update->setTable(TBL_TEMPLATES);
                        $update->setWhere("id='".$template_id."'");
                        $update->setValue("content", $content);
                        $update->setValue("updatedate",$template_modified);
                        $update->Update();

                        if ($update->getError() == ''){
                            $this->generateTemplate($template_id);
                            //print "generated template db".$template_id."<br>";
                        }
                    }

                    if ($db_template_modified > $template_modified || empty($db_template_by_id[$template_id])){

                        @unlink($template_path);
                        $templatePathDeveloper = $this->livePath.$this->TemplatePath.$template_id.$this->TemplateExtension;
                        $templatePathContejo = $CJO['FOLDER_GENERATED_TEMPLATES']."/".$template_id.".template";

                        if (file_exists($templatePathContejo)){
                            copy($templatePathContejo,$templatePathDeveloper);
                            @chmod($templatePathDeveloper,$CJO['FILEPERM']);
                        }
                    }
                }
            }
        }
        else {
            $this->writeTemplateFiles(true);
        }
    }

    public function generateTemplate($template_id){

        global $CJO;

        $templatePathDeveloper = $this->livePath.$this->TemplatePath.$template_id.$this->TemplateExtension;
        $templatePathContejo = $CJO['FOLDER_GENERATED_TEMPLATES']."/".$template_id.".template";
        copy($templatePathDeveloper,$templatePathContejo);
        @chmod($templatePathContejo, $CJO['FILEPERM']);
    }

    public function regenerateArticlesByModultypId($modules){

        global $PHP_SELF, $module_id, $CJO, $I18N;

        include_once($CJO['FILE_CONFIG_LANGS']);

        $modules = cjoAssistance::toArray($modules, "__");
        
        if (empty($modules)) return false;
        
        array_pop($modules);
        $sql = new cjoSql();

        foreach($modules as $modul_id) {
            $sql->flush();
            $sql->setQuery("SELECT DISTINCT ar.id AS article_id
                    FROM ".TBL_ARTICLES." ar
                    LEFT JOIN ".TBL_ARTICLES_SLICE." sl
                    ON ar.id=sl.article_id
                    WHERE sl.modultyp_id='".$modul_id."'");

            for ($i=0; $i<$sql->getRows(); $i++) {
                cjoGenerate::deleteGeneratedArticle($sql->getValue("article_id"));
                $sql->next();
            }
        }
    }

    public function developer_setStatus($what='modules',$status=false){

        global $CJO;

        $statusFile = $CJO['ADDON']['settings'][$mypage]['status'];
        $stat = $status == "true" ? "true" : "false";

        $this->setConfigValue($statusFile,$what,$status);

        if (strtoupper($what) == "MODULES" && $stat == "true"){
            $this->writeModuleFiles(true);
        }
        if (strtoupper($what) == "TEMPLATES" && $stat == "true"){
            $this->writeTemplateFiles(true);
        }
    }

    public function regenerateArticlesByJS($params) {

        global $CJO;

        $mypage = 'developer';

        if ($CJO['ADDON']['settings'][$mypage]['regenerate']){
            $regenerateString = $CJO['ADDON']['settings'][$mypage]['regenerate'];

            $content = $params['subject'];
            $content = '<html><head></head><body>
        	<div style="position: absolute; top: 10px; left: 10px;
        			background-color:#EFEFEF;border:2px solid;padding:4px; font-family: sans-serif;
        			font-size: 11px; width: 200px;"><b>CONTEJO Developer Addon regenerating Articles:</b>
        		<div id="status" style="font-weight: bold"></div>
        	</div>
            <script type="text/javascript">
            /* <![CDATA[ */
    
            function status(count){
              var div = document.getElementById(\'status\');
              if(div) div.innerHTML += \'. \';
              count++;
              window.setTimeout(\'status(\' + count + \');\', 50);
            }
            status(0);
    
            function rel(){
              location.reload();
            }
    
            if (document.implementation && document.implementation.createDocument){
                xmlDoc = document.implementation.createDocument(\'\', \'\', null);
                xmlDoc.onload = function() { rel(); };
              }
              else if (window.ActiveXObject){
                xmlDoc = new ActiveXObject(\'Microsoft.XMLDOM\');
                xmlDoc.onreadystatechange = function () {
                    if (xmlDoc.readyState == 4) rel()
                };
              }
              else {
                alert(\'Your browser cant handle this script\');
              }
              var url = \'./core/ajax.php?function=liveEdit::regenerateArticlesByModultypId&modules='.$regenerateString.'\';
              //document.write(url);
              xmlDoc.load(url);
    
            /* ]]> */
            </script>
            </body></html>';

            return $content;
        }
    }
    
    private static function md5ModulLayouts() {
        
        global $CJO;
        
        $files = cjoAssistance::rglob($CJO['ADDON']['settings']['developer']['edit_path'].'/'.
                                      $CJO['TMPL_FILE_TYPE'],
                                      '*.'.$CJO['TMPL_FILE_TYPE']);
        
        if (empty($files)) return false;
        $md5 = '';
        
        foreach($files as $file) {
            $md5 .= md5(file_get_contents($file));
        }
        
        $md5 = md5($md5);
        
        if (cjo_session('layouts_md5', 'string', '0') != $md5) {
            cjo_set_session('layouts_md5', $md5);
            return true;
        }
        return false;
    }

    public function deleteLiveEdit($params){

        $liveEdit = new liveEdit();
        $module_files = $liveEdit->getModuleFiles();

        if (is_array($module_files)){
            foreach($module_files as $module_id){
                @unlink($liveEdit->livePath.$liveEdit->ModulePath.$module_id.$liveEdit->ModuleInputExtension);
                @unlink($liveEdit->livePath.$liveEdit->ModulePath.$module_id.$liveEdit->ModuleOutputExtension);
            }
            $liveEdit->writeModuleFiles();
        }

        $template_files = $liveEdit->getTemplateFiles();

        if (is_array($template_files)){
            $liveEdit->removeTemplateFiles();
            $liveEdit->writeTemplateFiles($templates_overwrite);
            $liveEdit->syncTemplates();
        }
    }

    public function insertCss($params) {

        global $CJO;

        $dir = $CJO['ADDON']['settings']['developer']['edit_path'].'/css';
        return cjo_insertCss($params['subject'], glob($dir.'/*.css'));
    }
}