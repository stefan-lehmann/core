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

class cjoPageToolsModulesSettings extends cjoPageToolsModules {
    
    protected function setAdd() {
        
        $this->setEdit();
        
        cjoAssistance::insertKeyAfterInArray($this->fields, 'name', 'id');
        
        $this->fields['id'] = new selectField('id', cjoI18N::translate("label_id"));
        $this->fields['id']->addAttribute('size', '1');
        $this->fields['id']->addAttribute('class', 'inp10');

        $i = 0; $c = 0;
        while($i < 10) {
            $c++;
            foreach(self::getModultyps() as $modultyp) {
                if ($c == $modultyp['id']) {
                    continue 2;
                }
            }
            $this->fields['id']->addOption(cjoI18N::translate("label_id").' '.$c,$c);
            $i++;
            if ($c == 100) break;
        }
    
        foreach(self::getModultyps() as $key=>$value) {
            if (strpos($value['module_id'],'_') === false)
                $used_modultyps[] = $value['type_id'];
        }
        $this->fields['id']->addValidator('notEmpty', cjoI18N::translate("msg_id_notEmpty"));
        
        $this->addUpdateFields();
        $this->AddSection(TBL_MODULES, '', array ('id' => ''));
    }

    protected function setEdit() {
        
        $this->fields['name'] = new textField('name', cjoI18N::translate("label_name"));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_name_notEmpty"));
        
        $this->fields['templates'] = new selectField('templates', cjoI18N::translate("label_template_connection"));
        $this->fields['templates']->setMultiple();
        $this->fields['templates']->setValueSeparator('|');
        $this->fields['templates']->addOption(cjoI18N::translate("label_rights_all").' '.cjoI18N::translate("title_templates"),0);
        $this->fields['templates']->addSqlOptions("SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_TEMPLATES." ORDER BY prior");
        $this->fields['templates']->addAttribute('size', count($this->fields['templates']->values)+1);
        
        if (cjoProp::countCtypes() > 0) {
            $this->fields['ctypes'] = new selectField('ctypes', cjoI18N::translate("label_ctype_connection"));
            $this->fields['ctypes']->setMultiple();
            $this->fields['ctypes']->setValueSeparator('|');
        
            foreach(cjoProp::get('CTYPE') as $key=>$val) {
                $this->fields['ctypes']->addOption($val,$key);
            }
            $this->fields['ctypes']->addAttribute('size', cjoProp::countCtypes()+1);
        
        } else {
            $this->fields['ctypes'] = new hiddenField('ctypes');
            $this->fields['ctypes']->setValue('0');
        }
        $this->addUpdateFields();
        $this->AddSection(TBL_MODULES, '', array ('id' => $this->oid));
        
    }
    protected function getDefault() {}
    
    public static function onFormSaveorUpdate($params) {
        
        $oid   = cjo_get('oid','int');
        
        if (self::isAddMode()) {
            $oid = cjo_post('id','int');
            cjoAssistance::updatePrio(TBL_MODULES,$oid,time());
            self::setSaveExtention(array ("moduletyp_id" => $oid));
        }
        else {
            self::setSaveExtention(array ("moduletyp_id" => $oid, 'ACTION' => 'LOGIC_UPDATED'));
        }
        cjoGenerate::generateTemplates($oid);
    }
}
