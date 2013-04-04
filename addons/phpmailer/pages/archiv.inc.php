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

// löschen
if ($function == 'delete') {
	$sql = new cjoSql();
	$sql->statusQuery("DELETE FROM ".TBL_20_MAIL_ARCHIV." WHERE id='".$oid."'",
	                  cjoAddon::translate(20,'msg_archiv_item_deleted'));
    unset($function);
    unset($oid);
}

if (cjo_post('dbdelete', 'bool') && cjoProp::getUser()->isAdmin()) {
	$sql = new cjoSql();
	$sql->statusQuery("TRUNCATE TABLE ".TBL_20_MAIL_ARCHIV,
	                  cjoAddon::translate(20,'msg_archiv_deleted'));
    unset($function);
}

if ($oid) {

    //Form
    $form = new cjoForm($mypage.'_'.$subpage.'_form');
    $form->setEditMode($oid != '');
    $form->debug = false;

    $fields['send_date'] = new readOnlyField('send_date', cjoAddon::translate(20,'label_send_date'));
    $fields['send_date']->setFormat('strftime',cjoI18N::translate('dateformat_sort'));

    $fields['sender'] = new readOnlyField('sender', cjoAddon::translate(20,'label_sender'));
    $fields['to'] = new readOnlyField('to', cjoAddon::translate(20,'label_to'));
    $fields['cc'] = new readOnlyField('cc', cjoAddon::translate(20,'label_cc'));
    $fields['bcc'] = new readOnlyField('bcc', cjoAddon::translate(20,'label_bcc'));
    $fields['subject'] = new readOnlyField('subject', cjoAddon::translate(20,'label_subject'), array('style'=> 'width: 720px'));
    $fields['message'] = new textAreaField('message', cjoAddon::translate(20,'label_message'), array('readonly'=>'readonly',
    																						'rows'=>20,
    																						'cols'=> 10,
    																						'style'=> 'width: 720px'));

    $fields['headline1'] = new headlineField(cjoAddon::translate(20,"label_info"), true);

    $fields['error'] = new readOnlyField('error', cjoAddon::translate(20,'label_phpmailer_error'));
    $fields['article_id'] = new readOnlyField('article_id', cjoAddon::translate(20,'label_article_id'));
    $fields['clang'] = new readOnlyField('clang', cjoAddon::translate(20,'label_clang_id'));
    $fields['remote_addr'] = new readOnlyField('remote_addr', cjoAddon::translate(20,'label_remote_addr'));
    $fields['user_agent'] = new readOnlyField('user_agent', cjoAddon::translate(20,'label_user_agent'));
    $fields['request'] = new readOnlyField('request', cjoAddon::translate(20,'label_request'));

    $fields['button'] = new buttonField();
    $fields['button']->addButton('cjoform_cancel_button', cjoI18N::translate('button_cancel'), true, 'img/silk_icons/cancel.png');
    $fields['button']->needFullColumn(true);

    //Add Fields
    $section = new cjoFormSection(TBL_20_MAIL_ARCHIV, cjoAddon::translate(20,"label_message_datails"), array ('id' => $oid));

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
    
    $cols['id'] = new resultColumn('id', cjoI18N::translate('label_id'));
    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon cjo_id"');


    $cols['subject'] = new resultColumn('subject', cjoAddon::translate(20,'label_subject'), 'truncate', array( 'length' => 30, 'etc' => '...', 'break_words' => false));
    $cols['subject']->setParams(array ('function' => 'message', 'oid'=> '%id%' ,'msg'=> false));
    $cols['subject']->setBodyAttributes('width="20%"');

    $cols['message'] = new resultColumn('message', cjoAddon::translate(20,'label_message'), 'truncate', array( 'length' => 150, 'etc' => '...', 'break_words' => false));


    $cols['sender'] = new resultColumn('sender', cjoAddon::translate(20,'label_sender'));
    $cols['to'] = new resultColumn('to', cjoAddon::translate(20,'label_to'));

    $cols['send_date'] = new resultColumn('send_date', cjoAddon::translate(20,'label_send_date'), 'strftime', cjoI18N::translate('dateformat_sort'));
    $cols['send_date']->delOption(OPT_SEARCH);

    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

    $img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid'=> '%id%' ,'msg'=> false));

    $list->addColumns($cols);

    if ($list->numRows() != 0) {

    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton( cjoAddon::translate(20,'label_delete_archiv_db'), false, 'img/silk_icons/bin.png', 'name="dbdelete" value="1" class="cjo_confirm" style="padding:2px;"');

    	$functions = '<p style="text-align:center">'.$buttons->getButtons().'</p>'."\r\n";

        if (cjoProp::getUser()->isAdmin()) $list->setVar(LIST_VAR_INSIDE_FOOT, $functions);
    }

    $list->show();
}

$popup_url = cjoUrl::createBEUrl(array('subpage'=>'show', 'popup'=>1));

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