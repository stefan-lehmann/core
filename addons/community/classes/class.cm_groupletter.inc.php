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

class cjoGroupLetter {

    public $clang;
    public $subject;
    public $group_ids;
    public $article_id;
    public $template;
    public $html;
    public $text;
    public $content;
    public $user;
    public $status;
    public $firstsenddate = 0;
    public $lastsenddate  = 0;
    public $prepared      = 0;
    public $send          = 0;
    public $errors        = 0;
    public $mail_error;

    private $mail         = '';
    private $recipient    = array();
    private $titletype    = '';
    private $inst         = 0;    

    public static $mypage = 'community';
    
    /**
     * Konstruktor
     * statische Konfigurationsdaten eintragen
     */
    function __construct() {

        global $CJO;

        $this->clang = $CJO['CUR_CLANG'];
        $this->getPreferences();

        $this->mail = new cjoPHPMailer();
    }

    private function getPreferences() {

        global $CJO;

        $sql = new cjoSql();
        $qry = "SELECT *
                FROM ".TBL_COMMUNITY_ARCHIV."
                WHERE
                    status='1' AND
                    (SELECT COUNT(*) FROM ".TBL_COMMUNITY_PREPARED.") > 0";

        $preferences = array_shift($sql->getArray($qry));

        $preferences['template']
            = (!$preferences['template'])
            ? $CJO['ADDON']['settings'][self::$mypage]['TEMPLATE']
            : $preferences['template'];

        $preferences['mail_account']
            = (!$preferences['mail_account'])
            ? $CJO['ADDON']['settings'][self::$mypage]['MAIL_ACCOUNT']
            : $preferences['mail_account'];

        $preferences['atonce']
            = (!$preferences['atonce'])
            ? $CJO['ADDON']['settings'][self::$mypage]['ATONCE']
            : $preferences['atonce'];
            
        $this->setPreferences($preferences);
    }

    public function isPrepared() {
        return !empty($this->content);
    }

    public function setPreferences($preferences) {

        global $CJO;

        $preferences['clang']
            = (!isset($preferences['clang']))
            ? cjo_request('clang','cjo-clang-id', 0)
            : $preferences['clang'];

        $this->id             = $preferences['id'];
        $this->clang          = $preferences['clang'];
        $this->subject        = $preferences['subject'];
        $this->group_ids      = $preferences['group_ids'];
        $this->article_id     = $preferences['article_id'];
        $this->template       = $preferences['template'];
        $this->mail_account   = $preferences['mail_account'];
        $this->content        = $preferences['content'];
        $this->atonce         = $preferences['atonce'];
        $this->firstsenddate  = $preferences['firstsenddate'];
        $this->lastsenddate   = $preferences['lastsenddate'];
        $this->prepared       = $preferences['prepared'];
        $this->send           = $preferences['send'];
        $this->errors         = $preferences['errors'];
        $this->user           = $preferences['user'];
        $this->inst           = (int) preg_replace('/\D/', '', $CJO['INSTNAME']);       

        $this->validateMailAccount();

        if ($this->article_id == -1) {
            $this->setBodyText($this->content);
        }
        else {
            $this->setBodyHtml($this->content);
        }
    }

    private function savePreferences() {

        global $CJO, $I18N_10;

        $insert = new cjoSql();
        $insert->setTable(TBL_COMMUNITY_ARCHIV);
        $insert->setValue("subject", $this->subject);
        $insert->setValue("article_id", $this->article_id);
        $insert->setValue("group_ids", $this->group_ids);
        $insert->setValue("content",$this->content);
        $insert->setValue("prepared", $this->prepared);
        $insert->setValue("status", 1);
        $insert->setValue("user",  $CJO['USER']->getValue("name"));
        $insert->setValue("clang", $this->clang);
        $insert->setValue("atonce", $this->atonce);
        $insert->setValue("mail_account", $this->mail_account);    
           
        $state = $insert->Insert($I18N_10->msg('msg_recipients_prepared',$this->prepared));

        $clang_conf = $CJO['ADDON']['settings'][self::$mypage]['CLANG_CONF'];
        $settings = $CJO['ADDON']['settings'][self::$mypage]['SETTINGS'];       

        if ($state && cjoAssistance::isWritable($settings) && cjoAssistance::isWritable($clang_conf)) {

            $config_data = file_get_contents($clang_conf);
            $config_data = preg_replace("!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['MAIL_ACCOUNT'\].?\=.?)[^;]*!",
                                        "\\1\"".$this->mail_account."\"",  $config_data);
            $state = cjoGenerate::replaceFileContents($clang_conf, $config_data);
            
            if (!$state) return $state;            
            
            $config_data = file_get_contents($clang_conf);
            $config_data = preg_replace("!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['TEMPLATE'\].?\=.?)[^;]*!",
                                        "\\1\"".$this->template."\"", $config_data);                                        
            $config_data = preg_replace("!(CJO\['ADDON'\]\['settings'\]\[.mypage\]\['ATONCE'\].?\=.?)[^;]*!",
                                        "\\1\"".$this->atonce."\"", $config_data);
            $state = cjoGenerate::replaceFileContents($clang_conf, $config_data);
            
        }
        return $state;
    }
    
    private function newLogfile(){
        
        global $CJO;
        
        $path = $CJO['ADDON']['settings'][self::$mypage]['LOGS_PATH'];
        $date = strftime('%Y-%m-%d_%H-%M-%S', time());           
        
        if (!file_exists($path)){
            @mkdir($path);
            @chmod($path, $CJO['FILEPERM']);
        }
        
        if (file_exists($path.'/current.log')) {
            rename($path.'/current.log', $path.'/'.$date.'.log');
        }
        $content = 'Groupletter Error Log: '.strftime('%d.%m.%Y %H:%M:%S', time())."\r\n";
        cjoGenerate::putFileContents($path.'/current.log',$content);
    }
    
    private function logError($recipient,$error) {
        global $CJO;
        $path = $CJO['ADDON']['settings'][self::$mypage]['LOGS_PATH'];
        $line = strftime('%d.%m.%Y %H:%M:%S', time()).
                "\t|\t".
                implode("\t",$recipient).
                "\t|\t".
                strip_tags($error);
        $line = str_replace(array("\r\n","\r","\n")," ", $line);
        file_put_contents($path.'/current.log',"\r\n".$line, FILE_APPEND | LOCK_EX);
    }

    public function resetPreferences($status=0) {

        $update = new cjoSql();
        $update->setTable(TBL_COMMUNITY_ARCHIV);
        $update->setWhere("status='1'");
        $update->setValue("status",$status);
        $update->Update();

        $this->setPreferences(array());
    }

    public function prepareGroupLetter($new_prefs){

        global $CJO, $I18N_10;

        if (!is_array($new_prefs['groups'])) {
            cjoMessage::addError($I18N_10->msg('err_no_groupselected'));
            return false;
        }
        
        @set_time_limit(180);
        @ini_set("memory_limit", "256M");

        $this->newLogfile();
        $this->resetprepearedRecipients();
        $this->resetPreferences(-1);

        $this->clang        = isset($new_prefs['clang']) ? $new_prefs['clang'] : cjo_request('clang','cjo-clang-id', 0);
        $this->subject      = $new_prefs['GL_SUBJECT'];
        $this->group_ids    = implode(' | ', $new_prefs['groups']);
        $this->article_id   = ($new_prefs['SEND_TYPE'] == 'text' ? -1 : $new_prefs['DEFAULTLETTER']);
        $this->template     = $new_prefs['TEMPLATE'];
        $this->text         = $new_prefs['TEXT'];
        $this->html         = $this->getArticle($new_prefs['DEFAULTLETTER'], $this->clang, $this->template);
        $this->mail_account = $new_prefs['MAIL_ACCOUNT'];
        $this->content      = ($new_prefs['SEND_TYPE'] == 'text' ? $this->text : $this->html);
        $this->atonce       = $new_prefs['ATONCE'] < 1 ? 1 : $new_prefs['ATONCE'];

        foreach($new_prefs['groups'] as $id) {
            $this->prepareGroups($id);
        }
        
        if ($this->prepared > 0) {
             return $this->savePreferences();
        }
        else {
            cjoMessage::addError($I18N_10->msg('err_no_recipients_selected'));
            return false;
        }
        return false;
    }

    private function prepareGroups($re_id){

        $this->prepareRecipients($re_id);
   
        $sql = new cjoSql();
        $qry = "SELECT id FROM ".TBL_COMMUNITY_GROUPS." WHERE re_id='".$re_id."'";
        $groups = $sql->getArray($qry);

        foreach ($groups as $group) {
            if ($group['id'] == $re_id) continue;
            $this->prepareGroups($group['id']);
        }   
    }
    
    public function resetprepearedRecipients(){
        
        global $CJO, $I18N_10; 

        $path = $CJO['ADDON']['settings'][self::$mypage]['PREPARED_PATH'];
        $date = strftime('%Y-%m-%d_%H-%M-%S', time());
        $content = cjoImportExport::generateSqlExport(array(TBL_COMMUNITY_PREPARED));
        
        if (!file_exists($path)){
            @mkdir($path);
            @chmod($path, $CJO['FILEPERM']);
        }
        
        if (!cjoGenerate::putFileContents($path.'/'.$date.'.sql',$content)) {
            cjoMessage::addError($I18N_10->msg("msg_err_get_prepared_recipients"));
            return false;
        }

        $sql = new cjoSql();
        $sql->setDirectQuery("TRUNCATE TABLE ".TBL_COMMUNITY_PREPARED);
        
        if ($sql->getError() != '') {
            cjoMessage::addError($I18N_10->msg("msg_err_get_prepared_recipients").'<br/>'.$sql->getError());
            return false;
        }
        return true;
    }

    private function prepareRecipients($group_id) {

        global $CJO;
        
        $sql = new cjoSql();
                
        $qry = "SELECT a.user_id as user_id
                FROM ".TBL_COMMUNITY_UG."  a
                LEFT JOIN ".TBL_COMMUNITY_USER." b
                ON a.user_id=b.id
                WHERE a.group_id='".$group_id."'
                AND b.activation = 1
                AND b.newsletter = 1
                AND b.status = 1
                AND b.clang = ".$this->clang."
                ORDER BY b.id";

        $recipients = $sql->getArray($qry);

        $insert = & $sql;

        foreach($recipients as $recipient) {

            $insert->flush();
            $insert->setTable(TBL_COMMUNITY_PREPARED);
            $insert->setValue('user_id', $recipient['user_id']);
            $insert->setValue('article_id', $this->article_id);
            $insert->setValue('clang', $this->clang);
            $insert->setValue('status', '1');
            $insert->Insert();
            if ($insert->getError() == '') $this->prepared++;
        }
        return true;
    }

    public function sendPrepared(){

        global $CJO, $I18N, $I18N_10;

        if (!$this->hasContent()) {
            cjoMessage::addError($I18N_10->msg("msg_err_no_content"));
            return false;
        }

        $sql = new cjoSql();
        $qry = "SELECT
                    us.id AS user_id,
                    gender,
                    firstname,
                    name,
                    email,
                    bounce,
                    us.activation_key AS activation_key
                FROM
                    ".TBL_COMMUNITY_PREPARED." pr
                LEFT JOIN
                    ".TBL_COMMUNITY_USER." us
                ON
                    pr.user_id=us.id
                WHERE
                    pr.clang='".$this->clang."' AND
                    pr.status = '1' AND
                    us.status = '1' AND
                    us.newsletter = '1'
                    LIMIT ".$this->atonce ;

        $recipients = $sql->getArray($qry);

        if ($sql->getError() != '') {
            cjoMessage::addError($I18N_10->msg("msg_err_get_prepared_recipients").'<br/>'.$sql->getError());
            return false;
        }

        if (count($recipients) == 0) {

            cjoMessage::addSuccess($I18N_10->msg("msg_all_gl_send",
                                                 $this->send,
                                                 $this->prepared,
                                                 $this->errors));
            if ($this->errors > 0) {
                $this->resetPreferences(0);
            } else {
                $this->resetPreferences(2);
            }
            return false;
        }
        
        $this->embedImages();

        $sql->flush();
        $update = $sql;
        $user = new cjoSql();

        foreach ($recipients as $recipient) {

            $update->flush();
            $update->setTable(TBL_COMMUNITY_PREPARED);
            $update->setWhere('user_id='.$recipient['user_id'].' LIMIT 1');

            $this->setRecipient($recipient);

            if ($this->sendGroupletter()) {
                $update->setValue("status", '0');
                $this->send++;
                
                $recipient['bounce'] = (int) $recipient['bounce'];
                
                if ($recipient['bounce'] > 0) {
                    $user->flush();
                    $user->setTable(TBL_COMMUNITY_USER);
                    $user->setWhere('id='.$recipient['user_id'].' LIMIT 1');
                    $user->setValue('bounce', ($recipient['bounce']-1));
                    $user->Update();
                }
            }
            else {
                $update->setValue("status", '-1');
                $update->setValue("error", $this->mail_error);
                $this->errors++;
                $this->logError($recipient,$this->mail_error);
            }
            $update->Update();
  

            $temp = array();

            if (!empty($this->user))
                $temp[] = $this->user;

            if (strpos($this->user, $CJO['USER']->getValue('name')) === false)
                $temp[] = $CJO['USER']->getValue('name');

            $this->user = implode(', ',$temp);

            $update->flush();
            $update->setTable(TBL_COMMUNITY_ARCHIV);
            $update->setValue("send",$this->send);
            $update->setValue("error",$this->errors);
            if (!$this->firstsenddate)
                $update->setValue("firstsenddate", time());
            $update->setValue("lastsenddate", time());
            $update->setValue("user", $this->user);
            $update->setWhere('status=1');
            $update->Update();
        }

        $this->mail->SmtpClose();

        if (($this->send+$this->errors) == $this->prepared){
            if (!$this->sendPrepared()) return false;
        }
        return true;
    }

    /**
     * Object: Text personalisieren
     */
    private function personalize($optin=false) {

        global $CJO;

        $email     = $this->recipient['email'];
        $name      = $this->recipient['name'];
        $firstname = $this->recipient['firstname'];
        $gender    = $this->recipient['gender'];
        $user_id   = $this->recipient['user_id'];
        $group_id  = $this->recipient['group_id'];
        $activation_key  = $this->recipient['activation_key'];        
        $clang     = $this->clang;
        $subject   = $this->subject;
        $html      = $this->html;
        $text      = $this->text;

        $signout_id = $CJO['ADDON']['settings'][self::$mypage]['NL_SIGNOUT'];

        if (OOArticle::isOnline($signout_id)) {
            $param = array("UID" => $user_id+$this->inst,"USR" => $activation_key);
            $linktext = ($optin) ? '[translate: subscribe_newsletter]' : '[translate: unsubscribe_newsletter]';
            $linktext = cjoOpfLang::translate($linktext);
            $url = cjoRewrite::getUrl($signout_id, $clang, $param);
            $link = '<a href="'.$url.'">'.$linktext.'</a>';
        }

        $this->mail->ClearAddresses();
        $this->mail->AddAddress($email, $firstname.' '.$name);

        if ($subject != "") {
            $subject = str_replace("%25", "%", $subject); 
            $subject = str_replace("%email%", $email, $subject);
            $subject = str_replace("%name%", ' '.$name, $subject);
            $subject = str_replace("%firstname%", ' '.$firstname , $subject);
            $subject = str_replace("%user_id%", $user_id, $subject);
            $subject = str_replace("%group_id%", $group_id , $subject);
            $subject = str_replace("%clang%", $clang, $subject);
            $subject = preg_replace_callback("#%([^<^>^%]+)%#imsU",array(&$this, "replaceTitleType"), $subject);
            $this->mail->Subject = $subject;
        }

        if ($this->article_id != -1) {
            $html = str_replace("%25", "%", $html); 
            $html = str_replace("%email%", $email, $html);
            $html = str_replace("%name%", ' '.$name, $html);
            $html = str_replace("%firstname%", ' '.$firstname, $html);
            $html = str_replace("%link_url%", $url, $html);
            $html = str_replace("%user_id%", $user_id, $html);
            $html = str_replace("%group_id%", $group_id, $html);
            $html = str_replace("%clang%", $clang, $html);
            
            if ($url != "") {
                $html = str_replace("%link%", $link, $html);
            }
            $html = preg_replace_callback( "#%([^<^>^%]+)%#imsU",array(&$this, "replaceTitleType"),$html);
        }

        $text = str_replace("%25", "%", $text); 
        $text = str_replace("%email%", $email, $text);
        $text = str_replace("%name%", $name, $text);
        $text = str_replace("%firstname%", $firstname, $text);
        $text = str_replace("%link_url%", $url, $text);
        $text = str_replace("%user_id%", $user_id, $text);
        $text = str_replace("%group_id%", $group_id, $text);
        $text = str_replace("%clang%", $clang, $text);
        
        if ($url != "") {
            $text = str_replace("%link%", $url, $text);
        }
 
        $text = preg_replace_callback( "#%([^<^>^%]+)%#imsU",array(&$this, "replaceTitleType"),$text);

        if ($this->article_id == -1){
            $this->mail->Body = $text;
        }
        else {
            $this->mail->AltBody = $text;
            $this->mail->Body = $html;
        }
    }

    /**
     * Object: Newsletter erstellen und senden. Der Newsletter wird auch gleich personalisiert.
     */
    function sendGroupletter($optin = false) {

        if (!$this->hasContent()) return false;
            
        if (!$this->mail->account_id || $this->mail_account) {
           $this->mail->setAccount($this->mail_account);
        }
        $this->mail->footer = null;
        $this->personalize();

        if ($this->mail->Send()) return true;
        $this->mail_error = $this->mail->ErrorInfo;
        
        return false;
    }

    /**
     * Intern: Ersetzung der Geschlechtsspezifischen Textelemente
     * @param $m Liste der Textelemente zum Ersetzen
     *
     * @return Ausgewählter Text
     */
    public function replaceTitleType($m) {
        if (rawurldecode($m[0]) != $m[0]) return $m[0];
        $gender = $this->recipient['gender'];
        preg_match('/(?<=\|'.$gender.'\=|^'.$gender.'\=).*?(?=\||$)/', trim($m[1]), $matches);
        return $matches[0];
    }

    public function setRecipient($recipient) {
        if (!is_array($recipient)) return false;
        $this->recipient = $recipient;
    }

    public function setBodyText($text) {
        $this->html = '';
        $this->text = stripslashes($text);
    }

    public function setBodyHtml($html) {

        $html = cjoOpfLang::translate($html);

        $text = str_replace (array("<br />","<br/>","<br>"),"\r\n",$html);
        $text = preg_replace ('/(\\n|\\r|\\r\\n|\\s){2,}/', "\r\n", $text);

        $text = preg_replace ('#<script[^>]*?>.*?<\/script>#si', ' ', $text);
        $text = preg_replace ('#<style[^>]*?>.*?<\/style>#siU', ' ', $text);
        $text = preg_replace ('#<![\s\S]*?--[ \t\n\r]*>#', ' ', $text);
        $text = preg_replace ("#<h1[^>]*>(.*)</h1>#siU","\r\n\r\n*\\1*\r\n",$text);
        $text = preg_replace ("#<(h[2-6]|p)[^>]*>(\s*<a[^>]*>)(.*)(</a>)?</\\1>#imsU","\r\n<\\1>\\3</\\1>\r\n",$text);
        $text = preg_replace ("#<h[2-6][^>]*>(.*)</h[2-6]>#siU","\r\n__\r\n\r\n\\1\r\n",$text);
        $text = preg_replace ('#<a[^>]*href="(.*)"[^>]*>(.*)</a>#siU', "\\2 \\1",$text);
        $text = preg_replace ("#(\<)(.*)(\>)#imsU", "\r\n",  $text);
        $text = preg_replace ('/(\\n|\\r|\\r\\n|\\s){2,}/', "\r\n\r\n", $text);        
        $text = html_entity_decode($text);

        $temp = explode("\n", $text);
        $text = '';

        foreach ($temp as $line){
            $line = str_replace(' *', '*', $line);
            $line = str_replace('&bdquo;', '"', $line);
            $line = str_replace('&euro;', 'EUR', $line);
            $line = wordwrap( $line, 70, "\r\n" );
            $text .= $line;
        }

        $this->html = wordwrap( $html, 240, "\r\n" );
        $this->text = $text;
        $this->embedImages();
    }
    
    private function embedImages() {
        
        if (empty($this->html) || !isset($this->mail) || !is_object($this->mail)) return false;
        
        preg_match_all('/cid:([^"|^\)]+)(?="|\))/', $this->html, $matches, PREG_SET_ORDER);
        
        if (!is_array($matches)) return false;
        $processed = array();

        foreach($matches as $match) {
            
            $search   = $match[0];
            $path     = $match[1];
            $cid      = md5($path);
            $name     = pathinfo($path, PATHINFO_BASENAME);

            if (isset($processed[$cid])) continue;

            $this->mail->AddEmbeddedImage($path, $cid, $name);

            $this->html = str_replace($search,'cid:'.$cid, $this->html);
            
            $processed[$cid] = true;
        }

        return true;
    }

    public function getArticle($article_id, $clang=false, $template_id, $ctype=-1) {

        global $CJO, $I18N_10;

        if ($clang === false) {
            $clang = $CJO['CUR_CLANG'];
        }

        if (!OOArticle::isOnline($article_id)) {
            cjoMessage::addError($I18N_10->msg('label_defaultletter_offline'));
            return false;
        }

        cjoExtension::registerExtension('GENERATE_URL', 'cjoCommunityExtension::changeNewsletterUrls');

        cjoGenerate::deleteGeneratedArticle($article_id);
        $CJO['CONTEJO'] = false;
        $CJO['ARTICLE_ID'] = $article_id;

        $article = new cjoArticle();
        $article->setArticleId($article_id);
        $article->setClang($clang);
        $article->setTemplateId($template_id, true);
        $content = $article->getArticleTemplate(); 
        $content = cjoOutput::replaceLinks($content);
        $content = cjoOpfLang::translate($content);
        $content = cjoOutput::prettifyOutput($content);        
        $CJO['CONTEJO'] = true;

        $base  = cjoRewrite::setServerUri(true, false);
        $base .= cjoRewrite::setServerPath();

        $content = str_replace(array('"../','"./'), '"'.$base, $content);
        $content = str_replace(array('(../','(./'), '('.$base, $content);

        $content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER', array('subject' => $content, 'environment' => 'frontend'));

        return $content;
    }
    
    private function validateMailAccount() {
        
        global $CJO;
        $default_account = $CJO['ADDON']['settings'][self::$mypage]['MAIL_ACCOUNT'];
        
        if ($this->mail_account == $default_account) return true;
        
        $sql = new cjoSql();
        $qry = "SELECT id
                FROM ".TBL_20_MAIL_SETTINGS."
                WHERE id='".$this->mail_account."'";
        $sql->setQuery($qry);
        
        if ($sql->getRows() == 1) return true;
        
        $this->mail_account = $default_account;
    }

    private function hasContent() {
        return trim($this->html.$this->text) != '';
    }
    
    public static function currentNumbers(){
            
        global $I18N;
               
        $dec_point = trim($I18N->msg('dec_point'));
        $thousands_sep = trim($I18N->msg('thousands_sep'));
        
        $sql = new cjoSql();
        $qry = "SELECT status, count(*) AS number FROM ".TBL_COMMUNITY_PREPARED." GROUP BY status";
        $temp = $sql->getArray($qry); 
        
        $results = array('open' => 0, 'send'=> 0, 'errors'=> 0);
        
        if ($sql->getRows() > 0) {
            foreach($temp as $key=>$val) {
                $number = number_format($val['number'], 0, $dec_point, $thousands_sep);
                switch($val['status']) {
                    case -1: $results['errors'] = $number; break;
                    case 0: $results['send'] = $number; break;
                    case 1: $results['open'] = $number; break;
                }
            }
        }
        return json_encode($results);
    }
    
    public static function ajaxSend() {
        $groupletter = new cjoGroupLetter();
        $status = $groupletter->sendPrepared();
        cjoMessage::flushAllMessages();
        return $status;
    }
}