<?php

cjoModulTemplate::addVars('TEMPLATE', array(
            'SHOW_NEW_UPDATED_CHECKED'	=> cjoAssistance::setChecked($CJO_EXT_VALUE['show_new_updated'], array("on")),
            'SHOW_MARGIN_CHECKED'		=> cjoAssistance::setChecked($CJO_EXT_VALUE['show_margin'], array("on")),
            'SHOW_TO_TOP_CHECKED'	 	=> cjoAssistance::setChecked($CJO_EXT_VALUE['show_to_top'], array("on")),
            'SHOW_INFOS_CHECKED'	 	=> cjoAssistance::setChecked($CJO_EXT_VALUE['show_infos'], array("on")),
            'ANCHOR_NAV_CHECKED'	 	=> cjoAssistance::setChecked($CJO_EXT_VALUE['anchor_nav'], array("on"))
            ));

cjoModulTemplate::getModul();
?>