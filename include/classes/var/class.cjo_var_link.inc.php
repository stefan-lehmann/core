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
 * CJO_LINK_BUTTON,
 * CJO_LINK,
 * CJO_LINK_ID,
 * CJO_LINKLIST_BUTTON,
 * CJO_LINKLIST
 */

class cjoVarLink extends cjoVars {
    // --------------------------------- Actions

    public function getACRequestValues($CJO_ACTION) {

        $values = cjo_request('LINK', 'array');

        for ($i = 1; $i <= 10; $i++) {
            $link     = isset($values[$i]) ? stripslashes($values[$i]) : '';
            $CJO_ACTION['LINK'][$i] = $link;
        }
        return $CJO_ACTION;
    }

    public function getACDatabaseValues($CJO_ACTION, & $sql) {

        for ($i = 1; $i <= 10; $i++) {
            $CJO_ACTION['LINK'][$i] = $this->getValue($sql, 'link'. $i);
        }
        return $CJO_ACTION;
    }

    public function setACValues(& $sql, $CJO_ACTION, $escape = false) {

        global $CJO;

        for ($i = 1; $i <= 10; $i++) {
            $this->setValue($sql, 'link'. $i, $CJO_ACTION['LINK'][$i], $escape);
        }
    }

    // --------------------------------- Output

    public function getBEOutput(& $sql, $content) {
        return $this->getOutput($sql, $content);
    }

    public function getBEInput(& $sql, $content) {
        $content = $this->getOutput($sql, $content);
        $content = $this->matchLinkButton($sql, $content);
        return $content;
    }

    public function getOutput(& $sql, $content) {
        $content = $this->matchLink($sql, $content);
        $content = $this->matchLinkId($sql, $content);
        return $content;
    }

    /**
     * @see cjo_var::handleDefaultParam
     */
    public function handleDefaultParam($varname, $args, $name, $value) {

        switch($name) {
            case 'width' :
                $args[$name] = (int) $value;
                break;                
        }
        return parent::handleDefaultParam($varname, $args, $name, $value);
    }

    /**
     * Button f�r die Eingabe
     */
    private function matchLinkButton(& $sql, $content) {

        global $CJO;

        $def_category = '';
        $article_id = cjo_request('article_id', 'int');
        
        if ($article_id != 0) {
            $art = OOArticle::getArticleById($article_id);
            $def_category = $art->getParentId();
        }

        $var = 'CJO_LINK_BUTTON';
        $matches = $this->getVarParams($content, $var);

        foreach ($matches as $match) {

            list ($param_str, $args) = $match;
            list ($id, $args) = $this->extractArg('id', $args, 0);
            
            if (!isset($args['width'])) $args['width'] = 297;
            $args['width'] = $args['width'] .= 'px';
            
            $replace = $this->getLinkButton($id, $this->getValue($sql, 'link'.$id), array('style' => 'width: '.$args['width']));
            $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
            $content = str_replace($var.'['.$param_str.']', $replace, $content);
        }
        return $content;
    }


    /**
     * Wert für die Ausgabe
     */
    private function matchLink(& $sql, $content) {

        $var = 'CJO_LINK';
        $matches = $this->getVarParams($content, $var);        
        $performed = array();
        
        foreach ($matches as $match) {

            list ($param_str, $args) = $match;
            list ($id, $args) = $this->extractArg('id', $args, 0);
            
            if (!empty($performed[$var][$id]) || $id < 1 || $id > 10) continue;  
            
            $replace = '';
            $link_id = $this->getValue($sql, 'link'.$id);
            if (!empty($link_id)) $replace = 'contejo://'.$link_id;            
            $replace = $this->handleGlobalVarParams($var, $args, $replace);
            $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
            $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);            
            $performed[$var][$id] = true; 
        }
        return $content;
    }

    /**
     * Wert für die Ausgabe
     */
    private function matchLinkId(& $sql, $content) {

        $var = 'CJO_LINK_ID';
        $matches = $this->getVarParams($content, $var);        
        $performed = array();
        
        foreach ($matches as $match) {

            list ($param_str, $args) = $match;
            list ($id, $args) = $this->extractArg('id', $args, 0);

            if (!empty($performed[$var][$id]) || $id < 1 || $id > 10) continue;  
                        
            $replace = $this->getValue($sql, 'link'.$id);
            $replace = $this->handleGlobalVarParams($var, $args, $replace);
            $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
            $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);            
            $performed[$var][$id] = true;
        }
        return $content;
    }

    /**
     * Gibt das Button Template zur�ck
     */
    private function getLinkButton($id, $link_id, $attributes = array(), $id_tag = 'cjo_linkbutton_') {

        global $CJO, $I18N;

        $link_button = new cjoLinkButtonField('LINK['.$id.']', $I18N->msg('label_linkbutton'), $attributes, $id_tag.$id);
		$link_button->setDisconnectAction('cjo.jconfirm(\''.$I18N->msg('label_remove_link').' ?\', \'cjo.disconnectLink\', [$(this)]); return false;');
        $link_button->setValue($link_id);
        return $link_button->get();
    }
}