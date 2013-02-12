<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  phpmailer
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


class cjoPHPMailer extends PHPMailer {

    /**
     * Sets the CharSet of the message.
     * @var string
     */
    public $CharSet           = 'utf-8';

    /**
     * Id of the cjoPHPMailer-Account
     * @var int
     */
    public $account_id;

    /**
     * The Mail-Footer
     * @var string
     */
    public $footer;

    /**
     * Enables the archiv functionality.
     * @var boolean
     */
    public $archiv_enabled;

    /**
     * Sets the language for all class error messages.
     * Returns false if it cannot load the language file.  The default language is English.
     * @param string $langcode ISO 639-1 2-character language code (e.g. Portuguese: "br")
     * @param string $lang_path Path to the language file directory
     * @access public
     */
    function SetLanguage($iso = null, $path = null)  {

        global $CJO;

        if ($iso == null) $iso = $CJO['CLANG_ISO'][$CJO['CUR_CLANG']];
        if ($path == null) $path = $CJO['ADDON_PATH']."/phpmailer/local/";

        parent :: SetLanguage($iso, $path);
    }

    /**
     * Adds more than one "To" address (comma-separated).
     * @param string $addresses
     * @return boolean true on success, false if address already used
     */
    public function AddAddresses($addresses) {

        $state = true;
        
        foreach(cjoAssistance::toArray($addresses, ',') as $adress) {
            $adress = trim($adress);
            $adress = preg_split('/\s+/', $adress, 2);
            if (empty($adress[0])) continue;
            if (!isset($adress[1])) $adress[1] = '';
            if (!$this->AddAddress($adress[0], $adress[1])){
                $state = false;
            }
        }
        return $state;
    }

    /**
     * Adds more than one "Cc" address (comma-separated).
     * Note: this function works with the SMTP mailer on win32, not with the "mail" mailer.
     * @param string $addresses
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function AddCCs($addresses, $name = '') {

        foreach(cjoAssistance::toArray($addresses, ',') as $adress) {
            $adress = preg_split('/\s+/', $adress, 2);
            if(empty($adress[0])) continue;
            return $this->AddCC($adress[0], $adress[1]);
        }
    }

    /**
     * Adds more than one "Bcc" address (comma-separated).
     * Note: this function works with the SMTP mailer on win32, not with the "mail" mailer.
     * @param string $addresses
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function AddBCCs($addresses, $name = '') {

        foreach(cjoAssistance::toArray($addresses, ',') as $adress) {
            $adress = preg_split('/\s+/', $adress, 2);
            if(empty($adress[0])) continue;
            return $this->AddBCC($adress[0], $adress[1]);
        }
    }

    public function setAccount($id) {

        global $CJO, $I18N_20;

        $this->SetLanguage();

        if ($this->account_id == $id) return false;

        $sql = new cjoSql();
        $qry = "SELECT *
        		FROM ".TBL_20_MAIL_SETTINGS."
        		WHERE (id='".$id."' AND status!='0') OR status='-1'
        		ORDER BY status DESC LIMIT 1";
        $sql->setQuery($qry);

        if ( $sql->getRows() != 1) {
            cjoMessage::addError($I18N_20->msg('err_account_is_empty'));
            return false;
        }

        switch ($sql->getValue('mailer')){

            case 'mail':
                $this->IsMail();
                break;

            case 'sendmail':
                $this->IsSendmail();
                break;

            case 'smtp':
                $this->IsSMTP();
                $this->SMTPAuth   = $sql->getValue('smtp_auth');
                $this->Host       = $sql->getValue('host');
                $this->Username   = $sql->getValue('username');
                $this->Password   = $sql->getValue('password');
                $this->Password   = $sql->getValue('password');
                $this->AddBCCs($sql->getValue('bcc'));
                $this->SMTPKeepAlive = true;
                break;
            default: return false;
        }

       	$this->From       = $sql->getValue('from_email');
       	$this->FromName   = $sql->getValue('from_name');
       	$this->WordWrap   = 80;
        $this->footer     = $sql->getValue('footer');
       	$this->AddBCC($sql->getValue('bcc'));
       	return true;
    }

    public function setBodyHtml($html, $text=false) {

        $html = cjoOpfLang::translate($html);

        if ($text === false) {
                $text = str_replace (array("<br />","<br/>","<br>"),"\r\n",$html);
                $text = preg_replace ('#<script[^>]*?>.*?<\/script>#si', ' ', $text);
                $text = preg_replace ('#<style[^>]*?>.*?<\/style>#siU', ' ', $text);
                $text = preg_replace ('#<![\s\S]*?--[ \t\n\r]*>#', ' ', $text);
                $text = preg_replace ("#<h1[^>]*>(.*)</h1>#siU","\r\n\r\n*\\1*\r\n",$text);
                $text = preg_replace ("#<(h[2-6]|p)[^>]*>(\s*<a[^>]*>)(.*)(</a>)?</\\1>#imsU","\r\n<\\1>\\3</\\1>\r\n",$text);
                $text = preg_replace ("#<h[2-6][^>]*>(.*)</h[2-6]>#siU","\r\n__\r\n\r\n\\1\r\n",$text);
                $text = preg_replace ('#<a[^>]*href="(.*)"[^>]*>(.*)</a>#siU', "\\2 \\1",$text);
                $text = preg_replace ("#(\<)(.*)(\>)#imsU", "\r\n",  $text);
        }
        
        $text = str_replace(array('\r\n','\n','\r'), "\r\n", $text);    
        $text = preg_replace ('/(\\r\\n){2,}/', "\r\n\r\n", $text);        
        $text = html_entity_decode($text);

        preg_match_all('/^.*$/m', $text, $temp);
        $text = '';
        foreach ($temp[0] as $line){
            $line = str_replace(' *', '*', $line);
            $line = str_replace('&bdquo;', '"', $line);
            $line = str_replace('&euro;', 'EUR', $line);
            $line = wordwrap( $line, 70, "\r\n" );
            $text .= $line;
        }
        
        $this->IsHTML(true);
        $this->Body = $html;
        $this->AltBody = $text;
        $this->embedImages();
    }
    
    private function embedImages() {
        
        preg_match_all('/cid:([^"|^\)]+)(?="|\))/', $this->Body, $matches, PREG_SET_ORDER);
        
        if (!is_array($matches)) return false;
        $processed = array();

        foreach($matches as $match) {
            
            $search   = $match[0];
            $path     = $match[1];
            $cid      = md5($path);
            $name     = pathinfo($path, PATHINFO_BASENAME);

            if (isset($processed[$cid])) continue;

            $this->AddEmbeddedImage($path, $cid, $name);

            $this->Body = str_replace($search,'cid:'.$cid, $this->Body);
            
            $processed[$cid] = true;
        }

        return true;
    }

    /**
     * Enables the archiv functionality.
     * @param bool $enable
     * @return void
     */
    public function setArchiv($enable = true) {
        $this->archiv_enabled = $enable;
    }

    public function AddMedialistAttachment($medialist) {

        global $CJO;

        foreach(cjoAssistance::toArray($medialist, ',') as $filename) {

            $media_obj = OOMedia::getMediaByFileName($filename);

            if (!OOMedia::isValid($media_obj) ||
                !file_exists($media_obj->getFullPath())) continue;

            $path     = $media_obj->getFullPath();
            $name     = '';
            $encoding = 'base64';
            $type     = $media_obj->getType();

            parent::AddAttachment($path, $name, $encoding, $type);
        }
    }

    /**
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false.  Use the ErrorInfo
     * variable to view description of the error.
     * @return bool
     */
    public function Send($archiv = null) {

        global $CJO;

        if ($archiv !== null) {
            $this->setArchiv($archiv);
        }

        $this->PluginDir = $CJO['ADDON_PATH']."/phpmailer/classes/";

        if ($this->ContentType == 'text/html') {
            $this->AltBody .= "\r\n\r\n".$this->footer;
            $this->AltBody = html_entity_decode($this->AltBody);
        } elseif ($this->ContentType == 'text/plain') {
            $this->Body .= "\r\n\r\n".$this->footer;
            $this->Body = html_entity_decode($this->Body);
        }
        parent::Send();

        if ($this->archiv_enabled == true ||
            $this->IsError()) {

            $sender = !empty($this->Sender) ? $this->Sender : $this->From;

            $insert = new cjoSql();
            $insert->setTable(TBL_20_MAIL_ARCHIV);
            $insert->setValue('sender', $this->From);
            $insert->setValue('to', implode(', ', @array_map('implode', array(' '), $this->to)));
            $insert->setValue('cc', implode(', ', @array_map('implode', array(' '), $this->cc)));
            $insert->setValue('bcc', implode(', ', @array_map('implode', array(' '), $this->bcc)));
            $insert->setValue('subject', $this->Subject);
            $insert->setValue('message', $this->Body);
            $insert->setValue('error', $this->ErrorInfo);
            $insert->setValue('content_type', $this->ContentType);
            $insert->setValue('article_id', cjo_request('article_id', 'cjo-article-id'));
            $insert->setValue('clang', cjo_request('article_id', 'cjo-clang-id'));
            $insert->setValue('send_date', time());
            $insert->setValue('remote_addr', cjo_server('REMOTE_ADDR', 'string'));
            $insert->setValue('user_agent', cjo_server('HTTP_USER_AGENT', 'string'));
            $insert->setValue('request', cjo_server('REQUEST_URI', 'string'));
            $insert->Insert();
        }
        return !$this->IsError();
    }
}