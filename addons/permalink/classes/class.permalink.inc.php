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
    
    static $addon = 'permalink';
    
    
    public static function getArticleId() {
        
        if (!cjoAddon::isActivated(self::$addon)) return false;
        
        if (cjoProp::get('VIRTUAL_PATH')) cjoProcess::getAdjustPath();
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere('name LIKE :name AND value LIKE :value', array('name'=>self::$addon,'value'=>cjoProp::get('VIRTUAL_PATH')));
        $sql->Select('article_id, clang');
        
        if ($sql->getRows() != 0) {
            cjoProp::set('ARTICLE_ID',$sql->getValue('article_id'));
            cjoProp::set('CUR_CLANG',$sql->getValue('clang'));
        }
    }
    
    public static function generatedUrl($params) {
        
        if (!cjoAddon::isActivated(self::$addon)) return $params;
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('name'=>self::$addon, 'article_id'=> $params['query']['article_id'], 'clang'=> $params['query']['clang']));
        $sql->Select();
        
        if ($sql->getRows() != 0 && strpos($sql->getValue('value'), '/') !== false) {
            $params['deeplink'] = '%name%/';
            $params['name'] = $sql->getValue('value');
        }
        return $params;
    }
    
    public static function initAddon() {
        
        $fields = cjoAddon::getParameter('FIELDS', self::$addon);
        
        if (!cjoAddon::isActivated('extend_meta') || 
            !is_array($fields['name']) ||
            !in_array(self::$addon,$fields['name'])) {
    
            if (!cjoProp::isBackend()) {
                cjoAddon::setProperty('status', false, self::$addon);
                return false;
            }
            
            if (!cjoAddon::isActivated('extend_meta')) {
                $url = cjoUrl::createBEUrl(array('page' => 'addons'));
                cjoMessage::addWarning(cjoAddon::translate(31,'msg_err_extend_meta_not_present', $url));
            } else {
                $url = cjoUrl::createBEUrl(array('page' => 'extend_meta', 'subpage'=>'settings'));
                cjoMessage::addWarning(cjoAddon::translate(31,'msg_err_permalink_not_present', $url));
            }
        }
    }
}