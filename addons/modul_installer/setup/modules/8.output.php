<?php

if ("CJO_VALUE[1]"){

    if ($CJO['CONTEJO']) {
        global $I18N;    
        $qry = "SELECT id, name, active FROM ".TBL_TEMPLATES." WHERE id='CJO_VALUE[1]'";
        $sql = new cjoSql();
        $sql->setQuery($qry);

        echo ($sql->getRows() == 1)
            ? '<p class="accept">'.$I18N->msg('template_included').': <b>'.$sql->getValue('name').'</b> (ID='.$sql->getValue('id').')</p>'
            : '<p class="error">'.$I18N->msg('err_include_template').': <b>'.$sql->getValue('name').'</b> (ID='.$sql->getValue('id').')</p>';

    } else {
        if (file_exists($CJO['FOLDER_GENERATED_TEMPLATES']."/CJO_VALUE[1].template")) {
            $modul_template = new cjoTemplate('CJO_VALUE[1]');
            $modul_template->executeTemplate("CJO_ARTICLE_ID");
        } else {
            echo '<!-- '.$I18N->msg('err_include_template').' -->';
        }
    }
}
?>