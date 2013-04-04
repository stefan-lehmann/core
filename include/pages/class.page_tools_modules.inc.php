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


class cjoPageToolsModules extends cjoPage {
    
    protected static $modultyps = array();
    protected static $curr_modultyp = array();
    
    protected function getModultyps() {

        if (!is_array(self::$curr_modultyp) || empty(self::$curr_modultyp)) {
                        
            $sql = new cjoSql();
            $qry = "SELECT * FROM ".TBL_MODULES." ORDER BY prior";
            $results = $sql->getArray($qry);

            if($sql->getRows() > 0) self::$modultyps = $results;
        }
        return self::$modultyps;

    }
    
    protected function getCurrModultyp($key=false) {
        
        if (!is_array(self::$curr_modultyp) || empty(self::$curr_modultyp)) {

            $sql = new cjoSql();
            $qry = "SELECT * FROM ".TBL_MODULES." WHERE id='".$this->oid."'";
            $results = $sql->getArray($qry);
            if($sql->getRows() == 1) self::$curr_modultyp = $results[0];
        }

        return $key !== false && isset(self::$curr_modultyp[$key]) 
               ? self::$curr_modultyp[$key] 
               : self::$curr_modultyp;
    }
    
    protected function generateTabs() {
    
        $tabs  = array();
    
        $tabs[] = array('settings',
                         'title' => cjoI18N::translate("label_modul_settings"),
                         'params' => array('mode' => 'settings', 'function' => 'edit', 'oid' => $this->oid),
                         'important' => true);
    
        if (self::isAddMode() && $this->mode == 'settings') {
    
            /**
             * Do not delete translate values for cjoI18N collection!
             * [translate: label_add_module]
             * [translate: label_edit_module]
             */
            $tabs[0]['title'] = cjoI18N::translate("label_".$this->function."_module");
        }
        else {
            $tabs[] = array('logic',
                             'title' => cjoI18N::translate("label_modul_logic"),
                             'params' => array('mode' => 'logic', 'function' => 'edit', 'oid' => $this->oid),
                             'important' => true);
    
            $tabs[] = array('layout',
                             'title' => cjoI18N::translate("label_modul_layouts"),
                             'params' => array('mode' => 'layout', 'function' => 'edit', 'oid' => $this->oid),
                             'important' => true);
    
            $tabs[] = array('actions',
                             'title' => cjoI18N::translate("label_modul_actions"),
                             'params' => array('mode' => 'actions', 'function' => 'edit', 'oid' => $this->oid),
                             'important' => true);
    
            if ($this->mode) {    
                echo '<h2 class="layout_name">
                        <span>'.cjoI18N::translate("label_modul").':</span> '.
                        self::getCurrModultyp('name').'
                        <span>(ID='.self::getCurrModultyp('id').')</span>
                      </h2>';
             }
        }
        cjoSubpages::setTabs($this->mode, $tabs, cjoProp::getPage());
    }

    protected function setAdd() {
        $this->setEdit();
    }
    
    protected function setEdit() {
        
        $this->generateTabs();
        
        $class_name = get_called_class().$this->mode;

        if (!is_object($this->subpage) && class_exists($class_name)) {
            $this->subpage = new $class_name();
        }
    }

    protected function getDefault() {
        
        if (is_object($this->subpage)) return false;        
        
        $this->list = new cjoList("SELECT *, id AS actions FROM ".TBL_MODULES, "prior", 'ASC', '', 100);
        
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/application_form.png" alt="" />',
                                         cjoUrl::createBELink(
                                                '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                 array('mode'=>'settings', 'function' => 'add', 'oid' => ''),
                                                 $this->list->getGlobalParams(),
                                                'title="'.cjoI18N::translate("button_add").'"'));
        
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
        
        $this->cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
        $this->cols['id']->setHeadAttributes('class="icon"');
        $this->cols['id']->setBodyAttributes('class="icon"');
        
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_name"));
        
        $this->cols['prio'] = new prioColumn();
        
        $replace_templates = array();
        $replace_templates[0] = cjoI18N::translate("label_rights_all").' '.cjoI18N::translate("title_templates");
        $sql = new cjoSql();
        $qry = "SELECT id, name FROM ".TBL_TEMPLATES." ORDER BY prior";
        $sql->setQuery($qry);
        for ($i=0;$i<$sql->getRows();$i++) {
            $replace_templates[$sql->getValue('id')] = $sql->getValue('name');
            $sql->next();
        }
        $this->cols['templates'] = new resultColumn('templates', cjoI18N::translate("label_template_connection"), 'replace_array', array($replace_templates,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
        
        $this->cols['ctypes'] = new resultColumn('ctypes', cjoI18N::translate("label_ctype_connection"), 'replace_array', array(cjoProp::get('CTYPE'),'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
        
        $replace_actions = array();
        $sql = new cjoSql();
        $qry = "SELECT *, (SELECT name FROM ".TBL_ACTIONS." WHERE id=ma.action_id LIMIT 1) AS name FROM ".TBL_MODULES_ACTIONS." ma";
        $sql->setQuery($qry);
        for ($i=0;$i<$sql->getRows();$i++) {
            $replace_actions[$sql->getValue('module_id')] .= $replace_actions[$sql->getValue('module_id')] ? ', '.$sql->getValue('name') : $sql->getValue('name');
            $sql->next();
        }
        $this->cols['actions'] = new resultColumn('actions', cjoI18N::translate("label_actions_connection"), 'replace_array', array($replace_actions,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
        
        $this->cols['edit'] = new editColumn(array ('function' => 'edit', 'mode' => 'settings', 'oid' => '%id%'));
        
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams());

        $this->list->addColumns($this->cols);
        $this->list->show(false);
    }

    public static function onListDelete($id) {

        if ($id != '') {
    
            $sql = new cjoSql();
            $qry = "SELECT DISTINCT
                            s.article_id AS id,
                            (SELECT name FROM ".TBL_ARTICLES." WHERE id=s.article_id AND clang=s.clang) AS name,
                            s.clang AS clang,
                            m.name AS modul_name
                    FROM ".TBL_ARTICLES_SLICE." s
                    LEFT JOIN ".TBL_MODULES." m
                    ON s.modultyp_id = m.id
                    WHERE s.modultyp_id='".$id."'";
            $results = $sql->getArray($qry);
    
            $temp = array();
            foreach ($results as $result) {
                $temp[] = cjoUrl::createBELink(
                                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
                                             array('page' => 'edit',
                                                   'subpage' => 'content',
                                                   'function' => '',
                                                   'oid' => '',
                                                   'article_id' => $result['id'],
                                                   'clang' => $result['clang'],
                                                   'mode' => 'edit'));
            }
    
            if (!empty($temp))
                cjoMessage::addError(cjoI18N::translate("msg_module_cannot_be_deleted",
                                     $results[0]['modul_name']).'<br />'.implode(' | ',$temp));
    
            if (!cjoMessage::hasErrors()) {
    
                $sql->flush();
                $sql->statusQuery("DELETE FROM ".TBL_MODULES." WHERE id='".$id."'",
                                  cjoI18N::translate("msg_module_deleted"));
    
                $sql->statusQuery("DELETE FROM ".TBL_MODULES_ACTIONS." WHERE module_id='".$id."'",
                                  cjoI18N::translate("msg_all_actions_deleted_from_modul"));
    
                $path['path']                = liveEdit::getEditPath(liveEdit::getTmplExtension());
                $path['type']                = $path['path'].'/'.$type;
                $path['type_template']       = $path['type'].'/'.$template.'.template';
                $path['type_ctype']          = $path['type'].'/'.$ctype.'.ctype';
                $path['type_template_ctype'] = $path['type_template'].'/'.$ctype.'.ctype';
    
                $path = array_reverse($path);
                foreach($path as $key=>$val) {
                    @unlink(cjoModulTemplate::getTemplatePath($id,$template,$ctype,'input'));
                    @unlink(cjoModulTemplate::getTemplatePath($id,$template,$ctype,'output'));
                    @rmdir($val);
                }
                cjoMessage::addSuccess(cjoI18N::translate("msg_modul_all_layout_deleted"));
                cjoAssistance::updatePrio(TBL_MODULES);
                cjoExtension::registerExtensionPoint('MODULE_DELETED', $results);
            }
        }
    }
}