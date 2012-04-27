<?php

$set = array();
$sql_query = array();
$results = array();

//EINSTELLUNGEN ÜBERNEHMEN
$set['more']['linktext'] = '[translate: read_more]';
$set['more']['title_prefix'] = '[translate: continue_reading]';

$set['modultyp']['id'] = $CJO_EXT_VALUE['list_settings_modultyp'];
$set['pagination']['xpage'] = cjo_get('xpage', 'int', 0);
$set['pagination']['xpage_query'] = array('xpage' => $set['pagination']['xpage']);
$set['pagination']['elm_per_page'] = ("CJO_VALUE[2]" == "") ? 20 : "CJO_VALUE[2]";
$set['pagination']['links_per_page'] = 5;
$set['pagination']['start'] = $set['pagination']['xpage'] * $set['pagination']['elm_per_page'];
$set['pagination']['end'] = $set['pagination']['elm_per_page'];
$set['pagination']['show'] = "CJO_VALUE[3]";
$set['truncate']['length'] = "CJO_VALUE[1]";
$set['related']['limit'] = "CJO_VALUE[4]";
$set['duration']['days'] = "CJO_VALUE[5]";
$set['duration']['end_date'] = time() - (mktime(0, 0, 0, 1, 2, 1970) * $set['duration']['days']);
$set['startcat']['id'] = str_replace(' ', '', "CJO_VALUE[6]");
$set['startcat']['sub_cats'] = "CJO_VALUE[7]";
$set['ctype']['id'] = ("CJO_VALUE[12]" == "") ? 0 : "CJO_VALUE[12]";
$set['sort']['key'] = ("CJO_VALUE[8]" == "") ? "prior" : "CJO_VALUE[8]";
$set['sort']['direction'] = ("CJO_VALUE[9]" == "") ? "ASC" : "CJO_VALUE[9]";
$set['backbutton'] = "CJO_VALUE[14]";
$count_results = 0;

if ($set['truncate']['length'] == "komplett")
    $set['truncate']['length'] = 'all';

// QUERYSTRING FÜR PAGINATION-LINKS IM BACKEND
if ($CJO['CONTEJO']) {
    $set['pagination']['query_array'] = array('page' => cjo_request('page', 'string'), 'subpage' => cjo_request('subpage', 'string'), 'article_id' => cjo_request('article_id', 'cjo-article-id'), 'function' => cjo_request('function', 'string', 'edit'), 'mode' => cjo_request('mode', 'string', 'edit'), 'clang' => cjo_request('clang', 'cjo-clang-id', 0), 'ctype' => cjo_request('ctype', 'int', 0), );
}

// DB-ABFRAGE AUF BESTIMMTE *MODULE* BESCHRÄNKEN
if (!empty($CJO_EXT_VALUE['modul_ids'])) {
    foreach ($CJO_EXT_VALUE['modul_ids'] as $modul_id) {
        if (trim($modul_id) == '')
            continue;
        $sql_query['modultyp_ids'] .= ($sql_query['modultyp_ids'] != '') ? " OR " : "";
        $sql_query['modultyp_ids'] .= "b.modultyp_id='" . $modul_id . "'";
    }
    if ($sql_query['modultyp_ids'] != '')
        $sql_query['modultyp_ids'] = "(" . $sql_query['modultyp_ids'] . ") AND";
}

// DB-ABFRAGE AUF BESTIMMTE *SLICES* BESCHRÄNKEN
if (count($CJO['CTYPE'] > 1)) {
    $sql_query['ctype_ids'] = "b.ctype='" . $set['ctype']['id'] . "' AND ";
}

// DB-ABFRAGE AUF NEUESTE BESCHRÄNKEN
$sql_query['only_newest'] = ($set['duration']['days'] > 0) ? "a.createdate > '" . $set['duration']['end_date'] . "' AND " : "";

// EIGENE SQL-WHERE-KLAUSEL
if (trim("CJO_HTML_VALUE[11]") != '') {

    $where_temp = trim("CJO_HTML_VALUE[11]");
    $where_temp = str_replace("&" . "#36;", "$", $where_temp);

    if (!preg_match('/\WAND$|\WOR$/i', $where_temp)) {
        $where_temp .= " AND ";
    }
    eval('$sql_query[\'own_where\'] = ' . "\"$where_temp\";");
}

// EIGENE SQL-WHERE-KLAUSEL
$online_from_to = "";
if ($CJO['ONLINE_FROM_TO_ENABLED']) {
    $online_from_to = "    a.online_from < '" . time() . "' AND
                        a.online_to > '" . time() . "' AND ";
}

// DB-ABFRAGE AUF BESTIMMTE *KATEGORIEN* BESCHRÄNKEN
if ($set['startcat']['id'] != '') {
    $startcat_ids = explode(',', $set['startcat']['id']);
    $startcat_ids = array_diff($startcat_ids, array(''));

    $end_pattern = ($set['startcat']['sub_cats']) ? '%' : '';

    foreach ($startcat_ids as $id) {
        // EIN- UND AUSSCHLIESSEN MEHRERER KATEGORIEN
        if (strpos($id, '-') !== false) {
            $id = str_replace('-', '', $id);
            $sql_query['cat']['false'] .= ($sql_query['cat']['false'] != '') ? " AND" . " " : "";
            $sql_query['cat']['false'] .= "a.path NOT LIKE '%|" . str_replace('-', '', $id) . "|" . $end_pattern . "' ";
        } else {
            $sql_query['cat']['true'] .= ($sql_query['cat']['true'] != '') ? " OR" . " " : "";
            $sql_query['cat']['true'] .= "a.path LIKE '%|" . $id . "|" . $end_pattern . "' ";
        }
    }
    if ($sql_query['cat']['false'] != '')
        $sql_query['cats'] .= "(" . $sql_query['cat']['false'] . ") AND ";
    if ($sql_query['cat']['true'] != '')
        $sql_query['cats'] .= "(" . $sql_query['cat']['true'] . ") AND ";
}

// DB-ABFRAGE SORTIERSCHLÜSSEL
$sql_query['sort_key'] = ($set['sort']['key'] != 'random') ? "a." . $set['sort']['key'] . "" : "RAND()";

// DB-ABFRAGE
$sql = new cjoSql();
// ZUSAMMENSTELLEN DER QUERY FÜR DIE DB-ABFRAGE
$qry = "SELECT
            a.id, a.clang, b.id as slice_id, b.value20 as CJO_EXT_VALUE
        FROM
            " . TBL_ARTICLES . " a, " . TBL_ARTICLES_SLICE . " b
        WHERE
            " . $sql_query['modultyp_ids'] . "
            " . $sql_query['only_newest'] . "
            " . $sql_query['cats'] . "
            " . $sql_query['ctype_ids'] . "
            " . $sql_query['own_where'] . "
            a.id = b.article_id AND
            a.status = '1' AND
           " . $online_from_to . "
            a.clang = '" . $CJO['CUR_CLANG'] . "' AND
            b.clang = '" . $CJO['CUR_CLANG'] . "' AND
            a.teaser = '1' AND
            a.id != '" . $CJO['ARTICLE_ID'] . "'
        ORDER BY
            " . $sql_query['sort_key'] . " " . $set['sort']['direction'];
$sql -> setQuery($qry);

if ("CJO_VALUE[10]")
    cjo_debug($sql);

for ($i = 0; $i < $sql -> getRows(); $i++) {

    if ($sql -> getValue('id') != '' && OOArticle::isOnline($sql -> getValue('id'))) {

        if ($set['related']['limit']) {
            $CJO_EXT_VALUE = OOArticleSlice::getExtValue($sql -> getValue('slice_id'), $sql -> getValue('CJO_EXT_VALUE'));

            if (!empty($CJO_EXT_VALUE['reference'])) {
                // THEMENGEBIETE ERMITTELN
                $references = implode("_", $CJO_EXT_VALUE['reference']);
                $references = explode("_", $references);
                $references = array_unique($references);
                sort($references);

                if (in_array($this -> article_id, $references)) {
                    $results[] = $sql -> getValue('id');
                }
            }
        } else {
            $results[] = $sql -> getValue('id');
        }
    } $sql -> next();
}

if (!empty($results)) {

    $results = array_unique($results);

    if ("CJO_VALUE[10]")
        cjo_debug($results, 'ALL RESULTS');

    // URSPRÜNGLICHE LÄNGE DES RESULTS-ARRAY
    $results_lenght = count($results);

    // RESULTS-ARRAY AUF AKTUELLEN PAGINATION-AUSSCHNITT 'BESCHNEIDEN'
    $results = array_slice($results, $set['pagination']['start'], $set['pagination']['end']);

    if ("CJO_VALUE[10]")
        cjo_debug($results, 'SLICED RESULTS');

    // AUSGABE DER PAGE-PAGINATION
    $pagination = ($set['pagination']['show']) ? cjoOutput::getPagePagination($set['pagination']['xpage'], $set['pagination']['elm_per_page'], $set['pagination']['links_per_page'], $results_lenght, $set['pagination']['query_array']) : '';

    cjoModulTemplate::addVars('TEMPLATE', array('PAGINATION' => $pagination, 'COUNT_RESULTS' => count($results), 'ARTICLE_DEVIDER' => '<div class="absatz"></div>'));

    // AUSGABE DER ARTIKEL
    foreach ($results as $result_id) {

        $set['count']++;
        $output = array();
        $image = array();

        $result = OOArticle::getArticleById($result_id, $CJO['CUR_CLANG']);
        // ERSTES SLICE ERMITTELN
        $slice = OOArticleSlice::getFirstSliceForArticle($result_id);

        // DURCHLAUF ZUM KORREKTEN SLICE, WENN:
        //  - MEHRERE CTYPS VERFÜGBAR ODER
        //  - MODULTYP NICHT PASST
        while ($slice !== null) {

            if (($sql_query['modultyp_ids'] == '' || strpos($sql_query['modultyp_ids'], "'" . $slice -> _modultyp_id . "'") !== false) && (count($CJO['CTYPE']) <= 1 || $set['ctype']['id'] == $slice -> _ctype))
                break;

            if ($slice -> getNextSlice() === null)
                break;
            $slice = $slice -> getNextSlice();
        }

        $CJO_EXT_VALUE = OOArticleSlice::getExtValue($slice -> getValue(20));

        $params = array();
        if ($set['backbutton']) {
            $params['ref'] = "CJO_ARTICLE_ID";
            if (!empty($set['pagination']['xpage'])) {
                $params['xpage'] = $set['pagination']['xpage'];
            }
        }
        $output['url'] = cjoRewrite::getUrl($result_id, false, $params);

        $output['more_link'] = '<a href="' . $output['url'] . '" ' . 'title="' . $slice -> getValue(2) . '" class="more">' . $set['more']['linktext'] . '</a>';

        if ($set['truncate']['length'] != 'meta') {

            // AUSGABE HEADLINES
            $output['headline'] = $slice -> getValue(2);
            $output['sub_headline'] = $slice -> getValue(3);

            // AUSGABE BLOCK-HAUPTTEXT
            $slice_text = str_replace("<", " <", $slice -> getValue(1));

            $words_count = count(preg_split("/[\s,]+/", $slice_text));

            // ABSCHNEIDEN NACH DEM ERSTEN ABSATZ
            if (strtolower($set['truncate']['length']) == "p" || strtolower($set['truncate']['length']) == "absatz") {
                preg_match_all("/<(p|table|ul|ol)[^>]*>.*?<\/(p|table|ul|ol)>/ims", $slice_text, $absatz);
                $output['text'] = $absatz[0][0];
                $output['display_more_link'] = (!empty($absatz[0][1]) || $slice -> getNextSlice() != '' || $slice -> _re_article_slice_id != 0) ? true : false;
            } else {
                // KEIN ABSCHNEIDEN, DA TEXT KÜRZER ALS GEWÜNSCHTE LÄNGE BZW. TEXT SOLL KOMPLETT AUSGEGEBEN WERDEN
                if ($words_count <= $set['truncate']['length'] || $set['truncate']['length'] == "all") {
                    $output['text'] = $slice_text;
                    $output['display_more_link'] = ($set['truncate']['length'] == "all") ? false : true;
                }
                // ABSCHNEIDEN NACH X-LEERZEICHEN
                else {

                    $slice_text = preg_replace("/<(table|ul|ol)\b[^>]*>(.*?)<\/(table|ul|ol)>/ims", '', $slice_text);
                    //konvertiert "unerwünschte" Elemente
                    $slice_text = preg_replace("/<(h[1-6]|pre|div|address|blockquote)\b[^>]*>(.*?)<\/(h[1-6]|pre|div|address|blockquote)>/ims", '<p></p>', $slice_text);
                    //konvertiert "unerwünschte" Elemente
                    $slice_text = trim($slice_text);
                    // Leerzeichen entfernen am Anfang und Ende des Strings

                    preg_match('/([\S]+\s*){0,' . $set['truncate']['length'] . '}/', $slice_text, $regs);

                    $output['text'] = preg_replace('/\w<(strong|b[^r]|em|i|a|span|u|img|div)+([^>]*(?!<))$/i', '', trim($regs[0]));
                    $output['text'] = cjoOutput::closeTags($output['text']);
                    $output['display_more_link'] = true;
                }
            }

            $output['text'] = trim($output['text']);

            if (!empty($output['text'])) {

                $output['text'] = str_replace(" </", "</", $output['text']);

                $show['show_margin'] = $CJO_EXT_VALUE['show_margin'];
                $show['show_infos'] = $CJO_EXT_VALUE['show_infos'];
                $show['show_to_top'] = $CJO_EXT_VALUE['show_to_top'];
                $show['show_new_updated'] = $CJO_EXT_VALUE['show_new_updated'];

                $slice_next = $slice;
                // nächstes Slice ermitteln

                while ($slice_next != '') {// Schleife wenn nächstes Slice nicht leer

                    $slice_next = $slice_next -> getNextSlice();

                    if ($slice_next == '')
                        break;
                    if ($slice_next -> _ctype != $set['ctype']['id'])
                        continue;

                    $slice_next -> CJO_EXT_VALUE = OOArticleSlice::getExtValue($slice_next -> getValue(20));

                    $show['show_margin'] = ($show['show_margin'] != 'on') ? $slice_next -> CJO_EXT_VALUE['show_margin'] : $show['show_margin'];
                    $show['show_infos'] = ($show['show_infos'] != 'on') ? $slice_next -> CJO_EXT_VALUE['show_infos'] : $show['show_infos'];
                    $show['show_to_top'] = ($show['show_to_top'] != 'on') ? $slice_next -> CJO_EXT_VALUE['show_to_top'] : $show['show_to_top'];
                    $show['show_new_updated'] = ($show['show_new_updated'] != 'on') ? $slice_next -> CJO_EXT_VALUE['show_new_updated'] : $show['show_new_updated'];

                    if ($slice -> _modultyp_id == $slice_next -> _modultyp_id || $slice_next -> getValue(19) != 'teaser')
                        continue;

                    // NACHFOLGENDE BOXEN AN TEXT ANHÄNGEN
                    if ($slice_next -> getValue(1) != '') {

                        $box_tpl_path = cjoModulTemplate::getTemplatePath(2, $this -> template_id, $this -> ctype);
                        $box_tpl = new cjoModulTemplate($box_tpl_path);
                        $box_tpl -> fillTemplate('TEMPLATE', array('LEFT_MARGIN_CSS' => ($vertical_img != '' ? ' left_margin' : ''), ), $slice_next);

                        $output['box'] .= $box_tpl -> get(false);
                    }
                }
            }
        } else {
            $output['id'] = $result -> getId();
            $output['headline'] = $result -> getName();
            $output['text'] = '<p>' . $result -> getDescription() . '</p>';
            $output['display_more_link'] = true;
        }

        switch ("CJO_VALUE[15]") {
            case 'off' :
                $filename = 'off';
                break;
            case 'meta' :
                $filename = $result -> _file;
                break;
            case '' :
                for ($a = 1; $a <= 10; $a++) {
                    $filename = $slice -> getFile($a);
                    if ($filename != '' && file_exists($CJO['MEDIAFOLDER'] . '/' . $filename)) {
                        break 2;
                    }
                }
                break;
            default :
                $filename = $slice -> getFile("CJO_VALUE[15]");
        }

        if ($filename != 'off') {
            if ($filename == '' && "CJO_VALUE[15]" == '') {
                $filename = $slice -> getFile($filename_num);
            }
        }

        $res_params = array();
        $res_params['img']['crop_num'] = "CJO_VALUE[16]";
        $res_params['des']['settings'] = '-';

        if ($output['display_more_link']) {

            $pattern = '/[\.,;\?\!:\(&' . '#x20;]?(<\/[a-z ]*>)$/im';
            $replace = '<span>...</span> ' . $output['more_link'] . '$' . '1';
            $output['text'] = preg_replace($pattern, $replace, $output['text'], 1);

            $res_params['img']['title'] = $slice -> getValue(2);
            $res_params['fun']['settings'] = 5;
            $res_params['fun']['ext'] = $output['url'];

        }

        if ($filename != '' && $filename != 'off') {
            $output['img'] = OOMedia::toResizedImage($filename, $res_params);
        }

        cjoModulTemplate::addVars('RESULTS', array(
            'CJO_ARTICLE_INFOS' => ($CJO['CONTEJO'] ? OOArticle::getArticleInfosBE($result, $set['ctype']['id']) : ''), 
            'DETAIL_URL'        => $output['url'], 
            'HEADLINE'          => $output['headline'], 
            'SUB_HEADLINE'      => $output['sub_headline'], 
            'LEFT_MARGIN_CSS'   => 'left_margin', 
            'TEASER_IMG'        => $output['img'], 
            'TEXT'              => $output['text'], 
            'BOX'               => $output['box'], 
            'DISPLAY_NEW'       => $show['show_new_updated'] == 'on' && !$CJO['CONTEJO'], 
            'ARTICLE_INFOS'     => (!$CJO['CONTEJO'] ? OOArticle::getArticleInfos($result_id, $show) : ''), 
            'ARTICLE_DEVIDER'   => (!$CJO['CONTEJO'] && $set['count'] != count($results) ? '<div class="absatz"></div>' : ''), 
            'DISPLAY_BOX'   => !empty($output['box']))
            , $slice);
    }
} else {
    cjoModulTemplate::addVars('TEMPLATE', array('COUNT_RESULTS' => count($results)));
}

cjoModulTemplate::getModul();
?>