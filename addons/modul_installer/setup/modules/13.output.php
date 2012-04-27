<?php
global $CJO;

$flash_vars = array();
$no_flash = (strpos(strtolower(cjo_server('HTTP_USER_AGENT')),'iphone') !== false || 
             strpos(strtolower(cjo_server('HTTP_USER_AGENT')),'ipod')  !== false  || 
             strpos(strtolower(cjo_server('HTTP_USER_AGENT')),'ipad')  !== false);

if ("CJO_MEDIA[1]" != '' && file_exists($CJO["MEDIAFOLDER"]."/CJO_MEDIA[1]")) {

 // Ausgabegröße
    $flash_size = @getimagesize($CJO["MEDIAFOLDER"]."/CJO_MEDIA[1]");

    // WENN FLV
    if ("CJO_MEDIA[2]" != '' && file_exists($CJO["MEDIAFOLDER"]."/CJO_MEDIA[2]")) {

        $flash_vars[] = "file: 'CJO_MEDIA[2]'";
        $flash_vars[] = ("CJO_VALUE[13]") ? "autostart: 'CJO_VALUE[13]'" : "";

        $flash_size[0] = ("CJO_VALUE[11]" != '') ? "CJO_VALUE[11]" : $flash_size[0];
        $flash_size[1] = ("CJO_VALUE[12]" != '') ? "CJO_VALUE[12]" : $flash_size[1];
    }

    $flash_vars[] = "CJO_VALUE[7]";
    $flash_vars[] = "CJO_VALUE[8]";
    $flash_vars[] = "CJO_VALUE[9]";
    $flash_vars[] = "CJO_VALUE[10]";

    $flash_vars = array_diff($flash_vars, array(''));
    
    $media_obj = OOMedia::getMediaByName("CJO_MEDIA[2]" ? "CJO_MEDIA[2]" : "CJO_MEDIA[1]");
    
    if (OOMedia::isValid($media_obj)) {
        $description = $media_obj->getDescription();
        $title = $media_obj->getTitle();
    }

    switch("CJO_VALUE[2]"){
        case 1:     break;
        case 2:     $description = "CJO_VALUE[3]";
                    break;
        default:    $description = '';
    }
}

$preview_image  = ("CJO_MEDIA[3]") ? OOMedia::toThumbnail("CJO_MEDIA[3]", false, array ('crop_num' => "CJO_VALUE[14]")) : '';
$ipad_alt_image = ($no_flash && "CJO_MEDIA[4]")  ? OOMedia::toThumbnail("CJO_MEDIA[4]", false, array ('crop_num' => "CJO_VALUE[15]")) : '';


cjoModulTemplate::addVars('TEMPLATE', array(
                          'IS_FLASH'         => (strpos($flash_size['mime'], 'flash') !== false),
                          'FLASH_ID'         => substr("CJO_MEDIA[1]", 0, strrpos("CJO_MEDIA[1]", ".")),
                          'FLASH_VARS'       => implode(", ",$flash_vars),
                          'FLASH_WIDTH'      => $flash_size[0],
                          'FLASH_HEIGHT'     => $flash_size[1],
                          'FLASH_VERSION'    => "CJO_VALUE[4]",
                          'FLASH_INSTALL'    => "CJO_VALUE[5]",
                          'TITLE'            => $title,
                          'DESCRIPTION'      => $description,
                          'PREVIEW_IMAGE'    => $preview_image,
                          'NO_FLASH'         => $no_flash,
                          'IPAD_ALT_IMAGE'   => $ipad_alt_image
                          ));

cjoModulTemplate::getModul();
?>