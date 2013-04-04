<?php

$params = array();
$media_list = array();

//Thumbnail Größen-Einstellungen
$params['tbnl']['img']['crop_num'] = ("CJO_VALUE[3]" == '') ? 3 : "CJO_VALUE[3]";
//Thumbnail Wasserzeichen true
$params['tbnl']['img']['brand'] = "CJO_VALUE[7]";

//Zoom-Image Größen-Einstellungen
$params['zoom']['img']['crop_num'] = ("CJO_VALUE[4]" == '') ? 1 : "CJO_VALUE[4]";
//Zoom-Image Wasserzeichen true
$params['zoom']['img']['brand'] = "CJO_VALUE[7]";
//Einstellung für Bildunterschriften
$params['zoom']['des']['settings'] = "CJO_VALUE[8]";

$files = cjoAssistance::toArray('CJO_MEDIALIST[1]',',');

foreach($files as $num => $file_name){

    $tbnl = array();
    $zoom = array();

    $media_obj = OOMedia::getMediaByName($file_name);

    if (!OOMedia::isImageType($media_obj->_type)) {
        continue;
    }

    $tbnl = OOMedia::toResizedImage($file_name, $params['tbnl'], false, true);
    $zoom = OOMedia::toResizedImage($file_name, $params['zoom'], false, true);

    $media_list['num'][]	 	 	   = $num;
    $media_list['real_num'][]	 	   = $num + 1;
    $media_list['file_name'][]	 	   = $file_name;
    $media_list['thumbnail'][]	 	   = $tbnl['image'];
    $media_list['zoomimage'][]	 	   = $zoom['image'];
    $media_list['description'][]	   = $zoom['description'];
    $media_list['display_up_link'][]   = ($num>0) ? 1 : '';
    $media_list['display_down_link'][] = ($num < count($files)-1) ? 1 : '';
}

cjoModulTemplate::addVars('TEMPLATE', array(
                          'TBNL_CROP_SELECTION'		  => cjoMedia::getCropSelection('VALUE[3]',$params['tbnl']['img']['crop_num'],'inp50'),
                          'ZOOM_CROP_SELECTION'		  => cjoMedia::getCropSelection('VALUE[4]',$params['zoom']['img']['crop_num'],'inp50'),
                          'ZOOM_BRAND_IMAGE_CHECKED'  => cjoAssistance::setChecked("CJO_VALUE[7]", array('off')),
                          'MPOOL_DESCRIPTION_CHECKED' => cjoAssistance::setChecked("CJO_VALUE[8]", array(1)),
                          'DISPLAY_BRAND_IMG'		  => $CJO['IMAGE_LIST_BUTTON']['BRAND_IMG'],
                         ));


cjoModulTemplate::addVarsArray('MEDIA_LIST', $media_list);
cjoModulTemplate::getModul();

?>