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

class cjoGenerate {

    /**
     * Starts the complete generation process, including articles, templates and clang.
     * @return void;
     * @access public
     */
    public static function generateAll() {

    	global $CJO, $I18N;

    	self::deleteGeneratedArticles();
    	self::generateTemplates();
    	self::generateClangs();

        if (!cjoMessage::hasErrors()) {
    	    cjoMessage::addSuccess($I18N->msg('msg_articles_generated')." ".
    	                           $I18N->msg('msg_old_articles_deleted'));
    	}

    	cjoExtension::registerExtensionPoint('ALL_GENERATED');
    }

    /**
     * Generates a single or all the template files.
     * @param int|boolean $id template id
     * @return void;
     * @access public
     */
    public static function generateTemplates($id=false) {

    	global $CJO;

    	$addsql = '';

    	if ($id === false) {
    		cjoAssistance::deleteDir($CJO['FOLDER_GENERATED_TEMPLATES'], 0);
    	}
    	else {
    		$addsql = " WHERE id='".$id."'";
    	}

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT id, content FROM ".TBL_TEMPLATES.$addsql);

    	for ($i = 0; $i < $sql->getRows(); $i++) {
    		$file = $CJO['FOLDER_GENERATED_TEMPLATES']."/".$sql->getValue("id").".template";
    		$new_content = $sql->getValue("content");
    		self::putFileContents($file, $new_content);
    		$sql->next();
    	}
    	return true;
    }

    /**
     * Deletes all files that belong to a generated article.
     * @param int $article_id id of the article
     * @param bool $aspath include cached aspath
     * @return void;
     * @access public
     */
    public static function deleteGeneratedArticle($article_id, $aspath=false) {

    	global $CJO;
    	
    	$pattern  = "*.alist";
        $pattern .= ",*.content";
        $pattern .= ",*.article";    
        if ($aspath) $pattern .= ",*.aspath";           

        $pattern = $CJO['FOLDER_GENERATED_ARTICLES'].'/'.$article_id.'.[0-9]{'.$pattern.'}';

    	foreach (cjoAssistance::toArray(glob($pattern,GLOB_BRACE)) as $filename) {
    	    @unlink($filename);
    	}
    }
    
    /**
     * Deletes all generated articles.
     * @param array $exclude filenames to exclude from delete
     * @return boolean;
     * @access public
     */
    public static function deleteGeneratedArticles($exclude=array()) {
    	global $CJO;
    	return cjoAssistance::deleteDir($CJO['FOLDER_GENERATED_ARTICLES'],false,$exclude);
    }

    /**
     * Generates all files that belong to a generated article.
     *
     * @param int $article_id id of the article
     * @param boolean $generate_content
     * @param int|boolean $clang
     */
    public static function generateArticle($article_id, $generate_content = true, $clang = false, $template_id=false) {

    	global $CJO;

    	$article = new cjoArticle();
        $article->getAsQuery(true); // Content aus Datenbank holen, no cache
        $article->setEval(false); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann

        foreach($CJO['CLANG'] as $curr_clang => $clang_name) {

            if ($clang !== false && $clang != $curr_clang) continue;

            $article->setCLang($curr_clang);
            if (!$article->setArticleId($article_id)) return false;
            $article->setTemplateId($template_id);
            
    	    if (!self::generateArticleMeta($article)) {
                return false;
        	}

        	if ($generate_content) {
        	    if (!self::generateArticleContent($article)) {
        	        return false;
        	    }
        	}
        }

    	self::generateLists($article_id);
    	self::generateLists($article->getValue("re_id"));
    	return true;
    }

    public static function generateArticles($generate_ids = false) {

    	global $CJO;

    	if ($generate_ids === false) {
    		global $generate_ids;
    	}
		foreach ($generate_ids as $id => $generate_content) {
			self::generateArticle($id, $generate_content);
		}
    	$generate_ids = array();
    }

    public static function generateArticleMeta($article) {

        global $CJO, $I18N;

        $article_id = $article->getValue("id");
        $clang = $article->getValue("clang");

	    $file = $CJO['FOLDER_GENERATED_ARTICLES']."/".$article_id.".".$clang.".article";

        if (!cjoAssistance::isWritable($CJO['FOLDER_GENERATED_ARTICLES'])) {

            cjoMessage::removeLastError();
			cjoMessage::addError($I18N->msg('msg_article_could_not_be_generated')." ".
								 $I18N->msg('msg_check_rights_in_directory').
								 $CJO['FOLDER_GENERATED_ARTICLES']);
	         return false;
        }
        
        cjoExtension::registerExtensionPoint('GENERATE_ARTICLE_META', array('article_id' => $article_id, 'clang' => $clang));

        $new_content = '<?php'."\r\n";
    	$new_content .= '$CJO[\'ART\'][\''.$article_id.'\'][\'article_id\'][\''.$clang.'\'] = "'.$article_id.'";'."\r\n";
    	$new_content .= '$CJO[\'ART\'][\''.$article_id.'\'][\'slices\'][\''.$clang.'\'] = "'.addslashes($article->getSlicesOfArticle(false)).'";'."\r\n";    	

 
        foreach(OOContejo::getClassVars() as $field) {
            if (in_array($field, array('pid', 'id', 'slices')) || empty($field)) continue;
                
    	    $new_content .= '$CJO[\'ART\'][\''.$article_id.'\'][\''.$field.'\'][\''.$clang.'\'] = "'.cjoAssistance::addSlashes($article->getValue($field)).'";'."\r\n";
        }
        
    	$new_content .= '$CJO[\'ART\'][\''.$article_id.'\'][\'last_update_stamp\'][\''.$clang.'\'] = "'.time().'";'."\r\n";

        $new_content .= '?>'."\r\n";
        
		foreach (cjoAssistance::toArray(glob($CJO['FOLDER_GENERATED_ARTICLES']."/"."*.".$clang.".aspath")) as $filename) {
	    	@ unlink($filename);
		}
		if (!self::putFileContents($file, $new_content)) return false;
    	return true;
    }


    public static function generateArticleContent($article) {

        global $CJO, $I18N;
        $temp = $CJO['CONTEJO'];
        $article_id = $article->getValue("id");
        $clang = $article->getValue("clang");

        $file = $CJO['FOLDER_GENERATED_ARTICLES']."/".$article_id.".".$clang.".content";

        if (!cjoAssistance::isWritable($CJO['FOLDER_GENERATED_ARTICLES'])) {

            cjoMessage::removeLastError();
			cjoMessage::addError($I18N->msg('msg_article_could_not_be_generated')." ".
								 $I18N->msg('msg_check_rights_in_directory').
								 $CJO['FOLDER_GENERATED_ARTICLES']);
	         return false;
        }
        $CJO['CONTEJO'] = false;
        $new_content = '?>'.$article->getArticle();
        $CJO['CONTEJO'] = $temp;
		if (!self::putFileContents($file, $new_content)) return false;

    	return true;
    }

    public static function toggleStartpageArticle($id='', $re_id='') {

    	global $CJO, $I18N;

    	if ($id == '' && $re_id == '') return false;

    	$sql = new cjoSql();

    	if ($re_id == '') {
    		$sql->setQuery("SELECT re_id FROM ".TBL_ARTICLES." WHERE id='".$id."' AND clang='0'");
    		$re_id = $sql->getValue('re_id');
    		$re_startpage = $sql->getValue('startpage');
    	}

    	$sql->flush();
    	$sql->setQuery("SELECT id, startpage FROM ".TBL_ARTICLES." WHERE re_id='".$re_id."' AND clang='0'");

    	$startpage = ($sql->getRows() > 0) ? 1 : 0;

    	$update = new cjoSql();
    	$update->setTable(TBL_ARTICLES);
    	$update->setWhere("id='".$re_id."'");
    	$update->setValue("startpage", $startpage);
    	$update->Update();

    	foreach ($CJO['CLANG'] as $lang=>$name) {
    		self::generateArticle($re_id, false, $lang);
    	}

    	return true;
    }

    /**
     * Generiert alle *.alist u. *.clist Dateien einer Kategorie/eines Artikels
     *
     * @param $re_id   KategorieId oder ArtikelId, die erneuert werden soll
     */
    public static function generateLists($re_id) {

    	global $CJO;

    	if (empty($re_id)) $re_id = 0;

    	foreach (cjoAssistance::toArray($CJO['CLANG']) as $clang=>$name) {

    		$sql = new cjoSql();
    		$sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE re_id='".$re_id."' AND clang='".$clang."' ORDER BY prior, name");

    		$new_content = "<?php\r\n";
    		for ($i = 0; $i < $sql->getRows(); $i++) {
    			$id = $sql->getValue("id");
    			$new_content .= "\$CJO['RE_ID']['$re_id']['$i'] = \"".$sql->getValue("id")."\";\r\n";
    			$sql->next();
    		}
    		$new_content .= "\r\n?>";
    		$file = $CJO['FOLDER_GENERATED_ARTICLES']."/".$re_id.".".$clang.".alist";
    		if (!self::putFileContents($file, $new_content)) {
                cjoMessage::removeLastError();
            }
    	}
    }

    /**
     * Berechnet die Prios der Kategorien in einer Kategorie neu
     *
     * @param $re_id    KategorieId der Kategorie, die erneuert werden soll
     * @param $clang    ClangId der Kategorie, die erneuert werden soll
     * @param $new_prio Neue PrioNr der Kategorie
     * @param $old_prio Alte PrioNr der Kategorie
     */
    public static function newPrio($re_id, $clang, $new_prio, $old_prio) {

    	global $CJO;

    	if ($new_prio != $old_prio) {

    		$update = new cjoSql();
    		$sql = new cjoSql();
    		$addsql = ($new_prio < $old_prio) ? "DESC" : "ASC";
    		$cat_group = ($re_id == 0) ? 'cat_group,' : '';

    		$sql->setQuery("SELECT pid FROM ".TBL_ARTICLES."
    						WHERE re_id='".$re_id."' AND
    						clang='".$clang."'
    						ORDER BY ".$cat_group."
    						prior, updatedate ".$addsql);

    		for ($i = 1; $i <= $sql->getRows(); $i++) {

    			$update->flush();
    			$update->setTable(TBL_ARTICLES);
    			$update->setWhere("pid='".$sql->getValue("pid")."'");
    			$update->setValue("prior", $i);
    			$update->Update();
    			$sql->next();
    		}
    		self::generateLists($re_id);
    	}
    	return true;
    }

    /**
     * Löscht einen Artikel
     *
     * @param $id ArtikelId des Artikels, der gelöscht werden soll
     */
    public static function deleteArticle($id, $recrusive = false) {

    	global $CJO, $I18N;

    	if (!$CJO['USER']->hasCatPermWrite($id) ||
    	    $CJO['USER']->hasPerm('editContentOnly[]')) {
    	    cjoMessage::addError($I18N->msg('msg_no_rights'));
    	    return false;
    	}

    	$id = (int) $id;
	    $article = OOArticle::getArticleById($id);


        if (!OOArticle::isValid($article)) {
    	    cjoMessage::addError($I18N->msg('msg_article_doesnt_exist'));
    	    return false;
    	}

    	if ($id == $CJO['START_ARTICLE_ID']) {
    		cjoMessage::addError($I18N->msg("msg_error_can_not_delete_start_article"));
    		return false;
    	}

        if ($id == $CJO['NOTFOUND_ARTICLE_ID']) {
    		cjoMessage::addError($I18N->msg("msg_error_can_not_delete_notfound_article"));
    		return false;
    	}

    	if ($recrusive) {

    	    $start_article = OOArticle::getArticleById($CJO['START_ARTICLE_ID']);

    	    if (strpos($start_article->path,'|'.$id.'|') !== false) {
        		cjoMessage::addError($I18N->msg("msg_error_start_article_in_tree"));
        		return false;
    	    }

    	    $notfound_article = OOArticle::getArticleById($CJO['NOTFOUND_ARTICLE_ID']);

    	    if (strpos($notfound_article->path,'|'.$id.'|') !== false) {
        		cjoMessage::addError($I18N->msg("msg_error_notfound_article_in_tree"));
        		return false;
    	    }
    	}

    	$sql = new cjoSql();
        $generate_ids = array();
    	$parent_id = $article->getValue("re_id");

    	if ($article->isStartPage()) {
    	    if (!$recrusive || !$CJO['USER']->hasPerm("deleteArticleTree[]")) {
                cjoMessage::addError($I18N->msg("msg_article_could_not_be_deleted", $article->getName())." ".
                                     $I18N->msg("msg_article_still_contains_articles"));
                return false;
        	}
        	else {
    			$sql->flush();
    			$children = $sql->getArray("SELECT id, re_id FROM ".TBL_ARTICLES." WHERE path LIKE '%|".$id."|%' AND clang='0'");

    			foreach($children as $child) {

    			    $sql->flush();
            		$sql->setQuery("DELETE FROM ".TBL_ARTICLES." WHERE id='".$child['id']."'");
            		$sql->setQuery("DELETE FROM ".TBL_ARTICLES_SLICE." WHERE article_id='".$child['id']."'");

    				foreach (cjoAssistance::toArray(glob($CJO['FOLDER_GENERATED_ARTICLES']."/".$child['id'].".*.*")) as $filename) {
            		    @ unlink($filename);
            		}

                    cjoExtension::registerExtensionPoint('ARTICLE_DELETED', array ("id" => $child['id'],
                                                                         		   "re_id" => $child['re_id']));
    			}
    		}
		}

		$sql->setQuery("DELETE FROM ".TBL_ARTICLES." WHERE id='".$id."'");
		$sql->setQuery("DELETE FROM ".TBL_ARTICLES_SLICE." WHERE article_id='".$id."'");

		foreach (cjoAssistance::toArray(glob($CJO['FOLDER_GENERATED_ARTICLES']."/".$id.".*.*")) as $filename) {
		    @ unlink($filename);
		}
		foreach (cjoAssistance::toArray(glob($CJO['FOLDER_GENERATED_ARTICLES']."/*.aspath")) as $filename) {
		    @ unlink($filename);
		}

        cjoExtension::registerExtensionPoint('ARTICLE_DELETED', array ("id" => $id,
                                                             		   "re_id" => $parent_id));

		foreach ($CJO['CLANG'] as $key => $lang) {
			self::newPrio($parent_id, $key, 0, 1);
		}
		self::toggleStartpageArticle('',$parent_id);
		self::generateArticle($parent_id, false);

		cjoMessage::addSuccess($I18N->msg('msg_article_deleted', $article->getName()));
        return true;
    }

    /**
     * Verschieben eines Artikels von einer Kategorie in eine Andere
     *
     * @param $id      	   Id des zu verschiebenden Artikels
     * @param $target_id   Id des Artikels in den verschoben werden soll
     * @param $permission  
     */
    public static function moveArticle($id, $target_id, $permission= true) {

    	global $CJO, $I18N;

    	if ($permission && (
    	    !$CJO['USER']->hasPerm("moveArticle[]") ||
    	    !$CJO['USER']->hasCatPermWrite($id) ||
    	    $CJO['USER']->hasPerm('editContentOnly[]'))) {
    	    cjoMessage::addError($I18N->msg('msg_no_rights'));
    	    return false;
    	}

        $generate_ids = array();
    	$id = (int) $id;
    	$target_id = (int) $target_id;
	    $article = OOArticle::getArticleById($id);
    	$target = OOArticle::getArticleById($target_id);

    	if (!OOArticle::isValid($article) || (
    	    $target_id != 0 && !OOArticle::isValid($target))) {
    	    cjoMessage::addError($I18N->msg('msg_article_doesnt_exist'));
    	    return false;
    	}

        if (!$CJO['USER']->hasCatPermWrite($target_id)) {
            cjoMessage::addError($I18N->msg('msg_no_target_rights'));
    	    return false;
    	}

        $target_path = ($target_id > 0) ?  $target->getPath().$target_id.'|' : "|";
        $target_name = ($target_id > 0) ?  $target->getName() : $I18N->msg('label_article_root');
    	$parent_id = $article->getValue('re_id');

    	if (strpos($target_path,'|'.$id.'|') !== false ||
    	    $parent_id == $target_id) {
    	    cjoMessage::addError($I18N->msg('msg_error_move_article_self', $article->getName()));
    	    return false;
    	}

		$update = new cjoSql();
		$update->setTable(TBL_ARTICLES);
		$update->setValue('path', $target_path);
		$update->setValue('re_id', $target_id);
		$update->setValue('prior', time());
		$update->addGlobalUpdateFields();
		$update->setWhere('id="'.$id.'"');
		$update->Update($I18N->msg("msg_content_article_moved", $article->getName(), $target_name));

		// Prios neu berechnen
		foreach ($CJO['CLANG'] as $clang=>$name ) {
			self::newPrio($target_id, $clang, 1, 0);
			self::newPrio($parent_id, $clang, 1, 0);
		}

    	$generate_ids[$id] = false;
    	$generate_ids[$parent_id] = false;
    	$generate_ids[$target_id] = false;

		if ($article->isStartPage()) {

	        $curr_path = $article->getPath().$id."|";

        	$sql = new cjoSql();
        	$sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE PATH LIKE '".$curr_path."%' AND clang=0");

            $update = new cjoSql();
        	for ($i=0; $i < $sql->getRows(); $i++) {
        		// path aendern und speichern
        		$new_path = $target_path.$id."|".str_replace($curr_path, "", $sql->getValue("path"));

        		// make update
        		$update->flush();
        		$update->setTable(TBL_ARTICLES);
        		$update->setWhere("id='".$sql->getValue("id")."'");
        		$update->setValue("path", $new_path);
        		$update->addGlobalUpdateFields();
        		$update->Update();

        		$generate_ids[$sql->getValue("id")] = false;
        		$sql->next();
            }
		}

    	self::toggleStartpageArticle('',$parent_id);
    	self::toggleStartpageArticle('',$target_id);
        self::generateArticles($generate_ids);

        cjoExtension::registerExtensionPoint('ARTICLE_UPDATED',
                                              array ('ACTION' => 'ARTICLE_MOVED',
                                                     'id' => $id,
                                             		 'old_parent_id' => $parent_id,
													 'new_parent_id' => $target_id));
        return true;
    }

    /**
     * Kopieren eines Artikels von einer Kategorie in eine andere
     *
     * @param $id          ArtikelId des zu kopierenden Artikels
     * @param $target_id   KategorieId in die der Artikel kopiert werden soll
     */
    public static function copyArticle($id, $target_id, $process_all=true) {

        global $CJO, $I18N;

    	if (!$CJO['USER']->hasPerm("copyArticle[]") ||
    	    !$CJO['USER']->hasCatPermWrite($id,false) ||
    	    $CJO['USER']->hasPerm('editContentOnly[]')) {
    	    cjoMessage::addError($I18N->msg('msg_no_rights'));
    	    return false;
    	}

        $generate_ids = array();
    	$id = (int) $id;
    	$target_id = (int) $target_id;
	    $article = OOArticle::getArticleById($id);
    	$target = OOArticle::getArticleById($target_id);

    	if (!OOArticle::isValid($article) || (
    	    $target_id != 0 && !OOArticle::isValid($target))) {
    	    cjoMessage::addError($I18N->msg('msg_error_move_article'));
    	    return false;
    	}

        if (!$CJO['USER']->hasCatPermWrite($target_id)) {
            cjoMessage::addError($I18N->msg('msg_no_target_rights'));
    	    return false;
    	}

        $target_path = ($target_id != 0) ?  $target->getPath().$target_id.'|' : "|";
        $target_name = ($target_id != 0) ?  $target->getName() : $I18N->msg('root');
    	$parent_id = $article->getValue('re_id');

    	$dont_copy = array ('id',
							'pid',
							'path',
							're_id',
							'updateuser',
							'createuser',
							'createdate',
							'updatedate',
							'startpage' );
    	$new_id = false;

    	foreach ($CJO['CLANG'] as $clang => $clang_name) {

            $article = OOArticle::getArticleById($id, $clang);

    		$target_sql = new cjoSql();
    		$qry = "SELECT * FROM ".TBL_ARTICLES." WHERE clang='".$clang."' AND id='".$to_id."'";
    		$target_sql->setQuery($qry);

    		$insert = new cjoSql();
    		$insert->setTable(TBL_ARTICLES);

    		foreach (array_diff(cjoSql::getFieldnames(TBL_ARTICLES), $dont_copy) as $fld_name) {
    			$insert->setValue($fld_name, $article->getValue($fld_name));
    		}

    		if (!$new_id) $new_id = $insert->setNewId('id');

    		$new_name = $article->getName();

    		$sql = new cjoSql();
    		$qry = "SELECT * FROM ".TBL_ARTICLES." WHERE clang='".$clang."' AND re_id='".$target_id."' AND name LIKE '".$new_name."' LIMIT 1";
    		$sql->setQuery($qry);

    		if ($sql->getRows() != 0) $new_name .= '_copy';

    		$insert->setValue('id', $new_id);
    		$insert->setValue('re_id', $target_id);
    		$insert->setValue('path', $target_path);
    		$insert->setValue('startpage', 0);
            $insert->setValue('name', $new_name);
    		$insert->setValue('prior', time());
    		$insert->setValue('status', 0);
    		$insert->setValue("clang", $clang);
    		$insert->addGlobalCreateFields();
    		$insert->Insert($I18N->msg("msg_content_article_copied", $article->getName(), $target_name));

        	cjoExtension::registerExtensionPoint('ARTICLE_ADDED', array("id" => $new_id,
                                                                        "clang" => $clang,
                                                                        "name" => $article->getName().'_copy',
                                                                        "re_id" => $target_id,
                                                                        "path" => $target_path,
            															"template_id" => $article->getValue('template_id'),
                                                                        "type_id" => $article->getValue('type_id'),
                                                                        "online_from" => $article->getValue('online_from'),
            															"online_to" => $article->getValue('online_to'),
                                                                        "user" => $CJO['USER']->getValue("name")
            															),
            													   true);

    		self::copyContent($id, $new_id, $clang, $clang);
    		self::newPrio($target_id, $clang, 1, 0);

    		$generate_ids[$new_id] = false;
    	}
                                                                   
        cjoExtension::registerExtensionPoint('ARTICLE_COPIED', array("source_id" => $id, 
                                                                     "target_id" => $new_id),
                                                                     true);
    	self::toggleStartpageArticle('', $target_id);

    	$generate_ids[$id] = false;
    	$generate_ids[$target_id] = false;

    	if ($process_all) self::generateArticles($generate_ids);

    	return $new_id;
    }

    /**
     * Kopieren einer Kategorie in eine andere
     *
     * @param $from_id KategorieId der Kategorie, die kopiert werden soll (Quelle)
     * @param $to_id   KategorieId der Kategorie, IN die kopiert werden soll (Ziel)
     */
    public static function copyArticleRecrusive($id, $target_id, $level = 0) {

    	global $CJO, $I18N, $generate_ids;

    	if ($level > 10) return false;

        if (!$CJO['USER']->hasPerm("copyArticle[]") ||
    	    !$CJO['USER']->hasCatPermWrite($id,false) ||
    	    $CJO['USER']->hasPerm('editContentOnly[]')) {
    	    cjoMessage::addError($I18N->msg('msg_no_rights'));
    	    return false;
    	}

    	$id = (int) $id;
    	$target_id = (int) $target_id;
        $real_target_id = false;

    	$generate_ids = array();

    	$article = OOArticle::getArticleById($id);
    	$target = OOArticle::getArticleById($target_id);

    	if ($level == 0) {

    	    if (!OOArticle::isValid($article) || (
        	    $target_id != 0 && !OOArticle::isValid($target))) {
        	    cjoMessage::addError($I18N->msg('msg_error_copy_article'));
        	    return false;
        	}

    		if ($target_id > 0 && ($id == $target_id ||
    		    strpos($target->getPath(), '|'.$id.'|') !== false)) {
    		    $real_target_id = $target_id;
    			$target_id = 0;
    		}
    	}

    	$children = $article->getChildren(false);
    	$new_id = self::copyArticle($id, $target_id);

    	if ($new_id === false) {
    	    return false;
    	}

    	$generate_ids[$new_id] = false;

    	foreach(cjoAssistance::toArray($children) as $child) {
    		if (!$child->isStartPage()) {
    			$new_child_id = self::copyArticle($child->getId(), $new_id);
    			$generate_ids[$new_child_id] = false;
    		}
    		else {
    			self::copyArticleRecrusive($child->getId(), $new_id, ($level+1));
    		}
    	}


    	if ($level == 0) {
    	    if ($real_target_id !== false) {
    	        $target_id = $real_target_id;
    	        self::moveArticle($new_id, $target_id);
    	    }
    	    self::generateArticles($generate_ids);

            $target_name = ($target_id > 0) ?  $target->getName() : $I18N->msg('root');

    	}

    	cjoMessage::flushSuccesses();
    	if(!cjoMessage::hasErrors())
    	    cjoMessage::addSuccess($I18N->msg("msg_content_article_rcopied", $article->getName(), $target_name));

    	return true;
    }

    /**
     * Kopiert die Inhalte eines Artikels in einen anderen Artikel
     *
     * @param $id Id des Artikels, aus dem kopiert werden (Quell ArtikelId)
     * @param $target_id Id des Artikel, in den kopiert werden sollen (Ziel ArtikelId)
     * @param [$clang] ClangId des Artikels, aus dem kopiert werden soll (Quell ClangId)
     * @param [$ctype] Limit to a specific ctype
     * @param [$target_clang] ClangId des Artikels, in den kopiert werden soll (Ziel ClangId)
     * @param [$re_id] Id des Slices, bei dem begonnen werden soll
     */
    public static function copyContent($id, $target_id, $clang=0, $target_clang=0, $ctype=-1, $re_id=0) {

    	global $CJO, $I18N;

        if ($id == $target_id && $clang == $target_clang) {
            cjoMessage::addError($I18N->msg('msg_error_copy_content_self'));
            return false;
        }
    	
        $id = (int) $id;
    	$target_id = (int) $target_id;
    
        if ($id == $target_id && $clang == $target_clang) {
            cjoMessage::addError($I18N->msg('msg_error_copy_content_self'));
            return false;
        }
    	
	    $article = OOArticle::getArticleById($id);
    	$target = OOArticle::getArticleById($target_id);
    	$limit_ctype = isset($CJO['CTYPE'][$ctype]) ? true : false;

    	if (!OOArticle::isValid($article) ||
    	    !OOArticle::isValid($target)) {
    	    cjoMessage::addError($I18N->msg('msg_error_copy_content'));
    	    return false;
    	}
    	
    	

        if ($article->getTemplateId() != $target->getTemplateId()) {
            cjoMessage::addError($I18N->msg('msg_error_copy_content_template_mismatch'));
            return false;
        }

        if (!$CJO['USER']->hasCatPermWrite($id)) {
	        cjoMessage::addError($I18N->msg('msg_no_rights').$id);
    	    return false;
    	}

        if (!$CJO['USER']->hasCatPermWrite($target_id,true)) {
            cjoMessage::addError($I18N->msg('msg_no_target_rights'));
    	    return false;
    	}
    	
    	$sql = new cjoSql();
    	$sql->setQuery("SELECT *
    				   FROM ".TBL_ARTICLES_SLICE."
    				   WHERE re_article_slice_id='".$re_id."' AND
    				   article_id='".$id."' AND
    				   clang='".$clang."'".
    	               $add_qry);

    	if ($sql->getRows() == 1) {
    	    
            $slice_id = $sql->getValue("id");
                
            if (!$limit_ctype || $ctype == $sql->getValue("ctype")) {
                
                $target_re_slice_id = cjoSlice::getLastSliceId($target_id, $target_clang);
    
                $insert = new cjoSql();
        	    $insert->setTable(TBL_ARTICLES_SLICE);
    
        	    $columns = cjoSlice::getTableColumns();
    
        		foreach($columns as $colname) {
                        
        		    switch($colname) {
                        case "id" : 
                                continue;   		        
        		        case "re_article_slice_id" : 
        		                $value = $target_re_slice_id;
                                break;
                        case "clang":
                                $value = $target_clang;
                                break;
                        case "article_id":
                                $value = $target_id;
                                break;
                        default:
                                $value = $sql->getValue($colname);
        		    }
            		$insert->setValue($colname, $value);
        		}
    
            	$insert->addGlobalCreateFields();
            	$insert->addGlobalUpdateFields();
            	$insert->Insert();
    
        		$new_slice_id = $insert->getLastID();
            }
    		self::copyContent($id, $target_id, $clang, $target_clang, $ctype, $slice_id);
    		return true;
    	}

	    $update = new cjoSql();
		$update->setTable(TBL_ARTICLES);
		$update->addGlobalUpdateFields();
		$update->setWhere("id='".$target_id."' AND clang='".$target_clang."'");
		$update->Update();

    	self::generateArticle($target_id);
    	
    	if (!$limit_ctype) {
    	   cjoMessage::addSuccess($I18N->msg("msg_copy_content", $article->getName(), $target->getName()));
    	}
    	else {
           cjoMessage::addSuccess($I18N->msg("msg_copy_content_ctype", $article->getName(), $target->getName(), $CJO['CTYPE'][$ctype]));
    	}
    	return true;
    }


    /**
     * Löscht eine Clang
     *
     * @param $id Zu löschende ClangId
     */
    public static function deleteCLang($id = 0) {

    	global $CJO, $I18N;

    	if ($id == 0) return false;

    	$status = true;

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS." WHERE id != '".$id."' ORDER BY id");

    	if ($sql->getRows() < 1) return false;

    	$new_content = "// --- DYN\r\n";
    	for ($i = 0; $i < $sql->getRows(); $i++) {
    		$new_content .= "\r\n\$CJO['CLANG']['".$sql->getValue("id")."'] = \"".$sql->getValue("name")."\";";
    		$new_content .= "\r\n\$CJO['CLANG_ISO']['".$sql->getValue("id")."'] = \"".$sql->getValue("iso")."\";";
    		$sql->next();
    	}
    	$new_content .= "\r\n// --- /DYN";

    	$status = self::replaceFileContents($CJO['FILE_CONFIG_LANGS'], $new_content);

    	foreach (cjoAssistance::toArray(glob($CJO['FOLDER_GENERATED_ARTICLES']."/*.".$id.".*")) as $filename) {
    	    @ unlink($filename);
    	}

    	if ($status) {
        	$sql->flush();
        	$status = $sql->setQuery("DELETE FROM ".TBL_CLANGS." WHERE id='".$id."'");
    	}
    	if ($status) {
        	$sql->flush();
        	$status = $sql->setQuery("DELETE FROM ".TBL_ARTICLES." WHERE clang='".$id."'");
    	}
    	if ($status) {
        	$sql->flush();
        	$status = $sql->setQuery("DELETE FROM ".TBL_ARTICLES_SLICE." WHERE clang='".$id."'");
    	}
    	if ($status) {
    		cjoMessage::addSuccess($I18N->msg("msg_clang_deleted"));
    	    unset ($CJO['CLANG'][$id]);
        	// ----- EXTENSION POINT
        	cjoExtension::registerExtensionPoint('CLANG_DELETED', array ('id' => $id));
    	}
    	return $status;
    }

    /**
     * Erstellt eine Clang
     *
     * @param $id   Id der Clang
     * @param $name Name der Clang
     */
    public static function addCLang($id, $name, $iso) {

    	global $CJO, $I18N;

    	$status = true;

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS." WHERE id='".$id."'");

    	if ($sql->getRows() > 0) return false;

    	$sql->flush();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS." ORDER BY id");

    	$new_content = "// --- DYN\r\n";
    	for ($i = 0; $i < $sql->getRows(); $i++) {
    		$new_content .= "\r\n\$CJO['CLANG']['".$sql->getValue("id")."'] = \"".$sql->getValue("name")."\";";
    		$new_content .= "\r\n\$CJO['CLANG_ISO']['".$sql->getValue("id")."'] = \"".$sql->getValue("iso")."\";\r\n";
    		$sql->next();
    	}
    	$new_content .= "\r\n\$CJO['CLANG']['".$id."'] = \"".$name."\";";
    	$new_content .= "\r\n\$CJO['CLANG_ISO']['".$id."'] = \"".$iso."\";";
    	$new_content .= "\r\n// --- /DYN";

    	$status = self::replaceFileContents($CJO['FILE_CONFIG_LANGS'], $new_content);

    	$sql->flush();
    	$sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE clang='0'");

    	if ($status) {

            $fields = cjoSql::getFieldNames(TBL_ARTICLES);
            
        	$insert = new cjoSql();
        	for ($i = 0; $i < $sql->getRows(); $i++) {

        		$insert->flush();
        		$insert->setTable(TBL_ARTICLES);
        		foreach($fields as $key=>$value ) {

        			if ($value == "pid") {
        			    continue;
        			}
        			if ($value == "clang") {
        				$insert->setValue("clang", $id);
        				continue;
        			}
        			if ($value == "status") {
        				$insert->setValue("status", "0");
        				continue;
        			}
        			$insert->setValue($value, $sql->getValue($value));
        		}
        		$insert->Insert();
        		$sql->next();
        	}

        	$insert->flush();
        	$insert->setTable(TBL_CLANGS);
        	$insert->setValue('id', $id);
        	$insert->setValue('name', $name);
        	$insert->setValue('iso', $iso);
        	$status = $insert->Insert();
    	}
    	if (!$status) {
    		$sql->flush();
    		$sql->setQuery("DELETE FROM ".TBL_ARTICLES." WHERE clang='".$id."'");
    	}
    	else {
    	    cjoMessage::addSuccess($I18N->msg("msg_clang_saved"));

        	cjoExtension::registerExtensionPoint('CLANG_ADDED', array ('id' => $id,'name' => $name));
        	self::generateAll();
    	}
    	return $status;
    }

    /**
     * Ändert eine Clang
     *
     * @param $id   Id der Clang
     * @param $name Name der Clang
     */
    public static function editCLang($id, $name, $iso) {

    	global $CJO, $I18N;

    	$status = true;

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS." WHERE id='".$id."'");
    	if ($sql->getRows() != 1) return false;

    	$sql->flush();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS." ORDER BY id");

    	$new_content = "// --- DYN\r\n";
    	for ($i = 0; $i < $sql->getRows(); $i++) {
    		$new_content .= "\r\n\$CJO['CLANG']['".$sql->getValue("id")."'] = \"".$sql->getValue("name")."\";";
    		$new_content .= "\r\n\$CJO['CLANG_ISO']['".$sql->getValue("id")."'] = \"".$sql->getValue("iso")."\";\r\n";
    		$sql->next();
    	}
    	$new_content .= "\r\n// --- /DYN";

    	$status = self::replaceFileContents($CJO['FILE_CONFIG_LANGS'], $new_content);

    	if ($status) {

    	    $update = $sql;
        	$update->setTable(TBL_CLANGS);
        	$update->setWhere("id='".$id."'");
        	$update->setValue('name', $name);
        	$update->setValue('iso', $iso);
        	$update->Update($I18N->msg("msg_clang_updated"));

        	cjoExtension::registerExtensionPoint('CLANG_UPDATED', array('id' => $id,
                                                            			'name' => $name,
                                                            			'iso' => $iso));
        	self::generateAll();
    	}
    	return $status;
    }

    public static function generateClangs() {

        global $CJO, $I18N;

        $sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_CLANGS." ORDER BY id");
    	$new_content = "// --- DYN\r\n";
    	for ($i = 0; $i < $sql->getRows(); $i++) {
    		$new_content .= "\r\n\$CJO['CLANG']['".$sql->getValue("id")."'] = \"".$sql->getValue("name")."\";";
    		$new_content .= "\r\n\$CJO['CLANG_ISO']['".$sql->getValue("id")."'] = \"".$sql->getValue("iso")."\";";
    		$sql->next();
    	}
    	$new_content .= "\r\n// --- /DYN";
    	self::replaceFileContents($CJO['FILE_CONFIG_LANGS'], $new_content);
    }
    
    public static function syncCLang($id, $sync_master, $params=array()) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_ARTICLES);
        $sql->setWhere(array('clang'=>$sync_master));
        $sql->Select();
        $results = $sql->getArray();
        $update = &$sql;
        
        foreach ($results as $key=>$article) {
            
            $update->flush();
            $update->setTable(TBL_ARTICLES);
            $update->setWhere('clang='.$id.' AND id='.$article['id']);
            $update->setValue('updatedate',time());            

            foreach($params as $key=>$value) {
                if (!$value) continue;
                if ($key == 'online_from_to') {
                    $update->setValue('online_from',$article['online_from']);
                    $update->setValue('online_to',$article['online_to']);    
                    continue;                
                }
                $update->setValue($key,$article[$key]);
            }
            $update->Update();
        }
    }

    /**
     * Schreibt Addoneigenschaften in die Datei $CJO['FILE_CONFIG_ADDONS']
     * @param array Array mit den Namen der Addons aus dem Verzeichnis addons/
     */
    public static function generateAddons($ADDONS, $debug = false) {

    	global $CJO;
    	natsort($ADDONS);

    	$new_content = "// --- DYN\r\n\r\n";
    	foreach ($ADDONS as $cur) {

    		$CJO['ADDON']['menu'][$cur] = str_replace('"','',$CJO['ADDON']['menu'][$cur]);

    		if (!OOAddon :: isInstalled($cur)) {
    			$CJO['ADDON']['install'][$cur] = 0;
    		}
    		if (!OOAddon :: isActivated($cur)) {
    			$CJO['ADDON']['status'][$cur] = 0;
    		}
    		if ($CJO['ADDON']['menu'][$cur] == '')
    			$CJO['ADDON']['menu'][$cur] = 0;

    		if (strlen($CJO['ADDON']['menu'][$cur]) > 1 ||
    			!is_numeric($CJO['ADDON']['menu'][$cur]))
    			$CJO['ADDON']['menu'][$cur] = '"'.$CJO['ADDON']['menu'][$cur].'"';

    		$new_content .= "\$CJO['ADDON']['install']['$cur'] = ".$CJO['ADDON']['install'][$cur].";\r\n".
    						"\$CJO['ADDON']['status']['$cur'] = ".$CJO['ADDON']['status'][$cur].";\r\n".
    						"\$CJO['ADDON']['menu']['$cur'] = ".$CJO['ADDON']['menu'][$cur].";\r\n\r\n";
    	}
    	$new_content .= "// --- /DYN";

    	if (!cjoAssistance::isReadable($CJO['FILE_CONFIG_ADDONS'])) {
            return false;
        }
    	if (!cjoAssistance::isWritable($CJO['FILE_CONFIG_ADDONS'])) {
    	    return false;
        }
    	return self::replaceFileContents($CJO['FILE_CONFIG_ADDONS'], $new_content);
    }

	/**
	 * Rewrites the settings file
	 * @param string $config_file
	 * @param array/string $var
	 * @param string $pattern
	 * @return bool
	 */

	public static function updateSettingsFile($config_file, $var = '_POST', $pattern = NULL) {
	    
		if (is_string($var)) {
            global $$var;
            $variable = &$$var;
        }
		if (is_array($var))  $variable = $var;
        if (!is_array($variable) || empty($variable)) return false;

        // get array to rewrite
        $config_data = file_get_contents($config_file);
		// default pattern
		if($pattern == NULL) $pattern = "!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['\$key'\].?\=.?)[^;]*!";
		// replace array values
		foreach($variable as $key=>$value) {
			 eval ("\$eval_pattern = \"".$pattern."\";");
			if (is_array($value)) $value = implode('|',$value);
			$value = addcslashes(addslashes($value), '$');

			$config_data = preg_replace($eval_pattern,"\\1\"".$value."\"",$config_data);
		}
		return self::putFileContents($config_file, $config_data);
	}

    public static function replaceFileContents($filename, $new_content) {

    	if ($new_content == '') return false;

    	$pattern = '/^(.*)(\/\/.---.DYN.*\/\/.---.\/DYN)(.*)$/sU';

    	$old_content = @file_get_contents($filename);
    	$new_content = preg_replace($pattern, '\2', $new_content);
    	$new_content = preg_replace($pattern, '\1'.$new_content.'\3', $old_content);

    	return self::putFileContents($filename, $new_content);
    }

    public static function putFileContents($filename, $new_content) {

        global $CJO;

        if (file_exists($filename)) {
            if (!cjoAssistance::isWritable($filename)) return false;
        }
        elseif (is_dir($filename.'../')) {
            if (!cjoAssistance::isWritable($filename.'../')) return false;
        }

    	$temp_file = $filename.'.'.@getmypid();
    	$state = file_put_contents($temp_file, $new_content);
    	@chmod($temp_file, $CJO['FILEPERM']);


    	if ($state != false) {
    		@unlink($filename);
    		@rename($temp_file, $filename);
    	    @chmod($temp_file, $CJO['FILEPERM']);    		
    		return true;
    	}
    	else{
    		return false;
    	}
    }

    public static function processImage($process_image = false) {

        global $CJO;

        $mypage = 'image_processor';

        if (!$process_image) {
            $process_image = cjo_get('process_image', 'string',
                                     $CJO['ADDON']['settings'][$mypage]['error_img'],
                                     true);
        }
        
    	$set = array();

    	if (is_readable($CJO['MEDIAFOLDER']."/".$process_image)) {
    		$process_image = $CJO['MEDIAFOLDER']."/".$process_image;
    	}
    	else if (!is_readable($process_image)) {
    	    $process_image = $CJO['MEDIAFOLDER']."/".$CJO['ADDON']['settings'][$mypage]['error_img'];
    	}

        $file_size = getimagesize($process_image);
    	$path_info = pathinfo($process_image);
    	$set['imagefile'] = $path_info['basename'];

    	$set['x'] = cjo_get('x', 'int');
    	$set['y'] = cjo_get('y', 'int');

   	    if ($set['x'] && !$set['y']) $set['y'] = null;
    	if ($set['y'] && !$set['x']) $set['x'] = null;

    	$set['ry'] = $set['x'] * $file_size[1] / $file_size[0];
    	$set['rx'] = $set['y'] * $file_size[0] / $file_size[1];

    	if ($set['x'] && $set['y'] > $set['ry']) $set['x'] = null;
    	if ($set['y'] && $set['x'] > $set['rx']) $set['y'] = null;

    	if (cjo_get('resize', 'bool'))
    	    $set['resize'] = "resize=".cjo_get('resize', 'string' ,'' ,true);

    	if (cjo_get('aspectratio', 'bool'))
    	    $set['aspectratio'] = "aspectratio=".cjo_get('aspectratio', 'string', '', true);

    	if (cjo_get('brand_on_off', 'bool'))
    	    $set['brand_on_off'] = "brand_on_off=".cjo_get('brand_on_off', 'string', '', true);

    	if (cjo_get('brandimg', 'bool'))
    	    $set['brandimg'] = "brandimg=".cjo_get('brandimg', 'string', '', true);

    	if (cjo_get('jpg-quality', 'bool'))
    	    $set['jpg_quality'] = "jpg-quality=".cjo_get('jpg-quality' ,'int' ,'', true);

    	$filename = imageProcessor_getImg($set['imagefile'],
                                          $set['x'],
                                          $set['y'],
                                          $set['resize'],
                                          $set['aspectratio'],
                                          $set['brand_on_off'],
                                          $set['brandimg'],
                                          $set['jpg_quality']);

        cjoClientCache::sendImage($filename, $file_size['mime'], 'frontend');
    }
}