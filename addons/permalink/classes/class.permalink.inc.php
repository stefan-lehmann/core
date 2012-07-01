<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  permalink
 * @version     2.6.2
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

class cjoPermalink {
    
    static $mypage = 'permalink';
    
    
    public static function getArticleId() {
        
        global $CJO;
        
        if (!isset($CJO['VIRTUAL_PATH'])) cjoProcess::getAdjustPath();
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere('name LIKE :name AND value LIKE :value', array('name'=>self::$mypage,'value'=>$CJO['VIRTUAL_PATH']));
        $sql->Select('article_id, clang');
        
        if ($sql->getRows() != 0) {
            $CJO['ARTICLE_ID'] = $sql->getValue('article_id');
            $CJO['CUR_CLANG'] = $sql->getValue('clang');
        }
    }
    
    public static function generatedUrl($params) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('name'=>'permalink', 'article_id'=> $params['query']['article_id'], 'clang'=> $params['query']['clang']));
        $sql->Select();
        
        if ($sql->getRows() != 0) {
            $params['deeplink'] = '%name%/';
            $params['name'] = $sql->getValue('value');
        }
        return $params;
    }
}