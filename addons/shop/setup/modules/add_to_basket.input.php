<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_shop
 * @subpackage 	modul
 *
 * @author 		Matthias Schomacker <ms@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

if (OOAddon::isActivated('shop')) {
    cjo_insertJS(false, $CJO['ADDON']['settings']['shop']['JS']['BACKEND']);
    cjo_insertCSS(false, $CJO['ADDON']['settings']['shop']['CSS']['BACKEND']);
    
$product_name = "CJO_VALUE[8]";
if (empty($product_name)) {
    $article = OOArticle::getArticleById($CJO['ARTICLE_ID']);
    if (OOArticle::isValid($article)) {
        $product_name = $article->getName();
    } else {
        $product_name = 'No Name';
    }
} 
$price = new cjoShopPrice('CJO_VALUE[2]', 0, 'CJO_VALUE[3]', 'CJO_VALUE[4]');
?>
<div class="settings">
    <h2 class="no_slide">[translate_21: shop_product_settings]</h2>
    <div class="formular">
        <label>[translate_21: shop_product_title]</label>
        <input type="text" class="inp50 headline_h3" name="VALUE[8]" value="<?php echo $product_name; ?>" />
    </div>
    <div class="formular">
        <label class="label_right">[translate_21: shop_state]</label>
        <input type="checkbox" name="VALUE[19]" value="1"
        <?php echo cjoAssistance::setChecked("CJO_VALUE[19]", array(1)); ?> />
        [translate_21: shop_product_online]
    </div>
    <div class="formular">
        <label>[translate_21: shop_order_id]</label>
        <input type="text" class="inp20" name="VALUE[12]" value="CJO_VALUE[12]" />
    </div>
    <div class="formular">
        <label>[translate_21: shop_products_in_stock]</label>
        <input type="text" class="inp10" name="VALUE[1]" value="CJO_VALUE[1]" style="display: inline" /> &nbsp;
        <input type="checkbox" name="VALUE[18]" value="1" id="shop_count_down_stock"
        <?php echo cjoAssistance::setChecked("CJO_VALUE[18]", array(1)); ?> />
        [translate_21: shop_count_down_stock] &nbsp;&nbsp;
        <input type="checkbox" name="VALUE[17]" value="1" id="shop_enable_out_of_stock"
        <?php echo cjoAssistance::setChecked("CJO_VALUE[17]", array(1)); ?> />
        [translate_21: shop_enable_out_of_stock] &nbsp;&nbsp;
        <input type="checkbox" name="VALUE[16]" value="1"
        <?php echo cjoAssistance::setChecked("CJO_VALUE[16]", array(1)); ?> />
        [translate_21: shop_show_in_stock]
    </div>
    <div class="formular">
        <label>[translate_21: shop_netto_price]</label>
        <input type="text" id="shop_netto_price" class="inp10" name="VALUE[2]"
               value="<?php echo cjoShopPrice::toCurrency('CJO_VALUE[2]', true,  false, 3) ?>"
               style="display: inline" /> &nbsp;
        <span id="shop_currency_sign"><?php echo $CJO['ADDON']['settings']['shop']['CURRENCY']['CURR_SIGN']; ?></span>
        &nbsp; | &nbsp; [translate_21: shop_brutto]:
        <strong id="shop_brutto_price">
            <?php echo cjoShopPrice::toCurrency(($price->getValue('final_price')), false,  false, 2) ?>
        </strong>
    </div>
    <div class="formular">
        <label>[translate_21: shop_tax]</label>
        <input type="text" id="shop_tax" class="inp10" name="VALUE[3]"
               value="<?php echo cjoShopPrice::toCurrency('CJO_VALUE[3]', true) ?>" style="display: inline"/> &nbsp; %
    </div>
    <div class="formular">
        <label>[translate_21: shop_discount]</label>
        <input type="text" class="inp10" name="VALUE[4]"
               value="<?php echo cjoShopPrice::toCurrency('CJO_VALUE[4]', true) ?>" style="display: inline"/> &nbsp; %
    </div>
    <div class="formular">
        <label>[translate_21: shop_packing_units]</label>
        <input type="text" class="inp10" name="VALUE[5]" value="CJO_VALUE[5]" style="display: inline"/>
    </div>
    <div class="formular">
        <label>[translate_21: shop_max_products_to_basked]</label>
        <input type="text" class="inp10" name="VALUE[11]" value="<?php echo ('CJO_VALUE[11]' < 1) ? 10 : 'CJO_VALUE[11]';  ?> " style="display: inline"/>
    </div>  
    <div class="formular">
        <label>[translate_21: shop_delivery_duration]</label>
        <?php echo cjoShopDelivery::getDurationSelection("VALUE[10]","CJO_VALUE[10]") ?>
    </div>
</div>
<div class="settings">
    <h2 class="no_slide">[translate_21: shop_product_attributes]</h2>   
    <?php echo cjoShopProductAttributes::getAttributeSelections("CJO_VALUE[6]"); ?>
</div>
<div class="settings">
    <h2 class="no_slide">[translate_21: shop_product_description]</h2>
	<div class="formular">
		<label>[translate_21: shop_product_image]</label>
		CJO_MEDIA_BUTTON[id=1 width=326 height=300]
	</div>
	<div class="formular">
        <label>[translate_21: shop_product_description]</label>
        CJO_WYMEDITOR[id=7 height=100]
    </div>
	<div class="formular">
		<label>[translate_21: shop_product_offline_message]</label>
		CJO_WYMEDITOR[id=9 height=100]
	</div>
</div>
<div class="settings">
	<h2>[translate_21: shop_product_statistic]
	    (<?php echo @strftime($I18N->msg('dateformat'), 'CJO_VALUE[13]').' - '.@strftime($I18N->msg('dateformat'), time()); ?>)</h2>
	<div class="formular">
		<input type="hidden" name="VALUE[13]" value="CJO_VALUE[13]" />
		<input type="checkbox" name="VALUE[13]" value="0" />
		[translate_21: shop_product_statistic_reset]
	</div>
	<div class="formular">
		<label>[translate_21: shop_added_to_basket]</label>
		<input type="text" class="inp10" name="VALUE[15]"
			   value="CJO_VALUE[15]" readonly="readonly" style="display: inline" />
	</div>
	<div class="formular">
		<label>[translate_21: shop_bought_products]</label>
		<input type="text" class="inp10" name="VALUE[14]"
		       value="CJO_VALUE[14]" readonly="readonly" style="display: inline" /> &nbsp;
	</div>
</div>
<?php
}
elseif ($CJO['CONTEJO']) {
    echo $I18N->msg('msg_addon_not_activated', 'Shop');
}
?>