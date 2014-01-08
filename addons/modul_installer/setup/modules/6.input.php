<?php

$results = array();
$sorted_results = array();
$curr_re_id = 0;

$clang_sel = new cjoSelect();
$clang_sel->setName("VALUE[3]");
$clang_sel->setSize(1);
$clang_sel->setId('cjo_clang_selection');
$clang_sel->setStyle('width: 317px');
$clang_sel->setSelected(("CJO_VALUE[3]" == '') ? $CJO['CUR_CLANG'] : "CJO_VALUE[3]");

foreach ($CJO['CLANG'] as $clang_id=>$clang_name){
   $clang_sel->addOption($clang_name, $clang_id);
}

$ctype_sel = new cjoSelect();
$ctype_sel->setName("VALUE[1]");
$ctype_sel->setSize(1);
$ctype_sel->setId('cjo_ctype_selection');
$ctype_sel->setStyle('width: 317px');
$ctype_sel->setSelected("CJO_VALUE[1]");

foreach ($CJO['CTYPE'] as $ctype_id=>$ctype_name){
    $ctype_sel->addOption($ctype_name, $ctype_id);
}

if ("CJO_LINK_ID[1]" && (
    $CJO['ARTICLE_ID'] != "CJO_LINK_ID[1]" ||
    $CJO['CUR_CLANG'] != "CJO_VALUE[3]")) {

    $qry = "SELECT
                sl.id as id, 
                sl.re_article_slice_id as re_id,
                sl.ctype as ctype, 
                sl.modultyp_id as modultyp_id, 
                md.name as name
            FROM
                ".TBL_ARTICLES_SLICE." sl
            LEFT JOIN
                ".TBL_MODULES." md
            ON
                sl.modultyp_id = md.id
            WHERE
                sl.article_id='CJO_LINK_ID[1]'
            AND
                sl.clang='CJO_VALUE[3]'
            ORDER BY
                sl.re_article_slice_id";

    $sql = new cjoSql();
    $sql->setQuery($qry);

    for ($i=0;$i<$sql->getRows();$i++){

        $re_id = $sql->getValue("re_id");

        $results['id'][$re_id]          = $sql->getValue("id");
        $results['re_id'][$re_id]       = $re_id;
        $results['ctype'][$re_id]       = $sql->getValue("ctype");
        $results['modul_id'][$re_id]    = $sql->getValue("modultyp_id");
        $results['modul_name'][$re_id]  = $sql->getValue("modul_name");
        $sql->next();
    }

    while (!empty($results['id'][$curr_re_id]) && $loops < 20) {

        if ($results['ctype'][$curr_re_id] == "CJO_VALUE[1]" &&
            $results['modul_id'][$curr_re_id] != $slice->_modultyp_id){

            $sorted_results[] = array('id' => $results['id'][$curr_re_id],
                                      're_id' => $results['re_id'][$curr_re_id],
                                      'ctype' => $results['ctype'][$curr_re_id],
                                      'modul_id' => $results['modul_id'][$curr_re_id],
                                      'modul_name' => $results['modul_name'][$curr_re_id]);
        }
        $curr_re_id = $results['id'][$curr_re_id];
        $loops++;
    }

    if(!empty($sorted_results)) {

        $output .= '<div class="cjo_block_selector">';
        $output .= '    <input type="radio" name="VALUE[2]" value="all"'.cjoAssistance::setChecked("CJO_VALUE[2]", array(-1, "all")).' />';
        $output .= '    <label class="right_label"><b>[translate: include_ctype_with_slices]</b></label>';
        $output .= '</div>';

        foreach($sorted_results as $result) {

            $article = new cjoArticle();
            $article->setArticleId("CJO_LINK_ID[1]");
            $article->setCLang("CJO_VALUE[3]" == "" ? $CJO['CUR_CLANG'] : "CJO_VALUE[3]");
            $article->setSliceId($result['id']);

            $output .= '<div class="cjo_block_selector">';
            $output .= '    <input type="radio" name="VALUE[2]" value="'.$result['id'].'"'.cjoAssistance::setChecked("CJO_VALUE[2]", array($result['id'])).' />';
            $output .= '    <label class="right_label"><b>'.$result['modul_name'].'</b> [ID='.$result['modul_id'].']</label><br/><br/>';
            $output .= '    <div class="container"><div>';
            $output .= '        '.$article->getArticle("CJO_VALUE[1]");
            $output .= '    </div></div>';
            $output .= '</div>';
        }
    }
    else {
        $output = '<p class="warning">[translate: no_slices_in_ctype]</p>';
    }
}
else {
    $output = '<p class="warning">[translate: no_alias_of_same_article_lang]</p>';
}
?>

<div class="settings">
    <h2 class="no_slide">[translate: step1]</h2>
    <input type="hidden" name="VALUE[1]" value="<?php echo $ctype; ?>" />
    <div class="formular">
        <label>[translate: select_article]</label>
         CJO_LINK_BUTTON[1]
    </div>
    <div class="formular">
        <label>[translate: label_select_language]</label>
        <?php echo $clang_sel->get(); ?>
    </div>
    <div class="formular last">
        <label>[translate: select_ctype]</label>
        <?php echo $ctype_sel->get(); ?>
    </div>
</div>

<?php if("CJO_LINK_ID[1]"){?>

    <div class="settings" style="margin-bottom:0;">
        <h2 class="no_slide">[translate: step2]</h2>
        <?php echo $output; ?>
    </div>

<?php } ?>

<script type="text/javascript">
/* <![CDATA[ */
var linkval = "CJO_LINK_ID[1]";

function checkLinkChange(){

    if (linkval > 0 && linkval != $('input[name^=LINK]').eq(0).val()){
        $('form#CJO_FORM input[name=update]').val(1);
        $('form#CJO_FORM').submit();
    }
    else {
        window.setTimeout("checkLinkChange();", 100);
    }
}


$(function(){
    checkLinkChange();

    $('.cjo_block_selector').bind('click', function(){
        var radio = $(this).find('input:radio:first');
        if(!radio.is(':checked')){
            radio.attr('checked','checked');
        }
    });

    $('#cjo_clang_selection, #cjo_ctype_selection').change(function(){
        $('form#CJO_FORM input[name=update]').val(1);
        $('form#CJO_FORM').submit();
    });

    $('.cjo_block_selector h2').unbind('click');
});
/* ]]> */
</script>


<style type="text/css">
<!--
.settings  div.cjo_block_selector {
    padding: 5px 10px;
    border-top: 1px solid #a3bac7;
    cursor: pointer
}
.settings  div.cjo_block_selector .container {
    clear: both;
    height: 120px;
    overflow-y: auto;
    margin: 0;
    padding: 5px;
    background-color: white;
}

.settings div.cjo_block_selector h2 {
    background: none;
    color: inherit;
    border: none;
    text-transform: none;
    font-weight: bold;
    padding: 0 0 .5em 0;
}
.settings div.cjo_block_selector h3{
    background: none;
    color: inherit;
    border: none;
    text-transform: none;
    font-weight: bold;
    padding: 0 0 .2em 0;

}
.settings div.cjo_block_selector:hover .container {
    background-color: #ccffcc;
}
-->
</style>