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

$sql = new cjoSql();
$qry = "SELECT * FROM ".TBL_ARTICLES_CAT_GROUPS;
$cat_groups = $sql->getArray($qry);

if ($function == "delete") {

    if ($oid == '1') {
        cjoMessage::addError(cjoI18N::translate("msg_catgroup_1_not_deleted"));
    }
    else {
		$sql = new cjoSql();
		$qry = "SELECT name, id
				FROM ".TBL_ARTICLES."
				WHERE re_id=0 AND cat_group='".$oid."'";

		$results = $sql->getArray($qry);

        $temp = array();
        foreach ($results as $result) {
            $temp[] = cjoUrl::createBELink(
			                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
                                        array('page' => 'edit',
                                        	  'subpage' => 'settings',
                                        	  'function' => '',
                                        	  'oid' => '',
                                        	  'article_id' => $result['id'],
                                        	  'clang' => $result['clang']));
		}

        if (!empty($temp))
            cjoMessage::addError(cjoI18N::translate("msg_catgroup_still_used").'<br/>'.implode(' | ',$temp));

        if (!cjoMessage::hasErrors()) {
            $sql->flush();
            $sql->statusQuery("DELETE FROM ".TBL_ARTICLES_CAT_GROUPS." WHERE group_id = '".$oid."' LIMIT 1",
                              cjoI18N::translate("msg_catgroup_deleted"));
            $sql->statusQuery("UPDATE ".TBL_ARTICLES." SET cat_group = 1 WHERE cat_group = '".$oid."'",
                              cjoI18N::translate("msg_catgroup_deleted"));

            foreach($cat_groups as $group) {
                if ($oid == $group['group_id']) {
                    $deleted = $group;
                    break;
                }
            }                  
            cjoExtension::registerExtensionPoint('CATGROUP_DELETED', $deleted);                              
        }
    }
}

if ($function == "add" || $function == "edit" ) {
    
    //Form
    $form = new cjoForm();
    $form->setEditMode($oid);
    $form->debug = false;

    //Fields
    if ($function == 'add') {

        $oid = '';
        $fields['group_id'] = new selectField('group_id', cjoI18N::translate("label_id"));
        $fields['group_id']->addAttribute('size', '1');
        for($c=1; $c <= 10; $c++) {
            foreach($cat_groups as $cat_group) {
                if ($c == $cat_group['group_id']) continue 2;
            }
            $fields['group_id']->addOption($c,$c);
        }
    }

    $fields['group_name'] = new textField('group_name', cjoI18N::translate("label_catgroup_name"));
    $fields['group_name']->addValidator('notEmpty', cjoI18N::translate("msg_cat_group_name_notEmpty"), false, false);

    $fields['group_structure'] = new textField('group_structure', cjoI18N::translate("label_catgroup_structure"));
    $fields['group_structure']->addValidator('notEmpty', cjoI18N::translate("msg_catgroup_structure_notEmpty"), false, false);

    $fields['group_style'] = new colorpickerField('group_style', cjoI18N::translate("label_catgroup_style"));
    $fields['group_style']->addValidator('notEmpty', cjoI18N::translate("msg_catgroup_style_notEmpty"), false, false);

    /**
     * Do not delete translate values for cjoI18N collection!
     * [translate: label_add_catgroup]
     * [translate: label_edit_catgroup]
     */
    $section = new cjoFormSection(TBL_ARTICLES_CAT_GROUPS, cjoI18N::translate("label_".$function."_catgroup"), array ('group_id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();    
    
    if ($form->validate()) {
        
        $posted = array();
        $posted['group_id'] = cjo_post('group_id','string');        
        $posted['group_name'] = cjo_post('group_name','string');
        $posted['group_structure'] = cjo_post('group_structure','string');
        $posted['group_style'] = cjo_post('group_style','string');
        
        if ($function == 'add') {
            cjoExtension::registerExtensionPoint('CATGROUP_ADDED', $posted); 
        }
        else {
            cjoExtension::registerExtensionPoint('CATGROUP_UPDATED', $posted); 
        }

        if (cjo_post('cjoform_save_button', 'boolean')) {
            if ($function == 'edit') {
                cjoMessage::addSuccess(cjoI18N::translate("msg_catgroup_updated"));
            }
            else {
                cjoMessage::addSuccess(cjoI18N::translate("msg_catgroup_saved"));
            }
        }
    }
}


//LIST Ausgabe
$list = new cjolist("SELECT * FROM ".TBL_ARTICLES_CAT_GROUPS,
                    "group_id",
                    '', 100);

$cols['icon'] = new staticColumn('<img src="img/silk_icons/flag_blue.png" alt="" />',
                                cjoUrl::createBELink(
                                		'<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                        $list->getGlobalParams(),
                                        array('function' => 'add', 'oid' => ''),
                                        'title="'.cjoI18N::translate("button_add").'"'));

$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['group_id'] = new resultColumn('group_id', cjoI18N::translate("label_id"));
$cols['group_id']->setHeadAttributes('class="icon"');
$cols['group_id']->setBodyAttributes('class="icon"');
$cols['group_id']->delOption(OPT_SORT);

$cols['group_name'] = new resultColumn('group_name', cjoI18N::translate("label_catgroup_name").' ');
$cols['group_name']->delOption(OPT_SORT);

$cols['group_structure'] = new resultColumn('group_structure', cjoI18N::translate("label_catgroup_structure").' ');
$cols['group_structure']->delOption(OPT_SORT);

$cols['group_style'] = new resultColumn('group_style', cjoI18N::translate("label_catgroup_style").' ', 'sprintf', '<span style="border: 1px solid #666; background-color: %1$s; display: block; width: 2em; height: 1em;" title="%1$s"></span>');
$cols['group_style']->delOption(OPT_SORT);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
$cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['edit']->setHeadAttributes('colspan="2"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%group_id%'));

$img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%group_id%'));

//Spalten zur Anzeige hinzuf?gen
$list->addColumns($cols);

//Tabelle anzeigen
$list->show(false);