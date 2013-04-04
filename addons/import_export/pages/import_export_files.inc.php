<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  import_export
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

// Für größere Exports den Speicher für PHP erhöhen.

ini_set('memory_limit', '100M');
ini_set('max_execution_time', 300);

if (cjo_request('impname', 'bool')) {

	$impname = str_replace("/", "", $impname);

	if ($function == "dbimport" &&
	    substr($impname, -4, 4) != ".sql") {
	    unset($impname);
	}
	if ($function == "fileimport" &&
	    substr($impname, -7, 7) != ".tar.gz") {
	    unset($impname);
	}
}

if (!cjo_request('expname', 'bool')) {
	$expname = cjo_specialchars('cjo_'.$CJO['VERSION'].'_'.$CJO['SERVERNAME']).'_'.date("Y-m-d");
}
if ($function == "delete") {
	// ------------------------------ FUNC DELETE
	if (@unlink($CJO['ADDON']['settings'][$mypage]['folder']."/".$impname));
	cjoMessage::addSuccess(cjoAddon::translate(3,"msg_file_deleted", $impname));

}
elseif ($function == "dbimport") {

	if (isset ($_FILES['FORM']) &&
	    $_FILES['FORM']['size']['importfile'] < 1 &&
	    $impname == "") {
		cjoMessage::addError(cjoAddon::translate(3,"err_no_import_file_chosen_or_wrong_version"));
	}
	else {
		if ($impname != "") {
			$file_temp = $CJO['ADDON']['settings'][$mypage]['folder']."/".$impname;
		}
		else  {
			$file_temp = $CJO['ADDON']['settings'][$mypage]['folder']."/sql.temp";
		}

		if ($impname != "" ||
		    @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {

		    cjoImportExport::importSqlFile($file_temp);
			// temp datei löschen
			if ($impname == "") @unlink($file_temp);
		}
		else {
			cjoMessage::addError(cjoAddon::translate(3,"err_file_could_not_be_uploaded")." ".
			                     cjoAddon::translate(3,"err_you_have_no_write_permission_in",
			                     $CJO['ADDON']['settings'][$mypage]['folder']));
		}
	}
}
elseif ($function == "fileimport") {
	if (isset($_FILES['FORM']) &&
	    $_FILES['FORM']['size']['importfile'] < 1 &&
	    $impname == "") {
		cjoMessage::addError(cjoAddon::translate(3,"err_no_import_file_chosen"));
	}
	else {

		if ($impname != "") {
			$file_temp = $CJO['ADDON']['settings'][$mypage]['folder']."/".$impname;
		}
		else {
			$file_temp = $CJO['ADDON']['settings'][$mypage]['folder']."/tar.temp";
		}

		if ($impname != "" ||
		    @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {

			cjoImportExport::importTarFile($file_temp);
			// temp datei löschen
			if ($impname == "") @unlink($file_temp);
		}
		else {
			cjoMessage::addError( cjoAddon::translate(3,"err_file_could_not_be_uploaded")." ".
			                      cjoAddon::translate(3,"err_you_have_no_write_permission_in",
			                      $CJO['ADDON']['settings'][$mypage]['folder']));
		}
	}
}
elseif ($function == "export") {


	$expname = stripslashes($expname);
	$filename = preg_replace("/[^\.a-z0-9_\-]/", "", $expname);

	if ($filename != $expname){
		cjoMessage::addSuccess(cjoAddon::translate(3,"msg_filename_updated"));
		$expname = $filename;
	}
	else {
		$content = "";
		if ($exptype == "sql")  {
			$header = "plain/text";
			$ext = ".sql";
			$content = cjoImportExport::generateSqlExport();
		}
		elseif ($exptype == "files")  {

			$header = "tar/gzip";
			$ext = ".tar.gz";

			if ($expdirs == "") {
				cjoMessage::addError(cjoAddon::translate(3,"err_please_choose_folder"));
			}
			else {
				$content = cjoImportExport::generateTarExport($expdirs, $filename);
			}
		}

		if ($content != "" && cjo_post('expdown','bool')){
			$filename = $filename.$ext;
			ob_end_clean();
			header("Content-type: ".$header);
			header("Content-Disposition: attachment; filename=".$filename);
			echo $content;
			exit;
		}
		elseif ($content != ""){
			// check filename ob vorhanden
			// aendern filename
			// speicher content in files

			$dir_filename = $CJO['ADDON']['settings'][$mypage]['folder']."/";
			$filename = $dir_filename.$filename;

			if (file_exists($filename.$ext)){
				for ($i = 0; $i < 1000; $i++){
					if (!file_exists($filename."__".$i.$ext)){
						$filename = $filename."__".$i.$ext;
						break;
					}
				}
			}
			else {
				$filename .= $ext;
			}

			if (is_writable($dir_filename) && file_put_contents($filename, $content)) {
				@chmod($filename, cjoProp::getFilePerm());
				cjoMessage::addSuccess(cjoAddon::translate(3,'msg_file_generated_in', $filename));
			}
			else {
				cjoMessage::addError( cjoAddon::translate(3,'err_file_could_not_be_generated').' '.
				                      cjoAddon::translate(3,'err_check_rights_in_directory', $dir_filename));
			}
		}
	}
}

$sql_table = '';
$tar_table = '';
$sub_table = '';

$dir = cjoImportExport::getImportDir();
$folder = cjoImportExport::readImportFolder(".sql");

if (count($folder) > 0) {

	foreach ($folder as $file) {

		preg_match('/(.*)_([0-9]{4}\-[0-1][0-9]\-[0-3][0-9][\._].*)$/', $file, $file_name);
		$file_name = (strlen($file_name[1]) > 17) ? substr($file_name[1],0, 15).'...'.$file_name[2] : $file;

		$filepath = $dir.'/'.$file;
		$filec = date("d.m.y H:i", filemtime($filepath));
		$sql_table .= '<tr>'.
					  '	<td><img src="img/silk_icons/page_white_database.png" alt="" /> <b title="'.$file.'">'.$file_name.'</b></td>'.
					  '	<td>'.$filec.'</td>'.
					  '	<td class="icon">'.
					  '		<a href="index.php?page='.$mypage.'&function=dbimport&impname='.$file.'" '.
					  '		   title="'.cjoAddon::translate(3,'label_import_sql', $file).'<br/><br/>'.cjoAddon::translate(3,'msg_proceed_sql_import').'" class="cjo_confirm">'.
					  '			 <img src="img/silk_icons/database_go.png" alt="'.cjoAddon::translate(3,'button_import').'" '.
					  '				  title="'.cjoAddon::translate(3,'label_import_sql', $file).'" />'.
					  '		</a>'.
					  '	</td>'.
					  '	<td class="icon">'.
					  '		<a href="index.php?page='.$mypage.'&function=delete&impname='.$file.'" '.
					  '		   title="'.cjoAddon::translate(3,'label_delete_sql', $file).'" class="cjo_confirm">'.
					  '			<img src="img/silk_icons/bin.png" alt="'.cjoAddon::translate(3,"button_delete").'" '.
					  '		         title="'.cjoAddon::translate(3,"label_delete_sql", $file).'" />'.
					  '		</a>'.
					  '	</td>'.
					  '</tr>';
	}
}
else {
		$sql_table  = '<tr>'.
					  '	<td><img src="img/silk_icons/page_white_database.png" alt="" /> <b>--</b></td>'.
					  '	<td>--</td>'.
					  '	<td class="icon">&nbsp;</td>'.
					  '	<td class="icon">&nbsp;</td>'.
					  '</tr>';
}
$dir = cjoImportExport::getImportDir();
$folder = cjoImportExport::readImportFolder(".tar.gz");

if(count($folder) > 0) {

	foreach ($folder as $file) {

		$filepath = $dir.'/'.$file;
		$filec = date("d.m.y H:i", filemtime($filepath));

		preg_match('/(.*)_([0-9]{4}\-[0-1][0-9]\-[0-3][0-9][\._].*)$/', $file, $file_name);
		$file_name = (strlen($file_name[1]) > 17) ? substr($file_name[1],0, 15).'...'.$file_name[2] : $file;

		$tar_table .= '<tr>'.
					  '	<td><img src="img/silk_icons/page_white_zip.png" alt="" /> <b title="'.$file.'">'.$file_name.'</b></td>'.
					  '	<td>'.$filec.'</td>'.
					  '	<td class="icon">'.
					  '		<a href="index.php?page='.$mypage.'&function=fileimport&impname='.$file.'" '.
					  '		   title="'.cjoAddon::translate(3,'label_import_tar', $file).'" class="cjo_confirm">'.
					  '			 <img src="img/silk_icons/page_white_go.png" alt="'.cjoAddon::translate(3,'button_import').'" '.
					  '				  title="'.cjoAddon::translate(3,'label_import_tar', $file).'" />'.
					  '		</a>'.
					  '	</td>'.
					  '	<td class="icon">'.
					  '		<a href="index.php?page='.$mypage.'&function=delete&impname='.$file.'"'.
					  '	       title="'.cjoAddon::translate(3,'label_delete_tar', $file).'<br/><br/>'.cjoAddon::translate(3,'msg_proceed_tar_import').'" class="cjo_confirm">'.
					  '			<img src="img/silk_icons/bin.png" alt="'.cjoAddon::translate(3,"button_delete").'" '.
					  '				 title="'.cjoAddon::translate(3,"label_delete_tar", $file).'" />'.
					  '		</a>'.
					  '	</td>'.
					  '</tr>';
	}
}
else {
		$tar_table  = '<tr>'.
					  '	<td><img src="img/silk_icons/page_white_zip.png" alt="" /> <b>--</b></td>'.
					  '	<td>--</td>'.
					  '	<td class="icon">&nbsp;</td>'.
					  '	<td class="icon">&nbsp;</td>'.
					  '</tr>';
}

$dir = $CJO['INCLUDE_PATH']."/../../";
$folders = cjoImportExport::readSubFolders($dir);

foreach ($folders as $file) {

	$checked = '';
	if ($expdirs != '')
		$checked = cjoAssistance::setChecked($exptype, array_keys(cjoAssistance::toArray($expdirs)));

	if ($file == 'contejo') continue;
	$sub_table .= '<input type="checkbox" id="expdirs_'.$file.'" name="expdirs['.$file.']" '.
				  '	  value="true"'.$checked.'/>&nbsp; '.
				  '<label for="expdirs_'.$file.'">'.$file.'</label><br />';
}

$buttons = new buttonField();
$buttons->addButton('cjoform_submit_button',cjoAddon::translate(3,'label_start_export'), true, 'img/silk_icons/disk.png');
$buttons->setButtonAttributes('cjoform_submit_button','style="margin: 10px 0 10px 28px;"');



echo '<div class="a22-cjolist">'.
     '	<div class="a22-cjolist-data">'.
	 '		<table class="cjo no_hover" cellspacing="0" cellpadding="0" border="0">'.
 	 '      <thead>'.
	 '		<tr>'.
	 '			<th>'.cjoAddon::translate(3,'label_import').'</th>'.
	 '			<th>'.cjoAddon::translate(3,'label_export').'</th>'.
	 '		</tr>'.
 	 '      </thead>'.
 	 '      <tbody>'.
	 '		<tr>'.
	 '			<td valign="top" width="50%">'.
     '				<p>'.cjoAddon::translate(3,"msg_intro_import").'</p>'.
     '				<form action="index.php" name="import1" method="post" enctype="multipart/form-data">'.
     '					<input type="hidden" name="page" value="'.$mypage.'" />'.
     '					<table cellspacing="0" cellpadding="0" border="0">'.
 	 '       			<thead>'.
     '						<tr><th align="left" colspan="2">'.cjoAddon::translate(3,"label_database").'</th></tr>'.
 	 '      			</thead>'.
 	 '      			<tbody>'.
     '						<tr>'.
     '							<td><input type="file" name="FORM[importfile]" size="55" /></td>'.
     '							<td class="icon">'.
     '								<input type="image" class="cjo_confirm" '.
     '									   name="function" value="dbimport" '.
     '									   title="'.cjoAddon::translate(3,"label_selected_file").' '.cjoAddon::translate(3,'label_import_sql').'" '.
     '									   src="img/silk_icons/database_go.png" '.
     '									   alt="'.cjoAddon::translate(3,'label_import_sql').'" />'.
     '							</td>'.
     '							</tr>'.
 	 '      			</tbody>'.
     '				</table>'.
     '				</form>'.
	 '				<table cellspacing="0" cellpadding="0" border="0">'.
 	 '       		<thead>'.
	 '		 		<tr>'.
	 '    				<th align="left">'.cjoAddon::translate(3,"label_filename").'</th>'.
	 '    				<th width="110">'.cjoAddon::translate(3,"label_createdate").'</th>'.
	 '    				<th width="60" colspan="2">'.cjoI18N::translate("label_functions").'</th>'.
	 '  			</tr>'.
 	 '      		</thead>'.
 	 '      		<tbody>'.
 	 '      		'.$sql_table.
 	 '      		</tbody>'.
 	 '      		</table>'.
 	 '      		<br/><br/>'.
     '				<form action="index.php" name="import1" method="post" enctype="multipart/form-data">'.
     '					<input type="hidden" name="page" value="'.$mypage.'" />'.
     '					<table cellspacing="0" cellpadding="0" border="0">'.
 	 '       			<thead>'.
     '						<tr><th align="left" colspan="2">'.cjoAddon::translate(3,"label_files").'</th></tr>'.
 	 '      			</thead>'.
 	 '      			<tbody>'.
     '						<tr>'.
     '							<td><input type="file" name="FORM[importfile]" size="55" /></td>'.
     '							<td class="icon">'.
     '								<input type="image" class="cjo_confirm" '.
     '									   name="function" value="fileimport" '.
     '									   title="'.cjoAddon::translate(3,"label_selected_file").' '.cjoAddon::translate(3,'label_import_tar').'" '.
     '									   src="img/silk_icons/page_white_go.png" '.
     '									   alt="'.cjoAddon::translate(3,'label_import_tar').'" />'.
     '							</td>'.
     '							</tr>'.
 	 '      			</tbody>'.
     '				</table>'.
     '				</form>'.
	 '				<table cellspacing="0" cellpadding="0" border="0">'.
 	 '       		<thead>'.
	 '		 			<tr>'.
	 '    					<th align="left">'.cjoAddon::translate(3,"label_filename").'</th>'.
	 '    					<th width="110">'.cjoAddon::translate(3,"label_createdate").'</th>'.
	 '    					<th width="60" colspan="2">'.cjoI18N::translate("label_functions").'</th>'.
	 '  				</tr>'.
 	 '      		</thead>'.
 	 '      		<tbody>'.
 	 '      		'.$tar_table.
 	 '      		</tbody>'.
 	 '      		</table>'.
	 '			</td>'.
	 '			<td valign="top" width="50%">'.
     '				<p>'.cjoAddon::translate(3,"label_intro_export").'</p>'.
	 '				<form action="index.php" method="post" enctype="multipart/form-data">'.
	 '				<input type="hidden" name="page" value="'.$mypage.'" />'.
	 '				<input type="hidden" name="function" value="export" />'.
	 '				<table  cellspacing="0" cellpadding="0" border="0">'.
 	 '       		<thead>'.
     '					<tr><th align="left" colspan="2">'.cjoAddon::translate(3,"label_export_settings").'</th></tr>'.
 	 '      		</thead>'.
 	 '      		<tbody>'.
	 '				<tr>'.
	 '					<td width="10"><input type="radio" id="exptype_sql" name="exptype" value="sql"'.cjoAssistance::setChecked($exptype, array('files'), false).'/></td>'.
	 '					<td><label for="exptype_sql">'.cjoAddon::translate(3,"label_database_export").'</label></td>'.
	 '				</tr>'.
	 '				<tr>'.
	 '					<td><input type="radio" id="exptype_files" name="exptype" value="files"'.cjoAssistance::setChecked($exptype, array('files')).'/></td>'.
	 '					<td><label for="exptype_files">'.cjoAddon::translate(3,"label_file_export").'</label></td>'.
	 '				</tr>'.
	 '				<tr>'.
	 '					<td>&nbsp;</td>'.
	 '					<td>'.$sub_table.'</td>'.
	 '				</tr>'.
	 '				<tr>'.
	 '					<td><input type="radio" id="expdown_server" name="expdown" value="0"'.cjoAssistance::setChecked($expdown, array(1),false).'/></td>'.
	 '					<td><label for="expdown_server">'.cjoAddon::translate(3,'label_save_on_server').'</label></td>'.
	 '				</tr>'.
	 '				<tr>'.
	 '					<td><input type="radio" id="expdown_download" name="expdown" value="1"'.cjoAssistance::setChecked($exptype, array(1)).'/></td>'.
	 '					<td><label for="expdown_download">'.cjoAddon::translate(3,'label_save_as_file').'</label></td>'.
	 '				</tr>'.
	 '				<tr>'.
	 '					<td></td>'.
	 '					<td><input type="text" size="20" name="expname" class="inp100" value="'.$expname.'" /></td>'.
	 '				</tr>'.
 	 '      		</tbody>'.
	 '				</table>'.
	 '				'.$buttons->_get().
	 '				</form>'.
	 '			</td>'.
	 '		</tr>'.
	 '  	</tbody>'.
	 '		</table>'.
	 '	</div>'.
	 '</div>';
?>
<script type="text/javascript">
/* <![CDATA[ */

 $('input[id^="expdirs_"]').click(function() {
 	$('#exptype_sql').removeAttr('checked');
 	$('#exptype_files').attr('checked', 'checked');
 });

  $('#exptype_sql').click(function() {
 	$('input[id^="expdirs_"]').removeAttr('checked');
 });

/* ]]> */
</script>