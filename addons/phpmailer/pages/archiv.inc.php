<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  phpmailer
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

// löschen
if ($function == 'delete') {
	$sql = new cjoSql();
	$sql->statusQuery("DELETE FROM ".TBL_20_MAIL_ARCHIV." WHERE id='".$oid."'",
	                  $I18N_20->msg('msg_archiv_item_deleted'));
    unset($function);
    unset($oid);
}

if (cjo_post('dbdelete', 'bool') && $CJO['USER']->isAdmin()) {
	$sql = new cjoSql();
	$sql->statusQuery("TRUNCATE TABLE ".TBL_20_MAIL_ARCHIV,
	                  $I18N_20->msg('msg_archiv_deleted'));
    unset($function);
}

if ($oid) {

    //Form
    $form = new cjoForm($mypage.'_'.$subpage.'_form');
    $form->setEditMode($oid != '');
    $form->debug = false;

    $fields['send_date'] = new readOnlyField('send_date', $I18N_20->msg('label_send_date'));
    $fields['send_date']->setFormat('strftime',$I18N->msg('dateformat_sort'));

    $fields['sender'] = new readOnlyField('sender', $I18N_20->msg('label_sender'));
    $fields['to'] = new readOnlyField('to', $I18N_20->msg('label_to'));
    $fields['cc'] = new readOnlyField('cc', $I18N_20->msg('label_cc'));
    $fields['bcc'] = new readOnlyField('bcc', $I18N_20->msg('label_bcc'));
    $fields['subject'] = new readOnlyField('subject', $I18N_20->msg('label_subject'), array('style'=> 'width: 720px'));
    $fields['message'] = new textAreaField('message', $I18N_20->msg('label_message'), array('readonly'=>'readonly',
    																						'rows'=>20,
    																						'cols'=> 10,
    																						'style'=> 'width: 720px'));

    $fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
    $fields['headline1']->setValue($I18N_20->msg("label_info"));

    $fields['error'] = new readOnlyField('error', $I18N_20->msg('label_phpmailer_error'));
    $fields['article_id'] = new readOnlyField('article_id', $I18N_20->msg('label_article_id'));
    $fields['clang'] = new readOnlyField('clang', $I18N_20->msg('label_clang_id'));
    $fields['remote_addr'] = new readOnlyField('remote_addr', $I18N_20->msg('label_remote_addr'));
    $fields['user_agent'] = new readOnlyField('user_agent', $I18N_20->msg('label_user_agent'));
    $fields['request'] = new readOnlyField('request', $I18N_20->msg('label_request'));

    $fields['button'] = new buttonField();
    $fields['button']->addButton('cjoform_cancel_button', $I18N->msg('button_cancel'), true, 'img/silk_icons/cancel.png');
    $fields['button']->needFullColumn(true);

    //Add Fields
    $section = new cjoFormSection(TBL_20_MAIL_ARCHIV, $I18N_20->msg("label_message_datails"), array ('id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show(false);
}
else {


    // Eintragsliste
    $qry = "SELECT * FROM ".TBL_20_MAIL_ARCHIV;
    $list = new cjolist($qry, 'send_date', 'desc', 'subject', 50);
    //$list->debug = true;
    
    $cols['id'] = new resultColumn('id', $I18N->msg('label_id'));
    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon cjo_id"');


    $cols['subject'] = new resultColumn('subject', $I18N_20->msg('label_subject'), 'truncate', array( 'length' => 30, 'etc' => '...', 'break_words' => false));
    $cols['subject']->setParams(array ('function' => 'message', 'oid'=> '%id%' ,'msg'=> false));
    $cols['subject']->setBodyAttributes('width="20%"');

    $cols['message'] = new resultColumn('message', $I18N_20->msg('label_message'), 'truncate', array( 'length' => 150, 'etc' => '...', 'break_words' => false));


    $cols['sender'] = new resultColumn('sender', $I18N_20->msg('label_sender'));
    $cols['to'] = new resultColumn('to', $I18N_20->msg('label_to'));

    $cols['send_date'] = new resultColumn('send_date', $I18N_20->msg('label_send_date'), 'strftime', $I18N->msg('dateformat_sort'));
    $cols['send_date']->delOption(OPT_SEARCH);

    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

    $img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid'=> '%id%' ,'msg'=> false));

    $list->addColumns($cols);

    if ($list->numRows() != 0) {

    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton( $I18N_20->msg('label_delete_archiv_db'), false, 'img/silk_icons/bin.png', 'name="dbdelete" value="1" class="cjo_confirm" style="padding:2px;"');

    	$functions = '<p style="text-align:center">'.$buttons->getButtons().'</p>'."\r\n";

        if ($CJO['USER']->isAdmin()) $list->setVar(LIST_VAR_INSIDE_FOOT, $functions);
    }

    $list->show();
}

$popup_url = cjoAssistance::createBEUrl(array('subpage'=>'show', 'popup'=>1));

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('td .cjo_popup').click(function(){
			var oid = $(this).parent().parent().find('.cjo_id').text();
			cjo.openPopUp('preview','<?php echo $popup_url; ?>&oid='+oid,980,600,'');
			return false;
		});
	});

/* ]]> */
</script>