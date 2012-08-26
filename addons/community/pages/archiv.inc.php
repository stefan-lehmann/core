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
if ($function == 'delete' && $CJO['USER']->isAdmin()) {
	$sql = new cjoSql();
	$sql->statusQuery("DELETE FROM ".TBL_COMMUNITY_ARCHIV." WHERE id='".$oid."'",
	                  $I18N_10->msg('msg_archiv_item_deleted'));
	unset($function);
}

if (cjo_post('dbdelete','bool') && $CJO['USER']->isAdmin()) {

	$sql = new cjoSql();
	$sql->statusQuery("TRUNCATE TABLE ".TBL_COMMUNITY_ARCHIV,
	                  $I18N_10->msg('msg_archiv_deleted'));
	$sql->flush();
	$sql->statusQuery("TRUNCATE TABLE ".TBL_COMMUNITY_PREPARED,
	                  $I18N_10->msg('msg_archiv_deleted'));
	unset($function);
}

// Eintragsliste
$qry = "SELECT
			*,
			concat(prepared, ' / ', send) AS processed,
			concat(firstsenddate, ' / ', lastsenddate) AS senddates
		FROM
			".TBL_COMMUNITY_ARCHIV."
		WHERE
			clang = ".$clang;

$list = new cjolist($qry, 'id', 'desc', 'author', 50);

$icons = array();
foreach($CJO['CLANG'] as $clang_id=>$clang_name){
	$icons[$clang_id] = sprintf ('<img src="img/flags/%s" title="%s" />', $CJO['CLANG_ISO'][$clang_id],$CJO['CLANG'][$clang_id]);
}

$cols['id'] = new resultColumn('id', $I18N->msg('label_id'));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon cjo_id"');

$cols['subject'] = new resultColumn('subject', $I18N_10->msg('label_subject'), 'truncate');
$cols['subject']->addCondition('article_id', array('!=', ''), '<a href="#" class="cjo_popup">%s</a>');

$cols['article_id'] = new resultColumn('article_id', $I18N_10->msg('label_article_id'));
$cols['article_id']->setBodyAttributes('class="icon"');
$cols['article_id']->addCondition('article_id', '-1', '--');
$cols['article_id']->addCondition('article_id', array('!=','-1'), '%s', array ('page' => 'edit', 'subpage' => 'content','article_id' => '%article_id%','clang' => $clang));

$cols['group_ids'] = new resultColumn('group_ids', $I18N_10->msg('label_groups'));
$cols['group_ids']->addOption(OPT_SORT);

$cols['processed'] = new resultColumn('processed', $I18N_10->msg('label_processed'));
$cols['processed']->setHeadAttributes('class="icon"');
$cols['processed']->setBodyAttributes('class="icon"');
$cols['processed']->delOption(OPT_ALL);

$cols['senddates'] = new resultColumn('senddates', $I18N_10->msg('label_senddates'), 'strftime', $I18N->msg('dateformat_sort'));
$cols['senddates']->addCondition('senddates', '0 / 0', '--');
$cols['senddates']->setBodyAttributes('height="40" width="110"');
$cols['senddates']->delOption(OPT_ALL);

$cols['user'] = new resultColumn('user', $I18N_10->msg('label_gl_editor'));
$cols['user']->setBodyAttributes('height="40" width="110"');

$cols['status'] = new staticColumn('status', $I18N_10->msg('label_status'));
$cols['status']->addCondition('status', '2', '<img src="img/silk_icons/tick.png" title="'.$I18N_10->msg("status_success").'" alt="'.$I18N_10->msg("status_success").'" />');
$cols['status']->addCondition('status', '1', '<img src="img/silk_icons/email_open.png" title="'.$I18N_10->msg("status_ready").'" alt="'.$I18N_10->msg("status_ready").'" />');
$cols['status']->addCondition('status', '0', '<img src="img/silk_icons/error.png"  title="'.$I18N_10->msg("status_incomplete").'" alt="'.$I18N_10->msg("status_incomplete").'" />');
$cols['status']->addCondition('status', '-1', '<img src="img/silk_icons/exclamation.png"  title="'.$I18N_10->msg("status_canceled").'" alt="'.$I18N_10->msg("status_canceled").'" />');
$cols['status']->setBodyAttributes('width="16"');

if ($CJO['USER']->isAdmin()){
	$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
	$cols['delete'] = new staticColumn($img, $I18N->msg("label_functions"));
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');
	$cols['delete']->setParams(array ('function' => 'delete', 'oid'=> '%id%' ,'msg'=> false));
}

$list->addColumns($cols);

    if ($list->numRows() != 0) {



    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton( $I18N_10->msg('label_delete_archiv_db'), false, 'img/silk_icons/bin.png', 'name="dbdelete" value="1" class="cjo_confirm red" style="margin-top:-28px"');

    	$functions = '<p style="text-align:center;">'.$buttons->getButtons().'</p>'."\r\n";

        if ($CJO['USER']->isAdmin()) $list->setVar(LIST_VAR_INSIDE_FOOT, $functions);
    }

    $list->show();


$popup_url = cjoAssistance::createBEUrl(array('subpage'=>'show', 'popup'=>1));

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('td .cjo_popup').click(function(){
			var oid = $(this).parent().parent().find('.cjo_id').text();
			cjo.openPopUp('preview','<?php echo $popup_url; ?>&oid='+oid,1010,600,'');
			return false;
		});
	});

/* ]]> */
</script>