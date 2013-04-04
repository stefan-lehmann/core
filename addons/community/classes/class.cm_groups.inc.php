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

class cjoCommunityGroups {

    public static function countUsers($clang=false) {

        global $CJO, $I18N;

        if ($clang === false) $clang = cjo_request('clang','cjo-clang-id', 0);
        
        $temp = array();
        $return = array();
        
        $setlocal_dec_point = trim(cjoI18N::translate('setlocal_dec_point'));
        $setlocal_thousands_sep = trim(cjoI18N::translate('setlocal_thousands_sep'));

        $sql = new cjoSql();
        $qry = "SELECT ug.group_id AS group_id, us.status AS status, COUNT(*) AS user
                FROM ".TBL_COMMUNITY_UG." ug
                LEFT JOIN ".TBL_COMMUNITY_USER." us
                ON us.id = ug.user_id
                WHERE us.clang = '".$clang."'
                GROUP BY ug.group_id, us.status
                ORDER BY us.status DESC";
        $results = $sql->getArray($qry);
        
        if ($sql->getRows() <= 0) return array();

 
        foreach($results as $i=>$result) {
            if ($result['status'] == 0 || $result['status'] == 1) {
                if (!isset($temp[$result['group_id']])) $temp[$result['group_id']] = array(0,0);
                $temp[$result['group_id']][$result['status']]  = number_format($result['user'], 0, $setlocal_dec_point, $setlocal_thousands_sep) ;
            }
        }
        
        foreach($temp as $group_id=>$group) {
            $return[$group_id] = $group[1].' | '.$group[0];
        }

        return $return;
    }

    public static function getSelectGroups($id='', $custom_select=false) {

    	global $CJO, $I18N_10;

    	$select = new cjoSelect;
    	$select->showRoot(cjoAddon::translate(10,'label_root'),'root');

    	$n = cjoCommunityGroups::countUsers();

    	$sql = new cjoSql();
    	$qry = "SELECT a.*,
				IF((SELECT count(b.re_id)
					FROM ".TBL_COMMUNITY_GROUPS." b
					WHERE b.re_id = a.id
					GROUP BY b.re_id), 'folder', 'file') AS title
    			FROM ".TBL_COMMUNITY_GROUPS." a
    			ORDER BY a.name";

    	$results = $sql->getArray($qry);

		foreach($results as $key => $result) {

		$user_number = $n[$result['id']] != '' ? $n[$result['id']] : 0;

			$select->addOption($result['name'].' ('.$user_number.')',
							   $result['id'],
							   $result['id'],
							   $result['re_id'],
                               $result['title']);
		}

    	if ($custom_select) {
    		$select->setName("custom_select");
    		$select->setStyle("width: 928px;");
    		$select->setSize(1);
    		$select->setMultiple(false);
    		$select->setSelected($id);
    		$select->setSelectedPath(cjoCommunityGroups::getPath($id));
    		return $select->get();
    	}
    	else {
    		$size = count($results)+1;
    		$size = $size > 12 ? 12 : $size;
    		$select->setMultiple(true);
    		$select->setName("groups[]");
    		$select->setSize($size);

    		$sql->flush();
    		$qry = "SELECT
                        ug.group_id AS group_id
                    FROM
                        ".TBL_COMMUNITY_USER." us
                    LEFT JOIN
                        ".TBL_COMMUNITY_UG." ug
                    ON
                        ug.user_id=us.id
                    WHERE
                        us.id='".$id."'";
    		$sql->setQuery($qry);

    		for ($i = 0; $i < $sql->getRows(); $i++){
    			$select->setSelected($sql->getValue('group_id'));
    			$sql->next();
    		}
    		return $select;
    	}
    }

    public static function getPath($group_id) {

        $path = $group_id;
        while($group_id != 0){
            $group_id = cjoCommunityGroups::getParentGroup($group_id);
            $path = $group_id.'|'.$path;
        }
        return $path;
    }

    private static function getParentGroup($group_id) {
        $sql = new cjoSql();
    	$qry = "SELECT re_id FROM ".TBL_COMMUNITY_GROUPS." WHERE id='".$group_id."'";
    	$sql->setQuery($qry);
    	return $sql->getValue('re_id');
    }

    public static function deleteGroups($group_id, $re_id) {

    	global $CJO, $I18N_10, $mypage, $subpage;

    	$error = '';

    	$sql = new cjoSql();
    	$qry = "SELECT type_id, groups
    			FROM ".TBL_ARTICLES_TYPE." WHERE
    			groups REGEXP '^".$group_id."[[.vertical-line.]]|[[.vertical-line.]]".$group_id."[[.vertical-line.]]|[[.vertical-line.]]".$group_id."$|^".$group_id."$'";
    	$sql->setQuery($qry);

    	if ($sql->getRows() != 0){
    	    #TODO Change subpage=types (Relocated to Admin tools)
    		cjoMessage::addError(cjoAddon::translate(10,'error_connected_type_ids','index.php?page=specials&subpage=types'));
    		return false;
    	}

        $sql->flush();
    	$sql->setQuery("SELECT * FROM ".TBL_COMMUNITY_GROUPS." WHERE re_id='".$group_id."'");

    	if ($sql->getRows() != 0){
    	    cjoMessage::addError(cjoAddon::translate(10,'error_has_children'));
    	    return false;
    	}

    	$sql->flush();
    	$qry = "SELECT u.id AS userid
               FROM ".TBL_COMMUNITY_USER." u
               LEFT JOIN ".TBL_COMMUNITY_UG." l
               ON u.id = l.userid
               WHERE l.groupid = ".$group_id ;
    	$user_ids = $sql->getArray($qry);

    	foreach ($user_ids as $user_id){
    		$sql->flush();
    		$qry = "SELECT userid FROM ".TBL_COMMUNITY_UG." WHERE groupid!=0 AND userid= '".$user_id['userid']."'";
    		$sql->setQuery($qry);

    		if ($sql->getRows() == 1) {
    			cjoCommunityUser::moveUser($user_id['userid'], $group_id, 0);
    		}
    	}
    	if (cjoMessage::hasErrors()){
    	    return false;
    	}

    	$sql->flush();
    	$sql->setQuery("DELETE FROM ".TBL_COMMUNITY_GROUPS." WHERE id='".$group_id."'");

    	if ($sql->getError() != '') $error .= '<br/>'.$sql->getError();
    	cjoCommunityUserGroups::delete(false,$group_id);

    	if ($error != '' || !cjoCommunityUserGroups::delete(false,$group_id)){
        	cjoMessage::addError(cjoAddon::translate(10,'error_groups_deleted').$error);
        	return false;

    	}
    	cjoMessage::addSuccess(cjoAddon::translate(10,'msg_groups_deleted'));
    	return true;
    }

    public static function updateGroups($user_id, $groups, $delete=false) {

    	global $CJO, $I18N_10;

    	if ($user_id == -1){
    		$sql = new cjoSql();
    		$qry = "SELECT max(id) AS lastinsert_id FROM ".TBL_COMMUNITY_USER;
    		$sql->setQuery($qry);
    		$user_id = $sql->getValue('lastinsert_id');
    	}

    	if (empty($user_id)) return false;

    	if ($delete && !cjoCommunityUserGroups::delete($user_id)) {
    	    return false;
    	}
        $performed = array();
		foreach (cjoAssistance::toArray($groups) as $group_id){
		    if (isset($performed[$group_id])) continue;
			cjoCommunityUser::copyUser($user_id, $group_id);
			$performed[$group_id] = true;
		}
		return true;
    }

    /**
     * Returns all article-type-ids or -names that the members
     * of the group belonging to the overgiven group-id have
     * access to.
     *
     * @param int $group_id
     * @param string $return_type - defines if article-type-names as a
     * 								'|'-separated string shall be
     * 								returned(= anything else than 'type_ids')
     * 								or article-type-ids as an
     * 								indicated array, the default is 'type_ids'
     * @return mixed article_type_ids -
     * @see ./addons/community/pages/groups_form.inc.php line 45
     * @author Matthias Schomacker <ms@contejo.com>
     */
    public static function getArticleTypesOfGroup($group_id, $return_type='type_ids') {

    	$article_types = array();

        $sql = new cjoSql();
    	$qry = "SELECT
    				type_id,
    				name
    			FROM "
    				.TBL_ARTICLES_TYPE."
    			WHERE
    				groups REGEXP '^".$group_id."\\\||\\\|".$group_id."\\\||\\\|".$group_id."$|^".$group_id."$'
    			ORDER BY
    				name";

    	$results = $sql->getArray($qry);

    	// convert results to an indicated array if
    	// article-type-ids shall be returned
    	if($return_type === 'type_ids') {
	    	foreach($results as $result) {
	    		$article_types[] = $result['type_id'];
	    	}
    	}
    	else {
	    	foreach($results as $result) {
		    	$article_types[] = $result['name'];
	    	}
	    	$article_types = empty($article_types) ? '--' : implode(' | ', $article_types);
    	}

    	return $article_types;
    }

    /**
     * Updates tbl_articles_type. It adds the posted group
     * to groups for every article_type the group members
     * shall have access to.
     *
     * @param void
     * @return void
     * @see ./addons/community/pages/groups_form.inc.php line 95
     * @author Matthias Schomacker <ms@contejo.com>
     */
    public static function saveArticleTypesOfGroup() {

    	// get posted values
    	$group_id = cjo_post('oid', 'int', 0);
    	$new_article_types = cjo_post('article_types', 'array', array());

    	// get old article-type-ids
    	$old_article_types = self::getArticleTypesOfGroup($group_id);

    	// get article-type-ids where group has to be added
    	$add_article_types = array_diff($new_article_types, $old_article_types);
    	// get article-type-ids where group has to be deleted
    	$delete_article_types = array_diff($old_article_types, $new_article_types);

    	// add group to article-type
    	foreach($add_article_types as $article_type) {
    		self::addGroupToArticleType($article_type, $group_id);
    	}

    	// delete group from article-type
    	foreach($delete_article_types as $article_type) {
    		self::deleteGroupFromArticleType($article_type, $group_id);
    	}
    }

    /**
     * Updates column groups of the row from tbl_articles_type
     * belonging to type_id. Will add a new group.
     *
     * @param int $article_type - row to update
     * @param int $group_id - group to add
     * @see self->saveArticleTypesOfGroup
     * @author Matthias Schomacker <ms@contejo.com>
     */
    private static function addGroupToArticleType($type_id, $group_id) {

    	$sql = new cjoSql();
    	$qry = "SELECT groups FROM ".TBL_ARTICLES_TYPE." WHERE type_id ='".$type_id."'";
    	$groups = array_shift($sql->getArray($qry));
    	$sql->flush();
    	$groups = $groups['groups'];

    	// update column
    	$groups .= empty($groups) ? $group_id : '|'.$group_id;
    	$qry = "UPDATE ".TBL_ARTICLES_TYPE." SET groups='".$groups."' WHERE type_id='".$type_id."'";
    	$sql->setQuery($qry);
    }

 /**
     * Updates column groups of the row from tbl_articles_type
     * belonging to type_id. Will delete a group.
     *
     * @param int $article_type - row to update
     * @param int $group_id - group to delete
     * @see self->saveArticleTypesOfGroup
     * @author Matthias Schomacker <ms@contejo.com>
     */
    private static function deleteGroupFromArticleType($type_id, $group_id) {

    	$sql = new cjoSql();
    	$qry = "SELECT groups FROM ".TBL_ARTICLES_TYPE." WHERE type_id ='".$type_id."'";
    	$groups = array_shift($sql->getArray($qry));
    	$sql->flush();
    	$groups = $groups['groups'];

    	// delete group from string
    	$groups = preg_replace(array('/^'.$group_id.'\||\|'.$group_id.'$|^'.$group_id.'$/', '/\|'.$group_id.'\|/'),
    						   array('','|'), $groups, 1);

    	// update column
    	$qry = "UPDATE ".TBL_ARTICLES_TYPE." SET groups='".$groups."' WHERE type_id='".$type_id."'";
    	$sql->setQuery($qry);
    }
}