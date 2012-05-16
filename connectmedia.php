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
 * @version     2.6.0
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

error_reporting(E_ALL ^ E_NOTICE);
// ----- caching start für output filter

ob_start();

$CJO                = array();
$CJO['HTDOCS_PATH'] = '../';
$CJO['CONTEJO']     = true;
$cur_page           = array();

require_once "./include/functions/function.cjo_mquotes.inc.php";
require_once 'include/master.inc.php';

$mode           = cjo_request('mode', 'string', '');
$filename       = cjo_request('filename', 'string', '');
$media_category = cjo_request('media_category','int', cjo_session('MEDIA_CATEGORY'));
$filenames      = cjoAssistance::toArray($filename, ',');
$media          = '';
$cjo_tbx_cur_id = '';

if (!empty($filenames[0]) && !isset($_GET['media_category'])){
    $media_obj = OOMedia::getMediaByFileName($filenames[0]);

    if (OOMedia::isValid($media_obj)) {
        $media_category = $media_obj->getCategoryId();
    }
}
new cjoSelectMediaCat();
$CJO['SEL_MEDIA']->setName("category_id");
$CJO['SEL_MEDIA']->setStyle("width:760px;");

$CJO['SEL_MEDIA']->setSelected($media_category);
$CJO['SEL_MEDIA']->setSelectedPath(OOMediaCategory::getPath($media_category));

ob_end_clean();

$media_objs = OOMediaCategory::getFilesOfCategory($media_category, 'filename');

foreach($media_objs as $val=>$media_obj) {

	preg_match('/(?<=src=\").*(?=\")/i',OOMedia::toIcon($media_obj->getFileName()),$iconpath);

	$size = (OOMedia::isImage($media_obj->getFileName())) ? $media_obj->getWidth().' &times; '.$media_obj->getHeight().' px' : '';

	$rel =  $iconpath[0].'|'.
			$media_obj->getTitle().'|'.
			$media_obj->getFileName().'|'.
			$media_obj->getType().'|'.
			$media_obj->_getFormattedSize().'|'.
			$size.'|'.
			$media_obj->getCreateDate($I18N->msg("datetimeformat")).'|'.
			$media_obj->getCreateUser().'|'.
			$media_obj->getUpdateDate($I18N->msg("datetimeformat")).'|'.
			$media_obj->getUpdateUser();

	$params = array('width'=>80,'height'=>80,'rel'=>$rel,'title'=>$media_obj->getTitle());

	if (in_array($media_obj->getFileName(), $filenames)) $params['css'] = ' selected';

	$media .= cjoMedia::getMediaContainer($media_obj->getFileName(), $media_obj->getFullPath(), false, $params);
}

if ($media == '') $media = '<div class="warning centered">'.$I18N->msg('msg_media_in_this_category').'</div>';

$button = new popupButtonField();
$button->addButton($I18N->msg('button_close'), '$.fancybox.close(); return false;', 'img/silk_icons/cross_sw.png', 'class="small" id="cjo_connectmedia_close"');

$url = 'connectmedia.php?mode='.$mode.'&filename='.$filename;
?>

<div id="cjo_connectmedia_top"><?php
	echo $CJO['SEL_MEDIA']->get(true);
	echo $button->getButtons();
?></div>
<div id="cjo_connectmedia_middle">
	<div class="container"><?php echo $media; ?></div>
</div>
<div id="cjo_connectmedia_bottom"><img src="img/mime_icons/default.png" id="cjo_connectmedia_icon" />
<div id="cjo_connectmedia_details">
<h3></h3>
<span
	style="background-image: url(img/mini_icons/page_white.png); clear: left;"
	title="<?php echo $I18N->msg('label_filename'); ?>"></span> <span
	style="background-image: url(img/mini_icons/flag_blue.png)"
	title="<?php echo $I18N->msg('label_filetype'); ?>"></span> <span
	style="background-image: url(img/mini_icons/drive.png)"
	title="<?php echo $I18N->msg('label_filesize'); ?>"></span> <span
	style="background-image: url(img/mini_icons/shape_handles.png)"
	title="<?php echo $I18N->msg('label_filesize'); ?>"></span> <span
	style="background-image: url(img/mini_icons/time.png); clear: left;"
	title="<?php echo $I18N->msg('label_createdate'); ?>"></span> <span
	style="background-image: url(img/mini_icons/user.png)"
	title="<?php echo $I18N->msg('label_createuser'); ?>"></span> <span
	style="background-image: url(img/mini_icons/time.png)"
	title="<?php echo $I18N->msg('label_updatedate'); ?>"></span> <span
	style="background-image: url(img/mini_icons/user.png)"
	title="<?php echo $I18N->msg('label_updateuser'); ?>"></span></div>
</div>

<script type="text/javascript">
/* <![CDATA[ */

	$('#category_id').selectpath({
			action : { root   		: "$.fancybox({'autoScale':false,'transitionIn':'none','transitionOut':'none','width':580,'height':860,'href':'<?php echo $url; ?>&media_category=0','type':'ajax'});",
					   categories 	: "$.fancybox({'autoScale':false,'transitionIn':'none','transitionOut':'none','width':580,'height':860,'href':'<?php echo $url; ?>&media_category='+id,'type':'ajax'});",
					   category     : "$.fancybox({'autoScale':false,'transitionIn':'none','transitionOut':'none','width':580,'height':860,'href':'<?php echo $url; ?>&media_category='+id,'type':'ajax'});"
	                },
	        types  : {root	 		: 'root',
                      folder 		: 'categories',
                      file	 		: 'category',
                      folder_locked : 'categories',
                      file_locked 	: 'category'
                    }
	});

	var cjo_come_detail = $('#cjo_connectmedia_bottom').children();
	var cjo_come_cur_id = '<?php echo $cjo_tbx_cur_id; ?>';
	var cjo_come_cur	= '';
	var cjo_come_all_ic = $('#cjo_connectmedia_middle .cjo_image_container');

	cjo_come_all_ic.find('span input').hide();

	if(cjo_come_cur_id != ''){
		cjo_come_cur = $('#'+cjo_come_cur_id);
		cjo_come_cur.addClass('selected');
		cjo_come_ic_hover(cjo_come_cur);
	}

	cjo_come_all_ic.bind('mouseover',function(){
		cjo_come_ic_hover($(this));
	});

	cjo_come_all_ic.bind('mouseout',function(){

		if(cjo_come_cur_id != ''){
			cjo_come_ic_hover(cjo_come_cur);
		}
		else{
			cjo_come_detail.hide();
		}
	});

	cjo_come_all_ic.bind('click',function(){
		cjo_come_ic_select($(this), '<?php echo $mode; ?>');
		$(this).addClass('selected');
	});

	function cjo_come_ic_select(el,mode){

		var filename = el.attr('id').replace(/_DOT_/,'.');

		if (mode == 'button'){
			$('#'+curr_media_button).val(filename);
			$.fancybox.close();
		}
		else if (mode == 'list'){
			$('#'+curr_media_button).append('<option value="'+filename+'">'+filename+'</option>');
			cjo.writeMediaList(curr_media_button, ',');
		}
	}

	function cjo_come_ic_hover(el){

		 if(el == '') return false;

		 var attr = el.find('img').attr('alt').split('|');

		 $('#cjo_connectmedia_icon').attr('src',el.find('img').attr('rel'));

		 $('#cjo_connectmedia_details').children().each(function(i){

			 if(attr[(i+1)] != ''){
			 	$(this).text(attr[(i+1)]).show();
			 }
			 else{
			 	$(this).hide();
			 }
		 });
		 cjo_come_detail.show();
	}
/* ]]> */
</script>