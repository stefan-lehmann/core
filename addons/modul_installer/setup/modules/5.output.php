<?php

$params = array();
$media_list = array();
    
$IMAGE_LIST_BUTTON_temp = $CJO['IMAGE_LIST_BUTTON'];
$CJO['IMAGE_LIST_BUTTON']['FUNCTIONS'] = true;
$CJO['IMAGE_LIST_BUTTON']['IMAGEBOX']  = true;

$files = cjoAssistance::toArray('CJO_MEDIALIST[1]', ',');

//Thumbnail Größen-Einstellungen
$params['img']['crop_num'] = "CJO_VALUE[3]";
//Thumbnail Wasserzeichen
$params['img']['brand'] = "CJO_VALUE[7]";
//Einstellung für Bildunterschriften
$params['des']['settings'] = "CJO_VALUE[8]";
//Zoom-Image Größen-Einstellungen
$params['fun']['zoom']['crop_num'] = "CJO_VALUE[4]";
//Zoom-Image Wasserzeichen true
$params['fun']['zoom']['brand'] = "CJO_VALUE[7]";
//Zoom-Image-Funktion einschalten
$params['fun']['settings'] = 1;

foreach($files as $filename){

    if (!file_exists($CJO['MEDIAFOLDER'].'/'.$filename)) continue;

    $image = '';

    $media_obj = OOMedia::getMediaByName($filename);

    if (!OOMedia::isValid($media_obj) || !OOMedia::isImageType($media_obj->_type)) continue;

    
    $image = OOMedia::toResizedImage($filename, $params, false);

    $pattern = '/(?<=href=")(?<url>[^"]*?)(?=")|'.
               '(?<=name=")(?<copy>[^"]*?)(?=")|'.
               '(?<=title=")(?<title>[^"]*?)(?=")|'.
               '(?<=src=")(?<src>[^"]*?)(?=")|'.
               '(?<=>)(?<desc>[^>]*?)(?=<\/a>|<\/span>)/ms';

    preg_match_all($pattern, $image, $temp);

    $params['src'] = implode('',$temp['src']);
    $params['url'] = implode('',$temp['url']);
    $params['copy'] = implode('',$temp['copy']);
    $params['sdesc'] = $temp['title'][2];
    $params['desc'] = implode('',$temp['desc']);

    $size = @getimagesize($image[3]);

    $media_list['filename'][]          = $filename;
    $media_list['file_name'][]         = $filename; // only for compatibility
    $media_list['image_src'][]         = $params['src'];
    $media_list['url'][]               = $params['url'];
    $media_list['copy'][]              = $params['copy'];
    $media_list['short_description'][] = $params['sdesc'];
    $media_list['description'][]       = $params['desc'];
    $media_list['name'][]              = $media_obj->getTitle();
    $media_list['rel'][]               = 'imagebox_CJO_SLICE_ID';
    $media_list['width'][]             = $size[0];
    $media_list['height'][]            = $size[1];
}
//cjo_debug($media_list);
cjoModulTemplate::addVars('TEMPLATE', array());
cjoModulTemplate::addVarsArray('MEDIA_LIST', $media_list);

cjoModulTemplate::getModul();

$CJO['IMAGE_LIST_BUTTON'] = $IMAGE_LIST_BUTTON_temp;
unset($IMAGE_LIST_BUTTON_temp);
?>