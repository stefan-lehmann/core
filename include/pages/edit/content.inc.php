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

unset($CJO_ACTION);
global $CJO;

if ($cjo_data) {

    if (cjo_request('save', 'boolean') &&
        in_array($function, array('add', 'edit', 'delete'))) {

		if ($function == "add") {
		    $module_id = cjo_request('module_id', 'cjo-module-id');
			$qry = "SELECT *, id AS modultyp_id FROM ".TBL_MODULES." md WHERE id='".$module_id."'";
		}
		else {
			$qry = "SELECT
				   		sl.*,
				   		md.*,
				   		md.id AS modultyp_id
				   FROM
				   		".TBL_ARTICLES_SLICE." sl
				   LEFT JOIN
				   		".TBL_MODULES." md
				   ON
				   		sl.modultyp_id=md.id
				   WHERE
					   sl.id='".$slice_id."' AND
					   clang='".$CJO['CUR_CLANG']."'";
		}

		$sql = new cjoSql();
		$curr_modul = $sql->getArray($qry);
		$curr_modul = array_shift($curr_modul);
		$module_id = $curr_modul['modultyp_id'];

		if (!is_array($curr_modul) && count($curr_modul) != 1) {
			cjoMessage::addError($I18N->msg('msg_module_not_found'));
			$slice_id = "";
			$function = "";
			$module_id = "";
			$save = "";
		}
		else {
			if (!($CJO['USER']->isAdmin() ||
				$CJO['USER']->hasPerm("module[".$module_id."]") ||
				$CJO['USER']->hasPerm("module[0]"))){
				cjoMessage::addError($I18N->msg('msg_no_rights_to_this_function'));
				$slice_id = "";
				$function = "";
				$module_id = "";
				$save = "";
			}
			else {

				$CJO_ACTION = array ();
				$CJO_ACTION['SAVE'] = true;

                foreach ($CJO['VARIABLES'] as $obj) {
					$CJO_ACTION = $obj->getACRequestValues($CJO_ACTION);
				}

				$CJO_ACTION = cjoSlice::execPreSaveAction($module_id, $function, $CJO_ACTION);

				$CJO['ACTION'] = $CJO_ACTION;

				if ($CJO_ACTION['SAVE']) {

					if ($function == "edit") {

						$update = new cjoSql();
						$update->setTable(TBL_ARTICLES_SLICE);
						$update->setWhere("id='".$slice_id."'");

						foreach ($CJO['VARIABLES'] as $obj){
							$obj->setACValues($update, $CJO_ACTION, true);
						}

						$update->addGlobalUpdateFields();
						$update->Update();

						if ($update->getError() != '') {
						    cjoMessage::addError($I18N->msg('msg_data_not_saved').'<br/>'.$update->getError());
						}
					}
					elseif ($function == "add") {

						$settings                        = array();
						$settings['re_article_slice_id'] = $slice_id;
						$settings['article_id']          = $article_id;
						$settings['modultyp_id']         = $module_id;
						$settings['clang']               = $CJO['CUR_CLANG'];
						$settings['ctype']               = $ctype;

						$slice_id = cjoSlice::addSlice($settings);
					}
					elseif ($function == "delete") {
						cjoSlice::deleteSlice($slice_id);
					}

					$update = new cjoSql();
					$update->setTable(TBL_ARTICLES);
					$update->setWhere("id='".$article_id."' AND clang='".$CJO['CUR_CLANG']."'");
					$update->setValue("updatedate",time());
					$update->setValue("updateuser",$CJO['USER']->getValue("name"));
					$update->Update();

                    cjoSlice::execPostSaveAction($module_id, $function, $CJO_ACTION);
                    cjoGenerate::deleteGeneratedArticle($article_id);                    
                    cjoGenerate::generateArticle($article_id);
                    
                    cjoExtension::registerExtensionPoint('ARTICLE_UPDATED', array('action' => 'CONTENT_UPDATED',
                                                                                  'id' => $article_id, 
                                                                                  'clang' => $CJO['CUR_CLANG'],
                                                                                  'ctype' => $ctype));

					if ($function == "add" && !cjoMessage::hasErrors()) {
					    //[translate: msg_block_added]
					    cjoAssistance::redirectBE(array('article_id' => $article_id,
					                                    'slice_id' => $slice_id,
					                                    'function' => 'edit',
					                                    'mode' => 'edit',
					                                    'clang' => $CJO['CUR_CLANG'],
					    								'ctype' => $ctype,
					    								'msg' => 'msg_block_added',
					    								'#' => 'slice'.$slice_id));
					}

					if ($function == "edit" && !cjoMessage::hasErrors())
					    cjoMessage::addSuccess($I18N->msg('msg_block_updated'));

					if (!cjo_post('update', 'boolean')) {
						unset($slice_id);
						$function = '';
					}
				}
			}
		}
	}

	if ($function == "moveup" || $function == "movedown") {
        cjoSlice::moveSlice($slice_id, $CJO['CUR_CLANG'], $function);
	}

    $cjo_data['ctypes'] = array();
    if (!empty($cjo_data['active_ctypes'])) {

        $sql = new cjoSql();
        $qry = "SELECT ctype, COUNT( ctype ) as slices FROM ".TBL_ARTICLES_SLICE." WHERE article_id=".$article_id." AND clang='".$CJO['CUR_CLANG']."' GROUP BY ctype LIMIT ".count($CJO['CTYPE']);
        $cjo_data['count_results'] = $sql->getArray($qry);
      
        if (count($cjo_data['active_ctypes']) > 0 && !in_array($ctype, $cjo_data['active_ctypes'])) {
            cjoAssistance::redirectBE(array('article_id' => $article_id,
                                            'mode' =>'edit',
                                            'clang' => $CJO['CUR_CLANG'],
                                            'ctype' => $cjo_data['active_ctypes'][0]));
        }

		foreach($cjo_data['active_ctypes'] as $ctype_id) {
		    
		    if (!$CJO['USER']->hasCtypePerm($ctype_id)) continue;
		    
            $slices = '';
		    foreach($cjo_data['count_results'] as $count_result) {
                if ($ctype_id == $count_result['ctype']) {
                    $slices = ' <i>('.$count_result['slices'].')</i>';
                    break;
                }
                
            } 
			$cjo_data['ctypes'][] = array($ctype_id, 'title' => $CJO['CTYPE'][$ctype_id].$slices, 'query_str' => 'page=edit&subpage=content&article_id='.$article_id.'&mode=edit&clang='.$CJO['CUR_CLANG'].'&ctype='.$ctype_id);
		}
		cjoSubpages::setTabs($ctype, $cjo_data['ctypes'], $mypage);
	}

	if (!isset($slice_id)) $slice_id = '';
	if (!isset($function)) $function = '';

	$content = new cjoArticle();
	$content->getAsQuery();
	$content->setArticleId($article_id);
    $content->setSliceId($slice_id);
    $content->setMode('edit');
    $content->setCLang($CJO['CUR_CLANG']);
    $content->setEval(true);
    $content->setFunction($function);
    echo '<div id="cjo_edit_content" class="cjo_template_'.$content->getTemplateId().' cjo_ctype_'.$ctype.'">';

	eval("?>".str_replace('$this->', '$content->', $content->getArticle($ctype)));
	echo "</div>";
}