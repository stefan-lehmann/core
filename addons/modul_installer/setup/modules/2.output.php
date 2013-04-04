<?php

if (cjoProp::isBackend()) {

    global $I18N;    
    cjoModulTemplate::addVars('TEMPLATE', array(
                              'TEASER_INFO'  => ("CJO_VALUE[19]" == "teaser"
                                                ? cjoI18N::translate('viewable_in_listing]')
                                                : ' '),
    						  'DISPLAY_INFO' => cjoProp::isBackend()
                              ));
    cjoModulTemplate::getModul();
}
?>