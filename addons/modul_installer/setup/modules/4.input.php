<?php

// EINSTELLUNGEN FÜR DAS KÜRZEN DES TEXTES
$trunc_default = '30';
$trunc_options = array('p'  => '1. [translate: paragraph]',
					   '10' => '10 [translate: words]',
					   '20' => '20 [translate: words]',
					   '30' => '30 [translate: words]',
					   '40' => '40 [translate: words]',
					   '50' => '50 [translate: words]',
					   '60' => '60 [translate: words]',
					   '70' => '70 [translate: words]',
					   '80' => '80 [translate: words]',
					   '90' => '90 [translate: words]',
					   '100' => '100 [translate: words]',
					   '120' => '120 [translate: words]',
					   '150' => '150 [translate: words]',
					   '180' => '180 [translate: words]',
					   'all' => '[translate: whole_text]');

$truncate = new cjoSelect();
$truncate->setName("VALUE[1]");
$truncate->setSize(1);
$truncate->setStyle('class="form-element inp50"');

$truncate->addOption('[translate: use_meta_desc]', 'meta', 0, 0) ;
$truncate->addOption('[translate: use_content]', 'content', 1, 0) ;
$truncate->setDisabled('content');

foreach($trunc_options as $value=>$option) {
    $truncate->addOption($option, $value, $value, 1) ;
}
$value1 = ("CJO_VALUE[1]" != '') ? "CJO_VALUE[1]" : $trunc_default;
$truncate->setSelected($value1);


// EINSTELLUNGEN FÜR DIE SORTIERUNG
$sort_default = 'name';
$sort_options = array('prior'           => '[translate: order_by_prior]',
                      'name' 	 		=> '[translate: order_by_name]',
                      'createdate' 		=> '[translate: order_by_createdate]',
                      'updatedate' 		=> '[translate: order_by_updatedate]',
                      'online_from' 	=> '[translate: order_by_online_from]',
                      'online_to' 		=> '[translate: order_by_online_to]',
                      'random'  		=> '[translate: order_by_random]');

$sort = new cjoSelect();
$sort->setName("VALUE[8]");
$sort->setSize(1);
$sort->setStyle('class="form-element inp50"');

foreach($sort_options as $value=>$option) {
    $sort->addOption($option, $value);
    if("CJO_VALUE[8]" != '' && "CJO_VALUE[8]" == $value)
        $sort->setSelected($value);
}
if (empty($sort->option_selected)) $sort->setSelected($sort_default);


// DES TEASER-IMG
$teaser_img_num = new cjoSelect();
$teaser_img_num->setName("VALUE[15]");
$teaser_img_num->setSize(1);
$teaser_img_num->setStyle('class="form-element inp50"');

$teaser_img_num->addOption('[translate: teaser_default_img]','');
$teaser_img_num->addOption('[translate: teaser_no_img]','off');
$teaser_img_num->addOption('[translate: teaser_met_img]','meta');

for($i=1; $i<=10; $i++) {
	$teaser_img_num->addOption('MEDIA['.$i.']', $i);
}
$teaser_img_num->setSelected("CJO_VALUE[15]");


// AUSWAHL KATEGORIEN
$cat_true = new cjoSelect();
$cat_true->setName('cat_true[]');
$cat_true->setId('cat_true');
$cat_true->setMultiple(true);
$cat_true->showRoot('ROOT');
$cat_true->setDisabled(0);
$cat_true->setStyle('class="form-element inp100" style="height: 210px"');

$cat_false = new cjoSelect();
$cat_false->setName('cat_false[]');
$cat_false->setId('cat_false');
$cat_false->setMultiple(true);
$cat_false->showRoot('ROOT');
$cat_false->setDisabled(0);
$cat_false->setStyle('class="form-element inp100" style="height: 210px"');

$sql = new cjoSql;
$sql->setQuery("SELECT id, name, re_id FROM ".TBL_ARTICLES." WHERE startpage=1 AND clang='".cjoProp::getClang()."' ORDER BY prior");
for ($i = 0; $i < $sql->getRows(); $i++) {

    $cat_true->addOption($sql->getValue("name"), $sql->getValue("id"), $sql->getValue("id"), $sql->getValue("re_id"));
    $cat_false->addOption($sql->getValue("name"),  $sql->getValue("id"), $sql->getValue("id"), $sql->getValue("re_id"));

    if (preg_match('/(?<=,|^)'.$sql->getValue("id").'(?=,|$)/', str_replace(' ','', "CJO_VALUE[6]"))) {
        $cat_true->setSelected($sql->getValue("id"));
    }
    if (preg_match('/(?<=,|^)-'.$sql->getValue("id").'(?=,|$)/', str_replace(' ','', "CJO_VALUE[6]"))) {
        $cat_false->setSelected($sql->getValue("id"));
    }
    $sql->nextValue();
}

// EINSTELLUNGEN FÜR NEUESTE TEASER
$dura_default = '0';
$dura_options = array('0'  => '[translate: duration_no]',
                      '7'  => '[translate: duration_yes] 1 [translate: duration_week]',
                      '14' => '[translate: duration_yes] 2 [translate: duration_weeks]',
                      '30' => '[translate: duration_yes] 1 [translate: duration_month]',
                      '60' => '[translate: duration_yes] 2 [translate: duration_months]');

$duration = new cjoSelect();
$duration->setName("VALUE[5]");
$duration->setSize(1);
$duration->setStyle('class="form-element inp50"');

foreach($dura_options as $value=>$option) {
    $duration->addOption($option, $value);
    if("CJO_VALUE[5]" != '' && "CJO_VALUE[5]" == $value)
        $duration->setSelected($value);
}
if (empty($duration->option_selected)) $duration->setSelected($dura_default);


// EINSTELLUNGEN FÜR DIE MODULZUORDNUNG
$modul_ids = (is_array($CJO_EXT_VALUE['modul_ids']))
           ? '|'.implode('|', $CJO_EXT_VALUE['modul_ids']).'|'
           : '';

$modules = new cjoSelect();
$modules->setName("CJO_EXT_VALUE[modul_ids][]");
$modules->setSize(6);
$modules->setMultiple(true);
$modules->setStyle('class="form-element inp50"');
$modules->addOption(' ', '');

$sql = new cjoSql();
$sql->setQuery("SELECT id, name FROM ".TBL_MODULES." ORDER BY prior");

for($i = 0; $i < $sql->getRows(); $i++) {

    $modul_name  = $sql->getValue('name');
    $modul_name .= (cjoProp::getUser()->hasPerm('advancedMode[]')) ? ' (ID='.$sql->getValue('id').')' : '';

    $modules->addOption($sql->getValue('name'), $sql->getValue('id'));

    if (strpos($modul_ids, '|'.$sql->getValue('id').'|') !== false)
        $modules->setSelected($sql->getValue('id'));

    $sql->next();
}

if (empty($modules->option_selected)) $modules->setSelected('');

$ctypes = new cjoSelect();
$ctypes->setName("VALUE[12]");
$ctypes->setSize(1);
$ctypes->setMultiple(false);
$ctypes->setStyle('class="form-element inp100"');

foreach (cjoProp::get('CTYPE') as $ctype_id=>$ctype_name) {
    $ctypes->addOption($ctype_name, $ctype_id);
}
$ctypes->setSelected("CJO_VALUE[12]");

cjoModulTemplate::addVars('TEMPLATE',
						array(
                        'TRUNCATE_SELECTION' => $truncate->get(),
                        'SORT_SELECTION' 	 => $sort->get(),
                        'DURATION_SELECTION' => $duration->get(),
                        'MODUL_SELECTION'    => $modules->get(),
                        'CTYPE_SELECTION'	 => (cjoProp::countCtypes() > 1 ? $ctypes->get() : ''),

						'CROP_SELECTION'	 => cjoMedia::getCropSelection("VALUE[16]",
						                                                   ("CJO_VALUE[16]" != "" ? "CJO_VALUE[16]" : $CJO['MODUL_SET'][1]['TEASER_CROP_NUM']),
						                                                   'form-element inp50'),
                        'TEASER_IMG_NUM'   	   => $teaser_img_num->get(),
                        'CAT_TRUE_SELECTION'   => $cat_true->get(),
                        'CAT_FALSE_SELECTION'  => $cat_false->get(),
                        'PANGINATION_CHECKED'  => cjoAssistance::setChecked("CJO_VALUE[3]", array('1')),
                        'SORT_ASC_CHECKED'	   => cjoAssistance::setChecked("CJO_VALUE[9]", array('asc', '')),
                        'SORT_DESC_CHECKED'	   => cjoAssistance::setChecked("CJO_VALUE[9]", array('desc')),
                        'SUB_CATS_CHECKED'	   => cjoAssistance::setChecked("CJO_VALUE[7]", array('1')),
                        'ONLY_RELATED_CHECKED' => cjoAssistance::setChecked("CJO_VALUE[4]", array('1')),
                        'DEBUGING_CHECKED' 	   => cjoAssistance::setChecked("CJO_VALUE[10]", array('1')),
                        'BACKBUTTON_CHECKED'   => cjoAssistance::setChecked("CJO_VALUE[14]", array('1')),

                        'DISPLAY_WHERE'		   => (!cjoProp::getUser()->hasPerm('advancedMode[]') && !cjoProp::getUser()->hasPerm('admin[]')
                                                   ? true : false)
                        ));

cjoModulTemplate::getModul();
?>