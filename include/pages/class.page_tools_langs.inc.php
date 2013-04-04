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

class cjoPageToolsLangs extends cjoPage {
    
    protected $list_delete = 'cjoGenerate::deleteCLang';
        
    protected function setAdd() {
                
        $this->oid = '';
        
        $this->form->setEditMode(false);
        
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_CLANGS;
        $clangs = $sql->getArray($qry);

        $this->fields['new_id'] = new selectField('new_id', cjoI18N::translate("label_id"));
        $this->fields['new_id']->addAttribute('size', '1');
        $this->fields['new_id']->activateSave(false);
        
        for ($c=0; $c <= 10; $c++) {
            foreach ($clangs as $clang) {
                if ($c == $clang['id']) continue 2;
            }
            $this->fields['new_id']->addOption($c,$c);
        }
    
        $this->fields['name'] = new textField('name', cjoI18N::translate("label_name"));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_clang_name_notEmpty"), false, false);
        $this->fields['name']->activateSave(false);
    
        $this->fields['iso'] = new selectField('iso', cjoI18N::translate("label_iso"));
        $this->fields['iso']->addAttribute('size', '1');
        $this->fields['iso']->addValidator('notEmpty', cjoI18N::translate("msg_iso_not"), false, false);
        $this->fields['iso']->addValidator('isLength', cjoI18N::translate("msg_iso_not"), array('min' => 2, 'max' => 2), false, false);
        $this->fields['iso']->addOption(cjoI18N::translate('please_choose'),0);
        $this->fields['iso']->activateSave(false); 
        
        foreach (cjo_get_country_codes() as $code => $name) {
            $this->fields['iso']->addOption($name,strtolower($code));
        }
        
        $this->AddSection(TBL_CLANGS, cjoI18N::translate("label_add_clang"), array ('id' => '-1'));        
    }    
        
    protected function setEdit() {
        
        $this->form->setEditMode(true);
        
        $this->fields['name'] = new textField('name', cjoI18N::translate("label_name"));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_clang_name_notEmpty"), false, false);
        $this->fields['name']->activateSave(false);
    
        $this->fields['iso'] = new selectField('iso', cjoI18N::translate("label_iso"));
        $this->fields['iso']->addAttribute('size', '1');
        $this->fields['iso']->addValidator('notEmpty', cjoI18N::translate("msg_iso_not"), false, false);
        $this->fields['iso']->addValidator('isLength', cjoI18N::translate("msg_iso_not"), array('min' => 2, 'max' => 2), false);
        $this->fields['iso']->addOption(cjoI18N::translate('please_choose'),0);
        $this->fields['iso']->activateSave(false);
    
        foreach (cjo_get_country_codes() as $code => $name) {
            $this->fields['iso']->addOption($name,strtolower($code));
        }
        
        if (cjoProp::countClangs() > 1) {
                
            $this->fields['headline1'] = new headlineField(cjoI18N::translate("label_sync_clang"));
            
            $this->fields['sync_clang'] = new checkboxField('sync_clang', '&nbsp;',  array('style' => 'width: auto;'),'sync_clang');
            $this->fields['sync_clang']->addBox(cjoI18N::translate("label_sync_clang_enable"), '1');  
            $this->fields['sync_clang']->activateSave(false);
            
            $this->fields['sync_master'] = new selectField('sync_master', cjoI18N::translate("label_sync_master"));
            $this->fields['sync_master']->addAttribute('size', '1');
            $this->fields['sync_master']->addAttribute('style', 'width:150px');   
            $this->fields['sync_master']->addAttribute('disabled', 'disabled');                
            $this->fields['sync_master']->activateSave(false);
        
            foreach (cjoProp::getClangs() as $clang_id) {
                if ($oid == $clang_id) continue;
                $this->fields['sync_master']->addOption(cjoProp::getClangName($clang_id),cjoProp::getClang($clang_id));
            }
            
            $this->fields['prior'] = new checkboxField('prior', cjoI18N::translate("label_update_sync"),  array('style' => 'width: auto;'));
            $this->fields['prior']->addBox(cjoI18N::translate("label_update_prior"), '1');  
            $this->fields['prior']->addAttribute('disabled', 'disabled');                
            $this->fields['prior']->activateSave(false);
            
            if (cjoProp::get('TEASER_ENABLED')) {
                $this->fields['teaser'] = new checkboxField('teaser', '&nbsp;',  array('style' => 'width: auto;'));
                $this->fields['teaser']->addBox(cjoI18N::translate("label_update_teaser"), '1');  
                $this->fields['teaser']->addAttribute('disabled', 'disabled'); 
                $this->fields['teaser']->disableBorderTop();                
                $this->fields['teaser']->activateSave(false);
            }
            
            $this->fields['navi_item'] = new checkboxField('navi_item', '&nbsp;',  array('style' => 'width: auto;'));
            $this->fields['navi_item']->addBox(cjoI18N::translate("label_update_navi_item"), '1');  
            $this->fields['navi_item']->addAttribute('disabled', 'disabled');    
            $this->fields['navi_item']->disableBorderTop();               
            $this->fields['navi_item']->activateSave(false);
              
            $this->fields['status'] = new checkboxField('status', '&nbsp;',  array('style' => 'width: auto;'));
            $this->fields['status']->addBox(cjoI18N::translate("label_update_status"), '1');  
            $this->fields['status']->addAttribute('disabled', 'disabled');  
            $this->fields['status']->disableBorderTop();                
            $this->fields['status']->activateSave(false);
                    
            if (cjoProp::get('ONLINE_FROM_TO_ENABLED')) {        
                $this->fields['online_from_to'] = new checkboxField('online_from_to', '&nbsp;',  array('style' => 'width: auto;'));
                $this->fields['online_from_to']->addBox(cjoI18N::translate("label_update_online_from_to"), '1'); 
                $this->fields['online_from_to']->addAttribute('disabled', 'disabled');
                $this->fields['online_from_to']->disableBorderTop();          
                $this->fields['online_from_to']->activateSave(false);       
            }
            if (cjoProp::get('LOGIN_ENABLED')) {
                $this->fields['type_id'] = new checkboxField('type_id', '&nbsp;',  array('style' => 'width: auto;'));
                $this->fields['type_id']->addBox(cjoI18N::translate("label_update_type_id"), '1'); 
                $this->fields['type_id']->addAttribute('disabled', 'disabled');  
                $this->fields['type_id']->disableBorderTop();         
                $this->fields['type_id']->activateSave(false);      
            }
            if (cjoAddon::isActivated('comments')) {
                $this->fields['comments'] = new checkboxField('comments', '&nbsp;',  array('style' => 'width: auto;'));
                $this->fields['comments']->addBox(cjoI18N::translate("label_update_comments"), '1'); 
                $this->fields['comments']->addAttribute('disabled', 'disabled');
                $this->fields['comments']->disableBorderTop();        
                $this->fields['comments']->activateSave(false);       
            }
            
            $this->fields['cat_group'] = new checkboxField('cat_group', '&nbsp;',  array('style' => 'width: auto;'));
            $this->fields['cat_group']->addBox(cjoI18N::translate("label_update_cat_group"), '1');  
            $this->fields['cat_group']->addAttribute('disabled', 'disabled'); 
            $this->fields['cat_group']->disableBorderTop();        
            $this->fields['cat_group']->activateSave(false);      
                    
            $this->fields['redirect'] = new checkboxField('redirect', '&nbsp;',  array('style' => 'width: auto;'));
            $this->fields['redirect']->addBox(cjoI18N::translate("label_update_redirect"), '1'); 
            $this->fields['redirect']->addAttribute('disabled', 'disabled'); 
            $this->fields['redirect']->disableBorderTop();        
            $this->fields['redirect']->activateSave(false);      
                    
            $this->fields['admin_only'] = new checkboxField('admin_only', '&nbsp;',  array('style' => 'width: auto;'));
            $this->fields['admin_only']->addBox(cjoI18N::translate("label_update_admin_only"), '1'); 
            $this->fields['admin_only']->addAttribute('disabled', 'disabled'); 
            $this->fields['admin_only']->disableBorderTop();     
            $this->fields['admin_only']->activateSave(false); 
            
            $this->fields['check_all'] = new checkboxField('check_all', '&nbsp;',  array('style' => 'width: auto;'),'check_all');
            $this->fields['check_all']->addBox('<b>'.cjoI18N::translate('label_select_deselect_all').'</b>', '1'); 
            $this->fields['check_all']->addAttribute('disabled', 'disabled'); 
            $this->fields['check_all']->activateSave(false);    
        }
            
        $this->AddSection(TBL_CLANGS, cjoI18N::translate("label_edit_clang"), array ('id' => $this->oid));
        
        ?>
        <script type="text/javascript">
        /* <![CDATA[ */
        $(function() {
        
            var sync_clang = $('input[id^="sync_clang"]');
            var check_all = $('input[id^="check_all"]');
            var sync_elements  = sync_clang.parentsUntil('.a22-col1').parent().nextAll().not(':last');
        
            if (!sync_clang.is(':checked')) {
                sync_elements.hide(); 
            }
            else {
                sync_elements.find('select, input[type="checkbox"]').removeAttr('disabled');
            }
                
            sync_clang
                .click(function(){
                    var $this = $(this);
                    var parent = $this.parentsUntil('.a22-col1').parent();
                    var next = parent.nextAll().not(':last');
                    if ($(this).is(':checked')) {
                        sync_elements.find('select, input[type="checkbox"]').removeAttr('disabled');
                        sync_elements.slideDown();
                    } else {
                        sync_elements.find('select, input[type="checkbox"]').attr('disabled','disabled');
                        sync_elements.slideUp();
                    }
                });  
        
            check_all
                .click(function(){
                   var $this = $(this);
                   var parent = $this.parentsUntil('.a22-col1').parent();
                   var prev = parent.prevAll('.a22-col1').find('input[type="checkbox"]:not(input[id^="sync_clang"])');
                   if ($(this).is(':checked')) {
                       prev.attr('checked','checked');
                   } else {
                       prev.removeAttr('checked');
                   }
               });             
        }); 
        /* ]]> */
        </script>
        <?php
    }

    protected function getDefault() {
        
        $this->list = new cjolist("SELECT * FROM ".TBL_CLANGS, "id", "asc", "", 100);
        
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/lang.png" alt="" />',
                                         cjoUrl::createBELink(
                                                    '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                    array('function' => 'add', 'oid' => ''),
                                                    $this->list->getGlobalParams(),
                                                    'title="'.cjoI18N::translate("button_add").'"'));
        
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
        
        $this->cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
        $this->cols['id']->setHeadAttributes('class="icon"');
        $this->cols['id']->setBodyAttributes('class="icon"');
        $this->cols['id']->delOption(OPT_SORT);
        
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_name").' ');
        $this->cols['name']->delOption(OPT_SORT);
        
        $this->cols['iso'] = new resultColumn('iso', cjoI18N::translate("label_iso").' ','sprintf', '<img src="img/flags/%s.png" alt="" />');
        $this->cols['iso']->delOption(OPT_SORT);

        $this->cols['edit'] = new editColumn();
        
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams());
        
        $this->list->addColumns($this->cols);
        $this->list->show(false);
    
    }

    public static function onFormSaveorUpdate($params) {

        $oid   = cjo_get('oid','int');

        $posted           = array();
        $posted['name']   = trim(cjo_post('name', 'string'));
        $posted['iso']    = trim(cjo_post('iso', 'string'));
        $posted['new_id'] = cjo_post('new_id', 'int');
        $posted['oid']    = cjo_post('oid', 'int', $oid);        

        if (self::isAddMode()) {
           cjoGenerate::addCLang($posted['new_id'],$posted['name'],$posted['iso']);    
        }
        else {
           cjoGenerate::editCLang($posted['oid'],$posted['name'],$posted['iso']);
            
            if (cjo_post('sync_clang', 'bool')) {
                
                $posted['sync_master'] = cjo_post('sync_master', 'int');
                $params = array('prior'             => cjo_post('prior', 'bool'),
                                'teaser'            => cjo_post('teaser', 'bool'),
                                'navi_item'         => cjo_post('navi_item', 'bool'),
                                'status'            => cjo_post('status', 'bool'),
                                'online_from_to'    => cjo_post('online_from_to', 'bool'),
                                'type_id'           => cjo_post('type_id', 'bool'),
                                'comments'          => cjo_post('comments', 'bool'),
                                'cat_group'         => cjo_post('cat_group', 'bool'),
                                'redirect'          => cjo_post('redirect', 'bool'),
                                'admin_only'        => cjo_post('admin_only', 'bool'));    
                
                cjoGenerate::syncCLang($posted['oid'],$posted['sync_master'],$params);                
            }
        }
    }
}
