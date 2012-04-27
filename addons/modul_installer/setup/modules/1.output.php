<?php

$anchor_nav = '';
$box = '';
$process_next_slice = true;

if (!$CJO['CONTEJO']) {
    $slice = OOArticleSlice::getArticleSliceById($slice_id);
    $next_slice = $slice; // nächstes Slice ermitteln

    while($next_slice != '') // Schleife wenn nächstes Slice nicht leer
    {
        $next_slice = $next_slice->getNextSlice();

        if(!$next_slice) break;
        if ($next_slice->_ctype != $slice->_ctype) continue;

        if ($slice->_modultyp_id != $next_slice->_modultyp_id &&
            strpos($next_slice->getValue(19),'teaser') !== false && // wenn Position höher und Modul = Box
            $next_slice->getValue(1) != '' &&
            $process_next_slice)
        {
            $box_tpl_path = cjoModulTemplate::getTemplatePath($next_slice->getModulTyp(),$this->template_id,$this->ctype);
            $box_tpl = new cjoModulTemplate($box_tpl_path);
            $box_tpl->fillTemplate('TEMPLATE', array(
                                    'LEFT_MARGIN_CSS'   => ($vertical_img != ''
                                                        ? ' left_margin' : ''),
                                   ),
                                   $next_slice);

            $box .= $box_tpl->get(false);
        }
        else{
            if($CJO_EXT_VALUE['anchor_nav'] == 'on' && $next_slice->getValue(4) != ''){
                $anchor_nav['anchor_link'][] = OONavigation::getAnchorLinkText($next_slice->getValue(4));
                $anchor_nav['anchor_link_text'][] = $next_slice->getValue(4);
                $process_next_slice = false;
            }
            else{
                break;
            }
        }
    }
}
cjoModulTemplate::addVars('TEMPLATE',
                        array(

                        'LEFT_MARGIN_CSS'    => 'left_margin',
                        'ANCHOR_LINK'        => OONavigation::getAnchorLinkText("CJO_VALUE[4]"),
                        'HAS_DOWNLOADS'      => trim("CJO_MEDIALIST[1]") != "",
                        'BOX'                => $box,
                        'DISPLAY_BOX'        => !empty($box) AND !$CJO['CONTEJO'],
                        'DISPLAY_NEW'        => $CJO_EXT_VALUE['show_new_updated'] == 'on',
                        'ARTICLE_INFOS'      => OOArticle::getArticleInfos("CJO_ARTICLE_ID", $CJO_EXT_VALUE),
                        'DISPLAY_ANCHOR_NAV' => !empty($anchor_nav)
                        ));

if ("CJO_MEDIALIST[1]") {
    cjoOutput::getDownloadsFromMedialist("CJO_MEDIALIST[1]");
}
if ($anchor_nav != '') {
    cjoModulTemplate::addVarsArray('ANCHOR_NAV', $anchor_nav);
}

cjoModulTemplate::getModul();
?>