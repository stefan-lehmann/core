<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  image_processor
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

$media_obj = OOMedia::getMediaById($oid);
$file 	  = $media_obj->getFileName();
$size 	  = @getimagesize($media_obj->getFullPath());
$max 	  = 560;
$default  = imageProcessor_initCropValues($size);
$ac 	  = '';
$croplist = '';

$sql = new cjoSql();
$qry = "SELECT * FROM ".TBL_IMG_CROP." WHERE status != '0' ORDER BY id";
$sets = $sql->getArray($qry);

if (!is_array($sets)) return false;

if ($CJO['USER']->hasMediaPerm()){

	foreach($sets as $key=>$set){
			$crop_val = ($media_obj->_crop[$set['id']] != '') ? $media_obj->_crop[$set['id']] : $default[$set['id']];
			$set_crop = explode("|", $crop_val);

			$ac .= 	'"crop_'.$set['id'].'": {'."\r\n";
			$ac .= 	'   	setSelect:	 [ '.$set_crop[1].','.$set_crop[2].','.($set_crop[1]+$set_crop[3]).','.($set_crop[2]+$set_crop[4]).' ],'."\r\n";

		if(!empty($set["width"]) || !empty($set["height"]))
			$ac .= 	'	minSize:	 [ '.$set["width"].','.$set["height"].' ],'."\r\n";
		if($set['aspectratio'] && !empty($set['width']) && !empty($set['height'])){
			$ac .= 	'	aspectRatio: '.$set["width"].'/'.$set["height"]."\r\n";
		}
		else{
			$ac .= 	'	aspectRatio: 0'."\r\n";
		}
			$ac .= 	'	},'."\r\n";
	}

	$ac = substr($ac, 0, -3);
	$cropbox =  '	<img src="'.$media_obj->getFullPath().'" width="'.$size[0].'" height="'.$size[1].'" id="cropimg" class="hide_me" alt="" />'."\r\n".
				'	<h3></h3>'."\r\n".
				'	<div id="cropcords">'."\r\n".
				'		<strong>X1:</strong> <span id="x"></span>'."\r\n".
				'		<strong>Y1:</strong> <span id="y"></span>'."\r\n".
				'		<strong>X2:</strong> <span id="x2"></span>'."\r\n".
				'		<strong>Y2:</strong> <span id="y2"></span>'."\r\n".
				'		<strong>W:</strong> <span id="w"></span>'."\r\n".
				'		<strong>H:</strong> <span id="h"></span>'."\r\n".
				'	</div>'."\r\n".
				' 	<button id="crop_reset" title="'.$I18N_8->msg('button_crop_reset').'" class="cjo_form_button">'."\r\n".
				' 		<img src="img/silk_icons/bin.png" alt="R" title="'.$I18N_8->msg('button_crop_reset').'" />'."\r\n".
				' 	</button>'."\r\n";

	$cropbox =  '<div id="cropbox" class="jc_coords">'.$cropbox.'</div>';

	$cropbuttons = 	'	<div class="cropbuttons">'."\r\n".
					' 		<button title="'.$I18N->msg('button_update').'" class="cjo_form_button crop_save small">'."\r\n".
					' 			<img src="img/silk_icons/tick.png" alt="OK" />'."\r\n".
					' 		</button>'."\r\n".
					' 		<button title="'.$I18N->msg('button_cancel').'" class="cjo_form_button crop_cancel small">'."\r\n".
					' 			<img src="img/silk_icons/cancel.png" alt="X" title="'.$I18N->msg('button_cancel').'" />'."\r\n".
					' 		</button>'."\r\n".
					'	</div>'."\r\n";

}

foreach ($sets as $key=>$set)
{
	$b = $set['id'];
	$msg = '';
	$css = '';
	$img = '';

	if ($set['width'] <= $size[0] && $set['height'] <= $size[1])
	{
		if ($media_obj->_crop[$b] == '')
			$locked = '<img src="img/mini_icons/error.png" title="'.$I18N_8->msg("msg_no_croping_set").'" alt="" /> ';

		$crop_item = explode('|', $media_obj->_crop[$b]);
		$img = imageProcessor_getImg($media_obj->getFileName(),
									 200,
									 200,
									 $resize=null,
									 $aspectratio=null,
									 'brand_on_off=0',
									 $brandimg=null,
									 'jpg_quality=40',
									 $crop_item[1],
									 $crop_item[2],
									 $crop_item[3],
									 $crop_item[4]);

		if ($CJO['USER']->hasMediaPerm()){
			$img = '<img src="'.$img.'" title="'.$I18N_8->msg('label_edit_crop').'" id="crop_img_'.$set['id'].'" alt="" />';
		}
		else{
			$img = '<img src="'.$img.'" title="'.$I18N->msg("msg_no_permissions").'" alt="" />';
			$css = ' locked';
		}
	}
	else {
		$img = '<p class="warning">'.$I18N_8->msg("msg_no_resize_img_to_small").'</p>';
		$css = ' locked';
	}

	$croplist .= '<div id="crop_'.$set['id'].'" class="container'.$css.'">'."\r\n".
			   	'	<h4>'.$locked.$set['name'].' ('.$set['width'].' &times; '.$set['height'].')</h4>'."\r\n".
			   	'	'.$img."\r\n".
			   	'	'.$cropbuttons."\r\n".
			   	'</div>'."\r\n";
}
$croplist = '<h2 id="cropheadline">'."\r\n".
			'	'.$I18N_8->msg("label_crop_function")."\r\n".
			'</h2>'."\r\n".
			'<div id="croplist" class="hide_me">'.$croplist.'&nbsp;</div>';

cjoExtension::registerExtension('OUTPUT_FILTER', 'cjo_insert_cropping');

function cjo_insert_cropping($params) {
    global $cropbox, $croplist;
    return preg_replace('/<div([^>]*)class="a22-container([^>]*)>/i','$0'.$cropbox.$croplist,$params['subject'],1);
}

?>
<script src="<?php echo $CJO['HTDOCS_PATH']; ?>core/js/Jcrop/js/jquery.Jcrop.js" type="text/javascript"></script>

<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('#cropimg').removeClass('hide_me');

		var _boxWidth  = $('#cropbox').width()-265;
		var _boxHeight = $('#cropbox').height()-41;
		var cur_crop   = false;

		if(_boxWidth == undefined || _boxWidth < 1)  _boxWidth = 695;
		if(_boxHeight == undefined || _boxHeight < 1) _boxHeight = 472;

		var $jcrop = $.Jcrop('#cropimg', {onChange: 		cjo_show_coords,
										  onSelect: 		cjo_show_coords,
										  setSelect: 		[ 0, 0, <?php echo $size[0]; ?>, <?php echo $size[1]; ?> ],
									      boxWidth: 		_boxWidth,
									      boxHeight: 		_boxHeight,
										  swingSpeed:		1.1
									      });
		var ac = { <?php echo $ac; ?>};

		$('#croplist').removeClass('hide_me');
		$('#croplist .container').each(function(i){
			if(!$(this).hasClass('locked')){
				$(this).bind('click', function(){
					cjo_toggle_crop($(this), 'show');
				});
			}
		});

		$('.crop_save').bind('click', function(){
			cjo_copy_crop_values($(this));
			return false;
		});

		$('.crop_cancel').bind('click', function(){
			cjo_toggle_crop($(this), 'hide');
			return false;
		});

		$('#crop_reset').bind('click', function(){

			var $this = $(this);
    		var message = $this.attr('title');
            if (!message.match(/\?/))  message += '?';
            var jdialog = cjo.appendJDialog(message);

			$(jdialog).dialog({
    			buttons: {
    				'<?php echo $I18N->msg('label_ok'); ?>': function() {
    					$(this).dialog('close');
    					$('input[name='+cur_crop+']').val('');
    					cjo_toggle_crop($this, 'hide');
    				},
    				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
    					$(this).dialog('close');
    				}
    			}
    		});
			return false;
		});

		function cjo_copy_crop_values(c){

			var s = cur_crop+'_'+
					$('#cropbox h3').text()+'|'+
					$('#x').text()+'|'+
					$('#y').text()+'|'+
					$('#w').text()+'|'+
					$('#h').text();

			$('input[name='+cur_crop+']').val(s);

			ac[cur_crop]['setSelect'] = [ $('#x').text(),
								   		  $('#y').text(),
										  $('#x2').text(),
										  $('#y2').text() ];

			cjo_toggle_crop($(c), 'hide');
		}


		function cjo_toggle_crop(c, a) {

			var c = $(c);
			var p = '0';
			var w = '960px';
			var name = c.find('h4').text();
			$('#croplist .selected').removeClass('selected');
			$('#croplist').block({ message: null });

			if (a == 'hide') {
				cur_crop = false;
				p = '960px';
				w = '0';
			}
			else {
				cur_crop = c.attr('id');
				c.addClass('selected');
			}

			$('#cropbox')
				.animate({left: p, width: w}, 1200, "easeout", function(){
					$('#croplist').unblock();
				})
				.find('h3')
				.text(name);

			if(cur_crop){
				$jcrop.setOptions(ac[cur_crop]);
			}
		}

		function cjo_show_coords(c) {
			$('#x').text(c.x);
			$('#y').text(c.y);
			$('#x2').text(c.x2);
			$('#y2').text(c.y2);
			$('#w').text(c.w);
			$('#h').text(c.h);
		};
	});

/* ]]> */
</script>