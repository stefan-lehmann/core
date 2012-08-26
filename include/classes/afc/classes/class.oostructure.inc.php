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
 * Klasse zur Abbiludung von Artikel/Kategorie UL-LI Strukturen
 */
class OOStructure {
    /*
     * Public Attribute
     */

    // clang der auszugebenden struktur
    public $clang;
    // level limitierung (-1 = kein Limit)
    public $depth_limit;
    // Kategorien ingorieren?
    public $ignore_categories;
    // Startartikel ignorieren?
    public $ignore_startarticles;
    // Artikel ignorieren?
    public $ignore_articles;
    // offline Artikel ignorieren?
    public $ignore_offlines;
    // Artikel "ohne namen" ignorieren?
    public $ignore_empty;
    // Kategory die als Wurzelverzeichnis genutzt werden soll
    // (int, object, oder array aus object u./o. int)
    public $root_category;

    // Tag des umgebenden Elements
    public $main_tag;
    // Tag von Eltern-Elementen
    public $parent_tag;
    // Tag der Kind-Elemente
    public $child_tag;

    // Attribute des umgebenden Elements
    public $main_attr;
    // Attribute von Eltern-Elementen
    public $parent_attr;
    // Attribute von Kind-Elementen
    public $child_attr;

    // Link für die Elemente
    public $link;
    // Spacer für Artikel "ohne namen"
    public $empty_value;

    /*
     * Private Attribute
     */

    // aktueller level der Ausgabe
    public $_depth;

    public function OOStructure($main_attr = '', $parent_attr = '', $child_attr = '') {

        $this->_depth = 0;
        $this->depth_limit = -1;
        $this->ignore_categories = false;
        $this->ignore_startarticles = false;
        $this->ignore_articles = false;
        $this->ignore_offlines = false;
        $this->ignore_empty = false;
        $this->clang = false;

        $this->root_category = null;

        $this->main_tag = 'ul';
        $this->parent_tag = 'ul';
        $this->child_tag = 'li';

        $this->main_attr = $main_attr != '' ? ' '.$main_attr : '';
        $this->parent_attr = $parent_attr != '' ? ' '.$parent_attr : '';
        $this->child_attr = $child_attr != '' ? ' '.$child_attr : '';

        $this->empty_value = '&nbsp;';
        $this->link = 'javascript:void(0);';
    }

    public function _formatNodeValue($name, & $node) {
        return $node->toLink();
    }

    public function _formatNode(& $node) {

        if ($this->ignore_startarticles &&
            OOArticle :: isValid($node) &&
            $node->isStartPage()) {
            return '';
        }

        $name = $node->getName();

        if ($name == '') {
            if ($this->ignore_empty) {
                return '';
            }
            else {
                $name = $this->empty_value;
            }
        }

        if ($this->depth_limit > 0 && $this->_depth >= $this->depth_limit) {
            return '';
        }

        $s = '';
        $s_self = '';
        $s_child = '';
        // Kategorien ingorieren?
        if (OOCategory :: isValid($node) && !$this->ignore_categories ||
            OOArticle :: isValid($node) && !($this->ignore_startarticles && $node->isStartPage()))
        {
            $s_self .= $this->_formatNodeValue($name, $node);

            if (OOCategory :: isValid($node)) {

                $childs = $node->getChildren($this->ignore_offlines, $this->clang);
                $articles = $node->getArticles($this->ignore_offlines, $this->clang);

                if (is_array($childs) &&
                    count($childs) > 0 || is_array($articles) &&
                    count($articles) > 0 && !$this->ignore_articles) {

                    $this->_depth++;

                    if (is_array($childs)) {
                        foreach ($childs as $child) {
                            $s_child .= $this->_formatNode($child);
                        }
                    }

                    // Artikel ingorieren?
                    if (!$this->ignore_articles) {
                        if (is_array($articles)) {
                            foreach ($articles as $article) {
                                //                if ($article->isStartPage())
                                //                {
                                //                  continue;
                                //                }

                                $s_child .= '<'.$this->child_tag.$this->child_attr.'>';
                                $s_child .= $this->_formatNodeValue($article->getName(), $article);
                                $s_child .= '</'.$this->child_tag.'>';
                            }
                        }
                    }

                    // Parent Tag nur erstellen, wenn auch Childs vorhanden sind
                    if ($s_child != '') {
                        $s_self .= '<'.$this->parent_tag.$this->parent_attr.'>';
                        $s_self .= $s_child;
                        $s_self .= '</'.$this->parent_tag.'>';
                    }
                    $this->_depth--;
                }
            }

            // Parent Tag nur erstellen, wenn auch Childs vorhanden sind
            if ($s_self != '') {
                $s .= '<'.$this->child_tag.$this->child_attr.'>';
                $s .= $s_self;
                $s .= '</'.$this->child_tag.'>';
            }
        }
        return $s;
    }

    public function get() {

        $s = '';
        $s_self = '';
        $this->_depth = 0;

        if ($this->root_category === null) {
            $root_nodes = OOCategory :: getRootCategories($this->ignore_offlines, $this->clang);
        }
        else {
            if (is_int($this->root_category) && $this->root_category === 0) {
                $root_nodes = OOArticle :: getRootArticles($this->ignore_offlines, $this->clang);
            }
            else {
                $root_nodes = array ();
                $root_category = OOCategory :: _getCategoryObject($this->root_category);
                // Rootkategorien selbst nicht anzeigen, nur deren Kind-Elemente
                if (is_array($root_category)) {
                    foreach ($root_category as $root_cat) {
                        $this->_appendChilds($root_cat, $root_nodes);
                        $this->_appendArticles($root_cat, $root_nodes);
                    }
                } else {
                    $this->_appendChilds($root_category, $root_nodes);
                    $this->_appendArticles($root_category, $root_nodes);
                }
            }
        }

        if (is_array($root_nodes)) {
            foreach ($root_nodes as $node) {
                $s_self .= $this->_formatNode($node);
            }

            // Parent Tag nur erstellen, wenn auch Childs vorhanden sind
            if ($s_self != '') {
                $s .= '<'.$this->main_tag.$this->main_attr.'>';
                $s .= $s_self;
                $s .= '</'.$this->main_tag.'>';
            }
        }
        return $s;
    }

    public function & _appendChilds(& $source, & $target) {

        $childs = $source->getChildren($this->ignore_offlines, $this->clang);
        if (is_array($childs)) {
            foreach ($childs as $child) {
                $target[] = $child;
            }
        }
    }

    public function & _appendArticles(& $source, & $target) {

        $articles = $source->getArticles($this->ignore_offlines, $this->clang);
        if (is_array($articles)) {
            foreach ($articles as $article) {
                $target[] = $article;
            }
        }
    }

    public function show() {
        echo $this->get();
    }
}

/**
 * Klasse zur Abbiludung von Artikel/Kategorie DIV Strukturen
 */
class OODivStructure extends OOStructure {

    public function OODivStructure() {
        $this->OOStructure();
        $this->main_tag = 'div';
        $this->parent_tag = 'div';
        $this->child_tag = 'div';
    }
}