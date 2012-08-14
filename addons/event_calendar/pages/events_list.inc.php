<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  event_calendar
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

//LIST Ausgabe
$sql = "SELECT
			*,
			(SELECT name FROM ".TBL_ARTICLES." WHERE id=article_id AND clang='".$clang."' LIMIT 1) AS article
		FROM ".TBL_16_EVENTS." WHERE clang='".$clang."'";
$list = new cjolist($sql,
                    'start_date',
                    'ASC',
                    '',
                    100);

$add_button = cjoAssistance::createBELink('<img src="img/silk_icons/add.png" title="'.$I18N->msg("button_add").'" alt="'.$I18N->msg("button_add").'" />',
                                           $list->getGlobalParams(),
                                           array('function' => 'add', 'oid' => ''),
                                           'title="'.$I18N->msg("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button, 'sprintf', '<span>%s</span>');
$cols['id']->setHeadAttributes('class="icon "');
$cols['id']->setBodyAttributes('class="icon cjo_id"');
$cols['id']->delOption(OPT_ALL);

$cols['file'] = new resultColumn('file', '&nbsp;', 'call_user_func', array('OOMedia::toThumbnail',array('%s')));
if (cjoAssistance::inMultival('file', $CJO['ADDON']['settings'][$mypage]['enabled_fields'])) {
    $cols['file']->setHeadAttributes('width="60"');
    $cols['file']->setBodyAttributes('class="preview" width="60" height="60" style="text-align:center!important;vertical-align:middle;"');
    $cols['file']->setParams(array ('page' => 'edit','subpage'=>'details','filename' => '%file%'));
}
$cols['file']->delOption(OPT_ALL);

$cols['title'] = new resultColumn('title', $I18N->msg('label_title'), 'truncate', array('length' => 60));
$cols['title']->setBodyAttributes('class="large_item" width="200"');

$cols['short_description'] = new resultColumn('short_description', $I18N_16->msg('label_short_description'), 'truncate', array('length' => 120));
$cols['short_description']->setBodyAttributes('width="200" valign="top"');

$cols['start_date'] = new resultColumn('start_date', $I18N_16->msg('label_start_date'), 'strftime', $I18N->msg("dateformat"));
$cols['start_time'] = new resultColumn('start_time', $I18N_16->msg('label_start_time'),'strftime','%H:%M');
$cols['end_date'] = new resultColumn('end_date', $I18N_16->msg('label_end_date'), 'strftime', $I18N->msg("dateformat"));
$cols['end_time'] = new resultColumn('end_time', $I18N_16->msg('label_end_time'),'strftime','%H:%M');

for ($i=1;$i<=10;$i++) {

    $attribute             = 'attribute'.$i;
    $attribute_typ         = $CJO['ADDON']['settings'][$mypage]['attribute_typ'.$i];
    $attribute_title       = $CJO['ADDON']['settings'][$mypage]['attribute_title'.$i];
    $attribute_display     = $CJO['ADDON']['settings'][$mypage]['attribute_display'.$i];
    $attribute_date_format = $CJO['ADDON']['settings'][$mypage]['attribute_date_format1'.$i];

    if (empty($attribute_display)) continue;

    switch($attribute_typ){

        case "text":
        case "select":
            $cols[$attribute] = new resultColumn($attribute, $attribute_title);
            break;

        case "textarea":
        case "wymeditor":
            $cols[$attribute] = new resultColumn($attribute, $attribute_title, 'truncate', array('length' => 60));
            break;

        case "datepicker":
            $cols[$attribute] = new resultColumn($attribute, $attribute_title, 'strftime', $attribute_date_format);
            break;

        case "media":
            $cols[$attribute] = new resultColumn($attribute, $attribute_title, 'call_user_func', array('OOMedia::toThumbnail',array('%s')));
            $cols[$attribute]->setHeadAttributes('width="60"');
            $cols[$attribute]->setBodyAttributes('class="preview" width="60" height="60" style="text-align:center!important;vertical-align:middle;"');
            $cols[$attribute]->setParams(array ('filename' => '%'.$attribute.'%','page' => 'edit','clang' => $clang));
            $cols[$attribute]->setParams(array ('page' => 'edit','subpage'=>'details','filename' => '%'.$attribute.'%'));
            break;

        case "article":
            $cols[$attribute] = new resultColumn($attribute, $attribute_title);
            $cols[$attribute]->setHeadAttributes('class="icon"');
            $cols[$attribute]->setBodyAttributes('class="icon"');
            $cols[$attribute]->addCondition('article_id', '0', ' ');
            $cols[$attribute]->setParams(array ('page' => 'edit','subpage'=>'content','clang' => $clang,'article_id' => '%'.$attribute.'%'));

            break;

         default: break;
    }
}

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="3"');
$cols['edit']->setBodyAttributes('width="20"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

// Status link
$cond['stat'][0] = '<img src="img/silk_icons/eye_off.png" title="'.$I18N_16->msg("label_status_do_online").'" alt="'.$I18N_16->msg("label_status_offline").'" />';
$cond['stat'][1] = '<img src="img/silk_icons/eye.png" title="'.$I18N_16->msg("label_status_do_offline").'" alt="'.$I18N_16->msg("label_status_online").'" />';
$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->setBodyAttributes('class="cjo_status"');
$cols['status']->addCondition('status', '0', $cond['stat'][0]);
$cols['status']->addCondition('status', '1', $cond['stat'][1]);

// Lösch link
$cond['delete'] = '<img src="img/silk_icons/bin.png" title="'.$I18N_16->msg("label_delete_event").'" alt="'.$I18N_16->msg("label_delete_event").'" />';
$cols['delete'] = new staticColumn($cond['delete'], NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');

if (strpos('|'.$CJO['ADDON']['settings'][$mypage]['enabled_fields'].'|', '|short_description|') === false) unset($cols['short_description']);
if (strpos('|'.$CJO['ADDON']['settings'][$mypage]['enabled_fields'].'|', '|times|') === false)             unset($cols['start_time']);
if (strpos('|'.$CJO['ADDON']['settings'][$mypage]['enabled_fields'].'|', '|times|') === false)             unset($cols['end_time']);
if (strpos('|'.$CJO['ADDON']['settings'][$mypage]['enabled_fields'].'|', '|end_date|') === false)          unset($cols['end_date']);
if (strpos('|'.$CJO['ADDON']['settings'][$mypage]['enabled_fields'].'|', '|article|') === false)           unset($cols['article']);

$list->addColumns($cols);

$list->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		var oid;

		$(".cjo_status,"+
		  ".cjo_delete").bind('click', function(){

			var el = $(this);
			var oid = el.siblings('.cjo_id').eq(0).text();
			var cl = el.attr('class');
			var mode = cl.substr(4);

			if(mode == 'delete'){
				if(!cjo,confirm(el.find('img'))){
					return false;
				}
			}
			cjo.toggleOnOff(el);

			$.get('ajax.php',{
				   'function': 'cjoEventCalendar::updateEvent',
				   'id': oid,
				   'mode' : mode,
				   'clang': clang },
				  function(message){

					if (cjo.setStatusMessage(message)){

					  	el.find('img.ajax_loader')
					  	  .remove();

					  	el.find('img')
					  	  .toggle();

						if (mode == 'delete' &&
							$('.statusmessage p.error').length == 0){

							el.parent('tr')
							  .siblings()
							  .find('.tablednd')
							  .each(function(i){
							  		$(this).children().text(i+1);
							  });

							el.parent('tr').remove();

							$('div[id^=cs_options][id$='+article_id+']').remove();
						}
					}
			});
		});
    });

/* ]]> */
</script>