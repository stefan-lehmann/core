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

// STATUS ÄNDERN
if ($function == 'status') {
    cjoComments::changeCommentStatus($oid);
    unset($function);
}
elseif ($function == 'delete') {
    cjoComments::deleteComment($oid);
    unset($function);
}


if ($function == 'edit' || $function == 'add') {

    //Form
    $form = new cjoForm();
    $form->setEditMode($oid);
    //$form->debug = true;

    //Fields
    $fields['autor'] = new textField('author', cjoAddon::translate(7,'label_author'));
    $fields['autor']->addValidator('notEmpty', cjoAddon::translate(7,'miss_author'));

    $fields['message'] = new textAreaField('message', cjoAddon::translate(7,'label_message'), array ('style' => 'height: 150px'));
    $fields['message']->addValidator('notEmpty', cjoAddon::translate(7,'miss_message'));

    $fields['url'] = new textField('url', cjoAddon::translate(7,'label_url'));

    $fields['email'] = new textField('email', cjoAddon::translate(7,'label_email'));

    $fields['city'] = new textField('city', cjoAddon::translate(7,'label_city'));

    $fields['status'] = new selectField('status', cjoAddon::translate(7,'label_status'));
    $fields['status']->addAttribute('size', '1');
    $fields['status']->addValidator('notEmpty', cjoAddon::translate(7,'miss_status'));
    $fields['status']->addOption('online', '1');
    $fields['status']->addOption('offline', '0');

    $fields['created'] = new readOnlyField('created', cjoAddon::translate(7,'label_created'));
    $fields['created']->setFormat('strftime', 'datetime');
    $fields['created']->activateSave(true);

    if ($function == 'add') {
    	$oid = '';
    	$fields['created']->setValue( time());
    }

    // Fields[Antworten]
    $fields['reply'] = new textAreaField('reply', cjoAddon::translate(7,'label_reply'), array ('style' => 'height: 100px'));

    //Add Fields
    $section = new cjoFormSection(TBL_COMMENTS, cjoAddon::translate(7,"label_edit"), array ('id' => $oid, 'clang' => $clang));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if (!cjoMessage::hasErrors() && $form->validate()) {
        unset($function);
    }
}

if ($function) return;

$where_article_id = $article_id ? "article_id = '".$article_id."' AND" : "";

// Eintragsliste
$qry = "SELECT * FROM ".TBL_COMMENTS." WHERE ".$where_article_id." clang = ".$clang;
$list = new cjolist($qry, 'id', 'desc', 'author', 50);
$list->addGlobalParams(array ('article_id' => $article_id));
$list->setName('COMMENTS_LIST');
$list->setAttributes('id="comments_list"');

// Artikel-ID
$cols['article_id'] = new resultColumn('article_id', cjoAddon::translate(7,'label_article_id'));
$cols['article_id']->setHeadAttributes('class="icon"');
$cols['article_id']->setBodyAttributes('class="icon"');
$cols['article_id']->setParams(array ('page'=>'edit','subpage'=>'content','clang'=>$clang,'article_id'=>'%article_id%'));

// Autor
$cols['author'] = new resultColumn('author', cjoAddon::translate(7,'label_author'));
$cols['author']->setBodyAttributes('width="100"');
$cols['author']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

// Kommentar
$cols['message'] = new resultColumn('message', cjoAddon::translate(7,'label_message'), 'truncate',array( 'length' => 140, 'etc' => '...', 'break_words' => false));
$cols['message']->delOption(OPT_SORT);

// Erstellungsdatum
$cols['created'] = new resultColumn('created', cjoAddon::translate(7,'label_created'), 'strftime', cjoI18N::translate('datetimeformat'));
$cols['created']->setBodyAttributes('style="white-space:nowrap;"');

// is URL
$cols['url'] = new resultColumn('url', cjoAddon::translate(7,'label_url'),'sprintf', '<a href="%1$s" target="_blank"><img src="img/silk_icons/house.png" title="%1$s" alt="'.cjoAddon::translate(7,"label_url").'" /></a>');
$cols['url']->addCondition('url', '', '&nbsp;');
$cols['url']->setHeadAttributes('class="icon"');
$cols['url']->setBodyAttributes('class="icon"');

// is eMail
$cols['email'] = new resultColumn('email', cjoAddon::translate(7,'label_email'),'sprintf', '<a href="mailto:%1$s"><img src="img/silk_icons/email.png" title="%1$s" alt="'.cjoAddon::translate(7,"label_email").'" /></a>');
$cols['email']->addCondition('email', '', '&nbsp;');
$cols['email']->setHeadAttributes('class="icon"');
$cols['email']->setBodyAttributes('class="icon"');

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
$cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['edit']->setHeadAttributes('colspan="3"');
$cols['edit']->setBodyAttributes('width="20"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

// Status link
$aktiv = '<img src="img/silk_icons/eye.png" title="'.cjoAddon::translate(7,"status_online").'" alt="'.cjoAddon::translate(7,"status_online").'" />';
$inaktiv = '<img src="img/silk_icons/eye_off.png" title="'.cjoAddon::translate(7,"status_offline").'" alt="'.cjoAddon::translate(7,"status_offline").'" />';
$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="40"');
$cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'oid' => '%id%'));
$cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'oid' => '%id%'));

// Lösch link
$img = '<img src="img/silk_icons/bin.png" alt="'.cjoI18N::translate("button_delete").'" title="'.cjoI18N::translate("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));

$list->addColumns($cols);

$list->show(false);
