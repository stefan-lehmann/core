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

class cjoPageToolsModulesLogic extends cjoPageToolsModules {

    protected function setEdit() {

        $this->fields['input'] = new codeField('input', cjoI18N::translate("label_input"));
        $this->fields['output'] = new codeField('output', cjoI18N::translate("label_output"));
        $this->addUpdateFields();
        $this->AddSection(TBL_MODULES, '', array ('id' => $this->oid));
    }
    
    public static function onFormSaveorUpdate($params) {
                  
        $oid = cjo_get('oid','int'); 
             
        $liveEdit = new liveEdit();
        $liveEdit->syncModules();
            
        self::setSaveExtention(array('ACTION' => 'LOGIC_UPDATED', 'moduletyp_id' => $oid));
    }
}
