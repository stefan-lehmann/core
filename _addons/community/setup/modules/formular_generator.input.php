<?php
$templates_sel = new cjoSelect();
$templates_sel->setName("VALUE[3]");
$templates_sel->setSize(1);
$templates_sel->setStyle('class="form-element inp75"');
$templates_sel->addOption('[translate: set_include_no_template]', '');
$templates_sel->addSqlOptions("SELECT DISTINCT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_TEMPLATES." WHERE active='0' ORDER BY prior");
$templates_sel->setSelected("CJO_VALUE[3]");

$account_sel = new cjoSelect();
$account_sel->setName('VALUE[9]');
$account_sel->setMultiple(false);
$account_sel->setSize(1);
$account_sel->setStyle('class="form-element inp75"');
$account_sel->addSqlOptions("SELECT DISTINCT CONCAT('&lt;',from_email,'&gt; ',from_name,' (',mailer,': ',host,')') as name,  id FROM ".TBL_20_MAIL_SETTINGS." WHERE status!='0' ORDER BY status, id");
$account_sel->setSelected("CJO_VALUE[9]");

$slice = OOArticleSlice::getArticleSliceById('CJO_SLICE_ID');

cjoModulTemplate::addVars('TEMPLATE', array(
                          'FORM_FIELDS'			   => preg_replace('/\\'.'\\'.'{2,}/','\\',$slice->getValue(11)),
                          'ACCOUNT_SELECTION'      => $account_sel->get(),
                          'TEMPLATE_SELECTION'     => $templates_sel->get(),
                          'SHOW_IS_FORM_CHECKED'   => cjoAssistance::setChecked("CJO_VALUE[8]", array('')),
                          'WITH_EMAIL_CHECKED'     => cjoAssistance::setChecked("CJO_VALUE[5]", array('1')),
                          'SENDCOPY_CHECKED'       => cjoAssistance::setChecked("CJO_VALUE[10]", array('1')),
                          'SHOW_LEGEND_CHECKED'    => cjoAssistance::setChecked("CJO_VALUE[19]", array('1','')),
                          ));

cjoModulTemplate::getModul();

?>