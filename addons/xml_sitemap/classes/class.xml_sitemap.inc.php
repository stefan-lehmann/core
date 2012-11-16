<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  xml_sitemap
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

class cjoXMLSitemap {
    
    private $_config = array('frequencies' => array('always','hourly','daily','weekly','monthly','yearly','never'),
                             'priorities'  => array('0.0','0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1.0'),
                             'max_level'   => 2,
                             'cached_file' => 'sitemap.xml');
    private $_content = '';    
    private $_entries;
    private $_blocks;
    private $_xml;

    public function __construct() {
        global $CJO;
        $this->_file = $CJO['CACHEFOLDER'].'/'.$this->_config['cached_file'];
        $this->generate();
    }
    
    private function send() {

        cjoClientCache::sendFile($this->_file); 
    }    
    
    private function generate() {
        
        if (is_readable($this->_file) && filemtime($this->_file) > strtotime('midnight')) return;
        
        cjoGenerate::putFileContents($this->_file, ' ');
        $this->getEntries();
        $this->build();
        cjoGenerate::putFileContents($this->_file, $this->_xml);
        
    }
    
    private function getEntries($parent_id=0,$level=0) {
        global $CJO;
        
        foreach ($CJO['CLANG_ISO'] as $clang_id=>$iso) {
            $articles = OOArticle::getArticlesOfCategory($parent_id, true, $clang_id);
            if ($parent_id == 0) $this->generateValidationContent($articles);

            foreach($articles as $article) {
                
                if (!OOArticle::isValid($article) || !$this->isSitemapArticle($article)) continue;
                
                $this->_entries[] = array('loc'         => $article->getUrl(),
                                          'priority'    => $this->getPriority($article, $level),
                                          'changefreq'  => $this->getChangefreq($article),
                                          'lastmod'     => $article->getUpdateDate('%Y-%m-%d'));
                                          
                if ($article->isStartPage() && $level < $this->_config['max_level']) {
                    $this->getEntries($article->getId(), ($level+1));
                }                      
            }
        }
    }
    
    private function getPriority($article, $level) {
        $priority = $article->getValue('priority');
        if (!$priority || !in_array($priority, $this->_config['priorities'])) {
            $key = 7 - ($level*2);
            if ($key < 2) $key = 2;
            return  $this->_config['priorities'][$key];
        } else {
            return $priority;
        }
    }
    
    private function getChangefreq($article) {
        $frequencies = $article->getValue('frequencies');
        if (!$frequencies || !in_array($frequencies, $this->_config['frequencies'])) {
            return  $this->_config['frequencies'][2];
        } else {
            return $frequencies;
        }
    }    
    
    private function isSitemapArticle(&$article) {
        
        if ($article->getRedirect()) {
            $article = OOArticle::getArticleById($article->getRedirect());
            
            if (OOArticle::isValid($article) || !OOArticle::isOnline($article)) {
                return false;
            }
            else {
                return $this->isSitemapArticle($article);
            }
        }
        
        if (!$article->hasCtypeContent()) return false;
        if (!$article->isNaviItem()) return false;
        
        return true;
    }

    private function generateValidationContent($articles) {
        foreach(array_slice($articles, 0, 8) as $article) {
            $this->_content .= @file_get_contents($article->getUrl());
        }
    }

    /**
     * retrieve XML sitemap as a string
     *
     * @return unknown
     */
    public function getXml() {
        $this->build();
        return $this->_xml;
    }

    public function toString() {
        $this->build();
        return $this->_xml;
    }

    private function append($xml) {
        $this->_xml .= $xml;
    }

    private function build() {
        $this->append($this->buildHeader());
        $this->append($this->buildBlocks());
        $this->append($this->buildFooter());
    }

    private function buildHeader() {
        $header  = '<'.'?'.'xml version="1.0" encoding="UTF-8"?'.'>'."\n";
        $header .= "\t".'<urlset ';
        $header .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        return $header;
    }

    private function buildFooter() {
        return '</urlset>'."\n";
    }

    private function buildBlocks() {
        foreach ($this->_entries AS $entry) {
            $this->_blocks .= $this->buildEntry($entry);
        }
        return $this->_blocks;
    }

    private function buildEntry($entry) {

        if (strpos($this->_content, $entry['loc']) === false) return '';

        return sprintf("<url>\n%s%s%s%s</url>\n",
                $this->buildLine('loc', $entry['loc']),
                $this->buildLine('priority', $entry['priority']),
                $this->buildLine('changefreq', $entry['changefreq']),
                $this->buildLine('lastmod', $entry['lastmod']));
    }

    private function buildLine($tagname, $content) {
        if(!$this->isUtf8($content)) {
            $content = trim(utf8_encode($content));
        }
        return sprintf("\t<%s>%s</%s>\n",
                       $tagname, $content, $tagname);
    }

    private function isUtf8($str) {
        // function borrowed from:
        // http://w3.org/International/questions/qa-forms-utf-8.html

        return preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $str);
    }

    
    public static function isRequested() {
        global $CJO;
        
        if ($CJO['CONTEJO']) return false;
        
        $sitemap = new cjoXMLSitemap();
        
        $file = basename(cjo_server('REQUEST_URI','string'));
        if ($file == 'sitemap.xml') {
            if ($CJO['ADDON']['status']['xml_sitemap'] == 1) {
                $sitemap->send();
            } else {
                header("HTTP/1.0 404 Not Found");
                exit("HTTP/1.0 404 Not Found");
            }
        }
    }
}