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

/**
 * cjoUser class
 *
 * The cjoUser class provides updating functions for contejo users.
 * @package 	contejo
 * @subpackage 	core
 */

if (!cjoProp::isBackend()) return false;

class cjoUser {

    function addRightsPrefix(&$item1, $key, $prefix) {

        if ($item1 !== '') {
            $item1 = $prefix.'['.$item1.']';
        }
    }

    function updateUserRights($user_id, $perm_lang = false, $perm_admin = false) {

        foreach (cjoAssistance::toArray(glob(cjoPath::generated('articles'), $user_id.'.*.aspath')) as $filename) {
    	    @ unlink($filename);
    	}

        $rights = array();

        $sql = new cjoSql();
        $qry = "SELECT
                    a.rights AS rights,
                    b.rights AS user,
                    b.name AS name
                FROM ".TBL_USER." a
                LEFT JOIN ".TBL_USER." b
                ON b.description LIKE (CONCAT('%|',a.user_id,'|%'))
                WHERE a.login REGEXP '^group_' AND b.user_id=".$user_id;
        $sql->setQuery($qry);

        $user_name = $sql->getValue('name');

        if (!empty($perm_admin)) {
            if ($perm_admin == 'perm_admin') {
            	$rights[] = 'admin[]';
            }
        }
        elseif ($user_id == 1) {			// strpos($sql->getValue('user'), '#admin[]#') !== false
            $rights[] = 'admin[]';
        }

        if ($perm_lang != false && is_array($perm_lang)) {
            array_walk($perm_lang, 'cjoUser::addRightsPrefix', 'clang');
            $rights = array_merge($perm_lang, $rights);
        }
        else {
            foreach(cjoProp::get('CLANG') as $lang_id => $name) {
                if (strpos($sql->getValue('user'), '#clang['.$lang_id.']#') !== false) {
                    $rights[] = 'clang['.$lang_id.']';
                }
            }
        }

        for ($i=0;$i<$sql->getRows();$i++) {
            $rights = array_merge(explode('#', $sql->getValue('rights')), $rights);
            $sql->next();
        }

        $rights = array_flip($rights);
        $rights = array_keys($rights);
        $rights = array_diff($rights, array(''));
        sort($rights);

        $rights = '#'.implode('#',$rights).'#';

        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("user_id = '".$user_id."'");
        $update->setValue('rights', $rights);

        $update->Update(cjoI18N::translate("msg_editor_updated", $user_name));
    }

    function updateCatReadPermissions() {

        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_USER." WHERE rights NOT LIKE '%#admin[]#%'";
        $users = $sql->getArray($qry);

        $update = $sql;

        foreach($users as $user) {

            $user_id = $user['user_id'];
            $rights  = $user['rights'];
            $perm_r  = array();
            $perm_w  = array();
    
            foreach (cjoAssistance::toArray(glob(cjoPath::generated('articles'), $user_id.'.*.aspath')) as $filename) {
                @ unlink($filename);
            }

            $rights = preg_replace('/#csr\[\d+\]#/', '#', $rights);
            preg_match_all('/(?<=#csw\[)\d+(?=\]#)/', $rights, $perm_w);

            if (!is_array($perm_w[0])) continue;
            $perm_w = $perm_w[0];

            foreach ($perm_w as $cat_id) {

            	if (empty($cat_id)) continue;
                $cat = OOArticle::getArticleById($cat_id);
                if (!OOArticle::isValid($cat)) continue;

                foreach(cjoAssistance::toArray($cat->getPath()) as $parent_id) {
                    if ($parent_id == $cat_id) continue;
                    $perm_r[$parent_id] = 'csr['.$parent_id.']';
                }
            }

            $rights = array_unique(array_merge(cjoAssistance::toArray($rights,'#'),$perm_r));
            sort($rights);

            $rights = '#'.implode('#', $rights).'#';
            $rights = preg_replace('/\#{1,}/','#',$rights);

            $update->flush();
            $update->setTable(TBL_USER);
            $update->setWhere("user_id = '".$user['user_id']."'");
            $update->setValue('rights', $rights);
            $update->Update();
        }
    }
}

cjoExtension::registerExtension('ARTICLE_MOVED', 'cjoUser::updateCatReadPermissions');
cjoExtension::registerExtension('ARTICLE_DELETED', 'cjoUser::updateCatReadPermissions');