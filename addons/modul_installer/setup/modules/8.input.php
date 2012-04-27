<?php

$qry = "SELECT CONCAT(name, ' (ID=',id,')') as name, id
		FROM ".TBL_TEMPLATES."
		WHERE active='0'
		ORDER BY prior ASC";

$templates = new cjoSelect();
$templates->setName("VALUE[1]");
$templates->setStyle('class="form-element headline_h3"');
$templates->addOption(' ', '');
$templates->addSqlOptions($qry);
$templates->setSize(1);
$templates->setSelected("CJO_VALUE[1]");

?>
<div class="settings">
    <h2 class="no_bg_image">[translate: include_template]</h2>
    <?php echo $templates->get(); ?>
</div>