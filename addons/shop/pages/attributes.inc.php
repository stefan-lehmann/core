<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.7.x
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
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

// declare or define required vars
global $CJO;
$addon = 'shop';

//// update product status if requested
if ($function == 'delete' && $oid != '') {
    cjoShopProductAttributes::removeAttribute($oid);
    unset($function);
}

if ($function == 'add' || $function == 'edit') {

    $dataset = array();

    $sql = new cjoSql();
    $qry = "SELECT
			t.clang AS clang,
			t.name AS name
		FROM ".TBL_21_ATTRIBUTES." n
		LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
		ON n.translate_id = t.translate_id
		WHERE n.id = '".$oid."'";
    $temp = $sql->getArray($qry);

    foreach($temp as $values) {
        $dataset['attribute_'.$values['clang']] = $values['name'];
    }

    $qry = "SELECT
    			t.*,
    			v.id AS value_id,
    			v.prior AS prior,
    			v.offset AS offset
			FROM ".TBL_21_ATTRIBUTE_VALUES." v
			LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
			ON v.translate_id = t.translate_id AND
			   t.clang = '".$clang."'
			WHERE v.attribute_id = '".$oid."' AND
			      v.status = '1'
			ORDER BY v.prior, t.name ASC";
    $sql->flush();
    $temp = $sql->getArray($qry);
    $temp[] = array();

    $count_values = $sql->getRows();

        foreach($temp as $i=>$data) {
        $key = (empty($data['id'])) ? 'new' : $i;
        $dataset['attribute_value_id_'.$key] = $data['value_id'];
        $dataset['attribute_value_prior_'.$key] = $i+1;
        $dataset['attribute_value_'.$key] = $data['name'];
        $dataset['attribute_offset_'.$key] = cjoShopPrice::toCurrency($data['offset'], true, true);
    }

    $form = new cjoForm();
	$form->setEditMode(true);
    $form->debug = false;

    foreach($CJO['CLANG'] as $id => $name) {

        $flag = '<img src="img/flags/'.$CJO['CLANG_ISO'][$id].'.png" alt="'.$name.'" />';

        $fields['attribute_'.$name] = new textField('attribute_'.$id, $flag.' '.cjoAddon::translate(21,'shop_attribute'));
        $fields['attribute_'.$name]->needFullColumn(true);
        $fields['attribute_'.$name]->activateSave(false);

    	if ($id != cjoProp::getClang()){
    		$fields['attribute_'.$name]->addAttribute('readonly', 'readonly');
    		if($function == 'add') unset($fields['attribute_'.$name]);
    	}
    	else {
            $fields['attribute_'.$name]->addValidator('notEmpty', cjoI18N::translate("label_attribute", $name), false, false);
    	}
    }

    //headline for attribute values
    $fields['headline1'] = new headlineField(cjoAddon::translate(21,'label_attribute_values'));

    $currency_sign = $CJO['ADDON']['settings'][$addon]['CURRENCY']['DEFAULT_SIGN'];
    
    foreach($temp as $i=>$data) {

        $key = (empty($data['id'])) ? 'new' : $i;
        $name = ($i == 0) ? cjoAddon::translate(21,'label_attribute_value') : '&nbsp;';
        $note = '';
        
        if (!empty($data['id'])) {
    		$note = '<input name="remove_attribute_value" class="cjo_confirm" value="'.($key+1).'"
    						src="img/silk_icons/cross.png" type="image"
    						alt="'.cjoI18N::translate("button_delete").'"
    						title="'.cjoI18N::translate("button_delete").'" />';
        }
        
        $fields['attribute_value_id_'.$key] = new hiddenField('attribute_value_id_'.$key);
        
        $fields['attribute_value_prior_'.$key] = new textField('attribute_value_prior_'.$key, $name, array('style' => 'margin-bottom:-3px'));
	    $fields['attribute_value_prior_'.$key]->addAttribute('style', 'width: 16px; text-align: center; color: #777');
	    $fields['attribute_value_prior_'.$key]->addAttribute('maxlength', '2');
	    $fields['attribute_value_prior_'.$key]->activateSave(false);

        $fields['attribute_value_'.$key] = new textField('attribute_value_'.$key, '');
	    $fields['attribute_value_'.$key]->addAttribute('style', 'width: 243px;');
	    $fields['attribute_value_'.$key]->activateSave(false);

        $fields['attribute_offset_'.$key] = new textField('attribute_offset_'.$key, '');
	    $fields['attribute_offset_'.$key]->addAttribute('style', 'width: 49px; text-align: right');
	    $fields['attribute_offset_'.$key]->setNote($currency_sign.' &nbsp;&nbsp; '.$note);
	    $fields['attribute_offset_'.$key]->activateSave(false);

	    $fields['attribute_offset_'.$key]->addValidator('isPrice', cjoAddon::translate(21,"msg_attribute_offset_no_price"), true, false);
    }
    
    //Add Fields
    $section = new cjoFormSection('TBL_21_ATTRIBUTES', cjoAddon::translate(21,'label_attribute_setup'), array(), array('25%', '28%', '47%'));
    $section->dataset = $dataset;

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);

    if ($form->validate()) {

        $posted['attribute_id'] = cjo_post('oid', 'int');
        $posted['attribute']    = cjo_post('attribute_'.$clang, 'string');

        if ($function == 'add') {
            $posted['attribute_id'] = cjoShopProductAttributes::addAttribute($posted['attribute']);
            $oid = $posted['attribute_id'];
        }
        else {

            if (cjo_post('remove_attribute_value', 'boolean')) {

                $i = cjo_post('remove_attribute_value', 'int')-1;

                $posted['attribute_value_id_'.$i]       = cjo_post('attribute_value_id_'.$i, 'int');
                $posted['attribute_value_prior_'.$i]    = cjo_post('attribute_value_prior_'.$i, 'int');
                $posted['attribute_value_'.$i]          = '';
                $posted['attribute_offset_'.$i]         = cjo_post('attribute_offset_'.$i, 'string');

                cjoShopProductAttributes::updateAttributeValue($posted['attribute_value_'.$i],
                                                               $posted['attribute_id'],
                                                               $posted['attribute_offset_'.$i],
                                                               $posted['attribute_value_prior_'.$i],
                                                               $posted['attribute_value_id_'.$i],
                                                               cjoProp::getClang());
            }
            else {

                if (!empty($posted['attribute'])) {
                    cjoShopProductAttributes::updateAttribute($posted['attribute'], $posted['attribute_id'], cjoProp::getClang());
                }

                if (!cjoMessage::hasErrors()) {

                    for($i = 0; $i < $count_values; $i++) {

                        $posted['attribute_value_id_'.$i]       = cjo_post('attribute_value_id_'.$i, 'int');
                        $posted['attribute_value_prior_'.$i]    = cjo_post('attribute_value_prior_'.$i, 'int');
                        $posted['attribute_value_'.$i]          = cjo_post('attribute_value_'.$i, 'string');
                        $posted['attribute_offset_'.$i]         = cjo_post('attribute_offset_'.$i, 'string');

                        cjoShopProductAttributes::updateAttributeValue($posted['attribute_value_'.$i],
                                                                       $posted['attribute_id'],
                                                                       $posted['attribute_offset_'.$i],
                                                                       $posted['attribute_value_prior_'.$i],
                                                                       $posted['attribute_value_id_'.$i],
                                                                       cjoProp::getClang());
                        if (cjoMessage::hasErrors()) break;
                    }
                }
            }
        }

        if (!cjoMessage::hasErrors() &&
            $posted['attribute_id'] &&
            cjo_post('attribute_value_new', 'bool')) {

            $posted['attribute_value_prior_new'] = cjo_post('attribute_value_prior_new', 'int');
            $posted['attribute_value_new']       = cjo_post('attribute_value_new', 'string');
            $posted['attribute_offset_new']      = cjo_post('attribute_offset_new', 'string');

            if (!$posted['attribute_offset_new']) $posted['attribute_offset_new'] = '0.00';

            cjoShopProductAttributes::addAttributeValue($posted['attribute_value_new'],
                                                        $posted['attribute_id'],
                                                        $posted['attribute_offset_new'],
                                                        $posted['attribute_value_prior_new']);
        }

        if (!cjoMessage::hasErrors()) {

        	if (cjo_post('cjoform_save_button', 'boolean')) {
                cjoUrl::redirectBE(array('function' => '', 'oid' => '', 'msg' => 'msg_data_saved'));
        	}
        	elseif (cjo_post('remove_attribute_value', 'boolean') ||
        	        cjo_post('cjoform_update_button', 'boolean')) {
        		cjoUrl::redirectBE(array('function' => 'edit', 'oid'=>$oid, 'msg' => 'msg_data_saved'));
        	}
        }
    }

    $form->show();
}

if ($function == '') {

    $sql = new cjoSql();
    $sql->setQuery("SET group_concat_max_len = 100000");

    $qry = "SELECT
    			n.id AS id,
    			t.name AS attribute,
    			(SELECT GROUP_CONCAT(
    					IF(v.offset <> 0, CONCAT('<span class=\"shop_attributes_list\">',
    											 t.name,
    						   					 ' <span style=\"float: right\">',
    						   					 REPLACE(v.offset, '.', '".$CJO['ADDON']['settings'][$addon]['CURRENCY']['DEFAULT_SEPARATOR']."') ,
    						   					 ' ".$CJO['ADDON']['settings'][$addon]['CURRENCY']['DEFAULT_SIGN']."',
    						   				     '</span>',
    						   				     '</span>'),
    						   			  CONCAT('<span class=\"shop_attributes_list\">', t.name, '</span>'))
    						   ORDER BY v.prior ASC
    						   SEPARATOR ' ')
    				FROM ".TBL_21_ATTRIBUTE_VALUES." v
    				LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
    				ON v.translate_id = t.translate_id AND
    				   t.clang = '".$clang."'
    				WHERE v.attribute_id = n.id AND
			      		  v.status = '1'
    				GROUP BY v.attribute_id
    				ORDER BY v.prior asc
    				) AS attribute_values
    		FROM ".TBL_21_ATTRIBUTES." n
    		LEFT JOIN ".TBL_21_ATTRIBUTE_TRANSLATE." t
    		ON n.translate_id = t.translate_id
    		WHERE t.clang = '".$clang."' AND
    		   	  n.status = '1'";

    // define list
    $list = new cjoList($qry, 't.name', 'ASC', 't.name', 40);
    $list->addGlobalParams(cjoUrl::getDefaultGlobalParams());
    $list->debug = false;
    $cols['id'] = new resultColumn('id',
                                   cjoUrl::createBELink('<img src="img/silk_icons/add.png" alt="'.cjoAddon::translate(21,"shop_add_attribute").'" />',
    							 		                       array('function' => 'add'), $list->getGlobalParams(),
                        									 	     'title="'.cjoAddon::translate(21,"shop_add_attribute").'"'));

    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon"');
    $cols['id']->delOption(OPT_ALL);

    $cols['attribute'] = new resultColumn('attribute', cjoAddon::translate(21,'shop_attributes'));
    $cols['attribute']->setBodyAttributes('class="large_item" style="width:35%"');
    $cols['attribute']->delOption(OPT_SEARCH);


    $cols['attribute_values'] = new resultColumn('attribute_values', cjoAddon::translate(21,'label_attribute_values'));
    $cols['attribute_values']->addCondition('attribute_values','', '--');
    $cols['attribute_values']->delOption(OPT_SEARCH);

    // update link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").
    	   '" alt="'.cjoI18N::translate("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setHeadAttributes('colspan="2"');
    $cols['edit']->setParams(array('function' => 'edit', 'oid' => '%id%'));

    // delete link
    $img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array('function'=> 'delete', 'oid' => '%id%'));
	// add columns to list
	$list->addColumns($cols);

	$list->addToolbar(new browseBar(), 'top', 'half');
	$list->addToolbar(new statusBar(), 'bottom', 'half');
	$list->addToolbar(new maxElementsBar(), 'bottom', 'half');

	// show table
	$list->show(false);
}
