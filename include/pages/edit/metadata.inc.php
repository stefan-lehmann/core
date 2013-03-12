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

//Form
$form = new cjoForm();
$form->setEditMode($article_id);

//Hidden Fields
$hidden['article_id'] = new hiddenField('article_id');
$hidden['article_id']->setValue($article_id);

$hidden['re_id'] = new hiddenField('re_id');
$hidden['re_id']->setValue($re_id);

$hidden['ctype'] = new hiddenField('ctype');
$hidden['ctype']->setValue($ctype);

//Fields
$fields['file'] = new cjoMediaButtonField('file', $I18N->msg('label_meta_image'), array('preview' => array('enabled' => 'auto')));
$fields['file']->needFullColumn(true);

$fields['title'] = new textField('title', $I18N->msg("label_meta_title"));

$fields['author'] = new textField('author', $I18N->msg("label_author"));

$fields['keywords'] = new textAreaField('keywords', $I18N->msg("label_keywords"));

$fields['description'] = new textAreaField('description', $I18N->msg("label_description"));

cjoExtension::registerExtensionPoint('META_FORM_INIT', array('fields' => & $fields));    

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_update_button', $I18N->msg('button_update'), true, 'img/silk_icons/tick.png');
$fields['button']->needFullColumn(true);

//Add Fields:
$section = new cjoFormSection(TBL_ARTICLES, $I18N->msg("title_metadata"), array ('id' => $article_id, 'clang' => $CJO['CUR_CLANG']));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show(false);

if ($form->validate()) {
    
    cjoExtension::registerExtensionPoint('META_FORM_VALID');    
	cjoGenerate::generateArticle($article_id);
}