<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  multidomain
 * @version     2.7.x
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

// LÖSCHEN
if ($function == 'delete') {

	$delete = new cjoSql();
	$delete->setTable(TBL_MULTIDOMAIN);
	$delete->setWhere("id='".$oid."'");
	$delete->Delete($I18N_15->msg('msg_item_deleted'));
	$function = '';
}


if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode($oid);

    //Fields
    $fields['servername'] = new textField('servername', $I18N->msg("label_servername"));
    $fields['servername']->addValidator('notEmpty', $I18N_15->msg("msg_err_servername_notEmpty"), false, false);

    $fields['domain'] = new textField('domain', $I18N_15->msg("label_domain"));
    $fields['domain']->addValidator('notEmpty', $I18N_15->msg("msg_err_domain_notEmpty"), false, false);

    $fields['error_email'] = new textField('error_email', $I18N->msg("label_error_email"));
    $fields['error_email']->addValidator('notEmpty', $I18N_15->msg("msg_err_error_email_notEmpty"), false, false);
    $fields['error_email']->addValidator('isEmail', $I18N_15->msg("msg_err_error_email_notEmail"), false, false);

    $fields['root_article_id'] = new cjoLinkButtonField('root_article_id', $I18N_15->msg('label_root_article_id'));
    $fields['root_article_id']->addValidator('notEmptyOrNull', $I18N_15->msg("msg_err_root_article_id_notEmpty"), false, false);

    $fields['start_article_id'] = new cjoLinkButtonField('start_article_id', $I18N_15->msg('label_start_article_id'));
    $fields['start_article_id']->addValidator('notEmptyOrNull', $I18N_15->msg("msg_err_start_article_id_notEmpty"), false, false);

    $fields['notfound_article_id'] = new cjoLinkButtonField('notfound_article_id', $I18N->msg('label_notfound_article_id'));
    $fields['notfound_article_id']->addValidator('notEmptyOrNull', $I18N_15->msg("msg_err_notfound_article_id_notEmpty"), false, false);

    //[translate_15: label_add_multidomain]
    //[translate_15: label_edit_multidomain]
    $section = new cjoFormSection(TBL_MULTIDOMAIN, $I18N_15->msg("label_".$function."_multidomain"), array ('id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);

    if ($form->validate()){

        $domain              = cjo_post('domain','string');
        $servername          = cjo_post('servername','string');
        $root_article        = OOArticle::getArticleById(cjo_post('root_article_id','string'));
        $start_article_id    = OOArticle::getArticleById(cjo_post('start_article_id','string'));
        $notfound_article_id = OOArticle::getArticleById(cjo_post('notfound_article_id','string'));

        $sql = new cjoSql();
	    $sql->setQuery("SELECT id FROM ".TBL_MULTIDOMAIN." WHERE servername LIKE '".$servername."' AND id != '".$oid."'");
	    if ($sql->getRows() > 0) {
    		cjoMessage::addError($I18N_15->msg("msg_err_double_servername", $servername));
    		$fields['servername']->addAttribute('class', 'invalid');
    		$form->valid_master = false;
	    }

        $sql->flush();
	    $sql->setQuery("SELECT id FROM ".TBL_MULTIDOMAIN." WHERE domain LIKE '".$domain."' AND id != '".$oid."'");
	    if ($sql->getRows() > 0) {
    		cjoMessage::addError($I18N_15->msg("msg_err_double_domain", $domain));
    		$fields['domain']->addAttribute('class', 'invalid');
    		$form->valid_master = false;
	    }

        $sql->flush();
	    $sql->setQuery("SELECT id FROM ".TBL_MULTIDOMAIN." WHERE root_article_id='".$root_article->_id."' AND id != '".$oid."'");
	    if ($sql->getRows() > 0) {
    		cjoMessage::addError($I18N_15->msg("msg_err_double_root_article_id"));
    		$fields['root_article_id']->addAttribute('class', 'invalid');
    		$form->valid_master = false;
	    }

    	if (!OOArticle::isValid($root_article) ||
    	    !empty($root_article->_re_id)) {
    		cjoMessage::addError($I18N_15->msg("msg_err_root_article_id"));
    		$fields['root_article_id']->addAttribute('class', 'invalid');
    		$form->valid_master = false;
    	}

        if (!OOArticle::isValid($start_article_id) || (
            strpos($start_article_id->_path,'|'.$root_article->_id.'|') === false &&
            $start_article_id->_id != $root_article->_id)) {
    		cjoMessage::addError($I18N_15->msg("msg_err_start_article_id"));
    		$fields['start_article_id']->addAttribute('class', 'invalid');
    		$form->valid_master = false;
    	}

        if (!OOArticle::isValid($notfound_article_id) || (
            strpos($notfound_article_id->_path,'|'.$root_article->_id.'|') === false &&
            $notfound_article_id->_id != $root_article->_id)) {
    		cjoMessage::addError($I18N_15->msg("msg_err_notfound_article_id"));
    		$fields['notfound_article_id']->addAttribute('class', 'invalid');
    		$form->valid_master = false;
    	}

        if ($form->valid_master == true && cjo_post('cjoform_save_button','bool')) {
            if ($function == 'edit') {
                cjoMessage::addSuccess($I18N_15->msg("msg_item_updated"));
            }
            else {
                cjoMessage::addSuccess($I18N_15->msg("msg_item_saved"));
        	}
        }
    }
    $form->show();
}

$list = new cjolist("SELECT * FROM ".TBL_MULTIDOMAIN, 'domain', 'ASC', 100);

$cols['icon'] = new resultColumn('id',
                                 cjoAssistance::createBELink(
                                 			  '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
                                               array('function' => 'add', 'oid' => ''),
                                               $list->getGlobalParams(),
                                              'title="'.$I18N->msg("button_add").'"'));
$cols['id'] = new resultColumn('id', $add_button);
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->delOption(OPT_ALL);

$cols['domain'] = new resultColumn('domain', $I18N_15->msg('label_domain'));
$cols['domain']->setOptions(OPT_SORT);

$cols['servername'] = new resultColumn('servername', $I18N->msg('label_servername'));
$cols['servername']->setOptions(OPT_SORT);

$cols['error_email'] = new resultColumn('error_email', $I18N->msg('label_error_email'));
$cols['error_email']->setOptions(OPT_SORT);

$cols['root_article_id'] = new resultColumn('root_article_id', $I18N_15->msg('label_root_article_id'));
$cols['root_article_id']->setOptions(OPT_SORT);

$cols['start_article_id'] = new resultColumn('start_article_id', $I18N_15->msg('label_start_article_id'));
$cols['start_article_id']->setOptions(OPT_SORT);

$cols['notfound_article_id'] = new resultColumn('notfound_article_id', $I18N->msg('label_notfound_article_id'));
$cols['notfound_article_id']->setOptions(OPT_SORT);

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

$list->addColumns($cols);

$list->show();