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
 * CJO_FILE[1],
 * CJO_FILELIST[1],
 * CJO_FILE_BUTTON[1],
 * CJO_FILELIST_BUTTON[1],
 * CJO_MEDIA[1],
 * CJO_IMAGE[1],
 * CJO_IMAGE_LIST[1],
 * CJO_VIDEO_URL[1], 
 * CJO_VIDEO[1], 
 * CJO_VIDEO_LINK[1], 
 * CJO_MEDIALIST[1],
 * CJO_MEDIA_BUTTON[1],
 * CJO_MEDIALIST_BUTTON[1]
 *
 * Alle Variablen die mit CJO_FILE beginnnen sind als deprecated anzusehen!
 */

class cjoVarMedia extends cjoVars {
    // --------------------------------- Actions

    public function getACRequestValues($CJO_ACTION) {

        $values     = cjo_request('MEDIA', 'array');
        $listvalues = cjo_request('MEDIALIST', 'array');

        for($i = 1; $i <= 10; $i++) {
            $media     = isset($values[$i]) ? stripslashes($values[$i]) : '';
            $medialist = isset($listvalues[$i]) ? stripslashes($listvalues[$i]) : '';

            $CJO_ACTION['MEDIA'][$i]     = $media;
            $CJO_ACTION['MEDIALIST'][$i] = $medialist;
        }
        return $CJO_ACTION;
    }

    public function getACDatabaseValues($CJO_ACTION, & $sql) {

        for ($i = 1; $i <= 10; $i++) {
            $CJO_ACTION['MEDIA'][$i]     = $this->getValue($sql, 'file'.$i);
            $CJO_ACTION['MEDIALIST'][$i] = $this->getValue($sql, 'filelist'.$i);
        }
        return $CJO_ACTION;
    }

    public function setACValues(& $sql, $CJO_ACTION, $escape = false) {

        for ($i = 1; $i <= 10; $i++) {
            $this->setValue($sql, 'file'. $i    , $CJO_ACTION['MEDIA'][$i]    , $escape);
            $this->setValue($sql, 'filelist'. $i, $CJO_ACTION['MEDIALIST'][$i], $escape);
        }
    }

    // --------------------------------- Output

    public function getBEInput(& $sql, $content) {
        $content = $this->matchMediaButton($sql, $content);
        $content = $this->matchMediaListButton($sql, $content);
        $content = $this->matchImageListButton($sql, $content);
        $content = $this->getOutput($sql, $content);
        return $content;
    }

    public function getBEOutput(& $sql, $content) {
        $content = $this->getOutput($sql, $content);
        return $content;
    }

    /**
     * Ersetzt die Value Platzhalter
     */
    public function getOutput(& $sql, $content) {
        $content = $this->matchMedia($sql, $content);
        $content = $this->matchImage($sql, $content);
        $content = $this->matchVideoUrl($sql, $content);  
        $content = $this->matchVideoLink($sql, $content);      
        $content = $this->matchVideo($sql, $content);
        $content = $this->matchMediaList($sql, $content);
        $content = $this->matchImageList($sql, $content);
        return $content;
    }

    /**
     * @see cjo_var::handleDefaultParam
     */
    public function handleDefaultParam($varname, $args, $name, $value) {

        switch($name) {
            case '1' :
            case 'id' :                
                $args['id'] = (int) $value;
                break;  
            case 'file' :                              
            case 'types' :
            case 'class' :
            case 'mimetype':     
            case 'controls':                             
                $args[$name] = (string) $value;
                break;
            case 'preview' :
            case 'crop_auto' :  
            case 'autoplay' :   
            case 'get_src' :                                        
                $args[$name] = (boolean) $value;
                break;
            case 'crop_num' :
            case 'width' :
            case 'height' :
                $args[$name] = (int) $value;
                break;    
        }
        return parent::handleDefaultParam($varname, $args, $name, $value);
    }

    /**
     * MediaButton für die Eingabe
     */
    private function matchMediaButton(& $sql, $content) {

        $vars = array ('CJO_FILE_BUTTON', 'CJO_MEDIA_BUTTON');

        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
            foreach ($matches as $match) {

                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);
                
                if (!isset($args['width'])) $args['width'] = 300;          
                
                $params = array();
                $params['preview']['enabled'] = false;
                
                foreach($args as $key=>$value) {
                    
                    if ($key == 'width' || $key == 'height') {
                        if ($key == 'width') $params['style'] .= $key.':'.$value.'px;';
                        $params['preview'][$key] = $value-4;
                      if ($args['preview'] !== false) 
                            $params['preview']['enabled'] = '';
                       
                    } else if ($key == 'crop_auto') {
                        $params['preview']['crop_auto'] = $value;
                      if ($args['preview'] !== false) 
                            $params['preview']['enabled'] = '';
                                               
                    } else if ($key == 'crop_num') {
                        $params['preview']['crop_num'] = $value;
                        if ($args['preview'] !== false) 
                            $params['preview']['enabled'] = '';
                    }
                }
                
                if (isset($args['preview']) && $args['preview'] == true) {
                    $params['preview']['enabled'] = true;
                }
                
                if ($id <= 10 && $id > 0) {
                    $replace = $this->getMediaButton($id, $this->getValue($sql, 'file'.$id), false, $params);
                    $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
                    $content = str_replace($var.'['.$param_str.']', $replace, $content);
                }
            }
        }
        return $content;
    }

    /**
     * MediaListButton für die Eingabe
     */
    private function matchMediaListButton(& $sql, $content) {

        $vars = array ('CJO_FILELIST_BUTTON', 'CJO_MEDIALIST_BUTTON' );

        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);

            foreach ($matches as $match) {

                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);

                if ($id <= 10 && $id > 0) {
                    
                    if (!isset($args['width'])) $args['width'] = 300;
                    $args['width'] = $args['width'] .= 'px';
                    
                    $replace = $this->getMedialistButton($id, $this->getValue($sql, 'filelist'.$id), array('style' => 'width: '.$args['width']));
                    $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
                    $content = str_replace($var.'['.$param_str.']', $replace, $content);
                }
            }
        }

        return $content;
    }

    private function matchImageListButton(& $sql, $content) {

        $vars = array ('CJO_IMAGE_LIST_BUTTON');

        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
            foreach ($matches as $match) {

                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);

                if ($id <= 2 && $id > 0) {
                    $replace = $this->getImageListButton($id, $sql);
                    $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
                    $content = str_replace($var.'['.$param_str.']', $replace, $content);
                }
            }
        }
        return $content;
    }

    /**
     * Wert für die Ausgabe
     */
    private function matchMedia(& $sql, $content) {

        $vars = array('CJO_FILE', 'CJO_MEDIA');
        $performed = array();  
            
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
            
            foreach ($matches as $match) {
                
                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);

                if (!empty($performed[$var][$id]) || $id < 1 || $id > 10) continue;  
                 
                // Mimetype ausgeben
                if (isset($args['mimetype'])) {
                    $media   = OOMedia::getMediaByName($this->getValue($sql, 'file'.$id));
                    $replace = OOMedia::isValid($media) ? $media->getType() : '';
                } else {
                    $replace = $this->getValue($sql, 'file'.$id);
                }
                $replace = $this->handleGlobalVarParams($var, $args, $replace);
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
                $performed[$var][$id] = true;
            }
        }
        return $content;
    }
    
    /**
     * Wert für die Ausgabe
     */
    private function matchImage(& $sql, $content) {

        $vars = array('CJO_IMAGE');
        $performed = array();  
        
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
            
            foreach ($matches as $match) {
                
                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);
                list ($file, $args) = $this->extractArg('file', $args, false);

                if (!$file && (!empty($performed[$var][md5($param_str)]) || $id < 1 || $id > 10)) continue;   
                
                $params = array();
                if (isset($args['width']))        $params['width']     = $args['width']; 
                if (isset($args['height']))       $params['height']    = $args['height']; 
                if (isset($args['crop_num']))     $params['crop_num']  = $args['crop_num'];  
                      
                if (!empty($args['crop_auto']) && 
                    empty($params['crop_num']))   $params['crop_auto'] = true;  
                if (!empty($args['get_src']))     $params['get_src']   = true;                                            
                
                if (!$file) $file = $this->getValue($sql, 'file'.$id);

                if (OOMedia::isImage($file)) {
                    $replace = OOMedia::toThumbnail($file, false, $params);
                } else {
                    $replace = '<!-- Sorry, '.$file.' has no supported image format. Supported image formats: '.implode (', ' ,OOMedia::getImageTypes()).' -->';
                }
                $replace = $this->handleGlobalVarParams($var, $args, $replace);
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);                    
                $performed[$var][md5($param_str)] = true;
            }
        }
        return $content;
    } 


    /**
     * Wert für die Ausgabe
     */
    private function matchVideoUrl(& $sql, $content) {
        
        $vars = array('CJO_VIDEO_URL');
        $performed = array();  
        
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
        
            foreach ($matches as $match) {
                
                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);
                list ($file, $args) = $this->extractArg('file', $args, 0);         

                if (!$file && (!empty($performed[$var][md5($param_str)]) || $id < 1 || $id > 10)) continue;  
                
                if (!$file) $file = $this->getValue($sql, 'file'.$id);

                $mediaset = OOMedia::getMediaSetByName($file);
     
                if (OOMedia::isValid($mediaset['image']) && OOMedia::isValid($mediaset['video'])) {
                
                    $params = array();
                    if (isset($args['width']))        $params['width']     = $args['width']; 
                    if (isset($args['height']))       $params['height']    = $args['height']; 
                    if (isset($args['crop_num']))     $params['crop_num']  = $args['crop_num'];  
                          
                    if (!empty($args['crop_auto']) && 
                        empty($params['crop_num']))   $params['crop_auto'] = true;        

                    $image = OOMedia::toThumbnail($mediaset['image']->getFileName(), false, $params);

                    $params = array();
                    $params['html5video'] = $mediaset['video']->getId();                    
                    $params['width']      = $mediaset['image']->getWidth(); 
                    $params['height']     = $mediaset['image']->getHeight();   
                    $params['preload']    = (isset($args['preload']))   ? true : false;                                                   
                    $params['autoplay']   = (!isset($args['autoplay']) || !empty($args['autoplay']))  ? true : false;  
                    $params['controls']   = (!isset($args['autoplay']) || !empty($args['controls'])) ? true : false;
                    
                    $replace = cjoRewrite::getUrl(cjoProp::getArticleId(),$params['clang'],$params);
                }
                else {
                    $replace = '<!-- Sorry, MediaSet of '.$file.' has no supportet video file and/or no valid preview image. -->';
                }

                $replace = $this->handleGlobalVarParams($var, $args, $replace); 
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
                $performed[$var][md5($param_str)] = true;
            }
        }
        
        return $content;
    }   

    /**
     * Wert für die Ausgabe
     */
    private function matchVideoLink(& $sql, $content) {
        
        $vars = array('CJO_VIDEO_LINK');
        $performed = array();  
        
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
        
            foreach ($matches as $match) {
                
                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);
                list ($file, $args) = $this->extractArg('file', $args, 0);         

                if (!$file && (!empty($performed[$var][md5($param_str)]) || $id < 1 || $id > 10)) continue;  
                
                if (!$file) $file = $this->getValue($sql, 'file'.$id);

                $mediaset = OOMedia::getMediaSetByName($file);
                
                if (OOMedia::isValid($mediaset['image']) && OOMedia::isValid($mediaset['video'])) {
                
                    $params = array();
                    if (isset($args['width']))        $params['width']     = $args['width']; 
                    if (isset($args['height']))       $params['height']    = $args['height']; 
                    if (isset($args['crop_num']))     $params['crop_num']  = $args['crop_num'];  
                          
                    if (!empty($args['crop_auto']) && 
                        empty($params['crop_num']))   $params['crop_auto'] = true;        

                    $image = OOMedia::toThumbnail($mediaset['image']->getFileName(), false, $params);

                    $params = array();
                    $params['video']     = $mediaset['video']->getId();                    
                    $params['width']     = $mediaset['image']->getWidth(); 
                    $params['height']    = $mediaset['image']->getHeight();   
                    $params['preload']   = (isset($args['preload']))   ? true : false;                                                   
                    $params['autoplay']  = (!isset($args['autoplay']) || !empty($args['autoplay']))  ? true : false;  
                    $params['controls']  = (!isset($args['autoplay']) || !empty($args['controls'])) ? true : false;

                    $url = cjoUrl::getUrl(cjoProp::getArticleId(),$params['clang'],$params);
                    $css = !empty($args['class']) ? ' '.$args['class'] : ''; 
                    
                    $replace = sprintf('<a rel="videobox" href="%s" name="%s" '.
                                       'title="%s" class="imagelink zoom video%s">%s<span></span><small>%s</small></a>',
                                       $url,
                                       $mediaset['image']->getCopyright(),
                                       $mediaset['image']->getTitle(),
                                       $css,
                                       $image,
                                       $mediaset['image']->getDescription());
                }
                else {
                    $replace = '<!-- Sorry, MediaSet of '.$file.' has no supportet video file and/or no valid preview image. -->';
                }
                
                $replace = $this->handleGlobalVarParams($var, $args, $replace); 
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
                $performed[$var][md5($param_str)] = true;
            }
        }
        
        return $content;
    }   
    
    /**
     * Wert für die Ausgabe
     */
    private function matchVideo(& $sql, $content) {

        $vars = array('CJO_VIDEO');
        $performed = array();  
        
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
        
            foreach ($matches as $match) {
                
                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);
                list ($file, $args) = $this->extractArg('file', $args, 0);         

                if (!$file && (!empty($performed[$var][md5($param_str)]) || $id < 1 || $id > 10)) continue; 
                     
                if (!$file) $file = $this->getValue($sql, 'file'.$id);
                $media = OOMedia::getMediaSetByName($file); 

                $params = cjoHtml5Video::getVideoLink($file, $args, true);

                if (OOMedia::isValid($media['video'])) {    
                    $replace = sprintf('<iframe class="cjo_video_iframe" src="%s" width="%s" height="%s" frameborder="0" scrolling="no" allowtransparency="true"></iframe>',
                                        $params['url'], $params['width'], $params['height']);
                } else {
                    $replace = '<!-- Sorry, '.$file.' has no supported video format. Supported video formats: '.implode (', ' ,OOMedia::getVideoTypes()).' -->';
                }

                $replace = $this->handleGlobalVarParams($var, $args, $replace);     
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
                $performed[$var][md5($param_str)] = true;
            }
        }
        return $content;
    }        

    /**
     * Wert für die Ausgabe
     */
    private function matchMediaList(& $sql, $content) {

        $vars = array('CJO_FILELIST', 'CJO_MEDIALIST');
        $performed = array();  
        
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);
        
            foreach ($matches as $match) {

                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);

                if (!empty($performed[$var][$id]) || $id < 1 || $id > 10) continue;   
                    
                $replace = $this->getValue($sql, 'filelist'.$id);
                $replace = $this->handleGlobalVarParams($var, $args, $replace);
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
                $performed[$var][$id] = true;
            } 
        }
        
        return $content;
    }

    /**
     * Wert für die Ausgabe
     */
    private function matchImageList(& $sql, $content) {

        $vars = array('CJO_IMAGE_LIST');
        $performed = array();  

        $params = OOArticleSlice::getExtValue($sql->getValue("sl.value20"));
        foreach ($vars as $var) {

            $matches = $this->getVarParams($content, $var);

            foreach ($matches as $match) {

                list ($param_str, $args) = $match;
                list ($id, $args) = $this->extractArg('id', $args, 0);

                if (!empty($performed[$var][$id]) || $id < 0 && $id > 2) continue;

                if ($id == 1) {
                    $start = cjoProp::get('IMAGE_LIST_BUTTON|1|FROM');
                    $end   = cjoProp::get('IMAGE_LIST_BUTTON|1|TO');
                }
                else {
                    $start = cjoProp::get('IMAGE_LIST_BUTTON|2|FROM');
                    $end   = cjoProp::get('IMAGE_LIST_BUTTON|2|TO');
                }                
                $replace = '';

                for ($i = $start; $i <= $end; $i++){
                    $filename = $this->getValue($sql, 'file'.$i);

                    if ($filename == '') break;
                    $replace .= OOMedia::toResizedImage($filename, $params[$i], true);
                }

                $replace = $this->handleGlobalVarParams($var, $args, $replace);
                $content = preg_replace('/(?<!\[\[)'.$var.'\['.$param_str.'\](?!\]\])/', $replace, $content);
                $content = str_replace('[['.$var.'['.$param_str.']]]', $var.'['.$param_str.']', $content);
                $performed[$var][$id] = true;        
            }
        }
        return $content;
    }

    /**
     * Gibt das Button Template zurück
     */
    private function getMediaButton($id, $filename, $imagelist= false, $attributes = array('style'=> 'width: 300px'), $id_tag = 'cjo_mediabutton_') {

        if ($id >= cjoProp::get('IMAGE_LIST_BUTTON|1|FROM') &&
            $id <= cjoProp::get('IMAGE_LIST_BUTTON|1|TO')) {

            $end = cjoProp::get('IMAGE_LIST_BUTTON|1|TO');
        }
        elseif ($id >= cjoProp::get('IMAGE_LIST_BUTTON|2|FROM') &&
                $id <= cjoProp::get('IMAGE_LIST_BUTTON|2|TO')) {

            $end = cjoProp::get('IMAGE_LIST_BUTTON|2|TO');
        }

        $media_button = new cjoMediaButtonField('MEDIA['.$id.']', cjoI18N::translate('label_mediabutton'), $attributes, $id_tag.$id);
        $media_button->setValue($filename);
        $media_button->setDisconnectAction('cjo.jconfirm(\''.cjoI18N::translate('label_remove_media').' ?\', \'cjo.disconnectContentMedia\',['.$id.','.$end.']); return false;');

        if (!$imagelist) return $media_button->get();

        return sprintf ('<h3 title="%1$s">%1$s</h3><div class="container hide_me clearfix">%2$s</div>'."\r\n",
                        cjoI18N::translate('label_file'),
                        $media_button->get());

    }


    /**
     * Gibt das ListButton Template zurück
     */
    private function getMedialistButton($id, $medialist, $attributes = array ('style'=> 'float: left; width: 300px'), $id_tag = 'cjo_medialist_') {

        $medialist_button = new cjoMediaListField('MEDIALIST['.$id.']', cjoI18N::translate('label_medialist'), $attributes, $id_tag.$id);
        $medialist_button->setValue($medialist);
        return $medialist_button->get();
    }

    /**
     * Gibt das Button Template zur�ck
     */
    private function getImageListButton($id, $sql, $attributes = array ('style'=> 'width: 300px'), $id_tag = 'cjo_mediabutton_') {

        if ($id == 1) {
            $start = cjoProp::get('IMAGE_LIST_BUTTON|1|FROM');
            $end   = cjoProp::get('IMAGE_LIST_BUTTON|1|TO');
        }
        else {
            $start = cjoProp::get('IMAGE_LIST_BUTTON|2|FROM');
            $end   = cjoProp::get('IMAGE_LIST_BUTTON|2|TO');
        }

        if (empty($start) || empty($end)) return false;

        $params = OOArticleSlice::getExtValue($sql->getValue("sl.value20"));
        $output = '';

        for ($i = $start; $i <= $end; $i++){

            $filename = $this->getValue($sql, 'file'.$i);

            $output .= '<div id="cjo_imgslice_'.$i.'" class="settings cjo_imgslice"> '."\r\n".
                       '   '.$this->getImageListHead($i, $params, $filename)."\r\n".
                       '   '.$this->getImageListHeadButtons($i, $filename)."\r\n".
                       '    <div class="cjo_inputs">'.
                       '        '.$this->getMediaButton($i, $filename, true)."\r\n".
                       '        '.$this->getImageCropSettings($i, $params[$i])."\r\n".
                       '        '.$this->getImageDescriptionSettings($i, $params[$i], $filename)."\r\n".
                       '        '.$this->getImageFunctionSettings($i, $params[$i])."\r\n".
                       '        '.$this->getStyleSettings($i, $params[$i])."\r\n".
                       '    <div class="cjo_inputs_bottom"></div>'."\r\n".
                       '    </div>'."\r\n".
                       '</div>'."\r\n";

            if (!$filename) break;
        }
        return sprintf('<div class="clearfix imagelistbutton%s">%s</div>', $id, $output);
    }

    private function getImageListHead($i, $params, $filename) {

        $up_down = '';

        if ($filename != ''){

            $params[$i]['des']['settings'] = '-';

            $image = OOMedia::toResizedImage($filename, $params[$i], true);

            if ($i != cjoProp::get('IMAGE_LIST_BUTTON|1|FROM') &&
                $i != cjoProp::get('IMAGE_LIST_BUTTON|2|FROM')) {
                $up_down .= sprintf ('<input type="image" class="cjo_img_move_up" value="%s"
                                     title="%s" src="img/silk_icons/move_up_green.png" />',
                                     $i, cjoI18N::translate('button_move_up'));
            }

            if ($i != cjoProp::get('IMAGE_LIST_BUTTON|1|TO') &&
                $i != cjoProp::get('IMAGE_LIST_BUTTON|2|TO') &&
                !empty($params[($i+1)]['img'])) {
                $up_down .= sprintf ('<input type="image" class="cjo_img_move_down" value="%s"
                                     title="%s" src="img/silk_icons/move_down_green.png" />',
                                     $i, cjoI18N::translate('button_move_down'));
            }
            if ($up_down != '') {
                $up_down = '<div class="up_down">'.$up_down.'</div>';
            }
        }

        return sprintf ('<div class="heading">
                        <h2 class="no_bg_image" title="CJO_MEDIA&#91;%s&#93;">%s</h2>
                        %s</div><div class="cjo_img_prev">%s</div>',
                        $i,
                        (($filename != '') ? cjoI18N::translate('label_image') : cjoI18N::translate('label_image_add')),
                        $up_down,
                        $image);
    }

    private function getImageListHeadButtons($i, $filename) {

        if ($filename == '') {

            return sprintf ('<div class="cjo_imgslice_buttons">
                            <input type="image" class="first" name="imgslice_edit" value="%1$s"
                                title="%2$s" src="img/silk_icons/add.png" />
                            <input type="image" name="imgslice_update" value="%1$s"
                                title="%3$s" src="img/silk_icons/tick.png" /></div>',
                            $i,
                            cjoI18N::translate('label_toggle_inserts'),
                            cjoI18N::translate('button_update'));
        }

        if ($i >= cjoProp::get('IMAGE_LIST_BUTTON|1|FROM') &&
            $i <= cjoProp::get('IMAGE_LIST_BUTTON|1|TO')) {
            $end = cjoProp::get('IMAGE_LIST_BUTTON|1|TO');
        }
        elseif ($i >= cjoProp::get('IMAGE_LIST_BUTTON|2|FROM') &&
            $i <= cjoProp::get('IMAGE_LIST_BUTTON|2|TO')) {
            $end = cjoProp::get('IMAGE_LIST_BUTTON|2|TO');
        }

        return sprintf ('<div class="cjo_imgslice_buttons">
                        <input type="image" class="first" name="imgslice_edit" value="%1$s"
                          title="%3$s" src="img/silk_icons/page_white_edit.png" />
                        <input type="image" name="imgslice_update" value="%1$s"
                          title="%4$s" src="img/silk_icons/tick.png" />
                        <input type="image" name="imgslice_remove" value="%1$s|%2$s"
                          title="%5$s" src="img/silk_icons/cross.png" />
                        </div>',
                        $i, $end,
                        cjoI18N::translate('label_toggle_inserts'),
                        cjoI18N::translate('button_update'),
                        cjoI18N::translate('label_remove_media'));
    }

    private function getImageCropSettings($i, $params) {

        return sprintf ('<h3>%1$s</h3><div class="container hide_me clearfix">%2$s%3$s</div>'."\r\n",
                        cjoI18N::translate('label_size_format'),
                        cjoMedia::getCropSelection('CJO_EXT_VALUE['.$i.'][img][crop_num]',
                                                   $params['img']['crop_num']),
                        cjoMedia::getBrandSelection('CJO_EXT_VALUE['.$i.'][img][brand]',
                                                    $params['img']['brand'],
                                                    $params['img']['crop_num']));
    }

    private function getImageDescriptionSettings($i, $params, $filename) {

        if (!cjoProp::get('IMAGE_LIST_BUTTON|DESCRIPTION')) {
           return false;   
        }
        
        $sel = new cjoSelect;
        $sel->setName('CJO_EXT_VALUE['.$i.'][des][settings]');
        $sel->setStyle('class="cjo_select_box toggle_next"');
        $sel->setSize(1);
        $sel->addOption(cjoI18N::translate('label_without'), '-');
        $sel->addOption(cjoI18N::translate('label_media_description'), 1);
        $sel->addOption(cjoI18N::translate('label_individual_description'), 2);
        $sel->setSelected($params['des']['settings']);

        if (!empty($params['img'])){
            $media = OOMedia::getMediaByFileName($filename);
            $description = $media->getDescription(false, false);
        }

        return sprintf ('<h3>%s</h3><div class="container hide_me clearfix">%s
                        <textarea rows="2" cols="10" class="hide_me" disabled="disabled">%s</textarea>
                        <textarea rows="2" cols="10" name="CJO_EXT_VALUE[%s][des][2]" class="hide_me">%s</textarea>
                        </div>',
                        cjoI18N::translate('label_description'),
                        $sel->get(),
                        $description,
                        $i,
                        $params['des'][2]);

    }

    private function getImageFunctionSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|FUNCTIONS')) return false;

        $options = array('-' => 'WITHOUT', 1 => 'IMAGEBOX', 2 => 'FLASHBOX',
                          4 => 'INT_LINK', 5 => 'EXT_LINK', 6 => 'VIDEOBOX');

        $sel = new cjoSelect;
        $sel->setName('CJO_EXT_VALUE['.$i.'][fun][settings]');
        $sel->setStyle('class="cjo_select_box toggle_next"');
        $sel->setSize(1);
        $sel->setSelected($params['fun']['settings']);

        foreach($options as $key => $option) {

            /**
             * Do not delete translate values for cjoI18N collection!
             * [translate: label_without]
             * [translate: label_imagebox]
             * [translate: label_flashbox]
             * [translate: label_int_link]
             * [translate: label_ext_link]
             * [translate: label_videobox]
             */

            if (is_string($key) || cjoProp::get('IMAGE_LIST_BUTTON|'.$option)) {
                $name = cjoI18N::translate('label_'.strtolower($option));
                $sel->addOption($name, $key);
            }
        }

        $test = sprintf ('<h3>%s</h3><div class="container hide_me clearfix">%s%s%s%s%s%s</div>',
                        cjoI18N::translate('label_functions'),
                        $sel->get(),
                        $this->getZoomSettings($i, $params),
                        $this->getFlashSettings($i, $params),
                        $this->getIntLinkSettings($i, $params),
                        $this->getExtLinkSettings($i, $params),
                        $this->getVideoSettings($i, $params));
         return $test;
    }

    private function getZoomSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|IMAGEBOX')) return false;

        return sprintf ("\r\n".'<div class="hide_me"><label>%s</label>%s%s</div>'."\r\n",
                        cjoI18N::translate('label_size_format'),
                        cjoMedia::getCropSelection('CJO_EXT_VALUE['.$i.'][zoom][crop_num]',
                                                   $params['zoom']['crop_num']),
                        cjoMedia::getBrandSelection('CJO_EXT_VALUE['.$i.'][zoom][brand]',
                                                    $params['zoom']['brand'],
                                                    $params['zoom']['crop_num']));
    }

    private function getFlashSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|FLASHBOX')) return false;

        $button = new cjoMediaButtonField('CJO_EXT_VALUE['.$i.'][fun][swf][name]', cjoI18N::translate('label_mediabutton'));
        if (!empty($params['fun']['swf']['name']))
            $button->setValue($params['fun']['swf']['name']);

        $flash_params = sprintf ('<label style="clear:both;">%s</label>
                                 <input type="text" size="255" name="CJO_EXT_VALUE[%s][fun][swf][prams]" value="%s" />',
                                 cjoI18N::translate('label_get_param'),
                                 $i,
                                 $params['fun']['swf']['prams']);

        if (!cjoProp::getUser()->hasPerm('advancedMode[]')) $flash_params = '';

        return sprintf ("\r\n".'<div class="hide_me"><label>%2$s</label>%3$s<label>%4$s</label>
                        <input type="text" name="CJO_EXT_VALUE[%1$s][fun][swf][width]" value="%5$s" class="cjo_float_l"
                          style="width:30px!important" /><span class="cjo_float_l">&nbsp;x&nbsp;</span>
                        <input type="text" name="CJO_EXT_VALUE[%1$s][fun][swf][height]" value="%6$s" class="cjo_float_l"
                          style="width:30px!important" />&nbsp;px
                        <input type="hidden" name="CJO_EXT_VALUE[%1$s][fun][swf][grp]" value="%7$s" />%8$s</div>',
                        $i,
                        cjoI18N::translate('label_flash_file'),
                        $button->get(),
                        cjoI18N::translate('label_width_x_height'),
                        $params['fun']['swf']['width'],
                        $params['fun']['swf']['height'],
                        $params['fun']['swf']['grp'],
                        $flash_params);
    }

    private function getVideoSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|VIDEOBOX')) return false;

        //compatibility
        if (!empty($params['fun']['flv']) && empty($params['fun']['video'])) {
            $params['fun']['video'] = & $params['fun']['flv'];
        }
        
        $button = new cjoMediaButtonField('CJO_EXT_VALUE['.$i.'][fun][video][name]', cjoI18N::translate('label_mediabutton'));
        $button->setValue($params['fun']['video']['name']);
        
        if (empty($params['fun']['video']['width'])) $params['fun']['video']['width'] = cjoProp::get('VIDEO|DEFAULT_WIDTH');
        if (empty($params['fun']['video']['height'])) $params['fun']['video']['height'] = cjoProp::get('VIDEO|DEFAULT_HEIGHT');        

        return sprintf ("\r\n".'<div class="hide_me"><label>%2$s</label>%3$s<label>%4$s</label>
                        <input type="text" size="5" name="CJO_EXT_VALUE[%1$s][fun][video][width]" value="%5$s"
                          class="cjo_float_l" style="width:30px!important" /><span class="cjo_float_l">&nbsp;&times;&nbsp;</span>
                        <input type="text" size="5" name="CJO_EXT_VALUE[%1$s][fun][video][height]" value="%6$s"
                          class="cjo_float_l" style="width:30px!important;" />&nbsp;px<br/>
                        <input type="hidden" name="CJO_EXT_VALUE[%1$s][fun][video][autoplay]" value="false" />
                        <input type="checkbox" name="CJO_EXT_VALUE[%1$s][fun][video][autoplay]" value="true" %7$s/>
                        <label class="right">%8$s</label>
                        <input type="hidden" name="CJO_EXT_VALUE[%1$s][fun][video][controls]" value="false" />
                        <input type="checkbox" name="CJO_EXT_VALUE[%1$s][fun][video][controls]" value="true" %9$s
                          style="margin-left: 10px;" />
                        <label class="right">%10$s</label>                        
                        <input type="hidden" name="CJO_EXT_VALUE[%1$s][fun][video][grp]" value="%11$s" /></div>',
                        $i,
                        cjoI18N::translate('label_video_file'),
                        $button->get(),
                        cjoI18N::translate('label_width_x_height'),
                        $params['fun']['video']['width'],
                        $params['fun']['video']['height'],
                        cjoAssistance::setChecked($params['fun']['video']['autoplay'], array('true','')),
                        cjoI18N::translate('label_autoplay'),
                        cjoAssistance::setChecked($params['fun']['video']['controls'], array('true','')),
                        cjoI18N::translate('label_controls'),                        
                        $params['fun']['video']['grp']);
    }

    private function getIntLinkSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|INT_LINK')) return false;

        $button = new cjoLinkButtonField('CJO_EXT_VALUE['.$i.'][fun][int][id]', cjoI18N::translate('label_linkbutton'), array('class'=>'hide_me', 'style'=>'width: 200px'));
        $button->setValue($params['fun']['int']['id']);
        return sprintf ("\r\n".'<div class="hide_me">%s</div>', $button->get());
    }

    private function getExtLinkSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|EXT_LINK')) return false;

        $url = ($params['fun']['ext'] != '') ? $params['fun']['ext'] : 'http://www.';

        return sprintf ("\r\n".'<div class="hide_me"><input type="text" name="CJO_EXT_VALUE[%s][fun][ext]" value="%s" /></div>',
                        $i, $url);
    }

    private function getStyleSettings($i, $params){

        if (!cjoProp::get('IMAGE_LIST_BUTTON|STYLE') && !cjoProp::get('IMAGE_LIST_BUTTON|CSS')) return false;

        $style = sprintf('<label>Style</label><input type="text" name="CJO_EXT_VALUE[%s][stl][0]" value="%s" />',
                         $i, $params['stl'][0]);

        $sel = new cjoSelect;
        $sel->setName('CJO_EXT_VALUE['.$i.'][css][0]');
        $sel->setSize(1);
        $sel->setSelected($params['css'][0]);
        $sel->addOption('','');

        foreach(cjoAssistance::toArray(cjoProp::get('IMAGE_LIST_BUTTON|CSS')) as $key => $option) {
            $sel->addOption($option,$option);
        }

        $css = sprintf('<label>CSS</label>%s', $sel->get());

        if (!cjoProp::get('IMAGE_LIST_BUTTON|STYLE') || !cjoProp::getUser()->hasPerm('advancedMode[]')) $style = '';
        if (!cjoProp::get('IMAGE_LIST_BUTTON|CSS')) $css = '';

        return sprintf ('<h3>%s</h3><div class="container hide_me">%s%s</div>', cjoI18N::translate('label_style'), $style, $css);
    }

}