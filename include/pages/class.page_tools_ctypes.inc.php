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

class cjoPageToolsCtypes extends cjoPage {
    
    protected $default_buttons = false; 
    
    protected function setEdit() {
        
        foreach(cjoProp::get('CTYPE') as $key => $val){
        
        	$this->fields['ctype_'.$key] = new textField($key, cjoI18N::translate("label_ctype").' (ID='.$key.')');
        	$this->fields['ctype_'.$key]->addValidator('notEmpty', cjoI18N::translate("msg_ctype_name_notEmpty"), false, false);
        
        	if ($key > 0)
        		$this->fields['ctype_'.$key]->setNote('<input name="delete_ctype" class="cjo_confirm" value="'.$key.'"
        												src="img/silk_icons/cross.png" type="image"
        												alt="'.cjoI18N::translate("button_delete").'"
        												title="'.cjoI18N::translate("button_delete").'" />');
        }
        ksort($this->fields);
        
        $add_key = $key+1;
        
        $this->fields['ctype_add'] = new textField($add_key, cjoI18N::translate("label_add_ctype"));
        
        $this->fields['buttons'] = new buttonField();
        $this->fields['buttons']->addButton('cjoform_update_button',cjoI18N::translate("button_update"), true, 'img/silk_icons/tick.png');

        $this->AddSection(cjoProp::get('CTYPE'), cjoI18N::translate("label_ctype"));
        $this->form->applyRedirectOnUpdate();
    }
    
    public static function onFormIsValid($params) {

        ksort($_POST,SORT_STRING);  
        
        $temp = array();  
        $deleted = cjo_post('delete_ctype', 'int');
        if (empty($deleted)) $deleted = -1;

        foreach($_POST as $key=>$val){
            if (!is_numeric($key) || empty($val) || $deleted == $key) continue;
            $temp[$key] = $val;
        }

        if (!isset($temp[0]) || empty($temp[0]))
            $temp[0] = 'main';

        ksort($temp);

        cjoProp::set('CTYPE',$temp);

        if (cjoProp::saveToFile(cjoPath::pageConfig('ctypes'))) {
            cjoExtension::registerExtensionPoint('CTYPES_UPDATED', array('deleted' => $deleted));  
            cjoUrl::redirectBE( array('msg' => 'msg_data_saved'));
        }
       
    }
}