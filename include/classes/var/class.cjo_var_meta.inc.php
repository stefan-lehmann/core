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
 * CJO_META[title]
 * CJO_META[id=1 title=1]
 */

class cjoVarMeta extends cjoVars {

   // private $meta = array();


    // --------------------------------- Output

    public function getTemplate($content, $article_id = false, $template_id = false) {

        global $CJO;

        if (empty($article_id)) $article_id = $CJO['ARTICLE_ID'];
        if (empty($article_id)) $article_id = cjo_request('article_id', 'cjo-article-id', $CJO['START_ARTICLE_ID']);

        $this->getMeta($article_id);

        $content = $this->matchMeta($content, $article_id);
        $content = $this->matchMetaFile($content, $article_id);
        return $content;
    }

    /**
     * @see cjo_var::handleDefaultParam
     */
    public function handleDefaultParam($varname, $args, $name, $value) {

        global $CJO;

        switch($name) {
            case '1' :
            case 'id' :
                $id = (int) $value;
                if (!empty($id)) {
                    $args['id'] = $id;
                } else {
                    $args['get'] = $value;
                    return $args;
                }
                break;
            case 'titel' :
            case 'title' :
            case 'keywords' :
            case 'description' :
            case 'author' :
            case 'file' :
                $args['get'] = (string) $name;
                break;
            case 'crop_num' :
                $args['id'] = (int) $value;
                break;
            case 'width' :
            case 'height' :
                $args[$name] = (int) $value;
                break;
            case 'get_src' :
            case 'crop_auto' :
                $args[$name] = (bool) $value;
                break;
            case 'get' :
                $args['get'] = (string) $value;
                break;
        }

        return parent::handleDefaultParam($varname, $args, $name, $value);
    }

    private function matchMeta($content, $article_id = 0) {

        global $CJO;

        $var = 'CJO_META';
        $matches = $this->getVarParams($content, $var);
    	$meta = array();

        foreach ($matches as $match) {

            list ($param_str, $args)  = $match;
            list ($get, $args) = $this->extractArg('get', $args, 0);
            
            if ($get == 'description' && $this->meta[$article_id][$get] == '') {
                $replace = self::getDescriptionFromContent($content);
                if ($replace == '...') {
                    cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoVarMeta::replaceDescriptionByExtension');
                    continue;   
                }
            }
            else {
                $replace = $this->meta[$article_id][$get];
            }
            $content = str_replace($var.'['.$param_str.']', $replace, $content);
        }
        return $content;
    }
    
    private static function getDescriptionFromContent($content) {
        preg_match('/<p>(.*?)<\/p>/s', $content, $matches);
        $matches[1] = strip_tags($matches[1]);
        $matches[1] = preg_replace('/\s{1,}/', ' ', $matches[1]);      
        $matches[1] = substr($matches[1], 0, 147).'...';     
        return  $matches[1];
    }
    
    public static function replaceDescriptionByExtension($params) {
        $replace  = self::getDescriptionFromContent($params['subject']); 
        return  str_replace('CJO_META[description]', $replace, $params['subject']);
    }

    private function matchMetaFile($content, $article_id) {

        $var = 'CJO_META_FILE';

        $matches = $this->getVarParams($content, $var);
        foreach ($matches as $match) {

            $replace = '';

            list ($param_str, $args) = $match;
            list ($id, $args) = $this->extractArg('id', $args, '-');

            if ($id != '-') {
                $params = array('crop_num'=>$id);
                if (!empty($args['get_src'])) $params['get_src']   = true;  
            }
            else {
                $params = array();
                if (isset($args['width']))        $params['width']     = $args['width']; 
                if (isset($args['height']))       $params['height']    = $args['height']; 
                if (isset($args['crop_num']))     $params['crop_num']  = $args['crop_num'];  
                      
                if (!empty($args['crop_auto']) && 
                    empty($params['crop_num']))   $params['crop_auto'] = true;  
                if (!empty($args['get_src']))     $params['get_src']   = true;    
            }
            $file = $this->meta[$article_id]['file'];

            if (!empty($file)) {
                $replace = OOMedia::isImage($file) ? OOMedia::toThumbnail($file,'', $params) : $file;
            }
            
            $content = str_replace($var.'['.$param_str.']', $replace, $content);
        }
        return $content;
    }

    private function getMeta($article_id){

        global $CJO;

        $meta = array();

       	$article = OOArticle::getArticleById($article_id);
        $tree = cjoAssistance::toArray($article->_path.$article_id);

        krsort($tree);

        $meta['title'] = ($article->getTitle() == '')
            ? $article->getName()
            : $article->getTitle();

        $meta['author'] = $article->getAuthor();

    	if ($article_id != $CJO['START_ARTICLE_ID'])
        	$tree[] = $CJO['START_ARTICLE_ID'];

        foreach ($tree as $cat_id) {

            if ($cat_id == 0) continue;

        	if ($article_id != $cat_id)
    			$article = OOArticle::getArticleById($cat_id);
            
            if (!OOArticle::isValid($article)) continue;

    		$file        = $article->getFile(false);
    		$description = $article->getDescription();
    		$keywords    = $article->getKeywords();

        	if (empty($meta['file']) &&
        	    !empty($file)) {
                $meta['file'] = $file;
            }

    		if (empty($meta['description'])) {
                $meta['description'] = $description;
            }

            if (empty($meta['keywords'])) {
                $meta['keywords'] = $keywords;
            }
        }
        $this->meta[$article_id] = $meta;
    }
}