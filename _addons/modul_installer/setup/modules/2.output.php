<?php

if ($CJO['CONTEJO']) {

    global $I18N;    
    cjoModulTemplate::addVars('TEMPLATE', array(
                              'TEASER_INFO'  => ("CJO_VALUE[19]" == "teaser"
                                                ? $I18N->msg('viewable_in_listing]')
                                                : ' '),
    						  'DISPLAY_INFO' => $CJO['CONTEJO']
                              ));
    cjoModulTemplate::getModul();
}
?>