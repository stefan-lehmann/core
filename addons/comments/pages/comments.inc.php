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
 * @version     2.6.0
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
    $fields['autor'] = new textField('author', $I18N_7->msg('label_author'));
    $fields['autor']->addValidator('notEmpty', $I18N_7->msg('miss_author'));

    $fields['message'] = new textAreaField('message', $I18N_7->msg('label_message'), array ('style' => 'height: 150px'));
    $fields['message']->addValidator('notEmpty', $I18N_7->msg('miss_message'));

    $fields['url'] = new textField('url', $I18N_7->msg('label_url'));

    $fields['email'] = new textField('email', $I18N_7->msg('label_email'));

    $fields['city'] = new textField('city', $I18N_7->msg('label_city'));

    $fields['status'] = new selectField('status', $I18N_7->msg('label_status'));
    $fields['status']->addAttribute('size', '1');
    $fields['status']->addValidator('notEmpty', $I18N_7->msg('miss_status'));
    $fields['status']->addOption('online', '1');
    $fields['status']->addOption('offline', '0');

    $fields['created'] = new readOnlyField('created', $I18N_7->msg('label_created'));
    $fields['created']->setFormat('strftime', 'datetime');
    $fields['created']->activateSave(true);

    if ($function == 'add') {
    	$oid = '';
    	$fields['created']->setValue( time());
    }

    // Fields[Antworten]
    $fields['reply'] = new textAreaField('reply', $I18N_7->msg('label_reply'), array ('style' => 'height: 100px'));

    //Add Fields
    $section = new cjoFormSection(TBL_COMMENTS, $I18N_7->msg("label_edit"), array ('id' => $oid, 'clang' => $clang));

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
$cols['article_id'] = new resultColumn('article_id', $I18N_7->msg('label_article_id'));
$cols['article_id']->setHeadAttributes('class="icon"');
$cols['article_id']->setBodyAttributes('class="icon"');
$cols['article_id']->setParams(array ('page'=>'edit','subpage'=>'content','clang'=>$clang,'article_id'=>'%article_id%'));

// Autor
$cols['author'] = new resultColumn('author', $I18N_7->msg('label_author'));
$cols['author']->setBodyAttributes('width="100"');
$cols['author']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

// Kommentar
$cols['message'] = new resultColumn('message', $I18N_7->msg('label_message'), 'truncate',array( 'length' => 140, 'etc' => '...', 'break_words' => false));
$cols['message']->delOption(OPT_SORT);

// Erstellungsdatum
$cols['created'] = new resultColumn('created', $I18N_7->msg('label_created'), 'strftime', $I18N->msg('datetimeformat'));
$cols['created']->setBodyAttributes('style="white-space:nowrap;"');

// is URL
$cols['url'] = new resultColumn('url', $I18N_7->msg('label_url'),'sprintf', '<a href="%1$s" target="_blank"><img src="img/silk_icons/house.png" title="%1$s" alt="'.$I18N_7->msg("label_url").'" /></a>');
$cols['url']->addCondition('url', '', '&nbsp;');
$cols['url']->setHeadAttributes('class="icon"');
$cols['url']->setBodyAttributes('class="icon"');

// is eMail
$cols['email'] = new resultColumn('email', $I18N_7->msg('label_email'),'sprintf', '<a href="mailto:%1$s"><img src="img/silk_icons/email.png" title="%1$s" alt="'.$I18N_7->msg("label_email").'" /></a>');
$cols['email']->addCondition('email', '', '&nbsp;');
$cols['email']->setHeadAttributes('class="icon"');
$cols['email']->setBodyAttributes('class="icon"');

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="3"');
$cols['edit']->setBodyAttributes('width="20"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

// Status link
$aktiv = '<img src="img/silk_icons/eye.png" title="'.$I18N_7->msg("status_online").'" alt="'.$I18N_7->msg("status_online").'" />';
$inaktiv = '<img src="img/silk_icons/eye_off.png" title="'.$I18N_7->msg("status_offline").'" alt="'.$I18N_7->msg("status_offline").'" />';
$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="40"');
$cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'oid' => '%id%'));
$cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'oid' => '%id%'));

// Lösch link
$img = '<img src="img/silk_icons/bin.png" alt="'.$I18N->msg("button_delete").'" title="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));

$list->addColumns($cols);

$list->show(false);
