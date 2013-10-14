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
 * CJO_ARTICLE[1]
 * CJO_ARTICLE[id=1]
 *
 * CJO_ARTICLE[id=1 ctype=2 clang=1]
 *
 * CJO_ARTICLE[field='id']
 * CJO_ARTICLE[field='description' id=3]
 * CJO_ARTICLE[field='description' id=3 clang=2]
 */

class cjoVarArticle extends cjoVars {
    // --------------------------------- Output

    public function getTemplate($content, $article_id = false, $template_id = false) {
        global $CJO;
        if (empty($article_id)) $article_id = $CJO['ARTICLE_ID'];
        $this->template_id = $template_id;
        $content = $this->replaceCommonVars($content, $article_id);
        $content = $this->matchCtype($content, $article_id);
        $content = $this->matchParentsCtype($content, $article_id);
        $content = $this->matchArticle($content, true);
        $content = $this->matchArticleFile($content, $article_id);
        return $content;
    }
    
    public function getBEOutput(& $sql, $content) {
        $content = $this->matchArticle($content);
        return $content;
    }

    /**
     * @see cjo_var::handleDefaultParam
     */
    public function handleDefaultParam($varname, $args, $name, $value) {

        switch($name) {
            case '0' :
            case 'id' :
                $args['id'] = (int) $value;
                break;
            case 'clang' :
            case 'ctype' :
            case 'width' :
            case 'height' :
            case 'template':
                $args[$name] = (int) $value;
                break;
            case 'get_src' :
            case 'crop_auto' :
            case 'strict' :    
                $args[$name] = (bool) $value;
                break;
            case 'field' :
            case 'crop_num':
                $args[$name] = (string) $value;
                break;
        }
        
        return parent::handleDefaultParam($varname, $args, $name, $value);
    }

    /**
     * Wert f�r die Ausgabe
     */
    protected function matchArticle($content, $replaceInTemplate = false) {

        global $CJO;

        $var = 'CJO_ARTICLE';

        $matches = $this->getVarParams($content, $var);

        foreach ($matches as $match) {

            list ($param_str, $args)  = $match;
            list ($article_id, $args) = $this->extractArg('id',    $args, 0);
            list ($clang, $args)      = $this->extractArg('clang', $args, $CJO['CUR_CLANG']);
            list ($ctype, $args)      = $this->extractArg('ctype', $args, -1);
            list ($field, $args)      = $this->extractArg('field', $args, '');
            list ($template, $args)   = $this->extractArg('template', $args, 0);            

            $tpl = '';
            if ($article_id == 0) {

                // CJO_ARTICLE[field=name] keine id -> feld von aktuellem artikel verwenden
                if ($field) {
                    if (OOArticle::hasValue($field)) {
                        $tpl = '<?php if (get_class($this) == \'cjoArticle\') echo htmlspecialchars('. $this->handleGlobalVarParamsSerialized($var, $args, '$this->getValue(\''. addslashes($field) .'\')') .'); ?>';
                    }
                }
                // CJO_ARTICLE[] keine id -> aktuellen artikel verwenden
                else {

                    if ($replaceInTemplate) {
                        // aktueller Artikel darf nur in Templates, nicht in Modulen eingebunden werden
                        $tpl = '<?php if(isset($this) && get_class($this) == \'cjoArticle\') echo '. $this->handleGlobalVarParamsSerialized($var, $args, '$this->getArticle('. $ctype .')') .'; ?>';
                    }
                }
            }
            else if ($article_id > 0) {

                $article = OOArticle::getArticleById($article_id);
                
                if (OOArticle::isValid($article)) {
                
                    // CJO_ARTICLE[field=name id=5] feld von gegebene artikel id verwenden
                    if ($field) {
    
                        if (OOArticle::hasValue($field)) {
                            // bezeichner wählen, der keine variablen
                            // aus modulen/templates überschreibt
                            $varname = '$__cjo_article';
                            $tpl = '<?php
                                   '. $varname .' = OOArticle::getArticleById('. $article_id .', '. $clang .');
                                   if ('. $varname .') echo htmlspecialchars('. $this->handleGlobalVarParamsSerialized($var, $args, $varname .'->getValue(\''. addslashes($field) .'\')') .');
                                   ?>';
                        }
                    }
                    // CJO_ARTICLE[id=5] kompletten artikel mit gegebener artikel id einbinden
                    else {
                        // bezeichner wählen, der keine variablen
                        // aus modulen/templates überschreibt
                        $varname = '$__cjo_article';
                        $tpl = '<?php'."\r\n";
                        $tpl .= $varname .' = new cjoArticle();'."\r\n";                  
                        $tpl .= $varname .'->setArticleId('. $article_id .');'."\r\n";                                             
                        $tpl .= $varname .'->setClang('. $clang .');'."\r\n";
                        if ($template > 0) {
                            $tpl .= $varname .'->setTemplateId('. $template .');'."\r\n";
                        }
                        $tpl .= 'echo '. $this->handleGlobalVarParamsSerialized($var, $args, $varname .'->getArticle('. $ctype .')') .';'."\r\n";
                        $tpl .= '?>';
                    }
                }
                else {
                    $tpl = '<!-- INVALID: '.$var.'['.$param_str.'] (the article (ID='.$article_id.') does not exists) -->';  
                }
            }
            if ($tpl != '')
                $content = str_replace($var.'['.$param_str.']', $tpl, $content);
        }
        return $content;
    }

    /**
     * Wert f�r die Ausgabe
     */
    protected function matchCtype($content, $article_id) {

        global $CJO;

        $var = 'CJO_ARTICLE_CTYPE';

        $matches = $this->getVarParams($content, $var);

        foreach ($matches as $match) {

            $replace = '';

            list ($param_str, $args)  = $match;
            list ($ctype, $args)      = $this->extractArg('id', $args, 0);
            list ($clang, $args)      = $this->extractArg('clang', $args, $CJO['CUR_CLANG']);
            list ($template, $args)   = $this->extractArg('template', $args, $this->template_id);  

            $replace = 'CJO_ARTICLE[id='.$article_id.' ctype='.$ctype.' clang='.$clang.' template='.$template.']';
            $count = OOArticle::countCtypeContent($article_id, $clang, $ctype);

            $content = str_replace($var.'_COUNT['.$param_str.']', $replace, $content);
            $content = str_replace($var.'['.$param_str.']', $replace, $content);
        }
        return $content;
    }

    /**
     * Wert für die Ausgabe
     */
    protected function matchParentsCtype(& $content, $article_id) {

        global $CJO;

        $var     = 'CJO_PARENTS_CTYPE';
        $clang   = $CJO['CUR_CLANG'];
        $matches = $this->getVarParams($content, $var);

        if (!empty($matches)) {
           	$article = OOArticle::getArticleById($article_id);
            $tree = cjoAssistance::toArray($CJO['START_ARTICLE_ID'].$article->_path.$article_id);
            $tree = array_unique($tree);
    
            krsort($tree);
    
            $article_ids = array(); 
    
            foreach($tree as $parent_id) {
                $parent_article = OOArticle::getArticleById($parent_id);
                $article_ids[0][] = $parent_id;
                if ($parent_article->_template_id == $article->_template_id){
                    $article_ids[1][] = $parent_id;
                }
            }
        }
        
        foreach ($matches as $match) {

            $replace = '';
            $count   = 0;
            list ($param_str, $args)  = $match;
            list ($ctype, $args)      = $this->extractArg('ctype', $args, null);
            if ($ctype == null) {
              list ($ctype, $args)    = $this->extractArg('id', $args, 0);
            }
            list ($strict, $args)     = $this->extractArg('strict', $args, 1);            
            list ($template, $args)   = $this->extractArg('template', $args, $this->template_id);  

            foreach($article_ids[$strict] as $id) {

                $slice = OOArticleSlice::getFirstSliceForArticle($id, $clang);

                while($slice != '') // Schleife wenn nächstes Slice nicht leer
                {
                    if ($slice->_ctype == $ctype) {
                        $replace = 'CJO_ARTICLE[id='.$id.' ctype='.$ctype.' clang='.$clang.' template='.$template.']';
                        $count = OOArticle::countCtypeContent($id, $clang, $ctype);
                        break 2;
                    }
                    $slice = $slice->getNextSlice();

                }
            }
            $content = str_replace($var.'_COUNT['.$param_str.']', $count, $content);
            $content = str_replace($var.'['.$param_str.']', $replace, $content);
        }
        return $content;
    }

    protected function matchArticleFile($content, $article_id) {

        global $CJO;

        $var = 'CJO_ARTICLE_FILE';

        $matches = $this->getVarParams($content, $var);

        foreach ($matches as $match) {

            $replace = '';

            list ($param_str, $args) = $match;
            list ($crop_num, $args)  = $this->extractArg('crop_num', $args);
            list ($clang, $args)     = $this->extractArg('clang', $args, $CJO['CUR_CLANG']);
            list ($get_src, $args)   = $this->extractArg('get_src', $args, 0);

            if (!$crop_num) {
                list ($crop_num, $args) = $this->extractArg('id', $args, '-');
            }
            else {
                list ($article_id, $args) = $this->extractArg('id', $args, false);
            }

            if ($crop_num != '-') {
                $params = array('crop_num'=> $crop_num, 'get_src' => $get_src);
            }
            else {
                $params = array();
                if ($args['crop_auto']) $params['crop_auto'] = $args['crop_auto'];
                if ($args['get_src'])   $params['get_src']   = $get_src;
                if ($args['width'])     $params['width']     = $args['width'];
                if ($args['height'])    $params['height']    = $args['height'];
            }

            $article = OOArticle::getArticleById($article_id, $clang);

            if (OOArticle::isValid($article) && $article->_file) {
                $replace = OOMedia::isImage($article->getFile()) ? OOMedia::toThumbnail($article->getFile(), '', $params) : $article->getFile();
            }

            $content = str_replace($var.'['.$param_str.']', $replace, $content);
        }
        return $content;
    }

    protected function replaceCommonVars($content, & $article_id = false) {

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

        if (empty($article_id))
            $article_id = cjo_request('article_id', 'cjo-article-id', $CJO['START_ARTICLE_ID']);

        $article = OOArticle::getArticleById($article_id);
        
        if (!OOArticle::isValid($article)) 
            return $content;
        
        if (empty($this->template_id))
            $this->template_id = $article->getTemplateId();
        
        $search = array('CJO_ARTICLE_ID'            => $article->getId(),
                        'CJO_TEMPLATE_ID'           => $article->getTemplateId(),
                        'CJO_ARTICLE_PARENT_ID'     => $article->getParentId(),
					    'CJO_PARENT_ID'             => $article->getParentId(),
                        'CJO_ARTICLE_ROOT_ID'       => @array_shift(cjoAssistance::toArray($article->getPath().$article_id.'|')),
                        'CJO_ARTICLE_AUTHOR'        => $article->getAuthor(),
                        'CJO_ARTICLE_NAME'          => $article->getName(),
                        'CJO_ARTICLE_TITLE'         => $article->getTitle(),
                        'CJO_ARTICLE_DESCRIPTION'   => $article->getDescription(),
                        'CJO_ARTICLE_KEYWORDS'      => $article->getKeywords(),
                        'CJO_ARTICLE_URL'           => $article->getUrl(),
                        'CJO_ARTICLE_ONLINE_FROM'   => $article->getOnlineFromDate(),
                        'CJO_ARTICLE_ONLINE_TO'     => $article->getOnlineToDate(),
                        'CJO_ARTICLE_CREATEUSER'    => $article->getCreateUser(),
                        'CJO_ARTICLE_UPDATEUSER'    => $article->getUpdateUser(),
                        'CJO_ARTICLE_CREATEDATE'    => $article->getCreateDate(),
                        'CJO_ARTICLE_UPDATEDATE'    => $article->getUpdateDate(),
                        'CJO_CLANG_ID'              => $article->getClang(),
                        'CJO_CLANG_ISO'             => $CJO['CLANG_ISO'][$article->getClang()],
					    'CJO_CLANG_NAME'            => $CJO['CLANG'][$article->getClang()],
                        'CJO_USER_ID'               => $user_id,
                        'CJO_USER_LOGIN'            => $user_login,
                        'CJO_SERVERNAME'            => $CJO['SERVERNAME'],
                        'CJO_SERVER'                => $CJO['SERVER'],
                        'CJO_START_ARTICLE_ID'      => $CJO['START_ARTICLE_ID'],
                        'CJO_NOTFOUND_ARTICLE_ID'   => $CJO['NOTFOUND_ARTICLE_ID'],
                        'CJO_HTDOCS_PATH'           => $CJO['HTDOCS_PATH'],
                        'CJO_MEDIAFOLDER'           => $CJO['MEDIAFOLDER'],
                        'CJO_FRONTPAGE_PATH'        => $CJO['FRONTPAGE_PATH'],
                        'CJO_ADJUST_PATH'           => $CJO['ADJUST_PATH']);
                        

        foreach($search as $key => $replace) {
           $content = preg_replace('/(?<!\[\[)'.preg_quote($key).'(?!\]\])/', $replace, $content); 
           $content = str_replace('[['.$key.']]', $key, $content);
        }
        $content = preg_replace('/CJO_ARTICLE_FILE(?!\[)/', $article->getFile(), $content);
        return $content;
    }
}