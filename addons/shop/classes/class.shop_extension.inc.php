<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.6.0
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
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


class cjoShopExtension {
    
    protected static $mypage = 'shop';

    public static function copyConfig($params) {

    	global $CJO, $I18N;

    	if (empty($params['id']) || cjoMessage::hasErrors()) return false;

    	$new_clang = $params['id'];
        $files     = array( array($CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/0.clang.inc.php',
        	                      $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/'.$params['id'].'.clang.inc.php'),
        	                array($CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/0.business_terms.txt',
        	                      $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/'.$params['id'].'.business_terms.txt'),
                            array($CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/0.confirm_mail.html',
                                  $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/'.$params['id'].'.confirm_mail.html'),
                            array($CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/0.send_mail.html',
                                  $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/'.$params['id'].'.send_mail.html')                                          	                      
        	               );

    	foreach ($files as $file) {

        	if (!file_exists($file[0]) ||
        	    !copy($file[0], $file[1])) {
    			cjoMessage::addError($I18N->msg("err_config_file_copy", $file[1]));
    		}
    		else {
    		    @chmod($file[1], $CJO['FILEPERM']);
    		}
    	}
    }

    public static function copyAttributes($params) {

    	global $CJO, $I18N_21;

    	$new_clang = $params['id'];

    	if(empty($new_clang) || cjoMessage::hasErrors()) return false;

        $sql = new cjoSql();
        $qry = "SELECT * ".TBL_21_ATTRIBUTE_TRANSLATE." WHERE clang='0'";
        $results = $sql->getArray($qry);

        $insert = $sql;

        foreach($results as $result){

            $insert->flush();
            $insert->setTable(TBL_21_ATTRIBUTE_TRANSLATE);
            foreach($result as $field=>$value){
                $insert->setvalue($field, $value);
            }
            $insert->setvalue('clang', $new_clang);
            $insert->Insert();

            if ($insert->getError() != '') {
                cjoMessage::addError($insert->getError());
            }
        }

        if (!cjoMessage::hasErrors()) {
            cjoMessage::addSuccess($I18N_21->msg("msg_shop_attributes_copied"));
        }
    }

    public static function replaceVars($params){

        global $CJO;

    	$content = $params['subject'];
        $css = '';

    	$css_file = $CJO['ADDON']['settings'][self::$mypage]['CSS']['FRONTEND'];

    	if (file_exists($css_file)) {
    	    $css .= "\r\n".'<link type="text/css" href="'.$css_file.'" rel="stylesheet" />';
    	}

    	$css_file = $CJO['ADDON']['settings'][self::$mypage]['CSS']['FRONTEND_IE'];

    	if (file_exists($css_file)) {
    	    $css .= "\r\n".'<!--[if lte IE 8]>'."\r\n".
    	    		'<link type="text/css" href="'.$css_file.'" rel="stylesheet" />'."\r\n".
    	            '<![endif]-->'."\r\n";
    	}


    	$content = preg_replace('/<\/head>/i', $css."\r\n".'</head>', $content, 1);

    	if (strpos($content,'[[SHOP_BASKET]]') !== false &&
    	    $CJO['ARTICLE_ID'] == $CJO['ADDON']['settings'][self::$mypage]['BASKET_ARTICLE_ID']) {
    		$content = str_replace('[[SHOP_BASKET]]', cjoShopBasket::out(), $content);
		}

        if (strpos($content,'[[SHOP_CHECKOUT]]') !== false &&
    	    $CJO['ARTICLE_ID'] == $CJO['ADDON']['settings'][self::$mypage]['CHECKOUT_ARTICLE_ID']) {
        	include_once $CJO['ADDON_PATH']."/".self::$mypage."/include/checkout.inc.php";
    		$content = str_replace('[[SHOP_CHECKOUT]]', $checkout, $content);
		}

    	if (strpos($content,'[[SHOP_BASKET_INFO]]') !== false) {
            	$content = str_replace('[[SHOP_BASKET_INFO]]', cjoShopBasket::basketInfo() , $content);
		}

        if (strpos($content,'[[SHOP_NAV]]') !== false) {

            $articles             = array();
            $articles['delivery'] = $CJO['ADDON']['settings'][self::$mypage]['DELIVERY_ARTICLE_ID'];
            $articles['basket']   = $CJO['ADDON']['settings'][self::$mypage]['BASKET_ARTICLE_ID'];
            $articles['checkout'] = $CJO['ADDON']['settings'][self::$mypage]['CHECKOUT_ARTICLE_ID'];

            foreach($articles as $key=>$article) {
                $article = OOArticle::getArticleById($article);
                if (!OOArticle::isValid($article)) continue;
                $articles[$key] = $article->toLink();
            }

            $html_tpl_content = @file_get_contents($CJO['ADDON']['settings'][self::$mypage]['HTML_TEMPLATE']['SHOP_NAV']);
	        $html_tpl = new cjoHtmlTemplate($html_tpl_content);
            // fill template with values
            $html_tpl->fillTemplate('TEMPLATE', array('DELIVERY_LINK' => $articles['delivery'],
                                                      'BASKET_LINK'	  => $articles['basket'],
                                                      'CHECKOUT_LINK' => $articles['checkout']));
            $content = str_replace('[[SHOP_NAV]]', $html_tpl->get(false), $content);
        }

        if (strpos($content,'[[CURRENCY_SELECT]]') !== false) {
    		$content = str_replace('[[CURRENCY_SELECT]]', cjoShopPrice::selectCurrency(), $content);
        }


    	return $content;
    }
    
    /**
     * A function to replace any character by another one in
     * a string or an array. This function support multi-
     * dimensional arrays.
     * 
     * @recursive
     * @param string $char - character to replace
     * @param string $esc - new character
     * @param array/string $value - string in that the char shall be replaced
     * @return string - masked string
     */
    public static function maskString($char, $esc, $values){
    	
    	if(is_array($values))
    	{
    		foreach($values as $key => $value){
    			$values[$key] = self::maskString($char, $esc, $values[$key]);
    		}
    		return $values;
    	}
    	else{
    		return	str_replace($char, $esc, $values);
    	}
    }
    
    /**
     * A function to replace several characters within a string 
     * by another one.
     * @param array $array - the replacement values, array-keys must be the characters
     * 						 to be replaced, values the new characters
     * @param array/string $values - the string or array of strings in that shall be replaced
     * @return string - the string with replaced characters
     * @see self->maskString
     */
    public static function maskStringArray($array, $values){
    	
    	foreach($values as $key => $value){
    		$value = self::maskString($key, $value, $string);
    	}
   
    	return $values;
    }
    /**
     * Function subtitutes a '|'-character by its html-unicode-sequence.
     * 
     * @param string/array $string - the string to mask
     * @return string - the masked string
     * @see self->maskChar
     */
    public static function maskPipe($string){    	
    	return self::maskString('|', '&#124', $string);
    }
}

cjoExtension::registerExtension('CLANG_ADDED', 'cjoShopExtension::copyConfig');
cjoExtension::registerExtension('CLANG_ADDED', 'cjoShopExtension::copyAttributes');

if ($CJO['CONTEJO']) return false;

cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoShopExtension::replaceVars');
