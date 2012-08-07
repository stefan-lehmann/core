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

$mypage        = $cur_page['page'];
$article_id    = cjo_request('article_id', 'cjo-article-id');
$re_id         = cjo_request('re_id', 'cjo-article-id');
$clang         = cjo_request('clang', 'cjo-clang-id', $CJO['START_CLANG_ID']);
$slice_id      = cjo_request('slice_id', 'cjo-slice-id', '');
$function      = cjo_request('function', 'string');
$ctype         = cjo_request('ctype', 'cjo-ctype-id');
$redirect_type = cjo_request('redirect_type', 'string', 'int');


if (!is_array($CJO['CTYPE']) || !array_key_exists($ctype, $CJO['CTYPE'])) $ctype = 0;

if ($article_id) {
    
    $sql = new cjoSql();
    $cjo_data = $sql->getArray("SELECT * FROM ".TBL_ARTICLES." WHERE id=".$article_id." AND clang=".$clang);
    if ($sql->getRows() != 1) unset ($article_id); else $cjo_data = $cjo_data[0]; 
    
    $re_id = $cjo_data['re_id'];   
    
    if ($subpage != 'structure' && $locked_user = cjoLog::isArticleLockedByUser($article_id)) {
        cjoAssistance::redirectBE(array('page'=>'edit', 'subpage' => 'structure', 'article_id' => $re_id, 'clang' => $clang, 'mode' => '', 'locked_user' => $locked_user, 'err_msg' => 'msg_edit_by_other_user_redirected'));
    }

    if (!$CJO['USER']->hasCatPermRead($article_id)) {
        cjoAssistance::redirectBE(array('page'=>'edit', 'subpage' => '', 'clang' => $clang, 'mode' => '', 'article_id' => ''));
    }
 
    $cjo_data['active_ctypes'] = cjoTemplate::getCtypes($cjo_data['template_id']);
    
    if (!empty($cjo_data['redirect']) && preg_match('/\D+/', $cjo_data['redirect'])) {
        $redirect_type = 'ext';
    }
    
    foreach($cjo_data['active_ctypes'] as $key=>$ctype_id) {
        if (!$CJO['USER']->hasCtypePerm($ctype_id)) {
            unset($cjo_data['active_ctypes'][$key]);
        }              
    }

    if (!in_array($ctype,$cjo_data['active_ctypes'])) {
        foreach($cjo_data['active_ctypes'] as $key=>$ctype_id) {
            $ctype = $ctype_id;  
            break;
        }
    }

    if (empty($cjo_data['active_ctypes']) && cjo_request('subpage','string') == 'content') {
	    cjoMessage::addError($I18N->msg('msg_template_has_no_ctype' ,
	                                    cjoAssistance::createBELink($I18N->msg('label_edit_now'),
	                                                                array('page'      => 'tools',
	                                      								  'templates' => 'templates',
	                                                                      'function'  => 'edit',
	                                                                      'oid'       => $cjo_data['template_id']))));
	}
}

$is_add_article = ($subpage == 'settings' && $function == 'add');

if (in_array($subpage, array('structure', 'content', 'settings', 'metadata', ''))) {
    $CJO['SEL_ARTICLE']->get(true);
}

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('structure',
					'query_str' => 'page=edit&subpage=structure&article_id='.$article_id.'&clang='.$clang.'&ctype='.$ctype,
					'important' => true));

if ($article_id && !$is_add_article && $cjo_data['active_ctypes']) {
    $subpages->addPage( array('content',
						'rights' => array('csw['.$article_id.']'),
						'query_str' => 'page=edit&subpage=content&article_id='.$article_id.'&clang='.$clang.'&ctype='.$ctype));
}
if ($article_id || ($is_add_article && (!$CJO['USER']->hasPerm('editContentOnly[]')))) {

    $re_id      = ($is_add_article && $article_id) ? $article_id : $re_id;
    $article_id = ($is_add_article) ? '' : $article_id;
	$title      = ($function == 'add') ? $I18N->msg("title_add_article") : false;
	$csw_id     = ($function == 'add') ? $re_id : $article_id;
    $type       = ($function == 'add') ? 'csr' : 'csw';	

    $subpages->addPage( array('settings',
						'title' => $title,
						'rights' => array($type.'['.$csw_id.']', 'publishArticle[]'),
						'query_str' => 'page=edit&subpage=settings&article_id='.$article_id.'&clang='.$clang.'&ctype='.$ctype));
}
if ($article_id && !$is_add_article) {
    $subpages->addPage( array('metadata',
						'rights' => array('csw['.$article_id.']'),
						'query_str' => 'page=edit&subpage=metadata&article_id='.$article_id.'&clang='.$clang.'&ctype='.$ctype));
}

require_once $subpages->getPage();

if ($subpage != 'content' && 
    isset($cjo_data['active_ctypes']) &&
    count($cjo_data['active_ctypes']) > 1 && 
    $CJO['USER']->hasCatPermWrite($article_id,true)) {
    $CJO['cjo_tabs']['content'] .= cjoArticle::createCtypeMultiLink($article_id, $ctype);
}

$CJO['title'] .= ($article_id)  ? ' | '.$cjo_data['name'] : '';

$CJO['SEL_LANG']->get();

if ($subpage == 'structure') return false;

cjoLog::updateArticleLockedByUser($article_id);

/**
 * Do not delete translate values for i18n collection!
 * [translate: title_structure]
 * [translate: title_content]
 * [translate: title_settings]
 * [translate: title_metadata]
 */
?>
<script type="text/javascript">/* <![CDATA[ */ cjo.updateEditLog(); /* ]]> */</script>