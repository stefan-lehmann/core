/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.5.2
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

$(function() {

	cjo.showScripttime('STANDARD_BEGIN');
	cjo.conf.form = $('#CJO_FORM');
    cjo.scrollToPos();
	cjo.toggleStatusMessage();
	cjo.initQuickLinks();
	cjo.initMultiLinks();
	cjo.initJConfirmByClass();
	cjo.initEditContent();	
	cjo.initDialogHelp();
    cjo.initSelectButtons();
    cjo.initA22Elements();


    $('.info_icons h5').click(function() {
    	var $this = $(this);
	
    	var open = $('.info_icons h5.open').next();

    	if ($this.is('.open')) {
    		open.hide('normal', function() {
    			$this.removeClass('open');
    		});
    	} else {
    		open.hide('normal', function() {
        		$('.info_icons h5.open').removeClass('open');
        		$this.addClass('open');	
    		});

    		$this.next().show('normal');
    		$this.addClass('open');	
    	}
        var button = $this.parent().find('.cjo_form_button');    
        if (button.is(':hidden')) {
        	button.fadeIn('fast');
        } else {
        	button.fadeOut('fast');
        }
    });

    $('*[class^=cjo_alpha_]').each(function() {
    	if($.browser.msie) return true;
        var $this = $(this);
        var css = $this.attr('class');
        var alpha = css.replace(/(.*cjo_alpha_)(\d+)(.*)/gi, '$2')*1/100;
        $(this).fadeTo(1, alpha);
    });

	$('input.cjo_jpicker').jPicker({setAlpha: '', window: {expandable: true} });
	
	$('#cjo.conf.form').find('input[type=text]:not(:input[readonly]), select, textarea:not(textarea[readonly])').eq(0).focus();
	
	// $('a.cjo_ajax:not(.cjo_confirm)').click(function() {
        // var el = $(this);
        // var href = el.attr('href');       
        // if(el.parent().is('.cjo_confirm')) return false;
        // cjo.processAjaxCall(href, el);
        // return false;
	// });
	
 
	
//	$(window).unload(function() {
//	  alert('Handler for .unload() called.');
//	});

	
	cjo.showScripttime('STANDARD_END');
});

var cjo = {
		
'conf': {	
	'ajax_loader':       '<img src="./img/contejo/ajax/ajax-loader1.gif" alt="" title="...please wait!" class="ajax_loader" />',
	'jdialog':			 '<div id="cjo_jdialog" class="hide_me">%MESSAGE%</div>',
	'curr_media_button': '',
	'timeout': 			 null
},
'initA22Elements' : function() {
    
    $('.a22-cjoform span.slide').each(function(i){
        var $this = $(this);
        $this.append('<span></span>');
        cjo.toggleFormSection($this, true, 0);
        $this.click(function(){
            var hide = ($this.parent().parent().next().css('display') != 'none') ? true : false;
            cjo.toggleFormSection($this, hide, 300);
        });
    });
   $('.a22-cjolist-data').each(function() {
       var $this = $(this);
       $this.find('.checkbox.get_all:not(:disabled)').click(function(){
            if ($('.a22-cjolist-data .checkbox.get_all:checked').length > 0 ||
                $(this).is(':checked')) {
                $this.find('.update_selection').removeAttr('disabled');
                $('.toolbar_ext .hidden_container')
                    .fadeIn('slow');
            } else {
                $this.find('.update_selection')
                    .attr('disabled', 'disabled')
                    .find('option')
                    .removeAttr('selected');
                $this.find('.update_selection')
                    .nextAll('span')
                    .addClass('hide_me');
                $('.toolbar_ext .hidden_container')
                    .fadeOut('slow');
            }
        });
                
        $this.find('.check_all').click(function(){
                if($(this).is(':checked')){
                    $this.find('.checkbox.get_all:not(:disabled)')
                        .attr('checked','checked');
                    $('.update_selection')
                        .removeAttr('disabled');
                }
                else {
                    $this.find('.checkbox.get_all')
                        .removeAttr('checked');
                    $this.find('.update_selection')
                        .attr('disabled', 'disabled')
                        .find('option')
                        .removeAttr('selected');
    
                    $this.find('update_selection')
                        .nextAll('span')
                        .addClass('hide_me');
                }
        });
    }); 
    
    $('.a22-cjolist-data .preview a').click(function(){
        var href = $(this).attr('href');
        var filename = href.replace(/^.*filename=/gi,'');
        var url = '../files/'+filename;
        cjo.openPopUp('Preview', url, 0, 0);
        return false;
    }); 
},

'toggleFormSection': function(el, hide, duration) {
	
	if (hide) {
		el.addClass('closed');
		el.removeClass('open'); 
	} 
	else { 
		el.addClass('open');
		el.removeClass('closed');
	}
	
    el.parent().parent().nextAll(':not(.hide_me)').each(function(i) {

        var $this = $(this);

	    if ($this.find('.button_field').length > 0 ||
			$this.find('.formheadline').length > 0) {
            return false;
        }
        else {
            if(hide) {
				$this.css('position', 'relative');
                if(duration != 0) {
                    $this.slideUp(duration);
                }
                else{
                    if ($this.find('.invalid').eq(0).height() == null)
                        $this.hide(0);
                }
            }
            else {
                $this.slideDown(duration, function() {
					$this.css('position', 'static');
				});
            }
        }
    });
},

'confirm': function(el) {
	return confirm(el.attr('title')) ?  true : false;
},

'openjDialog': function(message) {
    window.clearTimeout(cjo.conf.timeout);
	var jdialog = cjo.appendJDialog(message);
    $(jdialog).dialog({modal: true, dialogClass: 'cjo_jdialog_help' });
	if ($(jdialog).dialog('isOpen')) {
		cjo.conf.timeout = setTimeout("$(jdialog).dialog('close')", 10000);
	}
},

'appendJDialog': function(message) {
	 $('#cjo_jdialog').remove();
	return $(cjo.conf.jdialog.replace(/%MESSAGE%/,message)).appendTo('body');
},

'connectMedia':	function(el) {

	var inp = (el.is('input[type=text]')) ? el : el.parent().parent().find('input[type=text]');
	var filename = inp.val();
	var id = inp.attr('id');

	if(id == '') {
		id = new Date().getTime();
		inp.attr('id', id);
	}
	curr_media_button = id;
    $.fancybox({
    		'padding'		: 0,
    		'autoScale'		: false,
    		'transitionIn'	: 'none',
    		'transitionOut'	: 'none',
    		'width'		    : 580,
    		'height'		: 860,
    		'href'			: 'connectmedia.php?mode=button&filename='+filename,
    		'type'			: 'ajax'
    	});

    return false;
},

'disconnectMedia': function(el) {
	el.parent().prev('input').val('');
	el.parentsUntil('.cjo_select_button').parent().find('a.cjo_select_button_preview').hide('fast');
},	
		
'disconnectLink': function(el) {
	
	el.parents('.cjo_select_button')
      .find('input')
      .val('')
      .nextAll('div.cs_select_class')
      .empty();
},		

'connectMediaList': function(el) {

	var inp = el.parent().parent().find('input');
	var filename = inp.val();
	var id = el.parent().parent().find('select').attr('id');

	if(id == '') {
		id = new Date().getTime();
		inp.attr('id', id);
	}
	curr_media_button = id;
    $.fancybox({
		'padding'		: 0,
		'autoScale'		: false,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'title'			: this.title,
		'width'		    : 580,
		'height'		: 860,
		'href'			: 'connectmedia.php?mode=list&filename='+filename,
		'type'			: 'ajax'
	});
    return false;
},

'disconnectMediaList': function(el) {
	var sel = el.parent().parent().find('select');
	sel.find('option:selected').remove();
	cjo.writeMediaList(sel.attr('id'), ',');
},

'writeMediaList': function(id, separator) {

	var list = '';
    $('#'+id+' option').each(function() {
		list += (list != '') ? separator : '';
		list += $(this).val();
	});
	$('#'+id).parent().find('input').val(list);
},

'moveMediaListItem': function(id, direction, num) {
    // move top
    // move bottom
    // move up
    // move down
	var separator = ',';

    var source = document.getElementById(id);
	var sourcelength = source.options.length;
    var elements = [];
    var was_selected = [];
    
    var moveItem = function(arr, from, to) {
        if (from == to || to < 0) {
            return arr;
        }
        tmp = arr[from];
        if (from > to) {
            for (index = from; index > to; index--) {
                arr[index] = arr[index - 1];
            }
        }
        else {
            for (index = from; index < to; index++) {
                arr[index] = arr[index + 1];
            }
        }
        arr[to] = tmp;
        return arr;
    };
    
    for (ii = 0; ii < sourcelength; ii++) {
        elements[ii] = new Array();
        elements[ii]['value'] = source.options[ii].value;
        elements[ii]['title'] = source.options[ii].text;
        was_selected[ii] = false;
    }

	if (typeof num != 'undefined') {
		$(id).children().removeAttr('selected');
		source.options[num].selected = true;
	}

    var inserted = 0;
    var was_moved = new Array();
    was_moved[-1] = true;
    was_moved[sourcelength] = true;

    if (direction == 'top') {
        for (ii = 0; ii < sourcelength; ii++) {
            if (source.options[ii].selected) {
                elements = moveItem(elements, ii, inserted);
                was_selected[inserted] = true;
                inserted++;
            }
        }
    }
    if (direction == 'up') {
        for (ii = 0; ii < sourcelength; ii++) {
            was_moved[ii] = false;
            if (source.options[ii].selected) {
                to = ii - 1;
                if (was_moved[to]) {
                    to = ii;
                }
                elements = moveItem(elements, ii, to);
                was_selected[to] = true;
                was_moved[to] = true;
            }
        }
    }
    if (direction == 'down') {
        for (ii = sourcelength - 1; ii >= 0; ii--) {
            was_moved[ii] = false;
            if (source.options[ii].selected) {
                to = ii + 1;
                if (was_moved[to]) {
                    to = ii;
                }
                elements = moveItem(elements, ii, to);
                was_selected[to] = true;
                was_moved[to] = true;
            }
        }
    }
    if (direction == 'bottom') {
        inserted = 0;
        for (ii = sourcelength - 1; ii >= 0; ii--) {
            if (source.options[ii].selected) {
                to = sourcelength - inserted - 1;
                if (to > sourcelength) {
                    to = sourcelength;
                }
                elements = moveItem(elements, ii, to);
                was_selected[to] = true;
                inserted++;
            }
        }
    }

    for (ii = 0; ii < sourcelength; ii++) {
        source.options[ii] = new Option(elements[ii]['title'], elements[ii]['value']);
        source.options[ii].selected = was_selected[ii];
    }

    cjo.writeMediaList(id,separator);
},

'moveImage': function(pos, range) {

	var elA = $('#cjo_imgslice_'+pos+' .cjo_inputs');
	var elB = $('#cjo_imgslice_'+(pos+range)+' .cjo_inputs');

	if (elA.length == 0 || elB.length == 0) return false;

	var imgA = elA.find('#cjo_mediabutton_'+(pos)).val();
	var imgB = elB.find('#cjo_mediabutton_'+(pos+range)).val();
	var inpA = elA.find('input');
	var inpB = elB.find('input');
	var selA = elA.find('select');
	var selB = elB.find('select');
	var areA = elA.find('textarea');
	var areB = elB.find('textarea');

	inpA.each(function(i) {
		var bval = inpA.eq(i).val();
		var aval = inpB.eq(i).val();

		inpA.eq(i).val(aval);
		inpB.eq(i).val(bval);
		var bcheck = inpA.eq(i).is(':checked');
		var acheck = inpB.eq(i).is(':checked');

		if(acheck) {
			inpA.eq(i).attr('checked','checked');
		} else {
			inpA.eq(i).removeAttr('checked');
		}
		if(bcheck) {
			inpB.eq(i).attr('checked','checked');
		} else {
			inpB.eq(i).removeAttr('checked');
		}
		var bdisabl = inpA.eq(i).is(':disabled');
		var adisabl = inpB.eq(i).is(':disabled');
		if(adisabl) {
			inpA.eq(i).attr('disabled','disabled');
		} else {
			inpA.eq(i).removeAttr('disabled');
		}
		if(bdisabl) {
			inpB.eq(i).attr('disabled','disabled');
		} else {
			inpB.eq(i).removeAttr('disabled');
		}
	});

	selA.each(function(i) {
		var optA = selA.eq(i).children();
		var optB = selB.eq(i).children();
		var aselected = {};
		var bselected = {};
		var ii;

		optA.each(function(ii) {
			bselected[ii] = optA.eq(ii).is(':selected');
			aselected[ii] = optB.eq(ii).is(':selected');
		});

		optA.removeAttr('selected');
		optB.removeAttr('selected');
		ii = 0;

		optA.each(function(ii) {
			if(aselected[ii] == true) {
				$(this).attr('selected','selected');
			}
			if(bselected[ii] == true) {
				optB.eq(ii).attr('selected','selected');
			}
		});
		var bdisabl = selA.eq(i).is(':disabled');
		var adisabl = selB.eq(i).is(':disabled');
		if(adisabl) {
			selA.eq(i).attr('disabled','disabled');
		} else {
			selA.eq(i).removeAttr('disabled');
		}
		if(bdisabl) {
			selB.eq(i).attr('disabled','disabled');
		} else {
			selB.eq(i).removeAttr('disabled');
		}
	});

	areA.each(function(i) {
		var bval = areA.eq(i).val();
		var aval = areB.eq(i).val();
		areA.eq(i).val(aval);
		areB.eq(i).val(bval);
		var bdisabl = areA.eq(i).is(':disabled');
		var adisabl = areB.eq(i).is(':disabled');
		if(adisabl) {
			areA.eq(i).attr('disabled','disabled');
		} else {
			areA.eq(i).removeAttr('disabled');
		}
		if(bdisabl) {
			areB.eq(i).attr('disabled','disabled');
		} else {
			areB.eq(i).removeAttr('disabled');
		}
	});

    if ($('#last_active').val() != '')
        $('#last_active').val(pos);

	return true;
},

'setStatusMessage': function(message) {
	
	if (typeof message != 'undefined') {

		if ($('.statusmessage').length > 0) {
			$('.statusmessage').remove();			
			$('#cjo_tabs').before(message);
			cjo.toggleStatusMessage();
		} else{
			$('#cjo_tabs').before(message);
			cjo.toggleStatusMessage();
		}
		return true;
	}
	return false;
},
	
'toggleStatusMessage' : function() {
	
	var jgrowl = $('#jGrowl');
	var show = function() { jgrowl.show(); };
	
	var toggle = function() {
		var m = 0;
		var g = jgrowl.height()*1;
		var l;
		jgrowl.find('.jGrowl-notification').each(function() {
			l = $(this).height()*1;
			m = m+l
		});

		if (m-10>g) {
			jgrowl.css('overflow','auto');
		} else {
			jgrowl.css('overflow','visible');
		}
	};

	var hide = function() {
		toggle();
		if (jgrowl.find('.jGrowl-message').length == 0) jgrowl.hide();
	};	
	
	$('.statusmessage p').each(function() {
		var p = $(this);
		if (p.is('.info')) {
			$.jGrowl(p.html(), { life: 6000, theme: 'info', position: 'center', beforeOpen: show, afterOpen: toggle, close: hide});
		}
		else if (p.is('.warning')) {
			$.jGrowl(p.html(), { life: 12000, theme: 'warning', position: 'center', beforeOpen: show, afterOpen: toggle, close: hide});
		}
		else if (p.is('.error')) {
			$.jGrowl(p.html(), { life: 240000, theme: 'error', position: 'center', beforeOpen: show, afterOpen: toggle, close: hide});
		}
	});
},

'initSelectButtons': function() {
		
	$('.cjo_select_button').each(function() {
		
		var $this = $(this);
		
		if ($(this).is(':hidden')) return false;
		
		var w = $this.width();
		var o = $this.css('padding-left').replace(/px/,'') *1;
			o += $this.css('padding-right').replace(/px/,'') *1;
			o += $this.css('border-left-width').replace(/px/,'') *1;
			o += $this.css('border-right-width').replace(/px/,'') *1;

		var c = 0;
		$this.children().each(function() {
			var $this = $(this);
	    	var w  = $this.width() *1;
				w += $this.css('padding-left').replace(/px/,'') *1;
				w += $this.css('padding-right').replace(/px/,'') *1;
				w += $this.css('border-left-width').replace(/px/,'') *1;
				w += $this.css('border-right-width').replace(/px/,'') *1;
			
			if(c < w) c = w;
		});

		if (c > w) $this.css('max-width', '10000px');
	});
},

'disconnectContentMedia': function(i, _stop) {

	var el = $('#cjo_mediabutton_'+i);
	el.val('')
	  .parent()
	  .find('a.cjo_select_button_preview')
	  .hide('fast');

    for (n = (i*1+1); n <= _stop; n++) {
		if (!cjo.moveImage(n, -1) || n == _stop) {
			cjo.conf.form.find('input[name=update]').val(1);
			cjo.conf.form.submit();
		}
	}
},

'disconnectContentLink': function(el) {

	el.parents('.cjo_select_button')
		.find('input')
		.val('')
		.nextAll('div.cs_select_class')
		.empty();
	
	cjo.conf.form.find('input[name=update]').val(1);
	cjo.conf.form.submit();
},

'initQuickLinks': function() {
	
	var quicklinks = $('tbody .quicklinks:hidden');

	if (!quicklinks.length) return false;
	
	var show_details = function(obj) {
		var infos = obj.find('.infos:not(:hidden)');
		var quick = obj.find('.quicklinks:hidden');
		if (quick.length < 1) return false;
        infos.hide();
        quick.fadeIn('fast');
	}
	
	var hide_details = function(obj) {
		var infos = obj.find('.col_details .infos:hidden');
		var quick = obj.find('.col_details .quicklinks:not(:hidden)');
		var multi = quick.find('a.cjo_multi_link');
        quick.hide();
        multi.next().hide();
        infos.fadeIn('fast');
	}

	quicklinks.each(function() {
		var $this = $(this);
		var td 	  = $this.parentsUntil('td').parent();
		var a 	  = td.find('a:first');	
		a.bind("mouseover", function() {
			show_details($(this).next());
	     });
		td.bind("mouseleave", function() {
			hide_details($(this));
	    });
	});
},

'initMultiLinks': function() {
	
	var multi = $('a.cjo_multi_link_opener');	

	if (!multi.length) return false;

	var show_multi = function(obj) {
		obj.addClass('hover');
		if ($.browser.msie) {
			obj.css('border','1px solid #ddd');
			obj.show();
		} else {
			if(obj.is(':hidden')) {
				obj.fadeIn('fast');
				$('.cs_holder_class').hide();
			}
		}
	}
	
	var hide_multi = function(obj) { obj.hide(); }
		
	multi.click(function() { return false; });
	
	multi.bind("mouseenter", function() {
		$(this).addClass('hover');
		show_multi($(this).next()); });
	
	multi.parent().parent().parent().bind("mouseleave", function() {
		$(this).find('a.cjo_multi_link_opener').removeClass('hover');
		hide_multi(multi.next()); });	
	
	multi.parent().parent().siblings().bind("mouseenter", function() {
		$(this).parent().find('a.cjo_multi_link_opener').removeClass('hover');
		hide_multi(multi.next()); });	
},

'initJConfirmByClass': function() {
	
    $('.cjo_confirm, .cjo_confirm a, .cjo_delete a').click(function() {

        var $this = $(this);    	

        var message = $this.attr('title');
        if (typeof message == 'undefined' || message == '') message = $this.find('img:first').attr('title');

        if ($this.is('a')) {
            var href = $this.attr('href');
            cjo.jconfirm(message, 'cjo.changeLocation', [href,$this]);
            return false;
        }
        else if ($this.is('button, input')) {
            cjo.jconfirm(message, 'cjo.submitForm', [$this]);
            return false;
        }
        return false;
    });
    
    $('select.cjo_ajax').change(function() {
       var el = $(this);
       cjo.processAjaxCall(el.find(':selected:first').val(), el);
       return false; 
    }); 
},

'initDialogHelp': function() {
    $('.cjo_dialog_help').click(function() {
        cjo.openjDialog($(this).find('.cjo_dialog_help_text').html());
    });
},

'initEditContent': function() {

	var content = $('#cjo_edit_content');
	var date = new Date();
	var update_random = date.getMilliseconds();

	if (content.length > 0) {

	    content.find('.cjo_button_edit').bind('click', function(i) {
			var slice_id = $(this).attr('id').replace(/cjo_button_edit-/,'')
			cjo.changeLocation(cjo.conf.url+'&slice_id='+slice_id+'&function=edit&clang='+cjo.conf.clang+'&ctype='+cjo.conf.ctype+'#slice'+slice_id);
			return false;
    	});

	    content.find('.cjo_button_cancel').bind('click', function(i) {
			var slice_id = $(this).attr('id').replace(/cjo_button_cancel-/, '');
            var href = cjo.conf.url + '&slice_id=0&function=edit&clang=' + cjo.conf.clang + '&ctype=' + cjo.conf.ctype + '#slice' + slice_id;
            cjo.jconfirm($(this), 'cjo.changeLocation', [href]);
			return false;
    	});

	    content.find('.cjo_button_update').bind('click', function(i) {
			cjo.conf.form.find('input[name=update]').val(1);
			cjo.conf.form.submit();
			return false;
    	});

	    content.find('.cjo_button_save').bind('click', function(i) {
			cjo.conf.form.submit();
			return false;
    	});

	    content.find('.cjo_button_delete').bind('click', function(i) {
            var $this = $(this);

            var slice_id = $this.attr('id').replace(/cjo_button_delete-/, '');
            var prev_sice_id = $('#slice_id_'+slice_id).prev().attr('id')
                prev_sice_id = (typeof prev_sice_id != 'undefined') ? prev_sice_id = prev_sice_id.replace(/slice_id_/, '') : 0;
            var href = cjo.conf.url + '&slice_id=' + slice_id + '&function=delete&clang=' + cjo.conf.clang + '&ctype=' + cjo.conf.ctype +'&save=1#slice' + prev_sice_id;
            cjo.jconfirm($(this), 'cjo.changeLocation', [href]);
			return false;
    	});

	    content.find('.cjo_button_move_up').bind('click', function(i) {
			var slice_id = $(this).attr('id').replace(/cjo_button_move_up-/,'');
			cjo.changeLocation(cjo.conf.url+'&slice_id='+slice_id+'&function=moveup&clang='+cjo.conf.clang+'&ctype='+cjo.conf.ctype+'&upd='+update_random+'#slice'+slice_id);
			return false;
    	});

	    content.find('.cjo_button_move_down').bind('click', function(i) {
			var slice_id = $(this).attr('id').replace(/cjo_button_move_down-/,'');
			cjo.changeLocation(cjo.conf.url+'&slice_id='+slice_id+'&function=movedown&clang='+cjo.conf.clang+'&ctype='+cjo.conf.ctype+'&upd='+update_random+'#slice'+slice_id);
			return false;
    	});

	    content.find('.cjo_img_move_up').bind('click', function(i) {
			var pos = $(this).val()*1;
			if (cjo.moveImage(pos, -1)) {
				cjo.conf.form.find('input[name=update]').val(1);
				cjo.conf.form.submit();
			}
			return false;
    	});

	    content.find('.cjo_img_move_down').bind('click', function(i) {
			var pos = $(this).val()*1;
			if (cjo.moveImage(pos, 1)) {
				cjo.conf.form.find('input[name=update]').val(1);
				cjo.conf.form.submit();
			}
			return false;
    	});

	    content.find('.settings h2:not(.no_bg_image, .no_slide)').bind('click', function() {
            $(this).nextUntil('h2').not('script').slideToggle(300);
        });
	    
	    content.find('input[type=text], input[type=password], textarea').each(function() {
	    	
	    	$this = $(this);
	    	
	    	var width   = $this.width() *1 ;
	    	var parent  = $this.parent().width() *1;
	    	var offset	= $this.css('padding-left').replace(/px/,'') *1;
	    		offset += $this.css('padding-right').replace(/px/,'') *1;
	    		offset += $this.css('border-left-width').replace(/px/,'') *1;
	    		offset += $this.css('border-right-width').replace(/px/,'') *1;

		    if ($this.is('input[id^="cjo_mediabutton"]')) {
		    	offset = 6;	
		    }
		    
	    	if (parent-width < offset) {
	    		$this.css('width', width-offset);
	    	}
	    });

	    content.find('.settings').children('h2').each(function(i) {

			try {
                var width = $(this).width() * 1;
                $(this).next(':not(.formular)').children('input[type=text], select:not(.cjo_media_list)').width(width + 25);
            }
            catch (err) {
                return true;
            }

            if ($(this).is('.no_bg_image,.no_slide')) return true;

            var inp = $(this).next().find('input[value!=]:not(:hidden)').filter('input[type=text],input[type=password]');
            var are = $(this).next().find('textarea:not(:hidden)').text();
            var sel = $(this).next().find('select:not(:hidden) option:selected');

            if (inp.length > 0 ||
            	are.length > 0 ||
            	sel.length > 0) {
            	return true;
            }
            $(this).nextUntil('h2').not('script').addClass('hide_me').hide();
        });    
	}

	var imgslices = cjo.conf.form.find('div[id^=cjo_imgslice]');

	if (cjo.conf.form.length > 0) {

		imgslices.find('.cjo_imgslice_buttons input').click(function() {

			var el = $(this);
			var name = el.attr('name');
			var pos = el.val();
			var inputs = el.parent().next();
			var h3 = inputs.find('h3');
			var container = inputs.find('div.container');
			switch (name) {

				case 'imgslice_edit':
					if (inputs.is(':hidden')) {
						inputs.fadeIn('fast', function() {
							container.slideDown('fast', function() {
								h3.addClass('open');
							});
						});
						imgslices.find('.cjo_inputs').css('z-index', '1');
						inputs.css('z-index', '2');
					} else{
						container.slideUp('fast', function() {
							h3.removeClass('open');
							inputs.fadeOut('fast');
						});
					}
					return false;

				case 'imgslice_update':
					cjo.conf.form.find('input[name=update]').val('1');
					cjo.conf.form.submit();
					return false;

				case 'imgslice_remove':
                    pos = pos.split("|");
                    cjo.jconfirm($(this), 'cjo.disconnectContentMedia', [pos[0], pos[1]]);
					return false;
			}

		});
		
		imgslices.find('.cjo_inputs').mouseenter(function() {
			var $this = $(this);
			imgslices.find('.cjo_inputs').css('z-index', '1');
			$this.css('z-index', '2');
		});

		imgslices.find('h3').click(function() {
			var el = $(this);
			el.next().slideToggle('fast', function() {
				el.toggleClass('open');
			});
		});

		imgslices.find('select.toggle_next').change(function() {

			var next = $(this).nextAll().filter(".hide_me");
			next.hide();
			$(this).find("option").each(function(i) {
				if($(this).is(':selected') && $(this).val() != '-') {
					next.eq((i-1)).show();
				}
			});
        }).change();

		imgslices.find('select.cjo_crop_select').change(function() {

			var next = $(this).nextAll().filter("input:checkbox");
			next.removeAttr('disabled');

			$(this).find("option").each(function(i) {
				if ($(this).is(':selected') && $(this).attr('title') == 'no_watermark') {
					next.attr('disabled','disabled');
				}
			});
        }).change();

		imgslices.find('.error_msg_overlay').each(function() {
			var w1 = $(this).parent().parent().width();
			var w2 = $(this).parent().parent().find('img').width()+5;

			var w = w1 > w2 ? w1 : w2;

			$(this)
				.find('.warning')
				.width(w-38)
				.fadeTo(1,.75)
				.css('visibility', 'visible')
				.clone()
				.prependTo(this)
				.addClass('no_bg');
		});
	}
},

'submitForm': function(el) {
    var form  = el.parents('form');
    var name  = el.attr('name');
    var value = el.val();
    form.append('<input type="hidden" name="'+name+'" value="'+value+'" />');
    form.trigger("submit");
},

'updateEditLog': function() {
	
	if (cjo.conf.article_id < 1) return false;
	var interval = 600*1000;
	var writeLog = function() {
		$.get('ajax.php',{
			   'function': 'cjoLog::updateArticleLockedByUser',
			   'article_id': cjo.conf.article_id,
			   'clang': cjo.conf.clang});
	}
	window.setInterval(function(){ writeLog(); }, interval);
},
'updatePage': function() {
    cjo.saveScrollPos();
    location.href=location.href;
},
'scrollToPos': function() {
 
    if (!$.cookies.get('cjo_scroll')) return false;

    window.scroll(0, $.cookies.get('cjo_scroll'));
    $.cookies.del('cjo_scroll');
 
},
'saveScrollPos':function() {

    var yScroll;
    if (self.pageYOffset) {
        yScroll = self.pageYOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
        yScroll = document.documentElement.scrollTop;
    } else if (document.body) {
        yScroll = document.body.scrollTop;
    }
    if (yScroll > 0) {
        $.cookies.set('cjo_scroll', yScroll);
    }
},
'toggleOnOff': function(el) {

	var img = $(el).find('img');
	var src = img.attr('src');

	img.hide();

	$(el).append(cjo.conf.ajax_loader);

	if (src.match(/bin\.png$/)) return;


	if(src.match(/_off\.png$/)) {
		src = src.replace(/_off\.png$/g, ".png");
	}
	else{
		src = src.replace(/\.png$/g, "_off.png");
	}
	img.attr('src', src);
},


'popups': {'win':[], 'count': -1},

'createWindow': function(name, url, posx, posy, width, height, extra) {

    if (extra == 'toolbar') {
        extra = 'scrollbars=yes,toolbar=yes';
	}
    else {
       extra = (extra == 'empty') ? 'scrollbars=no,toolbar=no' : 'scrollbars=yes,toolbar=no,resizable=yes,' + extra;
	}

    this.name = name;
    this.url = url;
    var left = (self.screen.width - width) / 2;
    var top = (self.screen.height - height) / 2;
    this.obj = window.open(url, name, 'width=' + width + ',height=' + height + ', left=' + left + ', top=' + top + ', ' + extra);

	if(this.obj != null)
		this.obj.focus();

    return this;
},

'closePopUps': function() {
    for (var i = 0; i <= cjo.popups.count; i++) {
        if (cjo.popups.win[i])
        	cjo.popups.win[i].obj.close();
    }
},

'openPopUp': function(name, link, width, height, type) {

    if (width == 0) width = 550;
    if (height == 0) height = 400;

    if (type == 'scrollbars') {
        extra = 'toolbar';
    }
    else {
        if (type == 'empty') {
            extra = 'empty';
        }
        else {
            extra = type;
        }
	}
    if (type == "nav") {
        posx = parseInt(screen.width / 2) - 390;
        posy = parseInt(screen.height / 2) - 24 - 290;
        width = 320;
        height = 580;
    }
    else {
        if (type == "content") {
            posx = parseInt(screen.width / 2) - 390 + 330;
            posy = parseInt(screen.height / 2) - 24 - 290;
            width = 470;
            height = 580;
        }
        else {
            posx = '';
            posy = '';
        }
	}
    cjo.popups.count++;
    cjo.popups.win[cjo.popups.count] = new cjo.createWindow(name, link, posx, posy, width, height, extra);
},

'changeLocation': function(url,el) {
    if (!url.match(/ajax.php\?/)) {
        location.href = url.replace(/\&amp;/g,'&');
    }
    cjo.processAjaxCall(url,el)
},

'processAjaxCall': function(url, el, callback) {
    
    el = $(el);  
    
    el.hide().before(cjo.conf.ajax_loader);
    
    $.get(url, {}, function(message) {
        if (cjo.setStatusMessage(message) && !message.match(/class="error"/)) {
            if (el.is('.cjo_delete') || el.parent().is('.cjo_delete')) {
            	var table = el.parentsUntil('table').parent();
                el.parentsUntil('tr').parent().remove();
                cjo.updatePrio(table);
                return;
            }
            if (el.is('.cjo_status') || el.parent().is('.cjo_status')) {

                var image = el.find('img');
                var src = image.attr('src');
                src = (src.match(/_off\.png/)) 
                    ? src.replace(/_off\.png/, '.png') 
                    : src.replace(/\.png/, '_off.png');
   
                image.attr('src', src);
            }    
        
            if (typeof callback != 'undefined' && typeof callback == 'function') {
                callback();
                return;
            } 
            else if (el.data('callback') ) {
                eval(el.data('callback'));
                return;
            }
        }
        el.prev().remove();
        el.show();
    });
},

'updatePrio': function(table) {
	table.find('td.tablednd strong').each(function(i) {
		console.log(i+1);
		$(this).html(i+1);
	})
	
	
},

'openShortPopup': function(url) {
	window.open(url.replace(/\&amp;/g,'&'));
	return false;
},

//parseUri 1.2.2
//(c) Steven Levithan <stevenlevithan.com>
//MIT License

'parseUri': function (str) {
    var o   = {
            strictMode: false,
            key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
            q:   {
                name:   "queryKey",
                parser: /(?:^|&)([^&=]*)=?([^&]*)/g
            },
            parser: {
                strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
            }};
    var m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
        uri = {},
        i   = 14;

    while (i--) uri[o.key[i]] = m[i] || "";

    uri[o.q.name] = {};
    uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
        if ($1) uri[o.q.name][$1] = $2;
    });

    return uri;
}


}