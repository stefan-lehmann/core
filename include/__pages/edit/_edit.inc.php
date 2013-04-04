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

$article_id    = cjo_request('article_id', 'cjo-article-id');
$re_id         = cjo_request('re_id', 'cjo-article-id');
$clang         = cjo_request('clang', 'cjo-clang-id', cjoProp::get('START_CLANG_ID'));
$slice_id      = cjo_request('slice_id', 'cjo-slice-id', '');
$function      = cjo_request('function', 'string');
$ctype         = cjo_request('ctype', 'cjo-ctype-id');
$redirect_type = cjo_request('redirect_type', 'string', 'int');
//cjo_Debug($article_id); die();

if (!cjoProp::getCtype($ctype)) $ctype = 0;

if ($article_id) {
    
    $sql = new cjoSql();
    $cjo_data = $sql->getArray("SELECT * FROM ".TBL_ARTICLES." WHERE id=".$article_id." AND clang=".$clang);
    if ($sql->getRows() != 1) unset ($article_id); else $cjo_data = $cjo_data[0]; 
    
    $re_id = $cjo_data['re_id'];   
    
    if (cjoProp::getSubpage() != 'structure' && 
        $locked_user = cjoLog::isArticleLockedByUser($article_id)) {
        cjoUrl::redirectBE(array('page'=>'edit', 'subpage' => 'structure', 'article_id' => $re_id, 'clang' => $clang, 'mode' => '', 'locked_user' => $locked_user, 'err_msg' => 'msg_edit_by_other_user_redirected'));
    }

    if (!cjoProp::getUser()->hasCatPermRead($article_id)) {
        cjoUrl::redirectBE(array('page'=>'edit', 'subpage' => '', 'clang' => $clang, 'mode' => '', 'article_id' => ''));
    }
 
    $cjo_data['active_ctypes'] = cjoTemplate::getCtypes($cjo_data['template_id']);
    
    if (!empty($cjo_data['redirect']) && preg_match('/\D+/', $cjo_data['redirect'])) {
        $redirect_type = 'ext';
    }
    
    foreach($cjo_data['active_ctypes'] as $key=>$ctype_id) {
        if (!cjoProp::getUser()->hasCtypePerm($ctype_id)) {
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
	    cjoMessage::addError(cjoI18N::translate('msg_template_has_no_ctype' ,
	                                    cjoUrl::createBELink(cjoI18N::translate('label_edit_now'),
	                                                                array('page'      => 'tools',
	                                      								  'templates' => 'templates',
	                                                                      'function'  => 'edit',
	                                                                      'oid'       => $cjo_data['template_id']))));
	}
}

$is_add_article = (cjoProp::getSubpage() == 'settings' && $function == 'add');

if (in_array(cjoProp::getSubpage(), array('structure', 'content', 'settings', 'metadata', ''))) {
    cjoSelectArticle::getOutput(true);
}


cjoSubPages::addPage( array('structure',
					'query_str' => 'page=edit&subpage=structure&article_id='.$article_id.'&clang='.$clang.'&ctype='.$ctype,
                    'params' => array ('page'=>'edit', 'subpage'=>'structure', 'article_id'=>$article_id, 'clang'=>$clang, 'ctype'=>$ctype),
					'important' => true));

if ($article_id && !$is_add_article && $cjo_data['active_ctypes']) {
    cjoSubPages::addPage( array('content',
						'rights' => array('csw['.$article_id.']'),
                        'params' => array ('page'=>'edit', 'subpage'=>'content', 'article_id'=>$article_id, 'clang'=>$clang, 'ctype'=>$ctype)));
}
if ($article_id || ($is_add_article && (!cjoProp::getUser()->hasPerm('editContentOnly[]')))) {

    $re_id      = ($is_add_article && $article_id) ? $article_id : $re_id;
    $article_id = ($is_add_article) ? '' : $article_id;
	$title      = ($function == 'add') ? cjoI18N::translate("title_add_article") : false;
	$csw_id     = ($function == 'add') ? $re_id : $article_id;
    $type       = ($function == 'add') ? 'csr' : 'csw';	

    cjoSubPages::addPage( array('settings',
						'title' => $title,
						'rights' => array($type.'['.$csw_id.']', 'publishArticle[]'),
                        'params' => array ('page'=>'edit', 'subpage'=>'settings', 'article_id'=>$article_id, 'clang'=>$clang, 'ctype'=>$ctype)));
}
if ($article_id && !$is_add_article) {
    cjoSubPages::addPage( array('metadata',
						'rights' => array('csw['.$article_id.']'),
                        'params' => array ('page'=>'edit', 'subpage'=>'metadata', 'article_id'=>$article_id, 'clang'=>$clang, 'ctype'=>$ctype)));
}

require_once cjoSubPages::getPagePath();

if (cjoProp::getSubpage() != 'content' && 
    isset($cjo_data['active_ctypes']) &&
    count($cjo_data['active_ctypes']) > 1 && 
    cjoProp::getUser()->hasCatPermWrite($article_id,true)) {
    cjoProp::set('cjo_tabs|content', 
                 cjoProp::get('cjo_tabs|content').
                 cjoArticle::createCtypeMultiLink($article_id, $ctype));
}

$title = ($article_id)  ? ' | '.$cjo_data['name'] : '';
cjoProp::set('title', cjoProp::get('title').$title) ;

cjoSelectLang::get();

if (cjoProp::getSubpage() == 'structure') return false;

cjoLog::updateArticleLockedByUser($article_id);

/**
 * Do not delete translate values for cjoI18N collection!
 * [translate: title_structure]
 * [translate: title_content]
 * [translate: title_settings]
 * [translate: title_metadata]
 */
?>
<script type="text/javascript">/* <![CDATA[ */ cjo.updateEditLog(); /* ]]> */</script>