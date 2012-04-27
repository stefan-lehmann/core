<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
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

$article_str = '--';

if ($groupletter->article_id > 0) {
	$article = OOArticle::getArticleById($groupletter->article_id, $groupletter->clang);
	if (OOArticle::isValid($article)) {
		$article_str =  $I18N_10->msg('label_article').': '.$article->getName().' ('.
						cjoAssistance::createBELink(
						            $I18N->msg("label_edit"),
									array('article_id' => $article->getId(), 'clang'=> $groupletter->clang),
									array('page' => 'edit', 'subpage' => 'content',  'ctype' => 0)).
						')';
		}
}

$groups_str = '';
foreach(cjoAssistance::toArray($groupletter->group_ids) as $group_id) {

	$sql = new cjoSql();
	$qry = "SELECT name FROM ".TBL_COMMUNITY_GROUPS." WHERE id = ".$group_id;
	$sql->setQuery($qry);
	if ($sql->getValue('name') == '') continue;
	$groups_str .= ($groups_str == '') ? $sql->getValue('name') : ', '.$sql->getValue('name');
}

$icons = array();
foreach ($CJO['CLANG'] as $clang_id=>$clang_name) {
	$icons[$clang_id] = sprintf ('<img src="img/flags/%s" title="%s" alt="" />', $CJO['CLANG_ISO'][$clang_id],$CJO['CLANG'][$clang_id]);
}

if (!$groupletter->firstsenddate) {
	$senddates_str = $I18N_10->msg('msg_gl_not_send');
}
else {
	$senddates_str  = ($groupletter->firstsenddate > 0) ? strftime($I18N->msg("dateformat_sort"), $groupletter->firstsenddate) : '';
	$senddates_str .= ' -- ';
	$senddates_str .= ($groupletter->lastsenddate > 0) ? strftime($I18N->msg("dateformat_sort"), $groupletter->lastsenddate) : '';
}

$sql = new cjoSql();
$qry = "SELECT CONCAT(from_name,' &lt;',from_email,'&gt;') AS name
		FROM ".TBL_20_MAIL_SETTINGS."
		WHERE id='".$groupletter->mail_account."'";
$sql->setQuery($qry);
$mail_account = $sql->getValue('name');


/**
 * Do not delete translate values for i18n collection!
 * [translate: label_clang]
 * [translate: label_subject]
 * [translate: label_reply_to]
 * [translate: label_send_type]
 * [translate: label_groups]
 * [translate: label_processed]
 * [translate: label_senddates]
 * [translate: label_gl_editor]
 */

$infos = array(
	"label_clang" 		=> $icons[$groupletter->clang].' '.$CJO['CLANG'][$groupletter->clang],
	"label_subject" 	=> '<b>'.$groupletter->subject.'</b> (<a href="#" class="cjo_popup">'.$I18N_10->msg('label_preview').'</a>)',
    "label_reply_to" 	=> $mail_account,
    "label_send_type"	=> $groupletter->article_id > 0 ? $article_str : $I18N_10->msg('label_send_text'),
    "label_groups" 		=> $groups_str,
    "label_processed" 	=> $groupletter->prepared.' / '.$groupletter->send,
    "label_senddates" 	=> $senddates_str,
    "label_gl_editor" 	=> $groupletter->user
);



//Form
$form = new cjoForm($mypage.'_'.$subpage.'_send_form');
$form->setEditMode(true);
$form->debug = false;


//Fields
foreach($infos as $key => $val){
	$fields[$key] = new readOnlyField('testmail', $I18N_10->msg($key), array(), $key);
	$fields[$key]->setValue($val);

}

// Hilfetext  ----------------------------------------------------------------------------------------------------------------------------

$fields['headline4'] = new readOnlyField('headline4', '', array('class' => 'formheadline slide'));
$fields['headline4']->setValue($I18N_10->msg('label_help'));

$explain = 	$I18N_10->msg("text_explain_cycle1").
			$I18N_10->msg("text_explain_cycle2").
			$I18N_10->msg("text_explain_cycle3").
			$I18N_10->msg("text_explain_cycle4").
			$I18N_10->msg("text_explain_cycle5").
			$I18N_10->msg("text_explain_cycle6");

$fields['explain'] = new readOnlyField('', '', array('style'=>'display: block; padding:20px;'));
$fields['explain']->setValue($explain);

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_send_button',$I18N_10->msg("button_send"), true, 'img/silk_icons/email_go.png');
$fields['button']->addButton('cjoform_reset_button',$I18N_10->msg("button_reset_prepared"), true, 'img/silk_icons/cancel.png');

//Add Fields
$section = new cjoFormSection($CJO['ADDON']['settings'][$mypage], $I18N_10->msg('label_send_groupletter'), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show(false);

$js_reload = '';
$js_block = '';
if ($CJO['ADDON']['settings'][$mypage]['reload'] === true){

	$url = cjoAssistance::createBEUrl(array('cjoform_send_button' => 1));

	$js_reload = 'location.href = \''.$url.'\';';
	$js_block = '$(\'#cjo_page_margin\').block({ message: null });';
}

$popup_url = cjoAssistance::createBEUrl(array('subpage' => 'show', 'clang' => $groupletter->clang, 'popup'=>1, 'oid' => $groupletter->id));

cjoAssistance::resetAfcVars();

?>

<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		function reload(){
			<?php echo $js_reload; ?>
		}
		<?php echo $js_block; ?>

		setTimeout(reload, 8000);

		$('a.cjo_popup').click(function(){
			cjo.openPopUp('preview','<?php echo $popup_url; ?>',1010,600,'');
			return false;
		});
	});

/* ]]> */
</script>