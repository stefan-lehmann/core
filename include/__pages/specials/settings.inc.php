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

if ($function == "setup"){
	// REACTIVATE SETUP

	$data = file_get_contents($CJO['FILE_CONFIG_MASTER']);

	if ($data != '') {
		$data = preg_replace('/^(\$CJO\[\'SETUP\'\]\s*=\s*)(.*)(;.*?)$/imx', '$1true$3', $data);

		if (!file_put_contents($CJO['FILE_CONFIG_MASTER'], $data)){
			cjoMessage::addError(cjoI18N::translate("msg_setup_error", cjo_absPath($CJO['FILE_CONFIG_MASTER'])));
		}
		else {
			cjoMessage::addSuccess(cjoI18N::translate("msg_setup_accept"));
            cjoExtension::registerExtensionPoint('SPECIALS_SETUP_INITIATED');   			
		}
	}
	else {
		cjoMessage::addError(cjoI18N::translate("msg_setup_error",
		                     cjoFile::absPath($CJO['FILE_CONFIG_MASTER'])));
	}
}
elseif ($function == "repair_media"){
	cjoMedia::resetAllMedia();
}
elseif($function == "repair_startpage"){
	cjoAssistance::repairStartpage();
}
elseif ($function == "generate"){
	// generate all articles,cats,templates,caches
	cjoGenerate::generateAll();
}
elseif ($function == "linkchecker"){
    cjoAssistance::validateLinks();
}
elseif ($function == 'updateinfos'){

	$change_lang = false;

	cjoProp::set('SERVER',         cjo_post('new_SERVER', 'string', cjoProp::get('SERVER')));
	cjoProp::set('SERVERNAME',     cjo_post('new_SERVERNAME', 'string', cjoProp::get('SERVERNAME')));
	cjoProp::set('ERROR_EMAIL',    cjo_post('new_ERROR_EMAIL', 'string', cjoProp::get('ERROR_EMAIL')));
	$new_lang                = cjo_post('new_LANG', 'string');
	$new_start_article_id    = cjo_post('new_START_ARTICLE_ID', 'cjo-article-id');
	$new_notfound_article_id = cjo_post('new_NOTFOUND_ARTICLE_ID', 'cjo-article-id');

	if ($CJO['LANG'] != $new_lang){
		cjoProp::set('LANG',$new_lang);
		$change_lang = true;
	}

	if (cjoProp::get('START_ARTICLE_ID') != $new_start_article_id) {
	    
	    $article = OOArticle::getArticleById($new_start_article_id);
        
        if (OOArticle::isValid($article)) {
			cjoProp::set('START_ARTICLE_ID', $new_start_article_id);
		}
		else {
			cjoMessage::addError(cjoI18N::translate("msg_new_start_article_not_valid"));
		}
	}

	if (cjoProp::get('NOTFOUND_ARTICLE_ID') != $new_notfound_article_id) {
	    
	    $article = OOArticle::getArticleById($new_notfound_article_id);
	    
		if (OOArticle::isValid($article)) {
			cjoProp::set('NOTFOUND_ARTICLE_ID', $new_notfound_article_id);
		}
		else {
			cjoMessage::addError(cjoI18N::translate("msg_new_notfound_article_not_valid"));
		}
	}

	if (!cjoMessage::hasErrors()) {

        if (cjoProp::saveToFile(cjoPath::pageConfig('page'))){
            cjoMessage::addSuccess(cjoI18N::translate("msg_data_saved"));
            cjoExtension::registerExtensionPoint('SPECIALS_INFOS_UPDATED');
            if ($change_lang) {
                cjoUrl::redirectBE(array('function'=>'','msg'=>'msg_data_saved'));
            }
        }
	}
}

$buttons = new buttonField();
$buttons->addButton('cjoform_submit_button',cjoI18N::translate('button_update'), true, 'img/silk_icons/tick.png');
$buttons->setButtonAttributes('cjoform_submit_button','style="margin: 10px 0 10px 194px;"');

echo '<div class="a22-cjolist">'.
     '	<div class="a22-cjolist-data">'.
	 '		<table class="cjo no_hover" cellspacing="0" cellpadding="0" border="0">'.
 	 '       <thead>'.
	 '		 <tr>'.
	 '			<th colspan="2">'.cjoI18N::translate("label_special_features").'</th>'.
	 '		</tr>'.
 	 '      </thead>'.
 	 '      <tbody>'.
	 '		<tr>'.
	 '			<td width="50%" valign="top">'.
	 '				<p><b><a href="index.php?page=specials&function=generate" title="'.cjoI18N::translate("link_regenerate_articles").'">'.cjoI18N::translate("link_regenerate_articles").'</a></b><br />'.cjoI18N::translate("text_regenerate_articles").'</p>'.
	 '				<p><b><a href="index.php?page=specials&function=linkchecker" title="'.cjoI18N::translate("link_checker").'">'.cjoI18N::translate("link_checker").'</a></b><br />'.cjoI18N::translate("text_checker").'</p>'.
	 '				<p><b><a href="index.php?page=specials&function=repair_media" class="cjo_confirm" title="'.cjoI18N::translate("link_repair_media").'">'.cjoI18N::translate("link_repair_media").'</a></b><br />'.cjoI18N::translate("text_repair_media").'</p>'.
	 '				<p><b><a href="index.php?page=specials&function=repair_startpage" class="cjo_confirm" title="'.cjoI18N::translate("link_repair_startpage").'">'.cjoI18N::translate("link_repair_startpage").'</a></b><br />'.cjoI18N::translate("msg_repair_startpage").'</p>'.
	 '				<p><b><a href="index.php?page=specials&function=setup" class="cjo_confirm" title="'.cjoI18N::translate("link_re_setup").'">'.cjoI18N::translate("link_re_setup").'</a></b><br />'.cjoI18N::translate("text_re_setup").'</p>'.
	 '			</td>'.
	 '			<td valign="top">'.
	 '				<form action="index.php" method="post">'.
	 '					<input type="hidden" name="page" value="specials" />'.
	 '					<input type="hidden" name="function" value="updateinfos" />'.
	 '					<input type="hidden" name="neu_modrewrite" value="false" />'.
	 '					<table class="cjo" cellspacing="0" cellpadding="0" border="0">'.
	 '						<thead>'.
	 '						<tr><th colspan="2"><b>'.cjoI18N::translate("label_general_info").'</b></th></tr>'.
	 '						</thead>'.
	 '						<tbody>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_server").':</td><td><input type="text" size="5" name="new_SERVER" value="'.$CJO['SERVER'].'" class="inp100" /></td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_servername").':</td><td><input type="text" size="5" name="new_SERVERNAME" value="'.$CJO['SERVERNAME'].'" class="inp100" /></td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_error_email").':</td><td><input type="text" size="5" name="new_ERROR_EMAIL" value="'.$CJO['ERROR_EMAIL'].'" class="inp100" /></td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_contejo_version").':</td><td>'.$CJO['VERSION'].'.'.$CJO['RELEASE'].'</td></tr>'.
	 '						</tbody>'.
	 '					</table><br/>'.
	 '					<table class="cjo" cellspacing="0" cellpadding="0" border="0">'.
	 '						<thead>'.
	 '						<tr><th colspan="2"><b>'.cjoI18N::translate("label_change_db1_setup").'</b></th></tr>'.
	 '						</thead>'.
	 '						<tbody>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_table_prefix").':</td><td>'.cjoProp::getTablePrefix().'</td></tr>'.
	 '						<tr><td colspan="2" style="background:transparent!important"></td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_db_host").':</td><td>'.$CJO['DB']['1']['HOST'].'</td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_db_name").':</td><td>'.$CJO['DB']['1']['NAME'].'</td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_db_login").':</td><td>'.$CJO['DB']['1']['LOGIN'].'</td></tr>'.
	 '						<tr><td width="40%">'.cjoI18N::translate("label_db_psw").':</td><td>'.preg_replace('/./','*',$CJO['DB']['1']['PSW']).'</td></tr>'.
	 '						</tbody>'.
	 '					</table><br/>';

if ($CJO['DB']['LOCAL']['HOST'] &&
	$CJO['DB']['LOCAL']['NAME'] &&
	$CJO['DB']['LOCAL']['LOGIN'] &&
	$CJO['DB']['LOCAL']['PSW'] ){

	echo '				<table class="cjo" cellspacing="0" cellpadding="0" border="0">'.
		 '					<thead>'.
		 '					<tr><th colspan="2"><b>'.cjoI18N::translate("label_change_dblocal_setup").'</b></th></tr>'.
		 '					</thead>'.
	 	 '					<tbody>'.
	 	 '					<tr><td width="40%">'.cjoI18N::translate("label_table_prefix").':</td><td>'.cjoProp::getTablePrefix().'</td></tr>'.
	 	 '					<tr><td colspan="2" style="background:transparent!important"></td></tr>'.
		 '					<tr><td width="40%">'.cjoI18N::translate("label_db_host").':</td><td>'.$CJO['DB']['LOCAL']['HOST'].'</td></tr>'.
		 '					<tr><td width="40%">'.cjoI18N::translate("label_db_name").':</td><td>'.$CJO['DB']['LOCAL']['NAME'].'</td></tr>'.
		 '					<tr><td width="40%">'.cjoI18N::translate("label_db_login").':</td><td>'.$CJO['DB']['LOCAL']['LOGIN'].'</td></tr>'.
		 '					<tr><td width="40%">'.cjoI18N::translate("label_db_psw").':</td><td>'.preg_replace('/./','*',$CJO['DB']['LOCAL']['PSW']).'</td></tr>'.
		 '					</tbody>'.
		 '				</table><br/>';
}

echo '					<table class="cjo" cellspacing="0" cellpadding="0" border="0">'.
	 '						<thead>'.
	 '						<tr><th colspan="2"><b>'.cjoI18N::translate("label_specials_others").'</b></th></tr>'.
	 '						</thead>'.
	 '						<tbody>'.
	 '							<tr><td width="40%">'.cjoI18N::translate("label_start_article_id").':</td><td><input type="text" class="inp10" name="new_START_ARTICLE_ID" value="'.$CJO['START_ARTICLE_ID'].'" /></td></tr>'.
	 '							<tr><td width="40%">'.cjoI18N::translate("label_notfound_article_id").':</td><td><input type="text" class="inp10" name="new_NOTFOUND_ARTICLE_ID" value="'.$CJO['NOTFOUND_ARTICLE_ID'].'" /></td></tr>'.
	 '							<tr><td width="40%">'.cjoI18N::translate("label_backend_language").':</td><td>'.
	 '								<select name="new_LANG" size="1" style="width: auto">';
										foreach (cjoI18N::getLocales() as $l) {
											$selected = ($l == $CJO['LANG'] ? 'selected="selected"' : '');
											echo '<option value="'.$l.'" '.$selected.'>'.cjoI18N::translate($l).'&nbsp;&nbsp;</option>';
										}
echo '								</select>'.
	 '							</td></tr>'.
	 '						</tbody>'.
	 '					</table>'.
	 '					'.$buttons->_get().
	 '					</form>'.
	 '				</td>'.
	 '			</tr>'.
	 '			</tbody>'.
	 '			</table>'.
	 '		</div>'.
	 '	</div>';
