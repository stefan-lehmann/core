<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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
    cjoCommentsConfig::deleteConfig($params);
    unset($function);
}

if ($function == "add"){
	$oid = '';
}
elseif ($function == "edit") {

	$sql = new cjoSql();
	$sql->setQuery("SELECT id FROM ".TBL_COMMENTS_CONFIG." WHERE id='".$oid."' AND clang='".$clang."'");
	if ($sql->getRows() != 1) unset($function); 
}


// HINZUFÜGEN
if ($function == "add" || $function == "edit") {
    
    //Form
    $form = new cjoForm();
    $form->setEditMode($oid);
    //$form->debug = true;

    // Kommentarfunktion an/aus
    $fields['comment_function_hidden'] = new hiddenField('comment_function');
    $fields['comment_function_hidden']->setValue('0');
    $fields['comment_function'] = new checkboxField('comment_function', '&nbsp;');
    $fields['comment_function']->addBox(cjoAddon::translate(7,'label_disable_comment_function'), '1');

    $fields['clang'] = new hiddenField('clang');
    $fields['clang']->setValue($clang);

    $fields['headline1'] = new headlineField(cjoAddon::translate(7,'label_comments_output')); // Kommentar-Ausgabe

    $fields['form_article_id'] = new cjoLinkButtonField('form_article_id', cjoAddon::translate(7,'label_form_article_name'));
    $fields['form_article_id']->addValidator('notEmpty', cjoAddon::translate(7,'miss_form_article_name'));

    if ($oid == '1') {
        $fields['reference_article_id'] = new readOnlyField('', cjoAddon::translate(7,'label_reference_article_name'));
        $fields['reference_article_id']->setValue('<span style="display: block; float: left;"><strong>'.cjoAddon::translate(7,'label_default_setting').'</strong> '.cjoAddon::translate(7,'label_default_setting_text').'</span>');

        $fields['reference_article_id_hidden'] = new hiddenField('reference_article_id');
        $fields['reference_article_id_hidden']->setValue('-1');
    } else {
        $fields['reference_article_id'] = new cjoLinkButtonField('reference_article_id', cjoAddon::translate(7,'label_reference_article_name'));
        $fields['reference_article_id']->addValidator('notEmpty', cjoAddon::translate(7,'miss_reference_article_name'));
    }

    $fields['comments_by'] = new textField('filter_comments_by', cjoAddon::translate(7,'label_filter_comments_by'));
    $fields['comments_by']->setHelp(cjoAddon::translate(7,'note_filter_comments_by'));

    // Kommentarlisten-Typ
    $fields['list_typ'] = new selectField('list_typ', cjoAddon::translate(7,'label_list_typ'));
    $fields['list_typ']->addOptions(array (array (cjoAddon::translate(7,'label_list_typ_visible'), 'visible'),
                                       array (cjoAddon::translate(7,'label_list_typ_hidden'), 'hidden'),
                                       array (cjoAddon::translate(7,'label_list_typ_guestbook'), 'guestbook')));
    $fields['list_typ']->addAttribute('size', 1);
    $fields['list_typ']->addAttribute('style', 'width: 200px');    

    // Kommentare in Kurzform (+mehr-Link)
    $fields['short_comments_hidden'] = new hiddenField('short_comments');
    $fields['short_comments_hidden']->setValue('0');
    $fields['short_comments'] = new checkboxField('short_comments', '&nbsp;');
    $fields['short_comments']->addBox(cjoAddon::translate(7,'label_short_comments'), '1');

    // Kommentare in Kurzform (+mehr-Link)
    $fields['short_comments_length'] = new textField('short_comments_length', cjoAddon::translate(7,'label_short_comments_length'));
    $fields['short_comments_length']->addAttribute('style', 'width: 50px'); 
    
    // Sortierung
    $fields['order_comments'] = new selectField('order_comments', cjoAddon::translate(7,'label_order_comments'));
    $fields['order_comments']->addValidator('notEmpty', cjoAddon::translate(7,'miss_order_comments'));
    $fields['order_comments']->addAttribute('size', '1');
    $fields['order_comments']->addOption(cjoAddon::translate(7,'order_comments_asc'), 'ASC');
    $fields['order_comments']->addOption(cjoAddon::translate(7,'order_comments_desc'), 'DESC');
    $fields['order_comments']->addAttribute('style', 'width: 150px');    
    
    // Zeitformat
    $fields['date_format'] = new selectField('date_format', cjoAddon::translate(7,'label_date_format'));
    $fields['date_format']->addValidator('notEmpty', cjoAddon::translate(7,'miss_date_format'));
    $fields['date_format']->addOptions(array (array ('TT. Mon. YYYY - hh:mm', '%d. %b. %Y - %H:%M'),
                                          array ('TT.MM.YYYY', '%d.%m.%Y'),
                                          array ('Mon, DD YYYY - h:mm a.m./p.m.', '%b, %d %Y - %r'),
                                          array ('MM-DD-YYYY', '%m-%d-%Y')));
    $fields['date_format']->addAttribute('size', 1);
    $fields['date_format']->addAttribute('style', 'width: 150px');    
    
    // Hinweis wenn keine Einträge vorhanden
    $fields['no_entries_text'] = new textAreaField('no_entries_text', cjoAddon::translate(7,'label_no_entries'), array ('style' => 'height: 100px;'));


    $fields['headline2'] = new headlineField(cjoAddon::translate(7,'label_comments_input'), true); // Kommentar-Eingabe

    // Neue Einträge online/offline
    $fields['new_online_global'] = new selectField('new_online_global', cjoAddon::translate(7,'label_new_online_global'));
    $fields['new_online_global']->addAttribute('size', '1');
    $fields['new_online_global']->addOption(cjoAddon::translate(7,'new_online_global'), '1');
    $fields['new_online_global']->addOption(cjoAddon::translate(7,'new_offline_global'), '0');
    $fields['new_online_global']->addAttribute('style', 'width: 150px'); 
    
    $fields['oversize_length'] = new textField('oversize_length', cjoAddon::translate(7,'label_oversize_length'));
    $fields['oversize_length']->addAttribute('style', 'width: 50px'); 
    
    $fields['oversize_replace'] = new selectField('oversize_replace', cjoAddon::translate(7,'label_oversize_replace'));
    $fields['oversize_replace']->addValidator('notEmpty', cjoAddon::translate(7,'miss_oversize_replace'));
    $fields['oversize_replace']->addAttribute('size', '1');
    $fields['oversize_replace']->addOption(cjoAddon::translate(7,'space'), 'space');
    $fields['oversize_replace']->addOption(cjoAddon::translate(7,'trennstrich'), '-');
    $fields['oversize_replace']->addOption('&nbsp;', 'nbsp');
    $fields['oversize_replace']->addAttribute('style', 'width: 150px'); 
    
    // HTML-Tags erlauben/verbieten
    $fields['allow_html_tags_hidden'] = new hiddenField('allow_html_tags');
    $fields['allow_html_tags_hidden']->setValue('0');
//    $fields['allow_html_tags'] = new checkboxField('allow_html_tags', '&nbsp;' , array('disabled' => 'disabled'));
//    $fields['allow_html_tags']->addBox(cjoAddon::translate(7,'label_allow_html_tags'), '1');


    $fields['headline3'] = new headlineField(cjoAddon::translate(7,'label_comments_spam_filter'), true); // Spam-Filter;

    //  Spam-Level
    $fields['b8_spam_border'] = new textField('b8_spam_border', cjoAddon::translate(7,'label_b8_spam_border'));
    $fields['b8_spam_border']->setValue('0.5');
    $fields['b8_spam_border']->addValidator('isRange', cjoAddon::translate(7,'miss_b8_spam_border'), array('low' => '0', 'high' => 1));
    $fields['b8_spam_border']->addAttribute('style', 'width: 50px'); 
    
    // Selbstlern-Funktion
    $fields['b8_autolearn_hidden'] = new hiddenField('b8_autolearn');
    $fields['b8_autolearn_hidden']->setValue('0');
    $fields['b8_autolearn'] = new checkboxField('b8_autolearn', '&nbsp;');
    $fields['b8_autolearn']->addBox(cjoAddon::translate(7,'label_b8_autolearn'), '1');

    //Blacklist_1
    $fields['blacklist_1'] = new textAreaField('blacklist_0', cjoAddon::translate(7,'label_blacklist_0').' 1', array ('style' => 'height: 300px;'));

    //Blacklist_2
    $fields['blacklist_2'] = new textAreaField('blacklist_1', cjoAddon::translate(7,'label_blacklist_0').' 2', array ('style' => 'height: 300px;'));

    // debugging an/aus
    $fields['debuging_hidden'] = new hiddenField('debuging');
    $fields['debuging_hidden']->setValue('0');

    //Add Fields
    $section = new cjoFormSection(TBL_COMMENTS_CONFIG, cjoAddon::translate(7,$function."_config"), array ('id' => $oid, 'clang'=>$clang));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if (!cjoMessage::hasErrors() && $form->validate()) {
        if (cjo_post('cjoform_save_button','bool')) {
           // $message['accept'] = array_shift($form->getMessages());
            unset($function);
        }
    }
}


if ($function) return false;

    $qry =  "SELECT DISTINCT" .
            "	c.form_article_id AS form_article_id, " .
            "	c.reference_article_id AS reference_article_id, " .
            "  	IFNULL(a1.name,'--') AS form_article_name, " .
            "  	IFNULL(a2.name,'default') AS reference_article_name, " .
            "	c.* " .
            "FROM ".TBL_COMMENTS_CONFIG." c " .
            "LEFT JOIN ".TBL_ARTICLES." a1 " .
            "ON c.form_article_id = a1.id " .
            "LEFT JOIN ".TBL_ARTICLES." a2 " .
            "ON c.reference_article_id = a2.id " .
            "WHERE c.clang=".$clang." AND " .
            "((a1.clang=".$clang." AND  a2.clang=".$clang.")  " .
    		"OR c.reference_article_id='-1')";

    //LIST Ausgabe
    $list = new cjoList($qry, 'c.reference_article_id ASC', '', 100);
    $list->debug = false;

    $add_button = cjoUrl::createBELink(
                                '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                array('function' => 'add', 'oid' => ''),
                                $list->getGlobalParams(),
                                'title="'.cjoI18N::translate("button_add").'"');
                            
    $cols['icon'] = new staticColumn('<img src="img/silk_icons/gears.png" alt="" />',$add_button);
    $cols['icon']->setHeadAttributes('class="icon"');
    $cols['icon']->setBodyAttributes('class="icon"');

    $cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon"');

    $cols['form_article'] = new resultColumn('form_article_name', cjoAddon::translate(7,"label_form_article_name").' ');
    $cols['form_article']->setParams(array ('page' => 'edit', 'subpage' => 'content', 'article_id' => '%form_article_id%','mode' => 'edit','clang' => $clang));

    $cols['reference_article'] = new resultColumn('reference_article_name', cjoAddon::translate(7,"label_reference_article_name").' ');
    $cols['reference_article']->setParams(array ('page' => 'edit', 'subpage' => 'content', 'article_id' => '%reference_article_id%','mode' => 'edit','clang' => $clang));

    $cols['list_typ'] = new resultColumn('list_typ', cjoAddon::translate(7,'label_list_typ'));
    $cols['list_typ']->addCondition('list_typ', 'visible', cjoAddon::translate(7,'label_list_typ_visible'));
    $cols['list_typ']->addCondition('list_typ', 'hidden', cjoAddon::translate(7,'label_list_typ_hidden'));
    $cols['list_typ']->addCondition('list_typ', 'guestbook', cjoAddon::translate(7,'label_list_typ_guestbook'));
    $cols['list_typ']->setOptions(OPT_SORT);

    $cols['new_online_global'] = new resultColumn('new_online_global', cjoAddon::translate(7,'label_new_online_global'));
    $cols['new_online_global']->addCondition('new_online_global', '1', '<img src="img/silk_icons/eye.png" alt="" /> online');
    $cols['new_online_global']->addCondition('new_online_global', '0', '<img src="img/silk_icons/eye_off.png" alt="" /> offline');
    $cols['new_online_global']->setOptions(OPT_SORT);

    // Bearbeiten link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
    $cols['edit']->setHeadAttributes('colspan="2"');
    $cols['edit']->setBodyAttributes('width="40"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

    // Lösch link
    $img = '<img src="img/silk_icons/bin.png" alt="'.cjoI18N::translate("button_delete").'" title="'.cjoI18N::translate("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="30"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));

    $list->addColumns($cols);
    $list->show(false);