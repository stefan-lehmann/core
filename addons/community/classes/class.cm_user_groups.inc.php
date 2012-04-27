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

class cjoCommunityUserGroups {

    public static function add($user_id,$group_id){

        $insert = new cjoSql();
        $insert->setTable(TBL_COMMUNITY_UG);
        $insert->setValue("user_id", $user_id);
        $insert->setValue("group_id", $group_id);
        $insert->Insert();

        if ($insert->getError() != '') {
            cjoMessage::addError($sql->getError());
            return false;
        }
        return true;
    }

    public static function delete($user_id=false,$group_id=false){

        if ($user_id) $user_id_qry  = " user_id='".$user_id."'";
        if ($group_id) $group_id_qry = " group_id='".$group_id."'";
        if ($group_id && $user_id) $and_qry  = " AND";

        $sql = new cjoSql();
        $sql->setQuery("DELETE FROM ".TBL_COMMUNITY_UG." WHERE".$user_id_qry.$and_qry.$group_id_qry);

        if ($sql->getError() == '') return true;

        cjoMessage::addError($sql->getError());
        return false;

    }
}
