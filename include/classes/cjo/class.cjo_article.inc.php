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

class cjoArticle {

    public $slice_id;
    public $article_id;
    public $mode;
    public $content;
    public $function;
    public $eval;
    public $viasql;
    public $parent_id;
    public $CONT;
    public $template_id;
    public $view_slice_id;
    public $save;
    public $ctype;
    public $clang;
    public $type_id;
    public $slices;
    public $modul_sel;
    public $html;
    public $redirect;
    public $sql;
    public $warning;
    public $info;
    public $debug;

    function __construct($article_id = null, $clang = null) {

        global $CJO;
        
        $this->article_id  = 0;
        $this->template_id = 0;
        $this->ctype       = -1; // zeigt alles an
        $this->slice_id    = 0;
        $this->mode        = "view";
        $this->content     = "";
        $this->modul_sel   = false;
        $this->eval        = false;
        $this->viasql      = false;
        $this->debug       = false;
        $this->sql         = new cjoSql();
        
        $this->setCLang(($clang !== null ? $clang : $CJO['CUR_CLANG']));

        cjoExtension::registerExtensionPoint('ARTICLE_INIT', 
                                             array('article'    => &$this,
                                                   'article_id' => $article_id,
                                                   'clang'      => $this->clang));

        if ($article_id !== null) $this->setArticleId($article_id);
    }

    public function setSliceId($slice_id) {
        if (empty($slice_id)) return false;
        $this->slice_id = $slice_id;
        $this->setEval(true);
    }

    public function setCType($ctype) {
        $this->ctype = $ctype;
    }

    public function setCLang($clang){

        global $CJO;

        if ($CJO['CLANG'][$clang] == "") $clang = $CJO['CUR_CLANG'];
        $this->clang = $clang;
        $this->content = '';
    }

    public function getCLang() {
        return $this->clang;
    }

    public function setArticleId($article_id) {

        global $CJO;

        $article_id = (int) $article_id ;
        $this->article_id = (int) $article_id;

        if ($this->viasql) {
            $this->sql->flush();
            $this->sql->setQuery("SELECT *
                                  FROM ".TBL_ARTICLES."
                                  WHERE id='".$article_id."'
                                  AND clang='".$this->clang."'");

            if ($this->sql->getRows() == 1) {
                $this->template_id  = $this->sql->getValue("template_id");
                $this->parent_id    = $this->getValue("parent_id");
                $this->redirect     = $this->sql->getValue("redirect");
                $this->type_id      = $this->sql->getValue("type_id");
                $this->countSlicesOfArticle();                
                return true;
            }
        }
        else {
            $OOArticle = OOArticle::getArticleById($article_id, $this->clang);

            if (OOArticle::isValid($OOArticle)) {
                $this->template_id  = $OOArticle->getOrgTemplateId();
                $this->parent_id    = $OOArticle->getParentId();
                $this->redirect     = $OOArticle->getRedirect();
                $this->type_id      = $OOArticle->getTypeId();
                $this->countSlicesOfArticle();                 
                return true;
            }
        }

        $this->article_id   = 0;
        $this->template_id  = 0;
        $this->parent_id    = 0;
        $this->redirect     = false;
        $this->type_id      = 0;      
        return false;
    }

    public function getArticleId() {
        return $this->article_id;
    }

    public function setTemplateId($template_id, $change_mode=false) {

        global $CJO;
        if (!$template_id || $this->template_id == $template_id) {
            $CJO['ART'][$this->getArticleId()]['set_template_id'][$this->getClang()] = '';
            return true;
        }
        $this->template_id = $template_id;
        $CJO['ART'][$this->getArticleId()]['set_template_id'][$this->getClang()] = $template_id;

        if ($change_mode) {
            $this->getAsQuery(true);
            $this->setEval(true); 
        }
    }

    public function getTemplateId() {
        return $this->template_id;
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function setFunction($function) {
        $this->function = $function;
    }

    public function setEval($eval = true) {
        $this->eval = ($eval === true);
    }

    public function getAsQuery($viasql = true) {
        $this->viasql = ($viasql === true);
    }
    
    private function escapeContejoVars(&$content) {
        global $CJO;
        $content = str_replace(array('CJO_ARTICLE', 'CJO_TEMPLATE'), array('CJO__ARTICLE', 'CJO__TEMPLATE'), $content);
    }
    
    private function unescapeContejoVars(&$content) {
        global $CJO;
        $content = str_replace(array('CJO__ARTICLE', 'CJO__TEMPLATE'), array('CJO_ARTICLE', 'CJO_TEMPLATE'), $content);
    }

    public function hasRedirect() {
        return (!empty($this->redirect) && $this->redirect != $this->article_id);
    }

    public function hasValue($value) {

        global $CJO;
        $value = $this->correctValue($value);
        return (!$this->viasql) ? isset($CJO['ART'][$this->article_id][$value][$this->clang]) : $this->sql->hasValue($value);
    }

    public function getValue($value) {

        global $CJO;

        $value = $this->correctValue($value);

        return (!$this->viasql || !in_array($value, $this->sql->_getFieldnames()))
            ? $CJO['ART'][$this->article_id][$value][$this->clang]
            : $this->sql->getValue($value);
    }
    
    public function getSlicesOfArticle($return_array = true) {
        
        global $CJO;
        if ($return_array) return $this->slices;
        return serialize($this->slices);
    }
    
    public function countSlicesOfArticle() {
        
        global $CJO;

        $this->slices = array();
        
        $sql = new cjoSql();
        $qry = "SELECT ctype, COUNT( ctype ) as slices 
                FROM ".TBL_ARTICLES_SLICE." 
                WHERE article_id=".$this->article_id." AND clang='".$this->clang."'
                GROUP BY ctype, clang LIMIT ".count($CJO['CTYPE']);
        $count_results = $sql->getArray($qry);

        foreach($count_results as $count_result) {
            if (!isset($CJO['CTYPE'][$count_result['ctype']]) || !$CJO['CTYPE'][$count_result['ctype']]) continue;
            $this->slices[$count_result['ctype']] = $count_result['slices'];
        }
    }

    public function correctValue($value) {

        //just for compatibility
        if ($value == 'category_id' || $value == 'parent_id')
            return 're_id';

        //just for compatibility
        if ($value == 'autor') return 'author';

        //just for compatibility
        if ($this->viasql && $value == 'article_id') {
            return 'id';
        }

        return $value;
    }

    public function getArticle($curctype = -1, $empty_content_lang = -1) {

        global $CJO, $I18N;

        if ($this->content != "") {

            if (!$this->eval) {
                echo $this->content;
            } else {
                eval("?>".$this->content);
            }
            return;
        }

        if ($curctype != -1 &&
            !cjoTemplate::hasCtype($this->getTemplateId(), $curctype)) {

            echo '<!-- CTYPE "'.$CJO['CTYPE'][$curctype].'" (ID='.$curctype.') is not '.
                 'available in this TEMPLATE (ID='.$this->getTemplateId().') -->'."\r\n";
            return;
        }

        ob_start();
        ob_implicit_flush(0);

        $this->ctype = $curctype;

        if (empty ($this->article_id)) return false;

        if (!$this->viasql && !$this->slice_id) {
            $this->getGeneratedArticle();
        }
        else {
            $this->getParsedArticle();
        }

        // ----- end: article caching
        $content = ob_get_contents();
        ob_end_clean();


        if (!$CJO['CONTEJO'] &&
            empty($content) &&
            $this->article_id != 0 &&
            $empty_content_lang != -1 &&
            $empty_content_lang != $this->clang)
            {
            $msg = '<div class="cjo_no_content warning">[translate: WARNING CONTENT ONLY IN DEFAULT LANG]</div>'."\r\n";

            $temp_clang = $CJO['CUR_CLANG'];
            $CJO['CUR_CLANG'] = $empty_content_lang;
            $this->setCLang($empty_content_lang);
            $this->setArticleId($this->article_id);
            $content = trim($this->getArticle($curctype));

            if (!empty($content)) {
                $content = $msg.$content;
            }
            $CJO['CUR_CLANG'] = $temp_clang;
            $this->setCLang($temp_clang);
        }

        return cjoExtension::registerExtensionPoint('ARTICLE_CONTENT_GENERATED', 
                                                    array('subject'       => $content,
                                                          'article_id'    => $this->article_id,
                                                          'ctype'         => $this->ctype,
                                                          'clang'         => $this->clang ),
                                                    true);
    }

    public function getArticleTemplate() {

        global $CJO;

        if (empty($this->article_id)) {
            
            $article = OOArticle::getArticleById($CJO['START_ARTICLE_ID'], $this->clang);

            if (OOArticle::isValid($article)) {
                cjoAssistance::redirectFE($CJO['START_ARTICLE_ID'], $this->clang);
            }
        }

        if ($this->article_id != $CJO['NOTFOUND_ARTICLE_ID'] &&
            !OOArticle::isOnline($this->article_id)) {

            $article = OOArticle::getArticleById($CJO['NOTFOUND_ARTICLE_ID'], $this->clang);

            cjoExtension::registerExtensionPoint('ARTICLE_OFFLINE', array('article' => &$this), true);

            if (OOArticle::isValid($article)) {
                cjoAssistance::redirectFE($CJO['NOTFOUND_ARTICLE_ID'], $this->clang);
            }
            else {
                cjoAssistance::redirect($CJO['BACKEND_PATH']);
            }
        }

        if ($this->hasRedirect()) {
            if (preg_match('/\D+/', $this->redirect)) {
                cjoAssistance::redirect($this->redirect);
            }
            cjoAssistance::redirectFE($this->redirect, $this->clang);
       }

        // ----- start: template caching
        ob_start();

        if ($this->getTemplateId() == 0 &&
            $this->article_id != 0) {
            echo $this->getArticle();
        }
        elseif ($this->getTemplateId() != 0 && $this->article_id != 0) {

            ob_implicit_flush(0);
            
            $this->setTemplateId($this->getTemplateId());
            $template = new cjoTemplate();
            $template->setId($this->getTemplateId());
            $content = $template->getTemplate($this->article_id);
            eval("?>".$content);
        }
        else {
            $content = "no template";
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    private function editSlice($re, $curr_re_id) {

        global $CJO, $I18N;
                
        $slice_content = '<form enctype="multipart/form-data" action="index.php#slice'.$re['conts'][$curr_re_id].'" '."\r\n".
                         '     method="post" accept-charset="'.$I18N->msg("htmlcharset").'" id="CJO_FORM">'."\r\n".
                         '  <input type="hidden" name="article_id" value="'.$this->article_id.'" />'."\r\n".
                         '  <input type="hidden" name="page" value="edit" />'."\r\n".
                         '  <input type="hidden" name="subpage" value="content" />'."\r\n".
                         '  <input type="hidden" name="mode" value="'.$this->mode.'" />'."\r\n".
                         '  <input type="hidden" name="slice_id" value="'.$re['conts'][$curr_re_id].'" />'."\r\n".
                         '  <input type="hidden" name="function" value="edit" />'."\r\n".
                         '  <input type="hidden" name="save" value="1" />'."\r\n".
                         '  <input type="hidden" name="update" value="0" />'."\r\n".
                         '  <input type="hidden" name="clang" value="'.$this->clang.'" />'."\r\n".
                         '  <input type="hidden" name="ctype" value="'.$re['conts_ctype'][$curr_re_id].'" />'."\r\n".
                         '<?php'."\r\n".
                         'global $CJO_EXT_VALUE, $CJO_MODUL_TMPL;'."\r\n".
                         '$slice_id = "'.$re['conts'][$curr_re_id].'"; '."\r\n".
                         '$value20 =<<<EOT'."\r\n".
                         $this->CONT->getValue('value20')."\r\n".
                         'EOT;'."\r\n".
                         '$CJO_EXT_VALUE  = OOArticleSlice::getExtValue($value20);'."\r\n".
                         '$CJO_MODUL_HTML =<<<EOT'."\r\n".
                         $re['html_in'][$curr_re_id]."\r\n".
                         'EOT;'."\r\n".
                         '$CJO_MODUL_TMPL = new cjoModulTemplate($CJO_MODUL_HTML);'."\r\n".
                         '?>'."\r\n".
                         $re['modul_in'][$curr_re_id].
                         '</form>'."\r\n";
                         
        $slice_content = cjoExtension::registerExtensionPoint('SLICE_START_EDIT', 
                                                              array('slice_id' => $re['conts'][$curr_re_id],
                                                                    'subject' => $slice_content));
        
        return $this->replaceVars($this->CONT, $slice_content);

    }

    private function getSliceHead($re, $curr_re_id) {

        global $CJO, $I18N;
        
        $title = $CJO['USER']->hasPerm('advancedMode[]') 
               ? ' title="'.$I18N->msg('label_slice_id', $re['conts'][$curr_re_id]).'"' 
               : '';
        
        $slice_head = $this->getAddModulSel($curr_re_id)."\r\n".
                       '<div class="cjo_slice_head clearfix%s"'.$title.'>'."\r\n".
                       '<a name="slice'.$re['conts'][$curr_re_id].'" class="cjo_hidden_anchor"></a>'."\r\n".        
                       '    <div class="cjo_slice_right">%s</div>'."\r\n".
                       '    <div class="cjo_slice_left">'."\r\n".
                       '        <b>'.$re['modul_name'][$curr_re_id].'</b>'."\r\n".
                       '        (ID='.$re['modul_id'][$curr_re_id].')'."\r\n".
                       '    </div>'."\r\n".
                       '</div>'."\r\n";

        if ($CJO['USER']->hasModulPerm($re['modul_id'][$curr_re_id])) {
            $slice_head = sprintf($slice_head, '', $this->getSliceButtons($re['conts'][$curr_re_id]));
        }
        else {
            $slice_head = sprintf($slice_head, ' no_rights', $I18N->msg('msg_no_editing_rights'));
        }

        return cjoExtension::registerExtensionPoint('SLICE_HEAD_BUILD', array('article_id' => $this->article_id,
                                                                              'clang' => $this->clang,
                                                                              'ctype' => $re['conts_ctype'][$curr_re_id],
                                                                              'module_id' => $re['modul_id'][$curr_re_id],
                                                                              'slice_id' => $re['conts'][$curr_re_id],
                                                                              'subject' => $slice_head));
    }

    private function getGeneratedArticle(){

        global $CJO, $I18N;

        if (empty($this->article_id)) return false;

        $filename = $CJO['FOLDER_GENERATED_ARTICLES']."/". $this->article_id.".".$this->clang.".".$this->getTemplateId().".content";

        if (!file_exists($filename)) {
            require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_generate.inc.php";
            cjoGenerate::generateArticle($this->article_id, true, $this->clang, $this->getTemplateId());
        }
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            eval($content);
        }
    }

    private function getParsedArticle(){

        global $CJO, $I18N;

        $slice_limit = '';
        if ($this->mode != "edit" && $this->slice_id) {
            $slice_limit = " AND sl.id = '" . $this->slice_id . "' ";
        }

        $qry = "SELECT
                  md.id AS `md.id`, 
                  md.name AS `md.name`, 
                  md.output AS `md.output`,
                  md.input AS `md.input`,
                  md.php_enable AS `md.php_enable`, 
                  md.html_enable AS `md.html_enable`, 
                  md.label AS `md.label`,
                  sl.id AS `sl.id`,
                  sl.re_article_slice_id AS `sl.re_article_slice_id`,
                  sl.article_id AS `sl.article_id`,
                  sl.ctype AS `sl.ctype`,
                  sl.clang AS `sl.clang`,
                  sl.modultyp_id AS `sl.modultyp_id`,
                  sl.value1 AS `sl.value1`,
                  sl.value2 AS `sl.value2`,
                  sl.value3 AS `sl.value3`,
                  sl.value4 AS `sl.value4`,
                  sl.value5 AS `sl.value5`,
                  sl.value6 AS `sl.value6`,
                  sl.value7 AS `sl.value7`,
                  sl.value8 AS `sl.value8`,
                  sl.value9 AS `sl.value9`,
                  sl.value10 AS `sl.value10`,
                  sl.value11 AS `sl.value11`,
                  sl.value12 AS `sl.value12`,
                  sl.value13 AS `sl.value13`,
                  sl.value14 AS `sl.value14`,
                  sl.value15 AS `sl.value15`,
                  sl.value16 AS `sl.value16`,
                  sl.value17 AS `sl.value17`,
                  sl.value18 AS `sl.value18`,
                  sl.value19 AS `sl.value19`,
                  sl.value20 AS `sl.value20`,
                  sl.file1 AS `sl.file1`,
                  sl.file2 AS `sl.file2`,
                  sl.file3 AS `sl.file3`,
                  sl.file4 AS `sl.file4`,
                  sl.file5 AS `sl.file5`,
                  sl.file6 AS `sl.file6`,
                  sl.file7 AS `sl.file7`,
                  sl.file8 AS `sl.file8`,
                  sl.file9 AS `sl.file9`,
                  sl.file10 AS `sl.file10`,
                  sl.filelist1 AS `sl.filelist1`,
                  sl.filelist2 AS `sl.filelist2`,
                  sl.filelist3 AS `sl.filelist3`,
                  sl.filelist4 AS `sl.filelist4`,
                  sl.filelist5 AS `sl.filelist5`,
                  sl.filelist6 AS `sl.filelist6`,
                  sl.filelist7 AS `sl.filelist7`,
                  sl.filelist8 AS `sl.filelist8`,
                  sl.filelist9 AS `sl.filelist9`,
                  sl.filelist10 AS `sl.filelist10`,
                  sl.link1 AS `sl.link1`,
                  sl.link2 AS `sl.link2`,
                  sl.link3 AS `sl.link3`,
                  sl.link4 AS `sl.link4`,
                  sl.link5 AS `sl.link5`,
                  sl.link6 AS `sl.link6`,
                  sl.link7 AS `sl.link7`,
                  sl.link8 AS `sl.link8`,
                  sl.link9 AS `sl.link9`,
                  sl.link10 AS `sl.link10`,
                  sl.php AS `sl.php`,
                  sl.html AS `sl.html`,
                  sl.createuser AS `sl.createuser`,
                  sl.updateuser AS `sl.updateuser`,
                  sl.createdate AS `sl.createdate`,
                  sl.updatedate AS `sl.updatedate`,
                  ar.pid AS `ar.pid`,
                  ar.id AS `ar.id`,
                  ar.re_id AS `ar.re_id`,
                  ar.name AS `ar.name`,
                  ar.template_id AS `ar.template_id`,
                  ar.redirect AS `ar.redirect`,
                  ar.description AS `ar.description`,
                  ar.attribute AS `ar.attribute`,
                  ar.file AS `ar.file`,
                  ar.type_id AS `ar.type_id`,
                  ar.teaser AS `ar.teaser`,
                  ar.startpage AS `ar.startpage`,
                  ar.prior AS `ar.prior`,
                  ar.path AS `ar.path`,
                  ar.navi_item AS `ar.navi_item`,
                  ar.cat_group AS `ar.cat_group`,
                  ar.comments AS `ar.comments`,
                  ar.status AS `ar.status`,
                  ar.admin_only AS `ar.admin_only`,
                  ar.online_from AS `ar.online_from`,
                  ar.online_to AS `ar.online_to`,
                  ar.keywords AS `ar.keywords`,
                  ar.title AS `ar.title`,
                  ar.author AS `ar.author`
                FROM
                  ".TBL_ARTICLES_SLICE." sl
                LEFT JOIN
                  ".TBL_MODULES." md ON sl.modultyp_id=md.id
                LEFT JOIN
                  ".TBL_ARTICLES." ar ON sl.article_id=ar.id
                WHERE
                  sl.article_id='".$this->article_id."' AND
                  sl.clang='".$this->clang."' AND
                  ar.clang='".$this->clang."'".
                $slice_limit."
                ORDER BY
                  sl.re_article_slice_id";

        $this->CONT = new cjoSql();
        $this->CONT->setQuery($qry);

        // ---------- SLICE IDS/MODUL SETZEN - speichern der daten
        for ($i=0;$i<$this->CONT->getRows();$i++) {

            $re_id = $this->CONT->getValue("sl.re_article_slice_id");

            $re['conts'][$re_id]        = $this->CONT->getValue("sl.id");
            $re['conts_ctype'][$re_id]  = $this->CONT->getValue("sl.ctype");
            $re['modul_out'][$re_id]    = $this->CONT->getValue("md.output");
            $re['modul_in'][$re_id]     = $this->CONT->getValue("md.input");
            $re['modul_id'][$re_id]     = $this->CONT->getValue("md.id");
            $re['modul_name'][$re_id]   = $this->CONT->getValue("md.name");

            $html_path_in  = cjoModulTemplate::getTemplatePath($re['modul_id'][$re_id],$this->template_id,$re['conts_ctype'][$re_id],'input');
            $html_path_out = cjoModulTemplate::getTemplatePath($re['modul_id'][$re_id],$this->template_id,$re['conts_ctype'][$re_id],'output');

            $re['html_in'][$re_id]      = (is_readable($html_path_in)) ? file_get_contents($html_path_in) : '';
            $re['html_out'][$re_id]     = (is_readable($html_path_out)) ? file_get_contents($html_path_out) : '';
            $re['counter'][$re_id]      = $i;          
            
            $this->CONT->next();
        }

        // ---------- SLICE IDS SORTIEREN UND AUSGEBEN
        $curr_re_id = $this->mode != "edit" && $this->slice_id ? $re_id : 0;
        $prev_slice_id = 0;
        $last_slice_id = 0;
        $this->content = '';
        $this->CONT->reset();

        $post_html = '</div>';

        for ($i=0;$i<$this->CONT->getRows();$i++) {

            // ----- ctype unterscheidung
            if ($i == 0 &&
                $this->mode != "edit" &&
                !$this->slice_id) {

                $this->content .= '<?php '."\r\n".
                                  'if ($this->ctype == '.$re['conts_ctype'][$curr_re_id].' || '."\r\n".
                                  '    $this->ctype == -1) {  '."\r\n".
                                  '?> '."\r\n";
            }
            // ------------- EINZELNER SLICE - AUSGABE
            $this->CONT->setCurrent($re['counter'][$curr_re_id]);
            $slice_content  = '';
            $slice_head     = '';

            if ($this->mode == "edit") {

                $slice_head = $this->getSliceHead($re, $curr_re_id);

                    if ($this->slice_id == '') {

                        $sql = new cjoSql();
                        $qry = "SELECT createdate
                                FROM ".TBL_ARTICLES_SLICE."
                                WHERE
                                  id='".$re['conts'][$curr_re_id]."' AND
                                  updatedate = '0' AND
                                  updateuser = '' AND
                                  ctype = '".$this->ctype."'";
                        $sql->setQuery($qry);

                        if ($sql->getRows() == 1) {
                            cjoAssistance::redirectBE(array('article_id' => $this->article_id,
                                                            'mode' => 'edit',
                                                            'slice_id'=> $re['conts'][$curr_re_id],
                                                            'function'=> 'edit',
                                                            'clang'=> $this->clang,
                                                            'ctype'=> $this->ctype,
                                                            '#' => 'slice'.$re['conts'][$curr_re_id]
                            ));
                        }
                    }

                    $this->view_slice_id = $re['conts'][$curr_re_id];

                    if ($this->function=="edit" &&
                        $this->slice_id == $re['conts'][$curr_re_id] &&
                        $CJO['USER']->hasModulPerm($re['modul_id'][$curr_re_id])) {
                        $pre_html = '<div class="cjo_slice input cjo_modultyp_'.$re['modul_id'][$curr_re_id].' clearfix">';
                        $slice_content .= $this->editSlice($re, $curr_re_id);
                    }
                    else {
                        
                        $pre_html = '<div class="cjo_slice output cjo_modultyp_'.$re['modul_id'][$curr_re_id].' clearfix">';
                        
                        $content = "\r\n".
                                  '<!-- MODULE_ID '.$re['modul_id'][$curr_re_id].' ['.$re['modul_name'][$curr_re_id].'] >>> -->'."\r\n".
                                  '<?php'."\r\n".
                                  'global $CJO_EXT_VALUE, $CJO_MODUL_TMPL;'."\r\n".
                                  '$slice_id = "'.$re['conts'][$curr_re_id].'"; '."\r\n".
                                  '$value20 =<<<EOT'."\r\n".
                                  $this->CONT->getValue('value20')."\r\n".
                                  'EOT;'."\r\n".
                                  '$CJO_EXT_VALUE  = OOArticleSlice::getExtValue($value20);'."\r\n".
                                  '// MODUL_ID:'.$re['modul_id'][$curr_re_id].'  TEMPLATE_ID:'.$this->template_id.' CTYPE:'.$re['conts_ctype'][$curr_re_id]."\r\n".
                                  '$CJO_MODUL_HTML =<<<EOT'."\r\n".
                                  $re['html_out'][$curr_re_id]."\r\n".
                                  'EOT;'."\r\n".
                                  '$CJO_MODUL_TMPL = new cjoModulTemplate($CJO_MODUL_HTML);'."\r\n".
                                  '?>'."\r\n".                                           
                                  $re['modul_out'][$curr_re_id]."\r\n".
                                  self::clearLocalSliceVars()."\r\n".                         
                                  '<!-- MODULE_ID '.$re['modul_id'][$curr_re_id].' ['.$re['modul_name'][$curr_re_id].'] <<< -->'."\r\n";

                        if ($this->eval) {
                            
                            static $search = array('CJO_HTDOCS_PATH',
                                                   'CJO_MEDIAFOLDER',
                                                   'CJO_FRONTPAGE_PATH');
                    
                            $replace = array ($CJO['HTDOCS_PATH'],
                                              $CJO['MEDIAFOLDER'],
                                              $CJO['FRONTPAGE_PATH']);
                                              
                            $content = str_replace($search, $replace, $content);                              
                        }
                        
                        $slice_content .= $content;
                    }

                $slice_content = '<div id="slice_id_'.$re['conts'][$curr_re_id].'">'.$slice_head.$pre_html.$slice_content.$post_html.'</div>';
            }
            else {
                $slice_content .= "\r\n".
                                  '<!-- MODULE_ID '.$re['modul_id'][$curr_re_id].' ['.$re['modul_name'][$curr_re_id].'] >>> -->'."\r\n".
                                  '<?php'."\r\n".
                                  'global $CJO_EXT_VALUE, $CJO_MODUL_TMPL;'."\r\n".
                                  '$slice_id = "'.$re['conts'][$curr_re_id].'"; '."\r\n".
                                  '$value20 =<<<EOT'."\r\n".
                                  $this->CONT->getValue('value20')."\r\n".
                                  'EOT;'."\r\n".
                                  '$CJO_EXT_VALUE  = OOArticleSlice::getExtValue($value20);'."\r\n".
                                  '// MODUL_ID:'.$re['modul_id'][$curr_re_id].'  TEMPLATE_ID:'.$this->template_id.' CTYPE:'.$re['conts_ctype'][$curr_re_id]."\r\n".
                                  '$CJO_MODUL_HTML =<<<EOT'."\r\n".
                                  $re['html_out'][$curr_re_id]."\r\n".
                                  'EOT;'."\r\n".
                                  '$CJO_MODUL_TMPL = new cjoModulTemplate($CJO_MODUL_HTML);'."\r\n".
                                  '?>'."\r\n".
                                  $re['modul_out'][$curr_re_id]."\r\n".
                                  self::clearLocalSliceVars()."\r\n".
                                  '<!-- MODULE_ID '.$re['modul_id'][$curr_re_id].' ['.$re['modul_name'][$curr_re_id].'] <<< -->'."\r\n";
            
            }


            $slice_content = $this->replaceVars($this->CONT, $slice_content);

            // ---------- slice in ausgabe speichern wenn ctype richtig
            if ($this->ctype == -1 || $this->ctype == $re['conts_ctype'][$curr_re_id]) {
                $this->content .= $slice_content;
                $last_slice_id = $re['conts'][$curr_re_id];
            }

            if ($this->mode != "edit" &&
                isset($re['conts_ctype'][$re['conts'][$curr_re_id]]) &&
                $re['conts_ctype'][$curr_re_id] != $re['conts_ctype'][$re['conts'][$curr_re_id]] &&
                $re['conts_ctype'][$re['conts'][$curr_re_id]] != "") {

                $this->content .= "<?php "."\r\n".
                                  "}"."\r\n".
                                  "if (\$this->ctype == '".$re['conts_ctype'][$re['conts'][$curr_re_id]]."' || "."\r\n".
                                  "    \$this->ctype == '-1') {  "."\r\n".
                                  "?> "."\r\n";
            }

            // zum nachsten slice
            $curr_re_id = $re['conts'][$curr_re_id];
            $prev_slice_id = $curr_re_id;
        }

        // ----- end: ctype unterscheidung        
        if ($i > 0  &&
            $this->mode != "edit" &&
            !$this->slice_id) {
            $this->content .= "<?php } ?>";
        }

        // ----- add module im edit mode
        if ($this->mode == "edit") {
            $this->content .= $this->getAddModulSel($last_slice_id);
        }

        // -------------------------- schreibe content
        if ($this->eval === false) {
            echo $this->content;
        } else {
            eval("?>".$this->content);  
        }
    }
    
    private function getAddModulSel($slice_id){

        global $CJO, $I18N;

        if (!is_object($this->modul_sel)) {

            $this->modul_sel = new cjoSelect;
            $this->modul_sel->setName('module_id');
            $this->modul_sel->setSize(1);
            $this->modul_sel->setStyle('class="cjo_add_module"');
            $this->modul_sel->setSelectExtra('onchange="this.form.submit();"');
            $this->modul_sel->addOption('--------------------  '.$I18N->msg("add_block").'  --------------------','');     
            
            $sql = new cjoSql();
            $qry = "SELECT name, id, templates, ctypes
                    FROM ".TBL_MODULES."
                    WHERE
                       (templates LIKE '%|".$this->getValue("template_id")."|%' OR
                        templates LIKE '%|0|%' OR
                        templates LIKE '0') AND
                       (ctypes LIKE '%|".$this->ctype."|%' OR
                        ctypes='".$this->ctype."')                  
                    ORDER BY  prior";
            
            foreach ($sql->getArray($qry) as $module) {

                if (!$CJO['USER']->isAdmin() && $CJO['USER']->hasPerm("editContentOnly[]")) continue;

                if ($CJO['USER']->hasModulPerm($module['id'])) {
                    $this->modul_sel->addOption($module['name'],$module['id']);
                }
            }
        }

        $this->modul_sel->setId('module_id_'.$slice_id);

        return '<div class="cjo_modulselect">'."\r\n".
               '   <form action="index.php#addslice" method="get">'."\r\n".
               '     <input type="hidden" name="page" value="edit" />'."\r\n".
               '     <input type="hidden" name="subpage" value="content" />'."\r\n".
               '     <input type="hidden" name="article_id" value="'.$this->article_id.'" />'."\r\n".
               '     <input type="hidden" name="mode" value="'.$this->mode.'" />'."\r\n".
               '     <input type="hidden" name="slice_id" value="'.$slice_id.'" />'."\r\n".
               '     <input type="hidden" name="function" value="add" />'."\r\n".
               '     <input type="hidden" name="save" value="1" />'."\r\n".
               '     <input type="hidden" name="clang" value="'.$this->clang.'" />'."\r\n".
               '     <input type="hidden" name="ctype" value="'.$this->ctype.'" />'."\r\n".
               '     '.$this->modul_sel->get()."\r\n".
               '   </form>'."\r\n".
               '</div>';
    }

    private function getSliceButtons($slice_id) {

        global $CJO, $I18N;

        $slice_buttons = new buttonField();

        if ($this->slice_id == $slice_id &&
        $this->function == 'edit') {

            $slice_buttons->addButton('slice_cancel_button', $I18N->msg('button_cancel'), true, 'img/silk_icons/cancel.png');
            $slice_buttons->setButtonAttributes('slice_cancel_button',
                                                'class="cjo_button_cancel"
                                                id="cjo_button_cancel-'.$slice_id.'"');

            $slice_buttons->addButton('slice_update_button', $I18N->msg('button_update'), true, 'img/silk_icons/tick.png');
            $slice_buttons->setButtonAttributes('slice_update_button',
                                                'class="cjo_button_update"
                                                id="cjo_button_update-'.$slice_id.'"');

            $slice_buttons->addButton('slice_save_button', $I18N->msg('button_save'), true, 'img/silk_icons/disk.png');
            $slice_buttons->setButtonAttributes('slice_save_button',
                                                'class="cjo_button_save"
                                                id="cjo_button_save-'.$slice_id.'"');
        }
        else {
            $slice_buttons->addButton('slice_edit_button', $I18N->msg('button_edit'), true, 'img/silk_icons/page_white_edit.png');
            $slice_buttons->setButtonAttributes('slice_edit_button',
                                                'class="cjo_button_edit"
                                                id="cjo_button_edit-'.$slice_id.'"');
        }

        if ($CJO['USER']->isAdmin() || !$CJO['USER']->hasPerm("editContentOnly[]")) {
            $slice_buttons->addButton('slice_delete_button', $I18N->msg('button_delete'), true, 'img/silk_icons/bin.png');
            $slice_buttons->setButtonAttributes('slice_delete_button',
                                                'class="cjo_button_delete"
                                                id="cjo_button_delete-'.$slice_id.'"');
        }

        if ($CJO['USER']->isAdmin() || $CJO['USER']->hasPerm("moveSlice[]")) {

            $slice_buttons->addButton('slice_move_up_button', $I18N->msg('button_move_up'), true, 'img/silk_icons/move_up_green.png');
            $slice_buttons->setButtonAttributes('slice_move_up_button',
                                                'class="cjo_button_move_up small"
                                                id="cjo_button_move_up-'.$slice_id.'"');;

            $slice_buttons->addButton('slice_move_down_button', $I18N->msg('button_move_down'), true, 'img/silk_icons/move_down_green.png');
            $slice_buttons->setButtonAttributes('slice_move_down_button',
                                                'class="cjo_button_move_down small"
                                                id="cjo_button_move_down-'.$slice_id.'"');
        }

        return $slice_buttons->_get();
    }
    
    public static function createCtypeMultiLink($article_id, $ctype) {
        
        global $CJO, $I18N;
        
        $article = OOArticle::getArticleById($article_id);
        
        if (!OOArticle::isValid($article)) return false;

        $ctypes = cjoTemplate::getCtypes($article->getTemplateId());

        if (count($ctypes) < 1) return false;

        $output = '';

        foreach($ctypes as $ctype_id) {
            
            if (!$CJO['USER']->hasCtypePerm($ctype_id)) continue;

            $link_text = $CJO['CTYPE'][$ctype_id];
            $slices = ($article->_slices[$ctype_id]) ? ' <i>('.$article->_slices[$ctype_id].')</i>' : '';
            
            $class = $output == '' ? ' class="first"' : '';
           
            $output .= sprintf('<li%s>%s</li>',
                               $class,
                               cjoAssistance::createBELink($link_text.$slices, 
                                                           array('subpage'=>'content','article_id'=>$article_id,'clang'=>$CJO['CUR_CLANG'],'ctype'=>$ctype_id), 
                                                           array(), 
                                                           'title="'.$I18N->msg('label_edit_article_content', $CJO['CTYPE'][$ctype_id]).'"')
                               );
        } 
        
        $link = cjoAssistance::createBELink($I18N->msg('title_content'), 
                                            array('subpage'=>'content','article_id'=>$article_id,'clang'=>$CJO['CUR_CLANG'],'ctype'=>$ctype), 
                                            array(), 
                                            'title="'.$I18N->msg('title_content').'"  class="cjo_multi_link_opener"');
        
        return  "\r\n\t\t".'<div class="cjo_multi_link cjo_ctype_content">'.
                "\r\n\t\t".$link.
                "\r\n\t\t".'<ul class="cjo_multi_link_container">'.
                $output.
                "\r\n\t\t".'</ul></div>'."\r\n\t";

    }
    
    private static function clearLocalSliceVars() {

        return '<?php'."\r\n".
               '    $cjo_all_vars    = array_keys(get_defined_vars());'."\r\n".
               '    $cjo_global_vars = $GLOBALS;'."\r\n".              
               '    foreach($cjo_all_vars as $cjo_var) {'."\r\n".
               '       if (!key_exists($cjo_var, $GLOBALS)) {'."\r\n".
               '           unset($$cjo_var);'."\r\n".     
               '        }'."\r\n".
               '    }'."\r\n".
               '    unset($cjo_var);'."\r\n".         
               '    unset($cjo_global_vars);'."\r\n".        
               '    unset($cjo_all_vars);'."\r\n".      
               '?>'."\r\n";    
    }

    private function replaceVars(&$sql, $content) {

        $content = $this->replaceObjectVars($sql,$content);
        
        if ($this->viasql && $this->sql->getRows() == 0) {
            $this->sql = &$sql;
        }
        $content = $this->replaceCommonVars($content);
        return $content;
    }

    private function replaceObjectVars(&$sql, $content) {

        global $CJO;

        $tmp = '';
        $slice_id = $sql->getValue("sl.id");
        $this->escapeContejoVars($content);
        
        foreach ($CJO['VARIABLES'] as $var) {

            if ($this->mode == 'edit') {

                if (($this->function == 'add' && $slice_id == '0') ||
                    ($this->function == 'edit' && $slice_id == $this->slice_id)) {

                    if (isset($CJO['ACTION']['SAVE']) && $CJO['ACTION']['SAVE'] === false) {
                        // Wenn der aktuelle Slice nicht gespeichert werden soll
                        // (via Action wurde das Nicht-Speichern-Flag gesetzt)
                        // Dann die Werte manuell aus dem Post übernehmen
                        // und anschließend die Werte wieder zurücksetzen,
                        // damit die nächsten Slices wieder die Werte aus der DB verwenden
                        $var->setACValues($sql, $CJO['ACTION']);
                        $tmp = $var->getBEInput($sql, $content);
                        $sql->flushValues();
                    }
                    else  {
                        // Slice normal parsen
                        $tmp = $var->getBEInput($sql, $content);
                    }
                }
                else {
                    $tmp = $var->getBEOutput($sql,$content);
                }
            }
            else {
                $tmp = $var->getFEOutput($sql,$content);
            }

            // Rückgabewert nur auswerten wenn auch einer vorhanden ist
            // damit $content nicht verfälscht wird
            // null ist default Rückgabewert, falls kein RETURN in einer Funktion ist
            if($tmp !== null) {
                $content = $tmp;
            }
        }
        $this->unescapeContejoVars($content);
        return $content;
    }

    private function replaceCommonVars($content, $article_id = false) {

        global $CJO;

        static $user_id = null;
        static $user_login = null;

        // UserId gibts nur im Backend
        if ($user_id === null) {

            if (is_object($CJO['USER'])) {
                $user_id = $CJO['LOGIN']->getValue('user_id');
                $user_login = $CJO['LOGIN']->getValue('login');
            }
            else {
                $user_id = '';
                $user_login = '';
            }
        }
        $path = cjoAssistance::toArray($this->getValue('path').$this->article_id.'|');
           
        $search = array('GLOBALS[\'CJO_ARTICLE_ID\']'    => 'GLOBALS[\'CJ_O_ARTICLE_ID\']',
                        'GLOBALS[\'CJO_CLANG_ID\']'      => 'GLOBALS[\'CJ_O_CLANG_ID\']',
                        'GLOBALS[\'CJ_O_ARTICLE_ID\']'   => 'GLOBALS[\'CJO_ARTICLE_ID\']',
                        'GLOBALS[\'CJ_O_CLANG_ID\']'     => 'GLOBALS[\'CJO_CLANG_ID\']',
                        'CJO_ARTICLE_ID'                 =>  $this->article_id,
                        'CJO_TEMPLATE_ID'                =>  $this->getTemplateId(),
                        'CJO_ARTICLE_PARENT_ID'          =>  $this->parent_id,
                        'CJO_PARENT_ID'                  =>  $this->parent_id,
                        'CJO_ARTICLE_ROOT_ID'            =>  array_shift($path),
                        'CJO_ARTICLE_AUTHOR'             =>  $this->getValue('author'),
                        'CJO_ARTICLE_NAME'               =>  $this->getValue('name'),
                        'CJO_ARTICLE_TITLE'              =>  $this->getValue('title'),
                        'CJO_ARTICLE_DESCRIPTION'        =>  $this->getValue('description'),
                        'CJO_ARTICLE_KEYWORDS'           =>  $this->getValue('keywords'),
                        'CJO_ARTICLE_URL'                =>  cjoRewrite::getUrl($this->article_id, $this->clang),
                        'CJO_ARTICLE_ONLINE_FROM'        =>  $this->getValue('online_from'),
                        'CJO_ARTICLE_ONLINE_TO'          =>  $this->getValue('online_to'),
                        'CJO_ARTICLE_CREATEUSER'         =>  $this->getValue('createuser'),
                        'CJO_ARTICLE_UPDATEUSER'         =>  $this->getValue('updateuser'),
                        'CJO_ARTICLE_CREATEDATE'         =>  $this->getValue('createdate'),
                        'CJO_ARTICLE_UPDATEDATE'         =>  $this->getValue('updatedate'));

        foreach($search as $key => $replace) {
           $content = preg_replace('/(?<!\[\[)'.preg_quote($key).'(?!\]\])/', $replace, $content); 
        }

        return  '<?php'."\r\n".
                '   $'.'GLOBALS[\'CJO_ARTICLE_ID\'] = '.$this->article_id.";\r\n".
                '   $'.'GLOBALS[\'CJO_CLANG_ID\'] = '.$this->clang.";\r\n".
                '?>'."\r\n".
                $content;
    }

    /**
     * Creates a new article.
     * @param array $article
     * @return int id of the new article
     * @access public
     */
    public static function addArticle($article){

        global $CJO, $I18N;

        if (!is_array($article)) {
            return false;
        }

        if (!$CJO['CONTEJO'] ||
            $CJO['USER']->hasPerm('editContentOnly[]') ||
            !$CJO['USER']->hasPerm('publishArticle[]') ||
            !$CJO['USER']->hasCatPermWrite($article['re_id'])) {
            cjoMessage::addError($I18N->msg("msg_no_permissions"));
            return false;
        }

        if (!$CJO['USER']->hasOnlineFromToPerm() ||
            !$article['online_from']) {
            $article['online_from'] = time();
        }

        if (!$CJO['USER']->hasOnlineFromToPerm() ||
            !$article['online_to']) {
            $article['online_to']   = mktime(0, 0, 0, 1, 1, 2020);
        }

        if (!$CJO['USER']->hasLoginPerm()) {
            $article['type_id'] = "1";
        }

        $article['path'] = OOArticle::getArticlePath($article['re_id'], true);
        
        $article['cat_group'] = (!isset($article['cat_group'])) ? 1  : (int) $article['cat_group'];
        $article['re_id']     = (!isset($article['re_id']))     ? 0  : (int) $article['re_id'];
        $article['author']    = (!isset($article['author']))    ? '' : (string) $article['author'];        
        
        $article['id'] = false;
        $state = true;
        foreach (array_keys($CJO['CLANG']) as $clang_id){

            $insert = new cjoSql();
            $insert->setTable(TBL_ARTICLES);
            if (!$article['id']) $article['id'] = $insert->setNewId("id");
            else $insert->setValue("id",     $article['id']);
            $insert->setValue("name",        $article['name']);
            $insert->setValue("clang",       $clang_id);
            $insert->setValue("re_id",       (int) $article['re_id']);
            $insert->setValue("prior",       time());
            $insert->setValue("path",        $article['path']);
            $insert->setValue("startpage",   0);
            $insert->setValue("cat_group",   $article['cat_group']);
            $insert->setValue("status",      (int) $article['status']);
            $insert->setValue("teaser",      (int) $article['teaser']);            
            $insert->setValue("template_id", $article['template_id']);
            $insert->setValue("redirect",    (string) $article['redirect']);            
            $insert->setValue("type_id",     $article['type_id']);            
            $insert->setValue("author",      $article['author']);
            $insert->setValue("online_from", $article['online_from']);
            $insert->setValue("online_to",   $article['online_to']);
            $insert->addGlobalCreateFields();
            $state = $insert->Insert();

            if (!$state) {
                cjoMessage::addError($insert->getError());
                return false;
            }

            cjoGenerate::newPrio($article['re_id'], $clang_id, 0, 1);
            cjoGenerate::toggleStartpageArticle($article['id'], $article['re_id']);
            cjoGenerate::generateArticle($article['id']);
            cjoGenerate::generateArticle($article['re_id'], false);

            cjoExtension::registerExtensionPoint('ARTICLE_ADDED', 
                                                 array("id" => $article['id'],
                                                       "clang" => $clang_id,
                                                       "name" => $article['name'],
                                                       "re_id" => $article['re_id'],
                                                       "path" => $article['path'],
                                                       "template_id" => $article['template_id'],
                                                       "redirect" => $article['redirect'],
                                                       "type_id" => $article['type_id'],
                                                       "online_from" => $article['online_from'],
                                                       "online_to" => $article['online_to'],
                                                       "user" => $CJO['USER']->getValue("name")));
        }

        if ($state) cjoMessage::addSuccess($I18N->msg('msg_article_width_name_inserted', $article['name']));

        return $article['id'];
    }

    public static function updatePrio($id, $newprio=1000000000000, $clang=false){

        global $CJO, $I18N;

        if ($clang === false) {
            $temp = $CJO['CUR_CLANG'];
            $CJO['CUR_CLANG'] = $clang;
        }
        
        cjoExtension::registerExtension('PRIOR_UPDATED', 'cjoArticle::regenerateUpdatePrio');
        cjoAssistance::updatePrio(TBL_ARTICLES, $id, $newprio, $col = 'id', 're_id');
        
        if ($clang === false) {
            $CJO['CUR_CLANG'] = $temp;
        }
    }

    public static function regenerateUpdatePrio($params) {
        global $CJO;
        cjoGenerate::deleteGeneratedArticle($params['id']);
        cjoGenerate::deleteGeneratedArticle($params['re_id'],true);
    }

    public static function updateArticleParams($article_id, $mode, $clang) {

        global $CJO, $I18N;

        switch($mode) {
            case 'status':
                $action['row'] = 'status';
                $action['msg'] = $I18N->msg('msg_status_updated');
                break;
            case 'navi_item':
                $action['row'] = 'navi_item';
                $action['msg'] = $I18N->msg('msg_navi_item_updated');
                break;
            case 'teaser':
                $action['row'] = 'teaser';
                $action['msg'] = $I18N->msg('msg_teaser_updated');
                break;
            case 'comments':
                $action['row'] = 'comments';
                $action['msg'] = $I18N->msg('msg_comments_updated');
                break;
            case 'delete':
                $action['del']  = true;
                break;
        }

        if ($action['row'] == '' && !$action['del']) return false;

        if ($action['del']) {
            return cjoGenerate::deleteArticle($article_id);
        }
        else {

            $sql = new cjoSql();
            $sql->setQuery("SELECT ".$action['row']." FROM ".TBL_ARTICLES." WHERE id='".$article_id."' AND clang='".$clang."'");

            if ($sql->getRows() == 1){

                $new_val = ($sql->getValue($action['row']) == 1) ? 0 : 1;

                $update = new cjoSql();
                $update->setTable(TBL_ARTICLES);
                $update->setWhere("id='".$article_id."' AND clang='".$clang."'");
                $update->setValue($action['row'], $new_val);
                $update->addGlobalUpdateFields();
                $status = $update->Update($action['msg']);

                if ($status) {
                    cjoGenerate::generateArticle($article_id);
                    cjoExtension::registerExtensionPoint('ARTICLE_UPDATED',
                                                         array ("id" => $article_id,
                                                                "clang" => $clang,
                                                                "action" => strtoupper($action['row']),
                                                                "new_val" => $new_val));
                    return true;
                }
                else {
                    cjoMessage::addError($update->getError());
                }
            }
            else {
                cjoMessage::addError($I18N->msg("msg_no_such_article"));
            }
        }
        return false;
    }
}
