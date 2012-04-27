<?php

$media_obj = OOMedia::getMediaByName("CJO_FILE[1]");

$desc_values = array('1' => '[translate: label_media_description]',
                     '2' => '[translate: label_individual_description]',
                     '-' => '[translate: label_without]');

$desc_sel = new cjoSelect();
$desc_sel->setName('VALUE[2]');
$desc_sel->setMultiple(false);
$desc_sel->setSize(1);
$desc_sel->setStyle('class="inp37" style="margin-bottom:.5em;"');
$desc_sel->setId('CJO_VALUE_2');
$desc_sel->setSelected("CJO_VALUE[2]");
foreach ($desc_values as $value => $option) {
    $desc_sel->addOption($option, $value);
}

$flash_vsio_sel = new cjoSelect();
$flash_vsio_sel->setName('VALUE[4]');
$flash_vsio_sel->setMultiple(false);
$flash_vsio_sel->setSize(1);
$flash_vsio_sel->setStyle('class="inp20"');
$flash_vsio_sel->setId('CJO_VALUE_4');
$flash_vsio_sel->setSelected("CJO_VALUE[4]");
foreach (array("10", "9", "8", "7", "6") as $option) {
    $flash_vsio_sel->addOption($option, $option);
}

cjoModulTemplate::addVars('TEMPLATE', array(
                          'CJO_DESC_SELECTION'               => $desc_sel->get(),
                          'FLASH_VERSION_SELECTION'          => $flash_vsio_sel->get(),
                          'MPOOL_DESC'                       => $media_obj->getDescription(),
                          'DISPLAY_MPOOL_DESC'               => ("CJO_VALUE[2]" == '1' ? ' style="display: block"' : ''),
                          'DISPLAY_DESC_INPUT'               => ("CJO_VALUE[2]" == '2' ? ' style="display: block"' : ''),
                          'PREVIEW_CROP_SELECTION'          => cjoMedia::getCropSelection('VALUE[14]','CJO_VALUE[14]','inp20'),
                          'IPAD_ALTERNATIVE_CROP_SELECTION'  => cjoMedia::getCropSelection('VALUE[15]','CJO_VALUE[15]','inp20'),
                          ));

cjoModulTemplate::getModul();

?>