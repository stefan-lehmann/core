<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.6.0
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
 * @copyright   Copyright (c) 2008-2012 CONTEJO. All rights reserved. 
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *  CONTEJO is free software. This version may have been modified pursuant to the
 *  GNU General Public License, and as distributed it includes or is derivative
 *  of works licensed under the GNU General Public License or other free or open
 *  source software licenses. See _copyright.txt for copyright notices and
 *  details.
 * @filesource
 */

global $CJO;
$mypage = 'shop';

if (cjo_post('cjoform_export_button', 'bool')) {
    cjoShopExport::exportOrders();
}

if( $function == 'delete' && $oid != '') {
	$delete = new cjoSql();
	$delete->setTable(TBL_21_ORDERS);
	$delete->setWhere('id='.$oid);
	$delete->Delete();

	cjoAssistance::redirectBE( array( 'function' => '' ) );
}


if ($function == "edit" ) {
	$settings = $CJO['ADDON']['settings'][$mypage];
	$pay_methods_path = $settings['PAY_METHODS_PATH'];

	// create table for product output
	$sql = new cjoSql();
	$qry = "SELECT *,
				CONCAT(title,' ',firstname,' ',name) AS customer
			FROM ".TBL_21_ORDERS."
			WHERE id='".$oid."'";

	// get data
	$dataset = array_shift($sql->getArray($qry));


	// **** include pay method files **** //

	$pay_method  = $dataset['pay_method'];
	$config_file = $pay_methods_path.'/'.$pay_method.'/config.inc.php';
	$class_file  = $pay_methods_path.'/'.$pay_method.'/class.shop_'.$pay_method.'.inc.php';

	// status of the order
    $fields['state'] = new selectField('state', $I18N_21->msg('shop_state'), array('style'=>'width:230px;display:block!important'));
	$fields['state']->addAttribute('size', '1');
    $fields['state']->addOption($I18N_21->msg("shop_order_status_canceled"), -1);
    $fields['state']->addOption($I18N_21->msg("shop_order_status_added"), 1);
    $fields['state']->addOption($I18N_21->msg("shop_order_status_under_progress"), 2);
    $fields['state']->addOption($I18N_21->msg("shop_order_status_send"), 3);
    $fields['state']->addOption($I18N_21->msg("shop_order_status_finished"), 4);

    $temp = $dataset['state'] == 3
    	  ? array('style' => 'width: auto;')
    	  : array('style' => 'width: auto;', 'disabled' => 'disabled');

	$fields['notification'] = new checkboxField('notification', '', $temp);
	$fields['notification']->addBox($I18N_21->msg("label_send_notification"), '1');
	$fields['notification']->activateSave(false);

	// create object belonging to pay_method
	$pay_object = cjoShopPayMethod::getPayObject($pay_method, $dataset['pay_data']);
	$pay_costs = cjoShopPayMethod::getAllCosts();
	$pay_costs = $pay_costs[$pay_method];

	$address1 = new cjoShopAddress($dataset['address1']);
	$address2 = !empty($dataset['address2']) ? new cjoShopSupplyAddress($dataset['address2']) : '';

	$dataset['products'] 	= cjoShopProduct::toTable($oid);
	$dataset['address1'] 	= $dataset['company']."\r\n".$address1->out();
	$dataset['address2'] 	= empty($address2) ? $dataset['customer']."\r\n".$address1->out() : $address2->out();
	$dataset['pay_data'] 	= get_class($pay_object) == 'cjoShopEmptyPayMethod'
							? '' : $pay_object->out();
	$dataset['pay_method']	= cjoShopPayMethod::getName($dataset['pay_method']);

    // create formular
    $form = new cjoForm();
    $form->setEditMode(false);

    // set form fields and fill it
    $fields['id'] = new readOnlyField('id', $I18N_21->msg('shop_order_id'), array('class' =>'large_item'));

    $fields['customer'] = new readOnlyField('customer', $I18N_21->msg('shop_full_name'), array('class' =>'large_item'));

    $fields['createdate'] = new readOnlyField('createdate', $I18N_21->msg('shop_order_date'));
    $fields['createdate']->setFormat('strftime',$I18N->msg('datetimeformat'));

    $fields['address1'] = new readOnlyField('address1', $I18N_21->msg('shop_main_address'),
                                            array('class' => 'cjo_with_border cjo_float_l',
                                                  'style' => 'width:220px;white-space:pre'));

    $fields['phone_nr'] = new readOnlyField('phone_nr', $I18N_21->msg('shop_phone_nr'));

    $fields['email'] = new readOnlyField('email', $I18N_21->msg('shop_email'));
    $fields['email']->setFormat('sprintf', '<a href="mailto:%1$s?subject='.$I18N_21->msg('shop_email_subject_order', $CJO['SERVERNAME'], $oid).'">%1$s</a>');

    $fields['delivery_method'] 	= new readOnlyField('delivery_method', $I18N_21->msg('shop_deliverer'));

    $fields['address2'] = new readOnlyField('address2',  $I18N_21->msg('shop_supply_address'),
                                            array('class' => 'cjo_with_border cjo_float_l',
                                                  'style' => 'width:220px;white-space:pre'));


    $fields['pay_method'] = new readOnlyField('pay_method', $I18N_21->msg('shop_pay_method'));

    if(!empty($pay_object))
    	$fields['pay_data'] = new readOnlyField('pay_data', $I18N_21->msg('shop_pay_data'),
                                            array('class' => 'cjo_with_border cjo_float_l',
                                                  'style' => 'width:220px;white-space:pre'));

	$fields['birth_date'] = new readOnlyField('birth_date', $I18N_21->msg('shop_birth_date'));
    $fields['birth_date']->setFormat('strftime',$I18N->msg('dateformat'));



    $fields['products'] = new readOnlyField('products', $I18N_21->msg('shop_ordered_products'));
    $fields['products']->needFullColumn(true);

    $fields['comment'] = new textAreaField('comment', $I18N_21->msg('shop_order_comment'),
                                           array('class' => 'cjo_float_l',
                                           		 'style' => 'width:704px;white-space:pre',
                                                 'rows' => '6'));
    $fields['comment']->needFullColumn(true);


    $fields['updatedate'] = new readOnlyField('updatedate', $I18N->msg('label_updatedate'));
    $fields['updatedate']->setFormat('strftime',$I18N->msg('datetimeformat'));
    $fields['updateuser'] = new readOnlyField('updateuser', $I18N->msg('label_updateuser'));

    // add fields and data
    $section = new cjoFormSection(TBL_21_ORDERS, $I18N_21->msg('shop_edit_orders'), array ('id' => $oid), array('50%','50%'));
    $section->dataset = $dataset;
    $section->addFields($fields);
    $form->addSection($section);
    $form->show();

    if ($form->validate()) {
        
        if (cjo_post('cjoform_save_button','bool')) {
	         $update = new cjoSql();
	         $update->setTable(TBL_21_ORDERS);
	         $update->addGlobalUpdateFields();
	         $update->Update();
        }

        if (cjo_post('notification','bool')){
            cjoShopMail::sendMail('ORDER_SEND_SUBJECT', $oid);
        }

       cjoAssistance::redirectBE(array('function' => ''));
    }

} // end if function = edit


if ($function == '') {

	//LIST output
	$qry = "SELECT
					*,
				 id AS address1
			FROM "
				.TBL_21_ORDERS;

	$list = new cjoList($qry, 'id', 'DESC', '', 60);
    $list->addGlobalParams(cjo_a22_getDefaultGlobalParams());
	//$list-> debug = 1;

	$cols['id'] = new resultColumn('id', $I18N_21->msg('shop_order_id_short'));
	$cols['id']->setHeadAttributes('class="icon"');
	$cols['id']->setBodyAttributes('class="icon"');


	$cols['firstname'] = new resultColumn('firstname', $I18N_21->msg('shop_firstname'));
	$cols['name'] = new resultColumn('name', $I18N_21->msg('shop_lastname'));

	$cols['address1'] = new resultColumn('address1', $I18N_21->msg('shop_checkout_address1'), 'call_user_func',
												             array('cjoShopAddress::addressOut',array('%s', 22)));
	$cols['address1']->setBodyAttributes('style="overflow: hidden"');
	$cols['address1']->delOption(OPT_ALL);

	$cols['email'] = new resultColumn('email', $I18N_21->msg('shop_email'),'sprintf',
												   '<a href="mailto:%1$s?subject='.$I18N_21->msg('shop_email_subject_order', $CJO['SERVERNAME']).'"
												   title="%1$s"><img src="img/silk_icons/email.png" alt="'.$I18N_21->msg("shop_email").'" /></a>');
	$cols['email']->setHeadAttributes('class="icon"');
	$cols['email']->setBodyAttributes('class="icon"');
	$cols['email']->delOption(OPT_SORT);

	$cols['total_price'] = new resultColumn('total_price',  $I18N_21->msg('shop_total_price'), 'call_user_func',
												                          array('cjoShopPrice::toCurrency',array('%s')));
    $cols['total_price']->setBodyAttributes('style="text-align: right; font-weight: bold;"');

	$cols['createdate'] = new resultColumn('createdate', $I18N_21->msg('shop_order_date'),'strftime', $I18N->msg('datetimeformat'));
	$cols['createdate']->setBodyAttributes('style="text-align: right;"');

	$cols['state'] = new resultColumn('state', $I18N_21->msg("shop_state"));
	// add conditions and icons to state column
	$cols['state']->addCondition('state', -1, '<img src="img/silk_icons/cancel.png" alt="true" title="'.$I18N_21->msg("shop_order_status_canceled").'" />');
	$cols['state']->addCondition('state', 1, '<img src="img/silk_icons/add.png" alt="true" title="'.$I18N_21->msg("shop_order_status_added").'" />');
	$cols['state']->addCondition('state', 2, '<img src="img/silk_icons/time.png" alt="true" title="'.$I18N_21->msg("shop_order_status_under_progress").'" />');
	$cols['state']->addCondition('state', 3, '<img src="img/silk_icons/lorry.png" alt="true" title="'.$I18N_21->msg("shop_order_status_send").'" />');
	$cols['state']->addCondition('state', 4, '<img src="img/silk_icons/accept.png" alt="true" title="'.$I18N_21->msg("shop_order_status_finished").'" />');
	$cols['state']->setHeadAttributes('class="icon"');
	$cols['state']->setBodyAttributes('class="icon"');
	$cols['state']->addOption(OPT_SORT);


	// update link
	$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
	$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
	$cols['edit']->setBodyAttributes('width="16"');
	$cols['edit']->setHeadAttributes('colspan="2"');
	$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

	// delete link
	$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid'=> '%id%' ,'msg'=> false));

	// add columns to list
	$list->addColumns($cols);

        $functions  = '<p style="text-align:center">'."\r\n".
                      '		<input type="submit" name="cjoform_export_button" value="'.$I18N_21->msg('label_cjoform_export_button').'" />'."\r\n".
                      '</p>'."\r\n";

    $list->setVar(LIST_VAR_INSIDE_FOOT, $functions);
	// show table
	$list->show();

}

?>

<script type="text/javascript">
 //<![CDATA[
    $(function(){
		$('select[name=state]')
			.change(function(){
				var $this = $(this);
				var selected = $this.find('option:selected').val();
				var notification = $('input:checkbox[name=notification]');

				if (selected == 3) {
					notification.removeAttr('disabled');
				}
				else {
					notification.attr('disabled', 'disabled');
					notification.removeAttr('checked');
				}
			});

    });
 //]]>
</script>
