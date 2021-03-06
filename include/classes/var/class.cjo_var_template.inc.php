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
 * CJO_TEMPLATE[2]
 */

class cjoVarTemplate extends cjoVars {

    // --------------------------------- Output

    public function getBEOutput(& $sql, $content) {
        return $this->matchTemplate($content);
    }

    public function getTemplate($content, $article_id = false, $template_id = false) {
        return $this->matchTemplate($content,$article_id);
    }

    /**
     * Wert für die Ausgabe
     */
    private function matchTemplate($content, $article_id = false) {

        $var = 'CJO_TEMPLATE';
        $matches = $this->getVarParams($content, $var);
        
        if ($article_id) {
            $article = OOArticle::getArticleById($article_id);
            if (!OOArticle::isValid($article)) return $content;
            $origin_template_id = $article->getTemplateId();
        }
        
        foreach ($matches as $match) {

            list ($param_str, $args) = $match;
            list ($template_id, $args) = $this->extractArg('id', $args, 0);

            if ($template_id > 0) {
                $varname = '$__cjo_template'. $template_id;
                $tpl = '<?php
                       '. $varname .' = new cjoTemplate('. $template_id .');
                       eval(\'?>\'.'. $this->handleGlobalVarParamsSerialized($var, $args, $varname .'->getTemplate('.$article_id.','.$origin_template_id.')') .');
                       ?>';
                $content = str_replace($var.'['.$param_str.']', $tpl, $content);
            }
        }
        return $content;
    }
}