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
 * CJO_NAVIGATION[nav1]
 * CJO_NAVIGATION[id=nav1 article_id=1 level_depth=2]
 */

class cjoVarNavigation extends cjoVars {
    // --------------------------------- Output

    public function getTemplate($content, $article_id = false) {
        return $this->matchNavigation($content, $article_id);
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
                    $args['name'] = $value;
                    return $args;
                }
                break;
            case 'article_id':
            case 'start_level':
            case 'level_depth':
            case 'clang':
            	$args['_'.$name] =  (int) $value;
            	$args['custom_nav'] = true;
                break;
            case 'curr_nav_id':
            case 'show_sub_levels':
            case 'active_only':
            case 'online_only':
            case 'link_active_id':
            case 'disable_navi_names':
            	$args['_'.$name] =  (bool) $value;
            	$args['custom_nav'] = true;
                break;
            case 'curr_attr':
            case 'set_id':                   
            	$args['_'.$name] = (string) $value;
            	$args['custom_nav'] = true;
                break;
            case 'link_child':                
            case 'loop':
            	$args[$name] =  (bool) $value;
                break;
            case 'text_length':
            	$args[$name] =  (int) $value;
                break;
            case 'text':
            case 'link_text':
            case 'name':             
            case 'prefix':
            case 'root':
            case 'current_id':
            case 'reference':
            	$args[$name] = (string) $value;
                break;
        }
        return parent::handleDefaultParam($varname, $args, $name, $value);
    }

    private function matchNavigation($content, $article_id = false) {

        global $CJO;

        $var = 'CJO_NAVIGATION';
        $matches = $this->getVarParams($content, $var);

        $nav = '__cjo_navigation';
        global $$nav;

        if (!OONavigation::isValid($$nav)) {
            $$nav = new OONavigation($article_id);
            $$nav->genereateNavis();
        }

        foreach ($matches as $match) {

            list ($param_str, $args)  = $match;

            list ($_article_id, $args) = $this->extractArg('_article_id', $args, $article_id);
            list ($name, $args) = $this->extractArg('name', $args, 'default');
            list ($level_depth, $args) = $this->extractArg('_level_depth', $args, null);

            if ($_article_id != $article_id){

                $nav = '__cjo_navigation'.md5($param_str);
                global $$nav;

                if (!OONavigation::isValid($$nav)) {
                    $$nav = new OONavigation($_article_id);
                    $$nav->genereateNavis();
                }
            }
            else {
                $nav = '__cjo_navigation';
            }

            if ($name == 'breadcrumbs' &&
                !$$nav->getNavi($name)) {

                $prefix_text = null;
                if ($args['prefix']) $prefix_text = $args['prefix'];

                $root_name = null;
                if ($args['root']) $root_name = $args['root'];

                $$nav->genereateBreadCrumbs($prefix_text,$root_name);
            }

            if ($name == 'lang' &&
                !$$nav->getNavi($name)) {

                $text_length = null;
                if ($args['text_length']) $text_length = $args['text_length'];

                $$nav->genereateLangNavi($text_length);
            }

            if ($name == 'prevnext' &&
                !$$nav->getNavi($name)) {

                $link_text = null;
                if ($args['link_text']) $parent_link_text = $args['link_text'];

                $loop = null;
                if ($args['loop']) $loop = $args['loop'];
                
                $link_child = null;
                if ($args['link_child']) $link_child = $args['link_child'];
                
                $text_length = null;
                if ($args['text_length']) $text_length = $args['text_length'];                

                $$nav->linkPrefNextArticles($parent_link_text, $loop, $link_child, $text_length);
            }
            
            if ($name == 'pageofpages' &&
                !$$nav->getNavi($name)) {

                $link_text = null;
                if ($args['text']) $text = $args['text'];

                $loop = null;
                if ($args['loop']) $loop = $args['loop'];

                $$nav->linkPageOfPages($text, $loop);
            }            

            if ($name == 'backtolist' &&
                !$$nav->getNavi($name)) {

                $link_text = null;
                if ($args['link_text']) $link_text = $args['link_text'];

                $reference = null;
                if ($args['reference']) $reference = $args['reference'];

                $$nav->linkBackToList($link_text, $reference);
            }

            if (!empty($args['custom_nav'])) {

                $nav = '__cjo_navigation'.md5($param_str);
                global $$nav;
                if (!OONavigation::isValid($$nav)) {
                    $$nav = new OONavigation($_article_id);
                    foreach($args as $key => $arg) {  $$nav->$key = $arg; }
                    $$nav->_level_depth = $level_depth;
                    $$nav->genereateNavis();
                }
            }
			$content = str_replace($var.'['.$param_str.']', $$nav->getNavi($name), $content);
        }
        return $content;
    }
}