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
if ($function == 'delete' && cjoProp::getUser()->isAdmin()) {
	$sql = new cjoSql();
	$sql->statusQuery("DELETE FROM ".TBL_COMMUNITY_ARCHIV." WHERE id='".$oid."'",
	                  cjoAddon::translate(10,'msg_archiv_item_deleted'));
	unset($function);
}

if (cjo_post('dbdelete','bool') && cjoProp::getUser()->isAdmin()) {

	$sql = new cjoSql();
	$sql->statusQuery("TRUNCATE TABLE ".TBL_COMMUNITY_ARCHIV,
	                  cjoAddon::translate(10,'msg_archiv_deleted'));
	$sql->flush();
	$sql->statusQuery("TRUNCATE TABLE ".TBL_COMMUNITY_PREPARED,
	                  cjoAddon::translate(10,'msg_archiv_deleted'));
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

$cols['id'] = new resultColumn('id', cjoI18N::translate('label_id'));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon cjo_id"');

$cols['subject'] = new resultColumn('subject', cjoAddon::translate(10,'label_subject'), 'truncate');
$cols['subject']->addCondition('article_id', array('!=', ''), '<a href="#" class="cjo_popup">%s</a>');

$cols['article_id'] = new resultColumn('article_id', cjoAddon::translate(10,'label_article_id'));
$cols['article_id']->setBodyAttributes('class="icon"');
$cols['article_id']->addCondition('article_id', '-1', '--');
$cols['article_id']->addCondition('article_id', array('!=','-1'), '%s', array ('page' => 'edit', 'subpage' => 'content','article_id' => '%article_id%','clang' => $clang));

$cols['group_ids'] = new resultColumn('group_ids', cjoAddon::translate(10,'label_groups'));
$cols['group_ids']->addOption(OPT_SORT);

$cols['processed'] = new resultColumn('processed', cjoAddon::translate(10,'label_processed'));
$cols['processed']->setHeadAttributes('class="icon"');
$cols['processed']->setBodyAttributes('class="icon"');
$cols['processed']->delOption(OPT_ALL);

$cols['senddates'] = new resultColumn('senddates', cjoAddon::translate(10,'label_senddates'), 'strftime', cjoI18N::translate('dateformat_sort'));
$cols['senddates']->addCondition('senddates', '0 / 0', '--');
$cols['senddates']->setBodyAttributes('height="40" width="110"');
$cols['senddates']->delOption(OPT_ALL);

$cols['user'] = new resultColumn('user', cjoAddon::translate(10,'label_gl_editor'));
$cols['user']->setBodyAttributes('height="40" width="110"');

$cols['status'] = new staticColumn('status', cjoAddon::translate(10,'label_status'));
$cols['status']->addCondition('status', '2', '<img src="img/silk_icons/tick.png" title="'.cjoAddon::translate(10,"status_success").'" alt="'.cjoAddon::translate(10,"status_success").'" />');
$cols['status']->addCondition('status', '1', '<img src="img/silk_icons/email_open.png" title="'.cjoAddon::translate(10,"status_ready").'" alt="'.cjoAddon::translate(10,"status_ready").'" />');
$cols['status']->addCondition('status', '0', '<img src="img/silk_icons/error.png"  title="'.cjoAddon::translate(10,"status_incomplete").'" alt="'.cjoAddon::translate(10,"status_incomplete").'" />');
$cols['status']->addCondition('status', '-1', '<img src="img/silk_icons/exclamation.png"  title="'.cjoAddon::translate(10,"status_canceled").'" alt="'.cjoAddon::translate(10,"status_canceled").'" />');
$cols['status']->setBodyAttributes('width="16"');

if (cjoProp::getUser()->isAdmin()){
	$img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
	$cols['delete'] = new staticColumn($img, cjoI18N::translate("label_functions"));
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');
	$cols['delete']->setParams(array ('function' => 'delete', 'oid'=> '%id%' ,'msg'=> false));
}

$list->addColumns($cols);

    if ($list->numRows() != 0) {



    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton( cjoAddon::translate(10,'label_delete_archiv_db'), false, 'img/silk_icons/bin.png', 'name="dbdelete" value="1" class="cjo_confirm red" style="margin-top:-28px"');

    	$functions = '<p style="text-align:center;">'.$buttons->getButtons().'</p>'."\r\n";

        if (cjoProp::getUser()->isAdmin()) $list->setVar(LIST_VAR_INSIDE_FOOT, $functions);
    }

    $list->show();


$popup_url = cjoUrl::createBEUrl(array('subpage'=>'show', 'popup'=>1));

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