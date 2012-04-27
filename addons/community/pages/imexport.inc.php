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

if (cjo_session('cm_export','bool')) {
    cjoCommunityImportExport::export();
}

if (cjo_get('automate','bool') &&
    cjo_session('cm_import','bool')) {
    cjoCommunityImportExport::import();
}

if (cjo_post('cjoform_delete_all_button', 'bool') &&
	$CJO['USER']->isAdmin()) {

    $sql_error = '';

    $sql = new cjoSql();
    $sql->setQuery("TRUNCATE TABLE ".TBL_COMMUNITY_USER);
    if ($sql->getError() != "") $sql_error .= $sql->getError();

    $sql->flush();
    $sql->setQuery("TRUNCATE TABLE ".TBL_COMMUNITY_GROUPS);
    if ($sql->getError() != "") $sql_error .= $sql->getError();

    $sql->flush();
    $sql->setQuery("TRUNCATE TABLE ".TBL_COMMUNITY_UG);
    if ($sql->getError() != "") $sql_error .= $sql->getError();

    $sql->flush();
    $sql->setQuery("TRUNCATE TABLE ".TBL_COMMUNITY_ARCHIV);
    if ($sql->getError() != "") $sql_error .= $sql->getError();

    $sql->flush();
    $sql->setQuery("TRUNCATE TABLE ".TBL_COMMUNITY_PREPARED);
    if ($sql->getError() != "") $sql_error .= $sql->getError();


    if ($sql_error == "") {
        cjoMessage::addSuccess($I18N_10->msg("msg_all_db_deleted"));
    } else {
        cjoMessage::addError($sql_error);
    }
    $_POST = array();
}


	// IMPORT
    $form = new cjoForm($mypage.'_'.$subpage.'_import');
    $form->setEditMode(true);
    $form->setEnctype('multipart/form-data');
    $form->debug = false;
    
    $fields['import'] = new readOnlyField('import', $I18N_10->msg('label_csv'), array( 'style' => 'float: left;'));
    $fields['import']->setValue('<input type="file" name="userfile" value="'.$_FILES['userfile']['tmp_name'].'" size="40" />');

	$sel_group = cjoCommunityGroups::getSelectGroups($group_id);

	$fields['groups'] = new readOnlyField('groups[]', $I18N_10->msg('label_groups'));
	$fields['groups']->setValue($sel_group->get());
	$fields['groups']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_groups'));

    $fields['clang'] = new selectField('clang', $I18N_10->msg('label_language'));
    $qry = "SELECT name, id FROM ".TBL_CLANGS." ORDER BY id";
    $fields['clang']->addSQLOptions($qry);
    $fields['clang']->addAttribute('size', '1');
    $fields['clang']->addAttribute('style', 'width: 130px;');
    $fields['clang']->setValue($CJO['CUR_CLANG']);
    $fields['clang']->setNote($I18N_10->msg("note_clang"));
    $fields['clang']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_lang'), false, false);

    //DATEI-EINSTELLUNGEN
    $fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline slide'));
    $fields['headline2']->setValue($I18N_10->msg('label_file_settings'));

    $fields['charset'] = new selectField('charset', $I18N_10->msg("label_charset"));
    $fields['charset']->addAttribute('size', '1');

    $charset = array('utf-8'=>'unicode (UTF-8)','iso'=>'ISO-8859-1');
    foreach($charset as $key=>$value){
        $fields['charset']->addOption('&nbsp;'.$value,$key);
    }

    $fields['divider'] = new selectField('divider', $I18N_10->msg("label_divider"));
    $fields['divider']->addAttribute('size', '1');
    $fields['divider']->addAttribute('style', 'width: 90px;');

    $divider = array(','=>',',';'=>';','\t'=>'{TAB}','\x20'=>'{SPACE}');
    foreach($divider as $key=>$value){
        $fields['divider']->addOption('&nbsp;'.$value,$key);
    }

    $fields['limit_start'] = new textField('limit_start', $I18N_10->msg('label_limit_start'));
    $fields['limit_start']->addAttribute('style', 'width: 80px;');
    $fields['limit_start']->setValue('1');
    $fields['limit_start']->addValidator('isNumber', $I18N_10->msg("err_limit_start"));

    $fields['limit_number'] = new textField('limit_number', $I18N_10->msg('label_limit_number'));
    $fields['limit_number']->setNote($I18N_10->msg('note_limit_number'));
    $fields['limit_number']->addAttribute('style', 'width: 80px;');
    $fields['limit_number']->addValidator('isNumber', $I18N_10->msg("err_limit_number"), true, true);

    $fields['automate'] = new checkboxField('automate', '&nbsp;',  array('style' => 'width: auto;'));
    $fields['automate']->addBox($I18N_10->msg('label_automate_import'), '1');  
    $fields['automate']->setValue('1');  
    
    $fields['button'] = new buttonField();
    $fields['button']->addButton('cjoform_import_button',$I18N_10->msg('button_import'), true, 'img/silk_icons/database_go.png');

    //Add Fields:
    $section= new cjoFormSection('', $I18N_10->msg('section_import'), array());

    $section->addFields($fields);
    $form->addSection($section);

    if ($form->validate()) {
        if (!empty($_FILES['userfile']['tmp_name']) || 
            !empty($_FILES['userfile']['name'])) {
            cjoCommunityImportExport::import();
        } else {
            cjoMessage::addError($I18N_10->msg('file_not_found'));
            $fields['import']->addAttribute('class', 'invalid', 'join');
        }
    }

    $form->show(false);

// EXPORT

    $form = new cjoForm($mypage.'_'.$subpage.'_export');
    $form->setEditMode(false);
    $form->setEnctype('multipart/form-data');
    
	$fields['groups'] = new readOnlyField('groups[]', $I18N_10->msg('label_groups'));
	$fields['groups']->setValue($sel_group->get());
	$fields['groups']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_groups'));

    $fields['clang'] = new selectField('clang', $I18N_10->msg('label_language'));
    $qry = "SELECT name, id FROM ".TBL_CLANGS." ORDER BY id";
    $fields['clang']->addSQLOptions($qry);
    $fields['clang']->addAttribute('size', '1');
    $fields['clang']->addAttribute('style', 'width: 130px;');
    $fields['clang']->setValue($CJO['CUR_CLANG']);
    $fields['clang']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_lang'), false, false);
    
    $fields['limit'] = new selectField('limit', $I18N_10->msg('label_limit_user_export'));
    $fields['limit']->addOption($I18N_10->msg('label_all_users'), 0);    
    $fields['limit']->addOption($I18N_10->msg('label_online_users'), 1);
    $fields['limit']->addOption($I18N_10->msg('label_offline_users'), 2);
    $fields['limit']->addOption($I18N_10->msg('label_bounced_users'), 3);
    $fields['limit']->addOption($I18N_10->msg('label_not_activated_users'), 4);
    $fields['limit']->addAttribute('size', '1');
    $fields['limit']->addAttribute('style', 'width: 130px;');

    $fields['button'] = new buttonField();
    $fields['button']->addButton('cjoform_export_button',$I18N_10->msg('button_export'), true, 'img/silk_icons/disk.png');

    //Add Fields:
    $section2= new cjoFormSection('', $I18N_10->msg('section_export'), array());
    $section2->addFields($fields);    
    $form->addSection($section2);
    
    if ($form->validate()) {
        cjoCommunityImportExport::export();
    }

      //Show Form
    $form->show(false);


// DELETE ALL

    if ($CJO['USER']->isAdmin()){

        $form = new cjoForm($mypage.'_'.$subpage.'_delete');
        $form->setEditMode(false);
        $form->setEnctype('multipart/form-data');
        $form->debug = false;

        $fields['button'] = new buttonField();
        $fields['button']->addButton('cjoform_delete_all_button',$I18N_10->msg('delete_all'), true, 'img/silk_icons/bin.png');
        $fields['button']->setButtonAttributes('cjoform_delete_all_button', 'class="confirm red"');

        //Add Fields:
        $section3= new cjoFormSection('', $I18N_10->msg('section_delete_all'), array());
        $section3->addField($fields['button']);
        $form->addSection($section3);

        $form->show(false);
    }
?>
    
<script type="text/javascript">
/* <![CDATA[ */

$(function(){
    $('form[name^="<?php echo $mypage.'_'.$subpage; ?>"]').submit(function(){
        $(this).block({ message: null })
        var form =  $(this);
        form.block({ message: null }); 
        $('button').attr('disabled','disabled');        
    });
});

function cm_automateScript(form, url) {

	if (!url.match(/\bfinished\b/)) {
		form.block({ message: null }); 
    	$('button').attr('disabled','disabled');
	}
	else {
		form.unblock(); 
    	$('button').removeAttr('disabled');
	}
    setTimeout(function(){ location.href = url },200);
}
/* ]]> */
</script>