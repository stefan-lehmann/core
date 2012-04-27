<?php

$form = new cjoFormGenerator();
$form->setName("CJO_VALUE[8]");
$form->setRecipients("CJO_VALUE[1]");
$form->setSubject("CJO_VALUE[4]");
$form->setPHPMailer("CJO_VALUE[5]");
$form->setPHPMailerAccount("CJO_VALUE[9]");
$form->setConfirmMail("CJO_VALUE[10]");
$form->setScriptTemplate("CJO_VALUE[3]");
$form->setReturnMailtext("CJO_HTML_VALUE[18]");
$form->addAttachments("CJO_MEDIALIST[1]");
$form->getFormElementsFromSlice('CJO_SLICE_ID');
$form->get();

if ($form->hasAfterActionOutput('before')) {
   $form->getAfterActionOutput('before');
}

if ($form->is_valid === true) {

    cjoModulTemplate::addVars('TEMPLATE', array(
                      'FORM_ID'     => $form->getName(),    
                      'FORM_NAME'   => $form->getName(),
                      'SHOW_FORM'   => '',
                      ));
}
elseif ($form->hasFormeElments()) {
    
    cjoModulTemplate::addVars('TEMPLATE', array(
                              'FORM_ID'     => $form->getName(),
                              'FORM_NAME'   => $form->getName(),
                              'ACTION_URL'  => preg_replace('/&(?!amp;)/i','&amp;', $_SERVER["REQUEST_URI"]),
                              'SHOW_FORM'   => 1,
                              'HIDE_LEGEND' => ("CJO_VALUE[19]" != 1)
                              ));

    cjoModulTemplate::addVarsArray('FORM_ELEMENTS', $form->getFormeElmentsOut());

}

cjoModulTemplate::getModul();

if ($form->hasAfterActionOutput('after')) {
   $form->getAfterActionOutput('after');
}

?>