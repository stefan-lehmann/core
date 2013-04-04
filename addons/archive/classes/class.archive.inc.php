<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  archive
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

class cjoArchive {
    
    private static $addon = 'archive';
    
    
    public static function archiveArticles() {
        
        if (!cjoAddon::getParameter('CATEGORIES', self::$addon) || 
            cjoAddon::getParameter('DISABLED', self::$addon))  return false;
        
        $cats     = cjoAssistance::toArray(cjoAddon::getParameter('CATEGORIES', self::$addon));
        $duration = (int) cjoAddon::getParameter('DURATION', self::$addon, 120*60*60*24);        
        $now      = time();
        $add_sql  = array();
        
        foreach($cats as $cat) {
            $add_sql[] = "(path LIKE '%|".$cat."|%' OR re_id='".$cat."')";
        }
        
        $sql = new cjoSql();
        $qry = "SELECT id, re_id, path, count(id) AS count ".
        	   "FROM ".TBL_ARTICLES." ". 
               "WHERE ((updatedate < '".($now-$duration)."' AND status=0) ".
               "OR online_to < '".($now-$duration)."') ".
               "AND (".implode(" OR ", $add_sql).")".
               "GROUP BY id";
        $results = $sql->getArray($qry);

        foreach($results as $result) {
            if ($result['count'] != cjoProp::countClangs()) continue;
            self::archive($result);
        }

        cjoGenerate::generateAll();
    }
    
    public static function delete($id) {
        $sql = new cjoSql();
        $sql->setQuery("DELETE FROM `".TBL_28_ARCHIVE_ARTICLES."` WHERE id='".$id."'");
        $sql->flush();
        $sql->setQuery("DELETE FROM `".TBL_28_ARCHIVE_ARTICLES_SLICE."` WHERE article_id='".$id."'");
    }
    
    private static function archive($article) {
        
        self::savePath($article['id'], $article['clang']);
        
        $sql = new cjoSql();
        $sql->setQuery("INSERT IGNORE INTO `".TBL_28_ARCHIVE_ARTICLES."` (SELECT * FROM `".TBL_ARTICLES."` WHERE id='".$article['id']."')");
        $sql->flush();
        $sql->setQuery("DELETE FROM `".TBL_ARTICLES."` WHERE id='".$article['id']."'");
        $sql->flush();
        $sql->setQuery("INSERT IGNORE INTO `".TBL_28_ARCHIVE_ARTICLES_SLICE."` (SELECT * FROM `".TBL_ARTICLES_SLICE."` WHERE article_id='".$article['id']."')");
        $sql->flush();
        $sql->setQuery("DELETE FROM `".TBL_ARTICLES_SLICE."` WHERE article_id='".$article['id']."'");
        
        cjoGenerate::newPrio($article['re_id'], $article['clang'], 0, 1);
        cjoGenerate::toggleStartpageArticle($article['clang'], $article['re_id']);
    }
    
    private static function savePath($id, $clang) {
        
        $article = OOArticle::getArticleById($id, $clang);
        $tree = array('Root');
        
        if (!OOArticle::isValid($article)) return false;
        
        foreach($article->getParentTree() as $parent) {
            $tree[] = $parent->getName();
        }
        
        array_pop($tree);
        
        $insert = new cjoSql();
        $insert->setTable(TBL_28_ARCHIVE_PATHS);
        $insert->setValue('id',$id);
        $insert->setValue('clang',$clang);
        $insert->setValue('path', implode('|',$tree));
        $insert->setValue('createdate', time());
        return $insert->Insert();
        
    }
    
    public static function restore($id, $target) {
        
        $sql = new cjoSql();
        $sql->setQuery("INSERT IGNORE INTO `".TBL_ARTICLES."` (SELECT * FROM `".TBL_28_ARCHIVE_ARTICLES."` WHERE id='".$id."')");
        $sql->flush();
        $sql->setQuery("DELETE FROM `".TBL_28_ARCHIVE_ARTICLES."` WHERE id='".$id."'");
        $sql->flush();
        $sql->setQuery("INSERT IGNORE INTO `".TBL_ARTICLES_SLICE."` (SELECT * FROM `".TBL_28_ARCHIVE_ARTICLES_SLICE."` WHERE article_id='".$id."')");
        $sql->flush();
        $sql->setQuery("DELETE FROM `".TBL_28_ARCHIVE_ARTICLES_SLICE."` WHERE article_id='".$id."'");
        $sql->flush();
        $sql->setQuery("DELETE FROM `".TBL_28_ARCHIVE_PATHS."` WHERE id='".$id."'");
        
        cjoGenerate::moveArticle($id, $target);
        
        foreach(cjoProp::getClangs() as $clang_id) {
            cjoGenerate::newPrio($target, $clang_id, 0, 1);
            cjoGenerate::toggleStartpageArticle($clang_id, $target);
        }
    }

    public static function formatPath($path) {
        $output = array();
        $bsps = '';
        foreach(cjoAssistance::toArray($path) as $key =>$value) {
           $pre = ($key == 0) ? ' ' : $bsps.'|&rarr;';
           $output[] = $pre.str_replace(' ','&nbsp;',$value);
           $bsps .= ($key == 0)  ? "&nbsp;&nbsp;&nbsp;" : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        return '<span style="display:block;text-overflow:ellipsis;display:block;overflow:hidden;width:170px">'.
                implode('<br/>',$output).
                '</span>';
    }
    
}