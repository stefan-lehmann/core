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
 * cjoHtmlTemplate class
 *
 * The cjoHtmlTemplate class makes it possible to devide PHP logic fom HTML-Layout.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoHtmlTemplate {

    /**
     * Sections of the HTML-template with the sections name
     * as the key and the sections content as value.
     * @var array
     */
    private $tmpl_sections;

    /**
     * Container for the number of the current value in a section.
     * Used to calculate different intervals in multi-value sections.
     * @var array
     */
    private $interval;

    /**
     * Container for the total amount of values in a section.
     * Used to mark the last value.
     * @var array
     */
    private $total;

    /**
     * The PHP-functions called from the HTML-Template with the section name
     * as key and an array with the function as value.
     * @var array
     */
    private $tmpl_functions;

    /**
     * A temporary storage which is needed to fill the sections.
     * @var array
     */
    private $html_buffer;

    /**
     * The storage for the genereated HTML-output.
     * @var string
     */
    private $rendered_html;


    /**
     * Constructor.
     * Reads the HTML-template file. Returns false if the template file does not readable.
     * @param string $template fullpath to the html-template file or the whole template as a string
     * @return void|false
     */
    public function __construct($template = false) {

        global $CJO;

        if ($template === false) return false;

        $this->tmpl_sections = array();
        $this->html_buffer = array();
        $this->interval = array();
        $this->total = array();
        $this->rendered_html = '';
        
        if (strlen($template) < 4096 &&  realpath($template) && file_exists($template)) {
            $raw_tmpl = file($template);
        }
        else {
            preg_match_all('/^.*$/m', $template, $raw_tmpl);
            $raw_tmpl = $raw_tmpl[0];
        }

        $raw_tmpl = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $raw_tmpl);

        $section = "";

        foreach($raw_tmpl as $line) {
            $line = trim($line);

            /**
             * Remove comments.
             */
            if (substr($line, 0, 1) == '#' && !preg_match('/(?<!#)###[\d|\w|\s].*?###(?!#)/', $line)) {
                $line = "";
            }
            /**
             * Start an new section.
             */
            else if (substr($line, 0, 2) == '[%' && substr($line, -2)== '%]') {
                $section = substr($line, 2, -2);
                $this->tmpl_sections[$section] = '';
            }
            /**
             * Fill the section line by line.
             */
            else {
                if ($section) {
                    $this->tmpl_sections[$section] .= $line."\n";
                    $this->html_buffer[$section] = "";
                }
            }
        }
    }

    /**
     * Fills a template section with a list of search and replace values.
     * @param string $section the section
     * @param array $replace_data the list of search and replace vars
     * @param object|boolean $slice commits a slice object for CONTEJO default replace vars
     * @return boolean
     */
    public function fillTemplateArray($section, $replace_data=array(), $slice=false){

        if (!is_string($section) || !is_array($replace_data))
        return false;

        $replace_data_set = array();

        foreach($replace_data as $search_key => $replace_values) {

            if (!is_array($replace_values)) continue;

            foreach($replace_values as $key=>$replace_value) {
                $replace_data_set[$key][$search_key] = $replace_value;
            }
        }

        $this->total[$section] = count($replace_data_set)-1;

        foreach($replace_data_set as $key=>$replace_value) {
            $this->fillTemplate($section, $replace_value, $slice);
        }
        return true;
    }

    /**
     * Fills a template section with search and replace values.
     * @param string $section the template section
     * @param array $replace_data search and replace vars (eg. array('SEACH_VALUE1'=>'replace_value1', 'seach_value2'=>'replace_value2'))
     * @param object|boolean $slice commits a slice object for CONTEJO default replace vars
     * @return void
     */
    public function fillTemplate($section, $replace_data=array(), $slice=false) {

        global $CJO, $I18N;

        $search_keys      = array();
        $tmpl_section     = $this->tmpl_sections[$section];

        if (!is_string($section) || !is_array($replace_data)) return false;

        if (OOArticleSlice::isValid($slice)) {
            $this->replaceSliceDefaultValues($slice, $tmpl_section);
        }
         
        $replace_data = array_merge($this->getInterVals($section), $replace_data);

        foreach($replace_data as $search_key => $replace_value) {
            if (in_array(strtoupper($search_key), $search_keys)) continue;
            $search_keys[] = '[['.strtoupper($search_key).']]';
        }
        $this->replaceSection($section, $tmpl_section, $search_keys, $replace_data);
    }

    protected function callFunctions($html){
        
        global $I18N;
        
        $search_keys = array();    
             
        preg_match_all('/\[\[(([a-z0-9_-]|::)*?[^\]]):\s*\"(.*?)\"\]\]/is', $html, $matches, PREG_SET_ORDER);         

        if (empty($matches)) return $html;
               
        /* PHP-Funktionsaufruf aus HTML-Template */
        foreach($matches as $search_key) {

            if (empty($search_key) || in_array($search_key[0], $search_keys)) continue;

            $search_keys[] = $search_key[0];
            $params_out    = '';
            $function      = $search_key[1];
            $arrays        = array();

            preg_match_all('/array\((?:[^\)]*?array\(.*?\)+[^\)]*?\)+)?[^\)]*\)+/x', $search_key[3], $temp);
             
            foreach($temp[0] as $array) {
                $md5           = md5($array);
                $arrays[$md5]  = $array;
                $search_key[3] = str_replace($array, $md5, $search_key[3]);
            }

            $params_in  = explode(',', $search_key[3]);

            if (is_array($params_in)){
                foreach($params_in as $param_in){
                    $param_in = trim($param_in);
                    $params_out .= ($params_out != '') ? ',' : '';

                    if (in_array(strtoupper($param_in), $search_keys)) {
                         
                        $param_temp = $replace_data[strtoupper($param_in)];
                        $params_out .= '"'.($param_temp).'"';
                    }
                    elseif(isset($arrays[$param_in])) {
                        $params_out .= $arrays[$param_in];
                    }
                    else {
                        if (stripslashes($param_in) == "''" ||
                        stripslashes($param_in) == '""') {
                            $params_out .= '""';
                        }
                        else {
                            $params_out .= '"'.$param_in.'"';
                        }
                    }
                }
            }   
            $call = '$replace = @'.$function.'('.$params_out.');'."\r\n";

            $test = cjoAssistance::toArray($function,'::');
            if (count($test) == 2 && method_exists($test[0], $test[1])) {
                eval($call);
            } else if (function_exists($function)) {
                eval($call);
            } else {
                $replace = '<!-- '.$I18N->msg('msg_class_or_function_not_available', $function.'('.$params_out.')' ).' -->';
            }
            if ($replace === null) {
                $replace = '[['.$function.'('.$params_out.')]]';
            }
            $html = str_replace($search_key[0], $replace, $html);
        }
        return $html;
    }

    /**
     * Replaces the CONTEJO-Vars.
     *
     * @param string $content
     * @param int|bool $article_id id of the current article
     */
    protected function replaceCommonVars($content, $article_id = false) {

        global $CJO;

        if ($article_id === false) $article_id = $GLOBALS['CJO_ARTICLE_ID'];

        foreach($CJO['VARIABLES'] as $var){
            $content = $var->getTemplate($content, $article_id);
        }

        if (strpos($content, '<?') !== false) {
            ob_start();
            ob_implicit_flush(0);
            eval('?>'.$content);
            $content = ob_get_contents();
            ob_end_clean();
        }
        return $content;
    }

    /**
     * Returns or writes processed template.
     * @param boolean $print if false the generated page is returned as string instead of sending it
     * @return string|boolean
     */
    public function get($print=true) {

        global $CJO;

        $rendered = array();
        $this->rendered_html = '';

        foreach (array_keys($this->tmpl_sections) as $section) {
            $search_sections[] = '<%'.$section.'%>';
        }

        /**
         * Removing of all unused placeholders
         */

        $this->rendered_html = str_replace($search_sections, $this->html_buffer, array_shift($this->html_buffer));
        if (isset($GLOBALS['CJO_ARTICLE_ID'])) {
            $this->rendered_html = $this->replaceCommonVars($this->rendered_html);
        }
        $this->rendered_html = $this->callFunctions($this->rendered_html);                
        $this->rendered_html = preg_replace('/\\[\[[A-Z0-9_]*\]\]/','',$this->rendered_html);
        $this->rendered_html = preg_replace('/\<%[A-Z0-9_]*%>/','',$this->rendered_html);
        $this->rendered_html = $this->parseConditions($this->rendered_html);

        if (!$CJO['CONTEJO']) {
            $this->rendered_html = preg_replace("/[\r\n|\r|\n]{2,}/", "\r\n", $this->rendered_html);
        }

        if (!$print) {
            return $this->rendered_html;
        }
        else {
            echo $this->rendered_html;
            return true;
        }
    }

    /**
     * Parses conditions ans removes all conditions that return false
     * @param string $content
     * @return string
     */
    protected function parseConditions($content) {

        preg_match_all('/(?<=IF\()([^\)]{0,2}|[^\)][^{][^%]*?)(?=\)\{%)/is', $content, $conditions);

        if (empty($conditions[0])) return $content;

        foreach($conditions[0] as $key=>$condition) {
            $content = preg_replace('/IF\(([^\)]{0,2}|[^\)][^{][^%]*?)\)\{%/is', '<!-- CONDITION_'.$key.' -->', $content, 1);
        }

        foreach($conditions[0] as $key=>$condition) {
            $content = str_replace('<!-- CONDITION_'.$key.' -->',
        			               'IF('.$this->checkCondition($condition).'){%',
            $content);
        }

        $content = preg_replace('/(IF\(\s*?\)\{%'.
        							'(?:IF\(.*?\)\{%'.
                                		'(?:IF\(.*?\)\{%'.
                                    		'(?:IF\(.*?\)\{%'.
                                                '(.*?)'.
                                    		'%\}ENDIF|.)*?'.
                                		'%\}ENDIF|.)*?'.
                                	'%\}ENDIF|.)*?'.
                                '%\}ENDIF)/ms',
                                '', $content);

        $content = preg_replace('/(IF\(.*?\)\{%)|(%\}ENDIF)/ms', '', $content);

        return $content;
    }

    /**
     * Checkes truth of a condition.
     * @param string $condition
     * @return bool
     */
    protected function checkCondition($condition) {

        $temp_ors = preg_split('/ (\|\||OR) /', $condition);
        $temp_ands = preg_split('/ (&&|AND) /', $condition);

        if (!empty($temp_ands)) {

            foreach($temp_ands as $and) {

                if (preg_match('/ (\|\||OR) /', $and)) continue;

                $and = trim($and);
                $and = str_replace('false', 0, $and);

                if (preg_match('/^!/', $and, $matches)) {
                    $and = substr($and, 1);
                    $bool = (bool) $and;
                    $bool = $bool ? false : true;
                } else {
                    $bool = (bool) $and;
                }

                if (!$bool) return false;
            }
        }

        if (!empty($temp_ors) && count($temp_ors) > 1) {

            foreach($temp_ors as $or) {

                if (preg_match('/ (&&|AND) /', $or)) continue;

                $or = trim($or);
                $or = str_replace('false', 0, $or);

                if (preg_match('/^!/', $or, $matches)) {
                    $or = substr($or, 1);
                    $bool = (bool) $or;
                    $bool = $bool ? false : true;
                } else {
                    $bool = (bool) $or;
                }

                if ($bool) return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Generates replace values for intervals in multi-value sections.
     *
     * @param string $section name of the current section
     * @return array
     */
    protected function getInterVals($section){

        $replace_data = array();
        $interval     = isset($this->interval[$section]) ? (int) $this->interval[$section] + 1 : 1;
        $total        = isset($this->total[$section]) ? (int) $this->total[$section] : 0;

        $replace_data["NUMBER"]       = $interval-1;
        $replace_data["REAL_NUMBER"]  = $interval;
        $replace_data["FIRST_ITEM"]	  = ($interval == 1);
        $replace_data["ODD_ITEM"]     = ($interval%2 == 0);
        $replace_data["EVERY_SECOND"] = ($interval > 0 && $interval%2 == 0);
        $replace_data["EVERY_THIRD"]  = ($interval > 0 && $interval%3 == 0);
        $replace_data["EVERY_FOURTH"] = ($interval > 0 && $interval%4 == 0);
        $replace_data["EVERY_FIFTH"]  = ($interval > 0 && $interval%5 == 0);
        $replace_data["EVERY_SIXT"]	  = ($interval > 0 && $interval%6 == 0);
        $replace_data["LAST_ITEM"]    = ($interval == $total+1) ? 1 : 0;

        $this->interval[$section] = $interval;
        return $replace_data;
    }

    /**
     * Replaces the placeholders of a section by its values
     *
     * @param string $section
     * @param string $tmpl_section
     * @param array $search_keys
     * @param array $replace_data
     */
    protected function replaceSection($section, $tmpl_section, $search_keys, $replace_data){
        $replace = array_values($replace_data);
        $this->html_buffer[$section] .= str_replace($search_keys, $replace, $tmpl_section);
    }
    
    /**
     * Replaces the placeholders of a section by default slice values
     * @param object $slice
     * @return array
     */
    public function replaceSliceDefaultValues($slice, &$content) {

        global $CJO;

        $search = array('[[CJO_IS_CONTEJO]]'    => $CJO['CONTEJO'],
                        '[[CJO_ARTICLE_ID]]'    => $slice->_article_id,
                        '[[CJO_MODULE_ID]]'     => $slice->_modultyp_id,
                        '[[CJO_SLICE_ID]]'      => $slice->_id,
                        '[[CJO_RE_SLICE_ID]]'   => $slice->_re_article_slice_id,
                        '[[CJO_CTYPE_ID]]'      => $slice->_ctype,
                        '[[CJO_CLANG_ID]]'      => $slice->_clang);

        
        if ($slice->_article_id != $CJO['ARTICLE_ID']) {
            
            $article = OOArticleSlice::getArticleBySliceId($slice->_id);
            
            if (OOArticle::isValid($article)) {
                
                $search = array_merge($search, 
                                      array('[[CJO_TEMPLATE_ID]]'           => $article->getTemplateId(),
                                            '[[CJO_ARTICLE_PARENT_ID]]'     => $article->getParentId(),
                                            '[[CJO_PARENT_ID]]'             => $article->getParentId(),
                                            '[[CJO_ARTICLE_ROOT_ID]]'       => @array_shift(cjoAssistance::toArray($article->getPath().$article_id.'|')),
                                            '[[CJO_ARTICLE_AUTHOR]]'        => $article->getAuthor(),
                                            '[[CJO_ARTICLE_NAME]]'          => $article->getName(),
                                            '[[CJO_ARTICLE_TITLE]]'         => $article->getTitle(),
                                            '[[CJO_ARTICLE_DESCRIPTION]]'   => $article->getDescription(),
                                            '[[CJO_ARTICLE_KEYWORDS]]'      => $article->getKeywords(),
                                            '[[CJO_ARTICLE_URL]]'           => $article->getUrl(),
                                            '[[CJO_ARTICLE_ONLINE_FROM]]'   => $article->getOnlineFromDate(),
                                            '[[CJO_ARTICLE_ONLINE_TO]]'     => $article->getOnlineToDate(),
                                            '[[CJO_ARTICLE_CREATEUSER]]'    => $article->getCreateUser(),
                                            '[[CJO_ARTICLE_UPDATEUSER]]'    => $article->getUpdateUser(),
                                            '[[CJO_ARTICLE_CREATEDATE]]'    => $article->getCreateDate(),
                                            '[[CJO_ARTICLE_UPDATEDATE]]'    => $article->getUpdateDate()));
            }
        }

        /* Kompatibilität erhalten */      
        $search = array_merge($search, 
                              array('[[SLICE_ID]]'           => $slice->_id,
                                    '[[SLICE_MODULTYP_ID]]'  => $slice->_modultyp_id,
                                    '[[CJO_CTYPE]]'          => $slice->_ctype));

        $content = str_replace(array_keys($search), array_values($search), $content);

        foreach ($CJO['VARIABLES'] as $var) {
            if ($CJO['CONTEJO']) {
                $tmp = $var->getBEOutput($slice, $content);
            }
            else {
                $tmp = $var->getFEOutput($slice, $content);
            }

            // Rückgabewert nur auswerten wenn auch einer vorhanden ist
            // damit $content nicht verfälscht wird
            // null ist default Rückgabewert, falls kein RETURN in einer Funktion ist
            if ($tmp !== null) {
                $content = $tmp;
            }
        }
    }


    /**
     * Deprecated method that returns or writes processed template.
     * @param boolean $print if false the generated page is returned as string instead of sending it
     * @return string|boolean
     * @see $this->get
     * @deprecated
     */
    public function render($print=true) {
        return $this->get($print);
    }
}