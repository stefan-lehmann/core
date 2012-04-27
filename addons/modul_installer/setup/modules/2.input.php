<?php

$regio_sel = new cjoSelect();
$regio_sel->setName("VALUE[5]");
$regio_sel->setSize(1);
$regio_sel->setId('country_for_map');
$regio_sel->showRoot('[translate: please_choose]');
$regio_sel->setStyle('class="form-element inp100"');
$regio_sel->addOption('[translate: please_choose]', '--');

cjoModulTemplate::addVars('TEMPLATE', array(
                          'REGIO_SELECTION'	 => $regio_sel->get(),
                          'TEASER_CHECKED'	 => cjoAssistance::setChecked("CJO_VALUE[19]", array("teaser"))
                          ));

cjoModulTemplate::getModul();


?>