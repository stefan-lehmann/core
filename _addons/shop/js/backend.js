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

$(function(){

	shop_enable_out_of_stock();

    $('#shop_enable_out_of_stock').click(function(){

		var count_down  = $('#shop_count_down_stock');

		if ($(this).is(':checked')){
			count_down
				.attr('checked','checked')
				.attr('disabled', 'disabled');
		}
		else {
			count_down
				.removeAttr('disabled');
		}
    });

    $('.shop_attribute_modul_select :checkbox').click(function(){
        var $this = $(this);
        var s  = $this.nextAll('span').first();

		if ($this.is(':checked')){
			s.show();
		}
		else {
			s.hide()
             .find('select option:selected')
             .removeAttr('selected');
		}
    });

    $('#shop_netto_price, #shop_tax').keyup(function() {
      var netto  = $('#shop_netto_price').val().replace(',', '.') *1;
      var tax    = $('#shop_tax').val().replace(',', '.')*1;
      var sign   = $('#shop_currency_sign').text();
      var brutto = netto + (Math.round(netto * tax)/ 100);
          brutto = Math.round(brutto*100)/100;

      if (!isNaN(brutto)) {
          brutto = brutto.toString();
          if (!$('#shop_netto_price').val().match(/\./)) {
              brutto = brutto.replace('.', ',');
          }
          $('#shop_brutto_price').html(brutto + ' ' + sign);
      }
    });



});

function shop_enable_out_of_stock(){

	var enable 		= $('#shop_enable_out_of_stock');
	var count_down  = $('#shop_count_down_stock');

	if (!enable.is('input:checked')){
		count_down.removeAttr('disabled')
	} else {
		count_down
			.attr('checked', 'checked')
			.attr('disabled', 'disabled');
	}
}