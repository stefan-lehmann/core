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

/**
 * cjoOutput class
 *
 * The cjoOutput class provides static methods for modules and
 * to manipulate the generated HTML-code before sending it to
 * the client.
 *
 * @package     contejo
 * @subpackage  core
 */
class cjoOutput {

    /**
     * Generates a download list from a CJO_MEIDALIST value.
     * @param string $medialist files seperated by comma (eg. "file1.jpg,file2.gif")
     * @return string
     * @access public
     */
    public static function getDownloadsFromMedialist($medialist, $varname = 'DOWNLOADS') {

        global $CJO, $I18N;

        $downloads = array();
        $clang     = $CJO['CUR_CLANG'];
        $securese  = false;
        $varname   = strtoupper($varname);

        $article = OOArticle::getArticleById($GLOBALS['CJO_ARTICLE_ID'],$clang);

        if (!OOArticle::isValid($article)) return false;

        $tree = $article->getParentTree();
        $tree[] = $article;

        foreach($tree as $art) {
            if ($art->_type_id != 1) {
                $secure = true;
                break;
            }
        }
        $i = 0;
        foreach (cjoAssistance::toArray($medialist,',') as $key=>$file) {

            $media_obj = OOMedia::getMediaByName($file);

            if (!file_exists($media_obj->getFullPath())) continue;

            $downloads['id'][$i]              = $media_obj->getId();          
            $downloads['title'][$i]           = ($media_obj->getTitle() == '' || $media_obj->getTitle() == '[translate: label_no_title]')
                                               ? $media_obj->getFileName() : $media_obj->getTitle();
            $downloads['description'][$i]     = $media_obj->getDescription($clang, false);
            $downloads['updatedate'][$i]      = $media_obj->getUpdateDate($CJO['setlocal']['short_date']);
            $downloads['copyright'][$i]       = $media_obj->getCopyright();
            $downloads['filesize'][$i]        = $media_obj->_getFormattedSize();
            $downloads['download_path'][$i]   = $media_obj->getFullPath();
            $downloads['filename'][$i]        = $media_obj->getFileName();
            $downloads['extension'][$i]       = $media_obj->_getExtension();
            
            $downloads['has_copyright'][$i]   = !empty($media_obj->_copyright);      
            $downloads['has_description'][$i] = $downloads['description'][$i] != '' && $downloads['description'][$i] !=  $I18N->msg('label_no_media_description');

            $i++;
        }
        cjoModulTemplate::addVarsArray($varname, $downloads);

        return;
    }


    /**
     * Generates a page pagination for a listing.
     * @param int $currentPage number of the current page
     * @param int $elementsPerPage number of list elements per page
     * @param int $paginationsPerPage number of displayed pagination items if available (eg. << < [2] [3] [4] [5] [6] > >> = 5)
     * @param int $resultsLenght total number of list elements
     * @param array $query  additional get vars for pagination urls
     * @return string HTML-Code of the pagination
     * @access public
     */
    public static function getPagePagination($currentPage, $elementsPerPage, $paginationsPerPage = 5, $resultsLenght, $query = false) {

        $oneSidePaginations = floor($paginationsPerPage / 2);

        if (!is_numeric($elementsPerPage) || $elementsPerPage <= 0) {
            $elementsPerPage = 1000;
        }

        $pageCount = ceil($resultsLenght / $elementsPerPage) + 1;
        if ($currentPage <= $oneSidePaginations) {
            (int) $start = 1;
        } else {
            (int) $start = $currentPage - $oneSidePaginations;
        }

        $str = '';

        if ($currentPage != 0) {
            $left .= "\r\n".cjoOutput::getPaginationUrl($query, 0, '&lt;&lt;', '[translate: label_first_page]')."\r\n";
            $left .= "\r\n".cjoOutput::getPaginationUrl($query, $currentPage-1, '&lt;', '[translate: label_prev_page]')."\r\n";
        } else {
            $left = '&nbsp;';
        }

        // Seitenzahlen in ein Array speichern
        $temp = array();

        if ($pageCount > 7 && $currentPage > 3) {
            $temp[] = "\r\n".cjoOutput::getPaginationUrl($query, 0, '1 ...', 1)."\r\n";
        }

        $begin_pagination = $currentPage - 3;
        $stop_pagination = $currentPage + 5;

        if ( $begin_pagination < 0) {
            $stop_pagination -= $begin_pagination;
            $begin_pagination = 0;
        }
        if ($pageCount < $stop_pagination ) {
            $begin_pagination -= $stop_pagination - $pageCount;
            $stop_pagination = $pageCount;
        }
        if ($pageCount<=7) {
            $begin_pagination = 0;
            $stop_pagination = $pageCount;
        }

        // erste Seite
        for ($i = $begin_pagination; $i < $stop_pagination-1; $i ++) {
            if ($currentPage == $i) {
                $temp[] = cjoOutput::getPaginationUrl($query, $i, $i+1, $i+1, 1);
            } else {
                $temp[] = cjoOutput::getPaginationUrl($query, $i, $i+1, $i+1);
            }
            $start ++;
        }

        if ($pageCount > 7 && $currentPage < ($pageCount -5) ) {
            $temp[] = "\r\n".cjoOutput::getPaginationUrl($query, $pageCount-2, '... '.($pageCount-1), ($pageCount-1))."\r\n";
        }

        if ($currentPage != ($pageCount -2)) {
            $right .= "\r\n".cjoOutput::getPaginationUrl($query, $currentPage+1, '&gt;','[translate: label_next_page]')."\r\n";
            $right .= "\r\n".cjoOutput::getPaginationUrl($query, $pageCount -2, '&gt;&gt;','[translate: label_last_page]')."\r\n";

        } else {
            $right = '&nbsp;';
        }

        if (count($temp) > 1) {
            $middle .= implode("\r\n", $temp);

            return '<div class="pagination">'."\r\n".
                   '    <div class="pagination_left"> '.$left.' </div>'."\r\n".
                   '    <div class="pagination_middle"> '.$middle.' </div>'."\r\n".
                    '   <div class="pagination_right"> '.$right.' </div>'."\r\n".
                   '</div>'."\r\n";
        }
    }

    /**
     * Generates link elements of a pagination.
     * @param array $query additional get vars for pagination urls
     * @param int $xpage
     * @param string $label
     * @param string $title
     * @param int $current
     * @return string
     * @access public
     */
    public static function getPaginationUrl($query, $xpage, $label = null, $title = '', $current = 0) {

        switch ($title) {
            case '[translate: label_first_page]':
                $css = 'class="page_link first"';
                break;
            case '[translate: label_prev_page]':
                $css = 'class="page_link prev"';
                break;
            case '[translate: label_next_page]':
                $css = 'class="page_link next"';
                break;
            case '[translate: label_last_page]':
                $css = 'class="page_link last"';
                break;
            default:
                $css = 'class="page_link"';
                $title = '[translate: label_page] '.$title;
        }

        $css = ($current) ?  'class="page_link current"' : $css;
        $label = ($label === null) ? $xpage : $label;

        $query_out = array();

        if (is_array($query)) {
            foreach($query as $key => $val) {
                $query_out[] = $key.'='.$val;
            }
        }

        $query_out[] = 'xpage='.$xpage;

        return '<a href="?'.implode('&amp;',$query_out).'" title="'.$title.'" '.$css.'>'.$label.'</a>'."\r\n";
    }

    /**
     * Closes unclosed HTML-tags.
     * @param string $content html
     * @return string
     * @access public
     */
    public static function closeTags($content){

        // put all opened tags into an array
        preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $content, $result);
        $opened_tags = array_reverse($result[1]);
        $opened_length = count($opened_tags);

        // put all closed tags into an array
        preg_match_all("#</([a-z]+)>#iU", $content, $result);
        $closed_tags = $result[1];
        $closed_length = count($closed_tags);

        // all tags are closed
        if ($opened_length == $closed_length){
            return $content;
        }

        // close tags
        foreach($opened_tags as $tag) {
            if (!in_array($tag, $closed_tags)){
                $content .= '</'.$tag.'>';
            }
            else {
                unset($closed_tags[array_search($tag, $closed_tags)]);
            }
        }
        return $content;
    }

    /**
     * Prettifies the generated html code..
     * @param string $content the generated html code
     * @return string
     * @access public
     */
    public static function prettifyOutput($content) {

        global $CJO;

        list($content, $textareas) = self::textareasToPlaceholders($content);
        list($content, $scripts)   = self::scriptTagsToPlaceholders($content);

        $content = stripslashes($content);

        $content = preg_replace_callback('/(?<=href\=")\S+(?=\"|\')/i',
                                         create_function(
                                         // hier sind entweder einfache Anführungszeichen nötig
                                         // oder alternativ die Maskierung aller $ als \$
                                         '$matches',
                                         'return preg_replace("/&(?!amp;)/", "&amp;", $matches[0]);'
                                          ),$content);
                                          
        $content = preg_replace("/&(?![\w|#]*?;)/", "&amp;", $content);         
        $content = preg_replace('/\[\[[^\[]+\]\]/','<!-- \0 -->', $content);

        $content = self::placeholdersToTextareas($content, $textareas);
        $content = self::placeholdersToScriptTags($content, $scripts);

        $content = str_replace('CJO_MEDIAFOLDER', $CJO['MEDIAFOLDER'], $content);
        $content = str_replace('/./','/', $content);
        return $content;
    }

    public static function textareasToPlaceholders($content) {

        global $CJO;

        preg_match_all('/(<textarea[^>]*>)(.*?)(<\/textarea>)/is', $content, $textareas, PREG_SET_ORDER);

        if (empty($textareas))  return array($content, array());

        foreach($textareas as $key=>$textarea) {

            $placeholder = '<!-- CJO_REPLACE_TEXTAREA_'.$key.' -->';

            $content = preg_replace('/<textarea[^>]*>.*?<\/textarea>/is', $placeholder, $content, 1);
        }

        return array($content, $textareas);
    }

    public static function placeholdersToTextareas($content, $textareas) {

        global $CJO;

        if (empty($textareas)) return $content;

        foreach($textareas as $key => $textarea) {

            $placeholder = '<!-- CJO_REPLACE_TEXTAREA_'.$key.' -->';

            $textarea = $textarea[1].$textarea[2].$textarea[3];

            $content = str_replace($placeholder, $textarea, $content);
        }
        return $content;
    }

    public static function scriptTagsToPlaceholders($content) {

        global $CJO;

        preg_match_all('/(<script[^>]*>)(.*?)(<\/script>)/is', $content, $scripts, PREG_SET_ORDER);

        if (empty($scripts))  return array($content, array());

        foreach($scripts as $key=>$script) {

            $placeholder = '<!-- CJO_REPLACE_SCRIPT_'.$key.' -->';

            $content = preg_replace('/<script[^>]*>.*?<\/script>/is', $placeholder, $content, 1);
        }

        return array($content, $scripts);
    }

    public static function placeholdersToScriptTags($content, $scripts) {

        global $CJO;

        if (empty($scripts)) return $content;

        foreach($scripts as $key => $script) {

            $placeholder = '<!-- CJO_REPLACE_SCRIPT_'.$key.' -->';

            $script = $script[1].$script[2].$script[3];

            $content = str_replace($placeholder, $script, $content);
        }
        return $content;
    }

    /**
     * Finds and replaces email adresses in
     * the generated html code.
     * @param string $content the generated html code
     * @param int $clang
     * @return string
     * @access public
     */
    public static function replaceLinks($content, $clang = false) {

        global $CJO;

        if ($clang === false) {
            $clang = $CJO['CUR_CLANG'];
        }
        $urls = array();

        // -- preg match contejo://[ARTICLEID] --
        preg_match_all("/(contejo:)\/\/([0-9]+)(\.([0-9]*))?(\??([^\"|^\'|^\\\|^\#]*))?(\#([^\"|^\'|^\\\]*))?/im", $content, $matches, PREG_SET_ORDER);

        foreach($matches as $match) {

            if (empty($match) || !is_array($match)) continue;

            $key = md5(implode($match));
            
            if (empty($urls[$key])) {
                $article_id = $match[2];
                $clang = empty($match[4]) && $match[4] != 0 ? $clang : $match[4];
                $query = !empty($match[5]) ? $match[5] : '';
                $hash  = !empty($match[7]) && $match[7] != '#' ? $match[7] : '';
                $urls[$key] = cjoRewrite::getUrl($article_id, $clang, $query, $hash);
            }
            $content = preg_replace('!'.preg_quote($match[0]).'!', $urls[$key], $content, 1);
        }

        return $content;
    }

    /**
     * Finds and encrypts email adresses in
     * the generated html code.
     * @param string $content the generated html code
     * @return string
     * @access public
     */
    public static function encryptEmails($content) {

        // hier via regEx alle email-adressen heraussuchen
        preg_match_all("/(?<=\s|>)(([A-Z0-9._%+-])+@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4}|museum))(?=\s|<)/im",
                        $content, $matches);

        // hier jetzt alle gefundenen durchgehen und ersetzen
        if ( isset ($matches[1][0]) && !empty($matches[1])) {
            foreach($matches[1] as $key=>$value) {

                $encrypted_email = '';

                $unencrypted_emails = explode('@',$value);

                foreach ($unencrypted_emails as $b => $part) {
                    for ($i=0; $i<strlen($part); $i++) {
                        $encrypted_email[$b]
                            .= (substr($part, $i, 1) == '.')
                            ? ' [dot] '
                            : '&#'.ord(substr($part, $i, 1)).';';
                    }
                }
                $encrypted_email_address = '<span class="s"><span class="u">'.
                                            $encrypted_email[0].'</span> [at] <span class="d">'.
                                            $encrypted_email[1].'</span></span>';
                $content = preg_replace('/(?<=\s|>)'.$matches[1][$key].'(?=\s|<)/',
                                        $encrypted_email_address, $content, 1);
            }
        }
        return $content;
    }
    
    public static function replaceHTML5Tags($content) {
        $elements = array('section', 'nav', 'article', 'aside', 'hgroup', 'header', 'footer');
        foreach($elements as $element) {
            $content = str_replace('<'.$element, '<div', $content);
            $content = str_replace($element.'>', 'div>', $content);            
        }
        return $content;
    }
}