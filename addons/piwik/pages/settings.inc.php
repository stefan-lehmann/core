<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  piwik
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

$dataset = $CJO['ADDON']['settings'][$mypage];

//create formular
$form = new cjoForm();
$form->setEditMode(true);

$fields['url'] = new textField('URL', $I18N_25->msg('label_url'));
$fields['url']->addValidator('notEmpty', $I18N_25->msg('err_empty_url'), false);
$fields['url']->addValidator('isUrl', $I18N_25->msg('err_no_url'), false);

$fields['idsite'] = new textField('IDSITE', $I18N_25->msg('label_idsite'));
$fields['idsite']->addValidator('notEmptyOrNull', $I18N_25->msg('err_empty_idsite'));
$fields['idsite']->addValidator('isNumber', $I18N_25->msg('err_no_number_idsite'));
$fields['idsite']->addAttribute('style', 'width: 50px;');
$fields['idsite']->addAttribute('maxlength', '3');

$fields['track_as_downloads'] = new textField('TRACK_AS_DOWNLOADS', $I18N_25->msg('label_track_as_downloads'));

$fields['download_class'] = new textField('DOWNLOAD_CLASS', $I18N_25->msg('label_download_class'));

$fields['email_campaign_prefix'] = new textField('EMAIL_CAMPAIGN_PREFIX', $I18N_25->msg('label_email_campaign_prefix'));
$fields['email_campaign_prefix']->addValidator('notEmpty', $I18N_25->msg('err_empty_email_campaign_prefix'));

$fields['email_campaign_filename'] = new textField('EMAIL_CAMPAIGN_FILENAME', $I18N_25->msg('label_email_campaign_filename'));
$fields['email_campaign_filename']->addValidator('notEmpty', $I18N_25->msg('err_empty_email_campaign_filename'));

$section = new cjoFormSection($dataset, $I18N->msg('title_edit_settings'));

$section->addFields($fields);
$form->addSection($section);
$form->show(true);

if ($form->validate()) {
	if (!cjoGenerate::updateSettingsFile($CJO['ADDON']['settings'][$mypage]['SETTINGS'])) {
		cjoMessage::addError($I18N_25->msg('err_saving_settings'));
	}
}