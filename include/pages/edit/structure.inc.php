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

cjoExtension::registerExtension('CJO_LIST_ARTICLES_LIST_CELLS', 'cjoFormateCells');
cjoExtension::registerExtension('CJO_LIST_ARTICLES_LIST_ROW_ATTR', 'cjoFormateRows');

// Condition für Feld NAVI_ITEM
$cond['navi'][0] = '<img src="img/silk_icons/chart_organisation.png" title="'.$I18N->msg("label_article_hide_navi_item").'" alt="'.$I18N->msg("label_article_navi_item").'" />';
$cond['navi'][1] = '<img src="img/silk_icons/chart_organisation_off.png" title="'.$I18N->msg("label_article_show_navi_item").'" alt="'.$I18N->msg("label_article_no_navi_item").'" />';

// Condition für Feld STATUS
$cond['stat'][0] = '<img src="img/silk_icons/eye_off.png" title="'.$I18N->msg("label_article_do_online").'" alt="'.$I18N->msg("label_article_offline").'" />';
$cond['stat'][1] = '<img src="img/silk_icons/eye.png" title="'.$I18N->msg("label_article_do_offline").'" alt="'.$I18N->msg("label_article_online").'" />';

// Condition für Feld TEASER
$cond['teas'][0] = '<img src="img/silk_icons/star_off.png" title="'.$I18N->msg("label_teaser_do_on").'" alt="'.$I18N->msg("label_teaser_off").'" />';
$cond['teas'][1] = '<img src="img/silk_icons/star.png" title="'.$I18N->msg("label_teaser_do_off").'" alt="'.$I18N->msg("label_teaser_on").'" />';

// Condition für Feld KOMMENTARE
$cond['comm'][0] = '<img src="img/silk_icons/comments_off.png" title="'.$I18N->msg("label_comment_do_on").'" alt="'.$I18N->msg("label_comment_off").'" />';
$cond['comm'][1] = '<img src="img/silk_icons/comments.png" title="'.$I18N->msg("label_comment_do_off").'" alt="'.$I18N->msg("label_comment_on").'" />';

// Condition für Feld DELETE
$cond['delete'] = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("label_delete_article").'" alt="'.$I18N->msg("label_delete_article").'" />';

$sql = "SELECT
			*,
			id AS checkbox,
			if (name='','".$I18N->msg("label_no_name")."',name) AS name,
			if (re_id<>0,0,cat_group) AS _cat_group,
			if ((SELECT id FROM ".TBL_ARTICLES_SLICE." WHERE article_id='id' AND clang='".$CJO['CUR_CLANG']."' LIMIT 1)>0, 1,0) AS slices
        FROM
            ".TBL_ARTICLES."
        WHERE
            re_id='".$article_id."' AND clang='".$CJO['CUR_CLANG']."'" ;


$list = new cjolist($sql, '_cat_group, prior', 'ASC', 'name', 100);
$list->setName('ARTICLES_LIST');
$list->setAttributes('id="articles_list"');

$add_button = '';
if ($CJO['USER']->isAdmin() ||
   ($CJO['USER']->hasCatPermWrite($article_id) &&
    $CJO['USER']->hasPerm('publishArticle[]') &&
    !$CJO['USER']->hasPerm('editContentOnly[]'))) {
	$add_button = cjoAssistance::createBELink(
						'<img src="img/silk_icons/add.png" title="'.$I18N->msg("button_add").'" alt="'.$I18N->msg("button_add").'" />',
                        array('subpage' => 'settings', 'article_id'=> $article_id, 'function' => 'add', 'oid' => ''),
                        $list->getGlobalParams(),
                        'title="'.$I18N->msg("button_add").'"');
}
$temp = '';
if ((!$CJO['USER']->hasPerm("copyArticle[]") &&
    !$CJO['USER']->hasPerm("copyContent[]") &&
    !$CJO['USER']->hasPerm("moveArticle[]") &&
    !$CJO['USER']->hasPerm("deleteArticleTree[]")) || 
    $CJO['USER']->hasPerm('editContentOnly[]')) {
    $temp = ' disabled="disabled"';
}
$cols['checkbox'] = new resultColumn('checkbox', $add_button, 'sprintf', '<input type="checkbox" class="checkbox" '.$temp.' value="%s" />');
$cols['checkbox']->setHeadAttributes('class="icon"');
$cols['checkbox']->setBodyAttributes('class="icon"');
$cols['checkbox']->delOption(OPT_ALL);

$cols['startpage'] = new resultColumn('startpage', $I18N->msg('label_name'));
$cols['startpage']->setHeadAttributes('colspan="2"');
$cols['startpage']->setBodyAttributes('class="icon"');
$cols['startpage']->delOption(OPT_ALL);

$cols['id'] = new resultColumn('id', null, 'sprintf', '<span title="'.$I18N->msg('label_id').'">ID %s</span>');
$cols['template_id'] = new resultColumn('template_id', null, 'sprintf', '<span title="'.$I18N->msg('label_template').'"><img src="img/mini_icons/layout.png" alt="" />%s</span>');
$cols['cat_group'] = new resultColumn('cat_group', null, 'sprintf', '<span title="'.$I18N->msg('label_cat_group').'"><img src="img/mini_icons/chart_organisation.png" alt="" />%s</span>');

$cols['name'] = new resultColumn('name', NULL, 'truncate',array( 'length' => 60, 'etc' => '...', 'break_words' => false));
$cols['name']->setBodyAttributes('class="large_item"');
$cols['name']->setParams(array ('article_id'=> '%id%', 're_id'=> $re_id, 'clang' => $CJO['CUR_CLANG']));
$cols['name']->delOption(OPT_ALL);

$cols['prio'] = new resultColumn('prior', $I18N->msg('label_prio'),'sprintf','<strong>%s</strong>');
$cols['prio']->setHeadAttributes('class="icon"');
$cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
$cols['prio']->setBodyAttributes('title="'.$I18N->msg("label_change_prio").'"');
$cols['prio']->delOption(OPT_ALL);

if ($CJO['ONLINE_FROM_TO_ENABLED'] == true) { 
    $cols['online_from'] = new resultColumn('online_from', $I18N->msg('label_from_to'), 'strftime', $I18N->msg("dateformat"));
    $cols['online_from']->addCondition('online_from', array('>', time()), '<i>'.$I18N->msg("date_from").'</i><span class="begin_date date_error">%s</span>');
    $cols['online_from']->addCondition('online_from', array('<', time()), '<i>'.$I18N->msg("date_from").'</i><span class="begin_date">%s</span>');
    if (!$CJO['USER']->hasPerm('editContentOnly[]')) $cols['online_from']->setBodyAttributes('class="online_from_to"');
    $cols['online_from']->delOption(OPT_ALL);
    
    $cols['online_to'] = new resultColumn('online_to', null, 'strftime', $I18N->msg("dateformat"));
    $cols['online_to']->addCondition('online_to', array('<', time()), '<i>'.$I18N->msg("date_to").'</i><span class="end_date date_error">%s</span>');
    $cols['online_to']->addCondition('online_to', array('>', time()), '<i>'.$I18N->msg("date_to").'</i><span class="end_date">%s</span>');
}
if ($CJO['LOGIN_ENABLED'] == true) {
    $cols['type_id'] = new resultColumn('type_id', $I18N->msg('label_login'), 'sprintf', '<span>%s</span>');
    $cols['type_id']->setBodyAttributes('width="100"');
    $cols['type_id']->delOption(OPT_ALL);
}

$colspan = false;
$count = 0;

$cols['navi_item'] = new staticColumn('navi_item', $I18N->msg('label_functions'));
$cols['navi_item']->setBodyAttributes('width="16"');
if (!$CJO['USER']->hasPerm('editContentOnly[]')) $cols['navi_item']->setBodyAttributes('class="cjo_navi_item"');
$cols['navi_item']->addCondition('navi_item', '1', $cond['navi'][0]);
$cols['navi_item']->addCondition('navi_item', '0', $cond['navi'][1]);
$colspan = 'navi_item';
$count++;

$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->setBodyAttributes('style="border-left: none;"');
if (!$CJO['USER']->hasPerm('editContentOnly[]')) $cols['status']->setBodyAttributes('class="cjo_status"');
$cols['status']->addCondition('status', '0', $cond['stat'][0]);
$cols['status']->addCondition('status', '1', $cond['stat'][1]);
$count++;

if ($CJO['TEASER_ENABLED']) {
	$cols['teaser'] = new staticColumn('teaser', NULL);
	$cols['teaser']->setBodyAttributes('width="16"');
	$cols['teaser']->setBodyAttributes('style="border-left: none;"');
	if (!$CJO['USER']->hasPerm('editContentOnly[]')) $cols['teaser']->setBodyAttributes('class="cjo_teaser"');
	$cols['teaser']->addCondition('teaser', '0', $cond['teas'][0]);
	$cols['teaser']->addCondition('teaser', '1', $cond['teas'][1]);
	$count++;
}

if (OOAddon::isAvailable('comments')) {
	$cols['comments'] = new staticColumn('comments', NULL);
	$style = !$colspan ? '' : ' style="border-left: none;"';
	$cols['comments']->setBodyAttributes('width="16"');
	$cols['comments']->setBodyAttributes($style);
	if (!$CJO['USER']->hasPerm('editContentOnly[]')) $cols['comments']->setBodyAttributes('class="cjo_comments"');
	$cols['comments']->addCondition('comments', '0', $cond['comm'][0]);
	$cols['comments']->addCondition('comments', '1', $cond['comm'][1]);
	$colspan = !$colspan ? 'comments' : 'navi_item';
	$count++;
}

if (!$CJO['USER']->hasPerm('editContentOnly[]')) {
    $cols['delete'] = new staticColumn($cond['delete'], NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $colspan = !$colspan ? 'delete' : 'navi_item';
    $count++;
}
$cols[$colspan]->setHeadAttributes('colspan="'.$count.'"');

// Entfernen von inaktiven Spalten
if ($re_id != '')
	unset($cols['cat_group']);

$rowAttributes = ' onclick="location.href=\'index.php?page=edit&amp;subpage=structure&amp;article_id='.$re_id.'&amp;clang='.$CJO['CUR_CLANG'].'\';" ' .
                 ' class="cat_uplink" title="'.$I18N->msg("label_level_up").'"';

$up_link  = '            <tr'.$rowAttributes.' valign="middle" class="nodrop">'."\r\n".
            '              <td class="icon" height="20"> &nbsp; </td>'."\r\n".
            '              <td colspan="'.(count($cols)-2).'" height="20">'."\r\n".
            '              	<img src="img/silk_icons/level_up.png" alt="up" />'."\r\n".
            '              </td>'."\r\n".
            '            </tr>'."\r\n";


if (isset($cjo_data) && $cjo_data['startpage']) $list->setVar(LIST_VAR_BEFORE_DATA, $up_link);
if (!isset($cjo_data) || !$cjo_data['startpage']) $list->setVar(LIST_VAR_NO_DATA, $up_link);

$list->addColumns($cols);

if ($list->numRows() != 0) {

	$CJO['SEL_ARTICLE']->setName("target_location");
	$CJO['SEL_ARTICLE']->setStyle("width:250px;clear:none;");

	$buttons = new popupButtonField('', '', '', '');
	$buttons->addButton($I18N->msg('label_run_process'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');

	$clang_a_sel = new cjoSelect();
    $clang_a_sel->setName('clang_a');
    $clang_a_sel->setSize(1);
    $clang_a_sel->setStyle('class="cjo_float_l" style="width: auto"');
	foreach($CJO['CLANG'] as $key => $val) {
		if ($CJO['USER']->hasPerm("clang[".$key."]")) {
			$clang_a_sel->addOption($val,$key);
		}
	}
	$clang_b_sel = clone($clang_a_sel);
    $clang_b_sel->setName('clang_b');

    $ctype_sel = new cjoSelect();
    $ctype_sel->setName('target_ctype');
    $ctype_sel->setSize(1);
    $ctype_sel->setSelectExtra('title="'.$I18N->msg('label_select_ctype').'"');    
    $ctype_sel->addOption($I18N->msg('label_all_ctypes'), '-1');
    $ctype_sel->setSelected('-1');    
    foreach ($CJO['CTYPE'] as $ctype_id=>$ctype_name) {
        $ctype_sel->addOption($ctype_name, $ctype_id); 
    }
    
    $update_sel = new cjoSelect();
    $update_sel->setName('update_selection');
    $update_sel->setSize(1);
    $update_sel->setStyle('class="cjo_float_l" disabled="disabled"');
    $update_sel->addOption($I18N->msg('label_update_selection'), 0);
    $update_sel->setSelected(0);

    $temp = false;

    if ($CJO['USER']->hasPerm("copyArticle[]")) {
        $update_sel->addOption($I18N->msg('label_copy_to'), 1);
        $temp = true;
    }
    if ($CJO['USER']->hasPerm("copyArticle[]")) {
        $update_sel->addOption($I18N->msg('label_rcopy_to'), 2);
        $temp = true;
    }
    if ($CJO['USER']->hasPerm("copyContent[]")) {
        $update_sel->addOption($I18N->msg('label_copy_content_to_article'), 6); 
        $update_sel->addOption($I18N->msg('label_copy_content'), 3);       
        $temp = true;
    }
    if ($CJO['USER']->hasPerm("moveArticle[]")) {
        $update_sel->addOption($I18N->msg('label_rmove_to'), 4);
        $temp = true;
    }
    if ($CJO['USER']->hasPerm("deleteArticleTree[]")) {
        $update_sel->addOption($I18N->msg('label_rdelete'), 5);
        $temp = true;
    }

	$toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
				   '	<td class="icon">'.
				   '    	<input type="checkbox" class="hidden_container check_all" title="'.$I18N->msg('label_select_deselect_all').'" />'.
				   '	</td>'.
				   '	<td colspan="'.(count($cols)-2).'">'.
				   '		<div class="hidden_container">'.$update_sel->get().
				   '		<span class="cjo_float_l cjo_article_path hide_me">'.$CJO['SEL_ARTICLE']->_get().'</span>'.
				   '		<span class="cjo_float_l cjo_clang hide_me">'.$clang_a_sel->get().
				   '          <img src="img/silk_icons/control_fastforward.png" alt=">>" class="icon" />'.
				   '		  '.$clang_b_sel->get().
				   '        </span>'.
                   '        <span class="cjo_float_l cjo_ctype hide_me">'.$ctype_sel->get().'</span>'.  
				   '		<span class="cjo_float_l hide_me">'.$buttons->getButtons().'</span>'.
	               '		</div>'.
				   '	</td>'.
				   '</tr>'."\r\n";

	if ($temp) {
    	$list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
	}
}

$list->show(false);

/**
 * Provides cell formating via extension point api
 * @param array $cell
 * @return array
 * @ignore
 */
function cjoFormateCells($cell) {

    global $CJO, $I18N, $list, $oid, $ctype, $re_id;

    $curr_body = $cell['cells'][$cell['name']]['body'];
    $curr_cell = $cell['cells'][$cell['name']]['cell'];

    $default_typenames = array('1' => '--',
    						   'out' => $I18N->msg('label_type_logged_out'),
    						   'in' => $I18N->msg('label_type_logged_in'),
                               'contejo' => $I18N->msg('label_preview_for_editors'));

    $article_id = $cell['cells']['checkbox']['unformated'];
   // cjo_Debug($cell['cells'],$cell['cells']['checkbox']['unformated']);
    $template_id = isset($cell['cells']['template_id']) ? $cell['cells']['template_id']['unformated'] : 1;

    $article = OOArticle::getArticleById($article_id);
    
    $perm      = array();
    $perm['r'] = $CJO['USER']->hasCatPermRead($article_id);
    $perm['w'] = $CJO['USER']->hasCatPermWrite($article_id,true);

    $sql = new cjoSql();
    
    switch($cell['name']) {

    	case 'startpage':
    			$icon = ($curr_cell) ? 'files' : 'file';
        		$sql->flush();
        		$sql->setQuery("SELECT id FROM ".TBL_ARTICLES_SLICE." WHERE article_id='".$article_id."' AND clang='".$CJO['CUR_CLANG']."'");
        		$icon .= ($sql->getRows() > 0) ? '2' : '';
        		$icon = '<img src="img/radium_icons/'.$icon.'.png" alt="" />';
				$sql->flush();
        		$sql->setQuery("SELECT redirect FROM ".TBL_ARTICLES." WHERE id='".$article_id."' AND clang='".$CJO['CUR_CLANG']."'");
        		
        		
                if ($article->isAdminOnly()) {
                    $icon .= '<img src="img/radium_icons/user_orange.png" class="icon_overlay" alt="" title="'.$I18N->msg('label_superadmin_only').'" />';
                }
                elseif ($locked_user = $article->isLocked()) {
                    $locked_user = cjoLogin::getUser($locked_user);
                    $icon .= '<img src="img/radium_icons/user.png" class="icon_overlay" alt="" title="'.$I18N->msg('msg_edit_by_user', $locked_user['name']).'" />';
                }
                elseif (preg_match('/\D+/', $sql->getValue('redirect'))) {
                    $icon .= '<a href="'.$sql->getValue('redirect').'" title="'.$I18N->msg('label_redirect_type').' '.$I18N->msg('label_ext_redirect').' ('.$sql->getValue('redirect').')">'.
                            '<img src="img/radium_icons/redirect.png" class="icon_overlay" alt="" />'.
                            '</a>';
        		} else if($sql->getValue('redirect')) {
                    $icon .= cjoAssistance::createBELink('<img src="img/radium_icons/redirect.png" class="icon_overlay" alt="" />', 
                                    array('subpage'=>'structure','article_id'=>$sql->getValue('redirect'),'clang'=>$CJO['CUR_CLANG'],'ctype'=>$ctype), 
                                    array(), 'title="'.$I18N->msg('label_redirect_type').' '.$I18N->msg('label_int_redirect').' (ID='.$sql->getValue('redirect').')"');
        		}
        		$curr_cell = '<span style="position: relative; display: block;"'.(!$perm['r'] ? ' class="cjo_alpha_50"' :'').'>'.$icon.'</span>';
        		break;

        case 'template_id':
        		$sql->flush();
        		$sql->setQuery("SELECT name FROM ".TBL_TEMPLATES." WHERE id='".$cell['unformated']."'");
        		$curr_cell = cjoFormatter :: format($sql->getValue('name'), $cell['format_type'], $cell['format']);
        		break;

        case 'cat_group':
        		$sql->flush();
        		$sql->setQuery("SELECT group_name FROM ".TBL_ARTICLES_CAT_GROUPS." WHERE group_id='".$cell['unformated']."'");
        		$curr_cell = cjoFormatter :: format($sql->getValue('group_name'), $cell['format_type'], $cell['format']);
        		break;

		case 'name':
				if (!$perm['r']) {
					$curr_cell = preg_replace('/(<a.*?href\="?\S+"[^>]*?>)(.+?)(<\/a>)/i',
											  '<span class="locked">$2</span>',
											  $curr_cell);
				}
				if (!$perm['w']) {
				    $temp  = 'locked active';
                    $curr_cell = preg_replace('/<a/i', '<a class="'.$temp.'"', $curr_cell);
				}

				$infos      = '';
				foreach(array('id', 'template_id', 'cat_group') as $temp) {
				    if (!isset($cell['cells'][$temp])) continue;
					$infos .= $cell['cells'][$temp]['cell'];
					unset($cell['cells'][$temp]);
				}
				
                $temp       = array();  
                $quicklinks = '';
                
				if ($perm['w']) {
				    
				    $active_ctypes = cjoTemplate::getCtypes($template_id);
    
                    foreach($active_ctypes as $key=>$ctype_id) {
                        if (!$CJO['USER']->hasCtypePerm($ctype_id)) {
                            unset($active_ctypes[$key]);
                        }              
                    }

                    if (!in_array($ctype,$active_ctypes)) {
                        foreach($active_ctypes as $key=>$ctype_id) {
                            $ctype = $ctype_id;  
                            break;
                        }
                    }
				    
				    $temp[] = count($active_ctypes) > 1 
				            ? cjoArticle::createCtypeMultiLink($article_id, $ctype)
				            : cjoAssistance::createBELink($I18N->msg('title_content'), 
                                            array('subpage'=>'content','article_id'=>$article_id,'clang'=>$CJO['CUR_CLANG'],'ctype'=>$ctype), 
                                            array(), 
                                            'title="'.$I18N->msg('title_content').'"');
				    
				    $temp[] = cjoAssistance::createBELink($I18N->msg('title_settings'), 
				                                          array('subpage'=>'settings','article_id'=>$article_id,'clang'=>$CJO['CUR_CLANG'],'ctype'=>$ctype), 
				                                          array(), 
				                                          'title="'.$I18N->msg('label_edit_article_settings').'"');
				                                               
				    $temp[] = cjoAssistance::createBELink($I18N->msg('title_metadata'), 
				                                          array('subpage'=>'metadata','article_id'=>$article_id,'clang'=>$CJO['CUR_CLANG'],'ctype'=>$ctype),
				                                          array(), 
				                                          'title="'.$I18N->msg('label_edit_article_metadata').'"');
				}   
				    $temp[] = '<a href="#" onclick="cjo.openShortPopup(\''.cjoRewrite::getUrl($article_id, $CJO['CUR_CLANG']).'\').focus();" '.
				                  'title="'.$I18N->msg('label_article_view_title').'">'.$I18N->msg('label_article_view').'</a>';

				foreach ($temp as $value) {
				    if (!$value) continue;
				    $class = ($quicklinks == '') ? ' class="first"' : '';
				    $quicklinks .= '<li'.$class.'>'.$value.'</li>';
				}  
				
				$curr_cell .= "\r\n".
							  '<ul class="col_details">'."\r\n".
							  '	<li class="infos">'.$infos.'</li>'."\r\n".
							  ' <li class="quicklinks">'."\r\n".
							  '    <ul>'.$quicklinks.'</ul>'."\r\n".
							  '</li>'."\r\n".
                              '</ul>'."\r\n";

				break;

		case 'online_to':
    		    if ($perm['w']) {
            		$curr_cell = $cell['cells']['online_from']['cell'].'<br/>'.$curr_cell;
            	    if (!$CJO['USER']->hasPerm('editContentOnly[]')) {
                		$curr_cell = cjoAssistance::createBELink(
                		                      $curr_cell,
                            				  array ('subpage' => 'settings',
                            				  		 'article_id'=> $article_id,
                            				  		 're_id'=> $re_id, 'clang' => $CJO['CUR_CLANG']),
        	        				          array(),
                                              'title="'.$I18N->msg('label_edit_from_to').'"');
    		        }
    		    }
    		    else {
    		        $curr_cell = '';
    		    }
	        	$cell['cells']['online_from']['cell'] = $curr_cell;
        		$curr_cell = null;
        		break;

		case 'type_id':
		        if ($perm['w']) {
            		$sql->flush();
            		$sql->setQuery("SELECT name FROM ".TBL_ARTICLES_TYPE." WHERE type_id='".$cell['unformated']."'");
    
            		if ($curr_cell != '') {
            		    $curr_name = preg_replace('/<(.*?)>.*<\/\1>/','',$default_typenames[$cell['unformated']]);
    
            		    if (empty($curr_name)) $curr_name = $sql->getValue('name');
            		    
            		    if (!$CJO['USER']->hasPerm('editContentOnly[]')) {
                			$curr_cell = cjoAssistance::createBELink(
                			                          cjoFormatter :: format($curr_name, $cell['format_type'], $cell['format']),
                									  array ('subpage' => 'settings',
                									  		 'article_id'=> $article_id,
                									  		 're_id'=> $re_id, 'clang' => $CJO['CUR_CLANG']),
                									  array(),
                                                      'title="'.$I18N->msg('label_edit_login').'"');
            		    }
            		    else {
            		        $curr_cell = cjoFormatter :: format($curr_name, $cell['format_type'], $cell['format']);
            		    }
            		}
		        } 
		        else {
		            $curr_cell = '';
		        }
		        
            	break;
            	
        case 'prior':  
        case 'online_from':            
                    break;            	
            	
        case 'checkbox':	
                if (!$perm['w']) $curr_cell = '';
                break;  
                
        default:  
                if (!$perm['w']) {                    
                    $curr_cell = '';
                    break;  
                }                          
	}

    $cell['cells'][$cell['name']]['body'] = $curr_body;
    $cell['cells'][$cell['name']]['cell'] = $curr_cell;

    return $cell['cells'];
}

/**
 * Provides row formating via extension point api
 * @param array $cell
 * @return array
 * @ignore
 */
function cjoFormateRows($rows) {

    global $CJO, $I18N, $list, $article_id;

    if (!cjo_request('article_id', 'cjo-article-id')) {

		$sql = new cjoSql();
		$sql->setQuery("SELECT * FROM ".TBL_ARTICLES_CAT_GROUPS." WHERE group_id='".$rows['row']['cat_group']."'");
		$style = $sql->getValue('group_style');
		$name = str_replace(' ','_',strtolower($sql->getValue('group_name')));
	    return ' class="cjo_catgoup_'.$name.'" style="background:'.$style.'!important"';
    }
    else {
	    return ' class="cjo_catgoup_0"';
    }
}

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function() {

		var article_id, curr_row_id, old_prio, new_prio;

		$('.cjo_navi_item,'+
		  '.cjo_status,'+
		  '.cjo_teaser,'+
		  '.cjo_comments,'+
		  '.cjo_delete')
			.click(function() {

			var el = $(this);

			if (el.children().length < 1) return false;
			
			var article_id = el.siblings().eq(0).find('input').val();
			var cl = el.attr('class');
			var mode = cl.substr(4);

			var confirm_action = function() {

				cjo.toggleOnOff(el);

    			$.get('ajax.php',{
    				   'function': 'cjoArticle::updateArticleParams',
    				   'id': article_id,
    				   'mode' : mode,
    				   'clang': cjo.conf.clang },
    				  function(message) {

    					if (cjo.setStatusMessage(message)) {

    					  	el.find('img.ajax_loader')
    					  	  .remove();

    					  	el.find('img')
    					  	  .toggle();

    						if (mode == 'delete' &&
    							$('.statusmessage p.error').length == 0) {

    							el.parent('tr')
    							  .siblings()
    							  .find('.tablednd')
    							  .each(function(i) {
    							  		$(this).children().text(i+1);
    							  });

    							el.parent('tr').remove();

    							$('div[id^=cs_options][id$='+article_id+']').remove();
    						}
    					}
    			});
			};

			if (mode == 'delete') {

					var jdialog = cjo.appendJDialog('<?php echo $I18N->msg('msg_confirm_delete_article'); ?>');

    				$(jdialog).dialog({
            			buttons: {
            				'<?php echo $I18N->msg('label_ok'); ?>': function() {
            					$(this).dialog('close');
            					confirm_action();
            				},
            				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
            					$(this).dialog('close');
            				}
            			}
            		});
			}
			else {
				confirm_action();
			}

		});

		if ($('td.cjo_delete').children().length != $('td.tablednd').length) {
			$('td.tablednd')
			 .removeClass('tablednd')
			 .removeClass('dragHandle')
			 .attr('title','');
		}

        $("#articles_list").tableDnD({
            onDragClass: "dragging",
            onDrop: function(table, row) {

				var cells   = $(table).find('td.tablednd');
				var allrows	= $(row).parent('tbody').children();
				var change  = true;

                cells.each(function(i) {

                	if ($(this).parent('tr').is('#'+curr_row_id)) {

                		new_prio = i+1;
                		if (old_prio == new_prio) {
                			change = false;
						}
						return true;
					}
				});

				allrows.removeClass('nodrop');

				if (!change) return false;

				var confirm_action = function() {

						allrows.block({ message: null });
			        	cells.each(function(i) {
    	                	$(this).children().hide().text((i+1));
    					});

    					cells.removeClass('dragHandle')
    						 .removeClass('tablednd')
    						 .append(cjo.conf.ajax_loader);

    	                if (old_prio < new_prio) new_prio++;

    					$.get('ajax.php',{
    						   'function': 'cjoArticle::updatePrio',
    						   'id': article_id,
    						   'new_prio' : new_prio,
    						   'clang': cjo.conf.clang },
    						  	function(message) {

    							  	if (cjo.setStatusMessage(message)) {

    								  	cells.find('img.ajax_loader')
    						  	  		  	 .remove();

    								  	cells.children()
    								  	     .toggle();

    								   	cells.addClass('dragHandle')
    								   		 .addClass('tablednd');

    								   	allrows.unblock();
    							   }
    					});
				};

        		var message = $(row).find('td.tablednd').attr('title');
                if (!message.match(/\?/))  message += '?';
            	var jdialog = cjo.appendJDialog(message);

				$(jdialog).dialog({
        			buttons: {
        				'<?php echo $I18N->msg('label_ok'); ?>': function() {
        					$(this).dialog('close');
        					confirm_action();
        				},
        				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
        					$(this).dialog('close');
        					location.reload();
        				}
        			}
        		});
            },
            onDragStart: function(table, row) {

            	old_prio = $(row).text();

            	curr_row_id = $(row).parent('tr').attr('id');
            	cur_class = $(row).parent('tr').attr('class');

            	 $(row).parent('tr').parent('tbody')
            	 	.children()
            	 	.removeClass('nodrop');

            	 $(row).parent('tr').parent('tbody')
            	 	.children('tr:not(.'+cur_class+')')
            	 	.addClass('nodrop');

            	var re = new RegExp('[0-9]+$');
  				var ma = re.exec(curr_row_id);
			    for (i = 0; i < ma.length; i++) {
			      article_id = ma[i];
			    }
			},
            dragHandle: "dragHandle"
        });

		$('#update_selection').change(function() {

			var $this = $(this);
			var selected = $this.val();
			var next_all = $this.nextAll('span');

			next_all.addClass('hide_me');

			if (selected > 0 && selected < 5 && selected != 3) {
				next_all.eq(0).removeClass('hide_me');
				next_all.eq(3).removeClass('hide_me');
			}
			if (selected == 3) {
				next_all.eq(1).removeClass('hide_me');
				next_all.eq(3).removeClass('hide_me');
			}
			if (selected == 5) {
                next_all.eq(3).removeClass('hide_me');	
			}
            if (selected == 6) {
                next_all.eq(0).removeClass('hide_me');
                next_all.eq(2).removeClass('hide_me');
                next_all.eq(3).removeClass('hide_me');
            }			
		});

		$('#target_location').selectpath({
			 path_len: 'short',
			 types   : {root		  : 'root',
                      	folder 		  : 'folder',
                      	file		  : 'file',
                      	folder_locked : 'folder locked',
              			file_locked   : 'locked'}
		});

		$('.checkbox:not(:disabled)').click(function() {

			if ($('.checkbox:checked').length > 0 ||
				$(this).is(':checked')) {

				$('#update_selection').removeAttr('disabled');
				$('.toolbar_ext .hidden_container')
					.fadeIn('slow');
			} else {
				$('#update_selection')
					.attr('disabled', 'disabled')
					.find('option')
					.removeAttr('selected');
				$('#update_selection')
					.nextAll('span')
					.addClass('hide_me');
				$('.toolbar_ext .hidden_container')
					.fadeOut('slow');
			}
		});

		$('.check_all').click(function() {
				if ($(this).is(':checked')) {
					$('#articles_list tbody .checkbox')
						.attr('checked','checked');
					$('#update_selection')
						.removeAttr('disabled');
				}
				else {
					$('#articles_list tbody .checkbox')
						.removeAttr('checked');

    				$('#update_selection')
    					.attr('disabled', 'disabled')
    					.find('option')
    					.removeAttr('selected');

    				$('#update_selection')
						.nextAll('span')
    					.addClass('hide_me');
				}
			});

		$('#ajax_update_button').click(function() {

			var $this = $(this);
            var tb = $('#articles_list tbody');
			var cb = tb.find('.checkbox:checked');
			var total = cb.length - 1;
			var selected = $('#update_selection :selected').val() *1;
			var target = $('input[name="target_location"]').val() *1;
			var clang_a = $('#clang_a :selected').val() *1;
			var clang_b = $('#clang_b :selected').val() *1;
			var target_ctype = $('#target_ctype :selected').val() *1;

			var messages 	= [];
			 	messages[1] = '<?php echo $I18N->msg('msg_confirm_copy_to') ?>';
			 	messages[2] = '<?php echo $I18N->msg('msg_confirm_rcopy_to') ?>';
			 	messages[3] = '<?php echo $I18N->msg('msg_confirm_copy_content') ?>';
			 	messages[4] = '<?php echo $I18N->msg('msg_confirm_rmove_to') ?>';
			 	messages[5] = '<?php echo $I18N->msg('msg_confirm_rdelete') ?>';
                messages[6] = '<?php echo $I18N->msg('msg_confirm_copy_content_to_article') ?>';			 	

			if (cb.length < 1) return false;

			var confirm_action = function() {

    			tb.block({ message: null });
				var round = 0;
				
    			cb.each(function(i) {
    				var $this = $(this);
    				var id = $this.val();
    				var tr = $('#row_articles_list_'+id);


    				$this.hide()
    				  .removeAttr('checked')
    				  .before(cjo.conf.ajax_loader);

        			switch(selected) {
        				case 1: params = {'function': 'cjoGenerate::copyArticle',
                						  'id': id,
                						  'target' : target
                						 }; break;

        				case 2: params = {'function': 'cjoGenerate::copyArticleRecrusive',
                						  'id': id,
                						  'target' : target
                						 }; break;

        				case 3: params = {'function': 'cjoGenerate::copyContent',
                						  'id': id,
                						  'target' : id,
                						  'clang': clang_a,
                						  'target_clang': clang_b
                						 }; break;

        				case 4: params = {'function': 'cjoGenerate::moveArticle',
                						  'id': id,
                						  'target' : target
                						 }; break;

        				case 5: params = {'function': 'cjoGenerate::deleteArticle',
                						  'id': id,
                						  'recrusive' : 'true'
                						 }; break;
                        case 6: params = {'function': 'cjoGenerate::copyContent',
                                            'id': id,
                                            'target' : target,
                                            'clang': '<?php echo $clang ?>',
                                            'target_clang': '<?php echo $clang ?>',
                                            'target_ctype': target_ctype                                                
                                           }; break;             						 
        			}

        			$.get('ajax.php', params,
        				  	function(message) {
        						if (cjo.setStatusMessage(message)) {
            						
        							round++;
        						  	tr.find('.ajax_loader').remove();
        						  	tr.find('.checkbox').show();
    								tr.removeClass('selected');

        						  	if (selected >= 4 && selected < 6 && !message.match(/class="error"/)) {
        						  		tr.fadeOut('slow', function() {
        						  			tr.remove();
        						  		});
        						  	}
        						  	tb.unblock();

       						  	if (round == total+1 && selected != 3 && selected != 6) {
       						  		window.setTimeout('location.reload();',2000);
    						   }
    						}
            		});
    			});
			};

		    var message = messages[selected];

        	var jdialog = cjo.appendJDialog(message);

			$(jdialog).dialog({
    			buttons: {
    				'<?php echo $I18N->msg('label_ok'); ?>': function() {
    					$(this).dialog('close');
    					confirm_action();
    				},
    				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
    					$(this).dialog('close');
    				}
    			}
    		});

			return false;
		});
    });

/* ]]> */
</script>