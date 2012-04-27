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
 * @version     2.6.0
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

$sql = new cjoSql();
$qry = "SELECT * FROM ".TBL_CLANGS;
$clangs = $sql->getArray($qry);


if ($function == "delete"){

	if($oid == 0){
		cjoMessage::addError($I18N->msg("msg_clang_0_not_deleted"));
	}
	else {
	    if (cjoGenerate::deleteCLang($oid)){
	      unset($function);
	      unset($oid);
	    }
	    else {
	        cjoMessage::addError($I18N->msg("msg_clang_not_deleted"));
	    }
	}
}

if ($function == "add" || $function == "edit" ) {
    
    //Form
    $form = new cjoForm();
    $form->setEditMode(true);
    //$form->debug = true;

    //Fields
    if ($function == 'add') {

		$oid = '';
        $fields['new_id'] = new selectField('new_id', $I18N->msg("label_id"));
        $fields['new_id']->addAttribute('size', '1');
        $fields['new_id']->activateSave(false);

        for ($c=0; $c <= 10; $c++) {
            foreach ($clangs as $clang) {
                if ($c == $clang['id']) continue 2;
            }
            $fields['new_id']->addOption($c,$c);
        }
    }

    $fields['name'] = new textField('name', $I18N->msg("label_name"));
    $fields['name']->addValidator('notEmpty', $I18N->msg("msg_clang_name_notEmpty"), false, false);
    $fields['name']->activateSave(false);

    $fields['iso'] = new selectField('iso', $I18N->msg("label_iso"));
    $fields['iso']->addAttribute('size', '1');
    $fields['iso']->addValidator('isLength', $I18N->msg("msg_iso_not"), array('min' => 2, 'max' => 2), false);
    $fields['iso']->activateSave(false);

    foreach (cjo_get_country_codes() as $code => $name) {
        $fields['iso']->addOption($name,strtolower($code));
    }

    $oid = ($function == 'add') ? -1 : $oid;
    
    if ($function == 'add') {
        $oid = -1;
    }
    elseif (count($CJO['CLANG']) > 1) {
        
        
        $fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
        $fields['headline1']->setValue($I18N->msg("label_sync_clang"));
        $fields['headline1']->needFullColumn(true);
        
        $fields['sync_clang'] = new checkboxField('sync_clang', '&nbsp;',  array('style' => 'width: auto;'),'sync_clang');
        $fields['sync_clang']->addBox($I18N->msg("label_sync_clang_enable"), '1');  
        $fields['sync_clang']->activateSave(false);
        
        $fields['sync_master'] = new selectField('sync_master', $I18N->msg("label_sync_master"));
        $fields['sync_master']->addAttribute('size', '1');
        $fields['sync_master']->addAttribute('style', 'width:150px');   
        $fields['sync_master']->addAttribute('disabled', 'disabled');                
        $fields['sync_master']->activateSave(false);
    
        foreach ($CJO['CLANG'] as $clang_id => $clang_name) {
            if ($oid == $clang_id) continue;
            $fields['sync_master']->addOption($clang_name,$clang_id);
        }
        
        $fields['prior'] = new checkboxField('prior', $I18N->msg("label_update_sync"),  array('style' => 'width: auto;'));
        $fields['prior']->addBox($I18N->msg("label_update_prior"), '1');  
        $fields['prior']->addAttribute('disabled', 'disabled');                
        $fields['prior']->activateSave(false);
        
        if ($CJO['TEASER_ENABLED'] == true) {
            $fields['teaser'] = new checkboxField('teaser', '&nbsp;',  array('style' => 'width: auto;'));
            $fields['teaser']->addBox($I18N->msg("label_update_teaser"), '1');  
            $fields['teaser']->addAttribute('disabled', 'disabled'); 
            $fields['teaser']->disableBorderTop();                
            $fields['teaser']->activateSave(false);
        }
        
        $fields['navi_item'] = new checkboxField('navi_item', '&nbsp;',  array('style' => 'width: auto;'));
        $fields['navi_item']->addBox($I18N->msg("label_update_navi_item"), '1');  
        $fields['navi_item']->addAttribute('disabled', 'disabled');    
        $fields['navi_item']->disableBorderTop();               
        $fields['navi_item']->activateSave(false);
          
        $fields['status'] = new checkboxField('status', '&nbsp;',  array('style' => 'width: auto;'));
        $fields['status']->addBox($I18N->msg("label_update_status"), '1');  
        $fields['status']->addAttribute('disabled', 'disabled');  
        $fields['status']->disableBorderTop();                
        $fields['status']->activateSave(false);
                
        if ($CJO['ONLINE_FROM_TO_ENABLED'] == true) {        
            $fields['online_from_to'] = new checkboxField('online_from_to', '&nbsp;',  array('style' => 'width: auto;'));
            $fields['online_from_to']->addBox($I18N->msg("label_update_online_from_to"), '1'); 
            $fields['online_from_to']->addAttribute('disabled', 'disabled');
            $fields['online_from_to']->disableBorderTop();          
            $fields['online_from_to']->activateSave(false);       
        }
        if ($CJO['LOGIN_ENABLED'] == true) {
            $fields['type_id'] = new checkboxField('type_id', '&nbsp;',  array('style' => 'width: auto;'));
            $fields['type_id']->addBox($I18N->msg("label_update_type_id"), '1'); 
            $fields['type_id']->addAttribute('disabled', 'disabled');  
            $fields['type_id']->disableBorderTop();         
            $fields['type_id']->activateSave(false);      
        }
        if (OOAddon::isActivated('comments')) {
            $fields['comments'] = new checkboxField('comments', '&nbsp;',  array('style' => 'width: auto;'));
            $fields['comments']->addBox($I18N->msg("label_update_comments"), '1'); 
            $fields['comments']->addAttribute('disabled', 'disabled');
            $fields['comments']->disableBorderTop();        
            $fields['comments']->activateSave(false);       
        }
        
        $fields['cat_group'] = new checkboxField('cat_group', '&nbsp;',  array('style' => 'width: auto;'));
        $fields['cat_group']->addBox($I18N->msg("label_update_cat_group"), '1');  
        $fields['cat_group']->addAttribute('disabled', 'disabled'); 
        $fields['cat_group']->disableBorderTop();        
        $fields['cat_group']->activateSave(false);      
                
        $fields['redirect'] = new checkboxField('redirect', '&nbsp;',  array('style' => 'width: auto;'));
        $fields['redirect']->addBox($I18N->msg("label_update_redirect"), '1'); 
        $fields['redirect']->addAttribute('disabled', 'disabled'); 
        $fields['redirect']->disableBorderTop();        
        $fields['redirect']->activateSave(false);      
                
        $fields['admin_only'] = new checkboxField('admin_only', '&nbsp;',  array('style' => 'width: auto;'));
        $fields['admin_only']->addBox($I18N->msg("label_update_admin_only"), '1'); 
        $fields['admin_only']->addAttribute('disabled', 'disabled'); 
        $fields['admin_only']->disableBorderTop();     
        $fields['admin_only']->activateSave(false); 
        
        $fields['check_all'] = new checkboxField('check_all', '&nbsp;',  array('style' => 'width: auto;'),'check_all');
        $fields['check_all']->addBox('<b>'.$I18N->msg('label_select_deselect_all').'</b>', '1'); 
        $fields['check_all']->addAttribute('disabled', 'disabled'); 
        $fields['check_all']->activateSave(false);    
    }
    

    /**
     * Do not delete translate values for i18n collection!
     * [translate: label_add_clang]
     * [translate: label_edit_clang]
     */
    $section = new cjoFormSection(TBL_CLANGS, $I18N->msg("label_".$function."_clang"), array ('id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {

        $posted           = array();
        $posted['name']   = trim(cjo_post('name', 'string'));
        $posted['iso']    = trim(cjo_post('iso', 'string'));
        $posted['new_id'] = cjo_post('new_id', 'int');
        $posted['oid']    = cjo_post('oid', 'int');        

        if ($function == "add") {
            cjoGenerate::addCLang($posted['new_id'],$posted['name'],$posted['iso']);
        }
        elseif ($function == "edit") {
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
        
        if (cjo_post('cjoform_save_button', 'boolean')) {
            unset($function);
        }
    }
}

if (!$function) {
    //LIST Ausgabe
    $list = new cjolist("SELECT * FROM ".TBL_CLANGS, "id", "asc", "", 100);
    
    $cols['icon'] = new staticColumn('<img src="img/silk_icons/lang.png" alt="" />',
    	                             cjoAssistance::createBELink(
    	                             			'<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
    	                                        array('function' => 'add', 'oid' => ''),
    	                                        $list->getGlobalParams(),
    	                                        'title="'.$I18N->msg("button_add").'"'));
    
    $cols['icon']->setHeadAttributes('class="icon"');
    $cols['icon']->setBodyAttributes('class="icon"');
    $cols['icon']->delOption(OPT_SORT);
    
    $cols['id'] = new resultColumn('id', $I18N->msg("label_id"));
    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon"');
    $cols['id']->delOption(OPT_SORT);
    
    $cols['name'] = new resultColumn('name', $I18N->msg("label_name").' ');
    $cols['name']->delOption(OPT_SORT);
    
    $cols['iso'] = new resultColumn('iso', $I18N->msg("label_iso").' ','sprintf', '<img src="img/flags/%s.png" alt="" />');
    $cols['iso']->delOption(OPT_SORT);
    
    // Bearbeiten link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
    $cols['edit']->setHeadAttributes('colspan="2"');
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));
    
    $img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));
    
    //Spalten zur Anzeige hinzufügen
    $list->addColumns($cols);
    
    //Tabelle anzeigen
    $list->show(false);
}
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