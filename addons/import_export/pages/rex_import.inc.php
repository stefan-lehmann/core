<?php

//SLICES SAUBER LÖSCHEN

//$sql = new sql;
//$sql->setQuery("SELECT * FROM ".TBL_ARTICLES_SLICE." WHERE ctype='1'");
//$results = $sql->get_array();
//
////cjo_debug($results, count($results));
//
//foreach($results as $result){
//	$slice_id = $result['id'];
//	$re_id = $result['re_article_slice_id'];
//
//	$sql->flush();
//	$sql->setQuery("SELECT * FROM ".TBL_ARTICLES_SLICE." WHERE re_article_slice_id='".$slice_id."'");
//	if ($sql->getRows()>0){
//		$update = new sql;
//		$update->setTable(TBL_ARTICLES_SLICE);
//		$update->where("id='".$sql->getValue("id")."'");
//		$update->setValue("re_article_slice_id",$re_id);
//		$update->update();
//		cjo_debug($update,$slice_id,'lightgreen');
//	}
//	$sql->flush();
//	$query = "DELETE FROM ".TBL_ARTICLES_SLICE." WHERE id='".$slice_id."'";
//	$sql->setQuery($query);
//	cjo_debug($sql,$query);
//	$message['accept'][] = $I18N->msg('msg_block_deleted');
//}

// ARTIKEL_EINSTELLUNG AUF ANDERE SPRACHEN MAPPEN
//$sql = new sql;
//$sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE clang='0'");
//cjo_debug($sql);
//for ($i = 0; $i < $sql->getRows(); $i++){
//	$update = new sql;
//	$update->setTable(TBL_ARTICLES);
//	$update->where("id='".$sql->getValue("id")."' AND clang='1'");
//	$update->setValue("teaser",$sql->getValue("teaser"));
//	$update->setValue("navi_item",$sql->getValue("navi_item"));
//	$update->setValue("online_from",$sql->getValue("online_from"));
//	$update->setValue("online_to",$sql->getValue("online_to"));
//	$update->setValue("status",$sql->getValue("status"));
//	$update->setValue("prior",$sql->getValue("prior"));
//	$update->setValue("catprior",$sql->getValue("catprior"));
//	$update->setValue("cat_group",$sql->getValue("cat_group"));
//	$update->setValue("template_id",$sql->getValue("template_id"));
//	$update->update();
//cjo_debug($update);
//	$sql->next();
//}



if ($_POST['cjo_form_name'] != $mypage.'_'.$subpage.'_form'){
	$dataset = $CJO['ADDON']['settings'][$mypage];
}
else{
	$dataset = $_POST;
}

//Form
$form = new cjoForm($mypage.'_'.$subpage.'_form');
$form->setEditMode(false);
//$form->debug = true;

$fields['FILES'] = new textField('FILES', 'TEMP-FOLDER');
$fields['FILES']->addValidator('notEmpty', 'Temp-Folder is empty');

$fields['START'] = new textField('START', 'IMPORT-START');
$fields['NUMBER'] = new textField('NUMBER', 'IMPORT-LIMIT');

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_update_button',"START IMPORT", true, 'img/silk_icons/database_go.png');


//Add Fields
$section = new cjoFormSection('', 'REX-Import', array());
$section->dataset = $dataset;

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()){

	$config_file = $CJO['ADDON']['settings'][$mypage]['settings'];
	$config_data = file_get_contents($config_file);

	if (empty($messege['error'])){
		foreach($_POST as $key=>$value){
			if (is_array($value)) $value = implode('|',$value);
			$pattern = "!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['".$key."'\].?\=.?)[^;]*!";

			$config_data = preg_replace($pattern,"\\1\"".$value."\"",$config_data);
		}
		if (cjoGenerate::replaceFileContents($config_file, $config_data)){

			include $config_file;
			include $CJO['ADDON_PATH'].'/'.$mypage.'/functions/rex_import_write_data.php';

			cjoMessage::addSuccess('Done REX-Import get data!');
		}
	}
}
$form->show(false);
