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

$hidden['ctype'] = new hiddenField('ctype');
$hidden['ctype']->setValue($ctype);

//Fields
$fields['name'] = new textField('name', cjoI18N::translate('label_name'), array('class' => 'large_item'));
$fields['name']->addValidator('notEmpty', cjoI18N::translate('msg_name_notEmpty'));
$fields['name']->needFullColumn(true);

$sql = new cjoSql();
$qry = "SELECT CONCAT(name, ' (ID=', id,')') AS name, id FROM ".TBL_TEMPLATES." WHERE active=1 OR id='".$cjo_data['template_id']."' ORDER BY prior";
$results = $sql->getArray($qry);
$fields['template_id'] = new selectField('template_id', cjoI18N::translate('label_template'));
$fields['template_id']->addAttribute('size', '1');
$fields['template_id']->needFullColumn(true);

foreach($results as $result) {
    if (!cjoProp::getUser()->hasTemplatePerm($result['id'])) continue;
    $fields['template_id']->addOption($result['name'], $result['id']);
}

if ($cjo_data['template_id'] != 0 && OOArticleSlice::getFirstSliceForArticle($article_id) != null) {
	$fields['template_id']->addAttribute('disabled', 'disabled');
	$fields['template_id']->activateSave(false);
	$fields['template_id']->setHelp(cjoI18N::translate('msg_help_change_template'));
}

$qry = "SELECT CONCAT('&nbsp;|&rarr; ', name, '&nbsp;&nbsp;(',description,')') AS name, type_id FROM ".TBL_ARTICLES_TYPE." WHERE type_id != 1 ORDER BY prior";
$fields['type_id'] = new selectField('type_id', cjoI18N::translate('label_secure_level'));
$fields['type_id']->addOption(cjo_html2txt(cjoI18N::translate('label_type_id_all')), '1');
$fields['type_id']->addOption(cjo_html2txt(cjoI18N::translate('label_type_logged_out')), 'out');
$fields['type_id']->addOption(cjo_html2txt(cjoI18N::translate('label_type_logged_in')), 'in');
$fields['type_id']->addOption(cjo_html2txt(cjoI18N::translate('label_preview_for_editors')), 'contejo');
$fields['type_id']->addSQLOptions($qry);
$fields['type_id']->addAttribute('size', '1');
$fields['type_id']->needFullColumn(true);

$qry = "SELECT group_name, group_id FROM ".TBL_ARTICLES_CAT_GROUPS." ORDER BY group_id";
$fields['cat_group'] = new selectField('cat_group', cjoI18N::translate('label_cat_group'));
$fields['cat_group']->addSQLOptions($qry);
$fields['cat_group']->addAttribute('size', '1');
$fields['cat_group']->needFullColumn(true);

$fields['redirect_type'] = new radioField('redirect_type', cjoI18N::translate('label_redirect_type'),  array('style' => 'width: auto;'));
$fields['redirect_type']->addRadio(cjoI18N::translate('label_int_redirect'), 'int');
$fields['redirect_type']->addRadio(cjoI18N::translate('label_ext_redirect'), 'ext');
$fields['redirect_type']->setValue($redirect_type);
$fields['redirect_type']->addColAttribute('class', 'redirect_type', 'join');
$fields['redirect_type']->addColAttribute('style', 'margin-bottom:-10px');
$fields['redirect_type']->activateSave(false);
$fields['redirect_type']->needFullColumn(true);

$fields['int_redirect'] = new cjoLinkButtonField('redirect', '&nbsp;');
$fields['int_redirect']->addValidator('isNot',cjoI18N::translate("msg_int_redirect_not_self"),array(cjoProp::getArticleId()),true);
$fields['int_redirect']->addColAttribute('class', 'int', 'join');
$fields['int_redirect']->needFullColumn(true);

$fields['ext_redirect'] = new textField('redirect', '&nbsp;');
$fields['ext_redirect']->addColAttribute('class', 'ext', 'join');
$fields['ext_redirect']->needFullColumn(true);

$fields['online_from'] = new datepickerField('online_from', cjoI18N::translate("label_from_to"), '', array('online_from','online_to'));
$fields['online_from']->addColAttribute('style', 'width: 37%');
$fields['online_from']->addSettings("defaultDate: 'd', buttonImage: 'img/silk_icons/calendar_begin.png'");
$fields['online_from']->setDefault(time());

$fields['online_to'] = new datepickerField('online_to', '', '', 'online_to');
$fields['online_to']->addColAttribute('style', 'width: 63%');
$fields['online_to']->addSettings("defaultDate: new Date(2020, 1 - 1, 1), buttonImage: 'img/silk_icons/calendar_end.png'");
$fields['online_to']->setDefault(mktime(0, 0, 0, 1, 1, 2020));
$fields['online_to']->setHelp(cjoI18N::translate('msg_help_online_from_to'));

if (cjoProp::getUser()->isAdmin()) {
    $fields['admin_only'] = new checkboxField('admin_only', '&nbsp;',  array('style' => 'width: auto;'));
    $fields['admin_only']->addBox(cjoI18N::translate("label_superadmin_only"), '1');  
    $fields['admin_only']->setUncheckedValue('0');
    $fields['admin_only']->needFullColumn(true);
}

$fields['updatedate'] = new hiddenField('updatedate');
$fields['updatedate']->setValue(time());

$fields['updateuser'] = new hiddenField('updateuser');
$fields['updateuser']->setValue(cjoProp::getUser()->getValue("name"));

if ($function == 'add'){

    $oid = '';
	unset($hidden['updatedate']);
	unset($hidden['updateuser']);

	$hidden['function'] = new hiddenField('function');
	$hidden['function']->setValue($function);

    $fields['redir_add'] = new checkboxField('redir_add', '', array('style' => 'width: auto; margin-left: 200px;'));
    $fields['redir_add']->addBox(cjoI18N::translate("label_redirect_to_add_article"), '1');
    $fields['redir_add']->activateSave(false);

	$fields['re_id'] = new hiddenField('re_id');
	$fields['re_id']->setValue($re_id);

	$title = cjoI18N::translate("title_add_article");
}
else {
	$title = cjoI18N::translate("title_edit_settings");
}

// Entfernen von inaktiven Spalten
if (!empty($re_id))
	unset($fields['cat_group']);

if (!cjoProp::getUser()->hasLoginPerm())
	unset($fields['type_id']);

if (!cjoProp::getUser()->hasOnlineFromToPerm()){
	if ($function == 'add')	{
		$fields['online_from'] = new hiddenField('online_from');
		$fields['online_from']->setValue(time());
		$fields['online_to'] = new hiddenField('online_to');
		$fields['online_to']->setValue(mktime(0, 0, 0, 1, 1, 2020));
	}
	else{
		unset($fields['online_from']);
		unset($fields['online_to']);
	}
}

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_update_button', cjoI18N::translate('button_update'), true, 'img/silk_icons/tick.png');
$fields['button']->needFullColumn(true);


$section = new cjoFormSection(TBL_ARTICLES, $title, array ('id' => $article_id, 'clang' => cjoProp::getClang()), array('50%', '50%'));
//if ($function == 'add') $section->dataset = $_GET;

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show(false);

if ($form->validate()) {

    $settings                = array();
	$settings['name']        = cjo_post('name','string');
	$settings['cat_group']   = cjo_post('cat_group','string');
	$settings['type_id']     = cjo_post('type_id','string');
	$settings['template_id'] = cjo_post('template_id','cjo-template-id');
    $settings['online_from'] = cjo_post('online_from','int');
    $settings['online_to']   = cjo_post('online_to','int');
    $settings['redirect']    = cjo_post('redirect','string');    
    $settings['redir_add']   = cjo_post('redir_add','bool');

    if ($function == 'add') {

        $settings['re_id'] = cjo_post('re_id','cjo-article-id');
        $settings['id'] = cjoArticle::addArticle($settings);

		if ($settings['id']) {
            
		    if (!cjo_post('redir_add','bool')) {
    			cjoUrl::redirectBE(array('subpage' => 'content',
    										    'article_id' => $settings['id'],
    										    'clang' => cjoProp::getClang(),
    										    'ctype' => $ctype,
    										    'function' => '',
    										    'msg' => 'msg_article_inserted'));
    		}
		}
	}
	else {

		$sql = new cjoSql();
		$sql->setQuery("SELECT id, clang FROM ".TBL_ARTICLES."
						WHERE (name LIKE '%_copy')
						AND id='".$article_id."' AND clang!='".cjoProp::getClang()."'");

		$update = new cjoSql();

		for ($i = 0; $i < $sql->getRows(); $i++) {

			$update->flush();
			$update->setTable(TBL_ARTICLES);
			$update->setWhere("id='".$sql->getValue('id')."' AND clang='".$sql->getValue('clang')."'");
			$update->setValue("name", $settings['name']);
			$update->addGlobalUpdateFields();
			$update->Update();

			$sql->next();
		}

    	if ($cjo_data['cat_group'] != $settings['cat_group'] && $cjo_data['re_id'] == 0) {
            cjoArticle::updatePrio($article_id);
    	}
    	cjoGenerate::toggleStartpageArticle($settings['re_id']);
    	cjoGenerate::generateArticle($article_id, false);
    	cjoGenerate::generateArticle($settings['re_id'], false);
                    
    	cjoExtension::registerExtensionPoint('ARTICLE_UPDATED', array('action' => 'SETTINGS_UPDATED',
    	                                                              'id' => $article_id, 
    	                                                              'clang' => cjoProp::getClang()));
    
        //[translate: msg_article_settings_updated]
    	cjoUrl::redirectBE(array('subpage' => 'settings',
    								    'article_id' => $article_id,
    								    're_id' => $re_id,
    								    'clang' => cjoProp::getClang(),
    								    'ctype' => $ctype,
    								    'msg' => 'msg_article_settings_updated'));

	}
}

?>
<script type="text/javascript">
/* <![CDATA[ */

    $(function() {

        $(".redirect_type :radio").click(function() {
        	cjo.toggleRedirectType();
        });
        cjo.toggleRedirectType();
    });

    cjo.toggleRedirectType = function() {
        
    	$(".redirect_type :radio").each(function() {

            if ($(this).is(':checked')) {

                var is_int    = ($(this).val() != 'ext');

                var int       = $('.a22-col1.int'); 
                var ext       = $('.a22-col1.ext');
                var ext_input = ext.find('input');
                var ext_val   = ext_input.val(); 

                int.prev('.hr').hide();  
                ext.prev('.hr').hide();
                ext_input.removeClass('invalid');        
                
                var show = (is_int) ? int : ext;
                var hide = (is_int) ? ext : int;

                hide.hide()
                    .addClass('hide_me')
                    .find('input')
                    .removeAttr('name');
                
                show.show()
                    .removeClass('hide_me')
                    .find('input')
                    .attr('name','redirect');

                if (show == ext) {
                    ext_input.focus();
                }

                if (ext_val == ext_val*1) {
                    ext.find('input').val('');
                }
            }
        });
    };

/* ]]> */
</script>