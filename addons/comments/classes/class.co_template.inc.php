<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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

class cjoCommentsTemplate {

    static $mypage = 'comments';
      
    public static function addComment($form_name,$article_id=false,$clang=false) {
    
        global $CJO;

        $return = array();
        
        if ($article_id === false) $article_id = $CJO['ARTICLE_ID'];
        if ($clang === false)      $clang = $CJO['CUR_CLANG'];        

        // Config aus DB holen
        $sql = new cjoSql();
        $qry = "SELECT * " .
                "FROM ".TBL_COMMENTS_CONFIG." " .
                "WHERE " .
                "  (reference_article_id='".$article_id."' OR" .
                "   reference_article_id='-1') AND" .
                "   clang='".$clang."' " .
                "ORDER BY reference_article_id DESC";
        $sql->setQuery($qry);

        $conf = ($sql->getRows() != 0) ? array_shift($sql->getArray()) : array();
        $send_data = cjo_post($form_name,'array');

        if (cjoComments::checkPostedValues( array($send_data['author'],
                                                  $send_data['message'],
                                                  $send_data['url'],
                                                  $send_data['email'],
                                                  $send_data['city'],
                                                  $send_data['country'],
                                                  $send_data['antispam']), 
                                                  preg_replace('/^www\./', '', cjo_server('HTTP_HOST','string')))) {
            $posted                  = array();
            $posted['author']        = $send_data['author'];
            $posted['message']       = $send_data['message'];
            $posted['url']           = $send_data['url'];
            $posted['email']         = $send_data['email'];
            $posted['city']          = $send_data['city'];
            $posted['country']       = $send_data['country'];
            $posted['md5_message']   = md5($send_data['message']);
            $posted['md5_ip']        = md5($_SERVER['REMOTE_ADDR']);

            $sql->flush();
            $qry = "SELECT id
                    FROM ".TBL_COMMENTS."
                    WHERE
                        md5_message = '".$posted['md5_message']."' OR (
                        article_id = '".$article_id."' AND
                        md5_ip = '".$posted['md5_ip']."' AND
                        created > '".(time() - (2 * 60))."')";
            $sql->setQuery($qry);

            if ($sql->getRows() == 0) {

                $status = ($conf['new_online_global'] && cjoComments::detectSpam($conf, $posted) != 'spam');

                $insert = new cjoSql();
                $insert->setTable(TBL_COMMENTS);
                $insert->setValue("status",$status);
                $insert->setValue("article_id",$article_id);
                $insert->setValue("author",$posted['author']);
                $insert->setValue("message",$posted['message']);
                $insert->setValue("url",$posted['url']);
                $insert->setValue("email",$posted['email']);
                $insert->setValue("city",$posted['city']);
                $insert->setValue("country",$posted['country']);
                $insert->setValue("created",time());
                $insert->setValue("clang",$CJO['CUR_CLANG']);
                $insert->setValue("md5_message", $posted['md5_message']);
                $insert->setValue("md5_ip",$posted['md5_ip']);
                $insert->Insert();

                if ($status){
                    $sql = new cjoSql();
                    $qry = "SELECT id FROM ".TBL_COMMENTS." WHERE author='".$posted['author']."' ORDER BY created DESC";
                    $sql->setQuery($qry);

                    $hash = '#comment_'.$sql->getValue('id');
                }
                else {
                    $hash = '#'.$form_name.'_acknowledgment';
                }
                $return['is_valid'] = ($insert->getError() == '') ? true : false;
            }
            else {
                $return['errors'] = array('[translate: active spam protection, try again after 2 minutes]');
                $return['is_valid'] = false;
            } 
            return $return;
        }
    }
}