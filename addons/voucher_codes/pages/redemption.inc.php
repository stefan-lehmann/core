<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  voucher_codes
 * @version     2.6.0
 *
 * @author      Stefan Lehmann <sl@contejo.com>
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

//LIST Ausgabe
$sql = "SELECT
			*,
			(SELECT
			 CONCAT( title, ' :: ',
			 (DATE_FORMAT( FROM_UNIXTIME( start_date ),'%d.%m.%y'))) AS event
			 FROM ".TBL_16_EVENTS."
			 WHERE id=event_id LIMIT 1
			 ) AS event
		FROM ".TBL_17_VOUCHER."
		WHERE event_id > 0";

$list = new cjolist($sql, 'event_id', 'ASC', '', 100);

$cols['code'] = new resultColumn('code', $I18N_17->msg("label_code"), 'sprintf', '<span>%s</span>');
$cols['code']->setHeadAttributes('class="icon"');
$cols['code']->setBodyAttributes('class="icon cjo_id"');
$cols['code']->delOption(OPT_ALL);
$cols['event'] = new resultColumn('event', $I18N_17->msg('label_event'));
$cols['event']->setParams(array ('page' => 'event_calendar','subpage'=>'events','clang' => $clang,'function' => 'edit','id' => '%event_id%'));
$cols['firstname'] = new resultColumn('firstname', $I18N_17->msg('label_firstname'));
$cols['name'] = new resultColumn('name', $I18N->msg('label_name'));
$cols['email'] = new resultColumn('email', $I18N_17->msg('label_email'), 'email');


// Lösch link
$cond['delete'] = '<img src="img/silk_icons/bin.png" title="'.$I18N_17->msg("label_delete_voucher").'" alt="'.$I18N_17->msg("label_delete_voucher").'" />';
$cols['delete'] = new staticColumn($cond['delete'], $I18N->msg("label_functions"));
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');

$list->addColumns($cols);

$browseBar = new browseBar();
$browseBar->setAddButtonStatus(false);
$list->addToolbar($browseBar, 'top', 'half');
$list->addToolbar(new searchBar(), 'top', 'half');
$list->addToolbar(new statusBar(), 'bottom', 'half');
$list->addToolbar(new maxElementsBar(), 'bottom', 'half');

$list->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		var code;

		$(".cjo_status,"+
		  ".cjo_delete").bind('click', function(){

			var el = $(this);
			var id = el.siblings('.cjo_id').eq(0).text();
			var cl = el.attr('class');

			if(!cjo,confirm(el.find('img'))){
				return false;
			}
			cjo.toggleOnOff(el);

			$.get('ajax.php',{
				   'function': 'vc_delete_voucher',
				   'code': id },
				  function(message){

					if(cjo.setStatusMessage(message)){

					  	el.find('img.ajax_loader')
					  	  .remove();

					  	el.find('img')
					  	  .toggle();

						if ($('.statusmessage p.error').length == 0){

							el.parent('tr')
							  .siblings()
							  .find('.tablednd')
							  .each(function(i){
							  		$(this).children().text(i+1);
							  });

							el.parent('tr').remove();

							$('div[id^=cs_options][id$='+article_id+']').remove();
						}
					}
			});
		});
    });

/* ]]> */
</script>