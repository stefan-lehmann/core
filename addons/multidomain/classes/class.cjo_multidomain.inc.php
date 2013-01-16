<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  multidomain
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

class cjoMultidomain {

    /**
     * Changes the global vars in order to match with the
     * settings of the current domain. It also redirects if the
     * current article is not part of the current domain.
     * @return void
     */
    public static function getDomain(){

        global $CJO;

        $is_redirect = false;

        $clang = $CJO['CUR_CLANG'];
        $article_id = cjo_get('article_id','cjo-article-id', 0);

        $sql = new cjoSql();
        
        if ($article_id != 0) {
        
            $article_path = OOArticle::getArticlePath($article_id).$article_id;
                
            $qry_where = array();
            foreach(cjoAssistance::toArray($article_path) as $id) {
                $qry_where[] = "root_article_id='".$id."'";
            }             
            $qry = "SELECT * FROM ".TBL_MULTIDOMAIN." WHERE ".implode(' OR ',$qry_where);
        }
        else {
            $qry = "SELECT * FROM ".TBL_MULTIDOMAIN." ORDER BY id";     
        }
        

        $sql->setQuery($qry);       

        if ($sql->getRows() == 0) return false;

        for ($i=0; $i<$sql->getRows(); $i++) {

            $test_domain = preg_match('/^\b'.$sql->getValue('domain').'\b/',
                                      $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'],
                                      $matches);

            if ($test_domain == true || $article_id != 0) {

                $root_article     = OOArticle::getArticleById($sql->getValue('root_article_id'));
                $start_article    = OOArticle::getArticleById($sql->getValue('start_article_id'));
                $notfound_article = OOArticle::getArticleById($sql->getValue('notfound_article_id'));

                if (!OOArticle::isValid($root_article)) return;

                $CJO['MULTIDOMAIN_ID']      = $sql->getValue('id');
                $CJO['SERVER']              = $sql->getValue('domain');
                $CJO['SERVERNAME']          = $sql->getValue('servername');
                $CJO['ERROR_EMAIL']         = $sql->getValue('error_email');
                $CJO['ROOT_ARTICLE_ID']     = $sql->getValue('root_article_id');
                $CJO['START_ARTICLE_ID']    = !OOarticle::isValid($start_article) ? $root_article->_id : $start_article->_id;
                $CJO['NOTFOUND_ARTICLE_ID'] = !OOarticle::isValid($notfound_article) ? $root_article->_id : $notfound_article->_id;

                $is_redirect = true;
                break;
            }
            $sql->next();
        }

        if (!$is_redirect) return false;
  
        $CJO['ARTICLE_ID'] = cjo_request('article_id', 'cjo-article-id', $CJO['START_ARTICLE_ID']);
            
        $article = OOArticle::getArticleById($CJO['ARTICLE_ID'] , $clang);

        $test_path = strpos('|'.$article->_path.$CJO['ARTICLE_ID'] .'|', '|'.$CJO['ROOT_ARTICLE_ID'].'|');

        if ($test_path !== false && $test_domain == true) return false;

        $redirect_article = OOArticle::getArticleById($CJO['START_ARTICLE_ID'], $clang);
        $url  = $_SERVER['HTTPS'] != '' ? 'https://' : 'http://';
        $url .= $CJO['SERVER'].substr($_SERVER['PHP_SELF'],0,(strrpos($_SERVER['PHP_SELF'],'/')+1));
        $url .= $CJO['CONTEJO'] ? '../' : '';
        $url .= cjoRewrite::parseArticleName($redirect_article->getName()).
                                     '.'.
                                     $CJO['START_ARTICLE_ID'].
                                     '.'.
                                     $clang.
                                     '.html';
                                     
        if (cjo_server('SCRIPT_URI','string') == $url) return false;
        
        header('Location: '.$url);
        exit;
    }
}