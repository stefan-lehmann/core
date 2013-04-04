/**
 * This file is part of CONTEJO ADDON - COMMENTS
 *
 * PHP Version: 5.2.6+
 *
 * @package     Addon_comments
 * @subpackage  config
 * @version     SVN: $Id$
 *
 * @author      Stefan Lehmann <sl@contejo.com>
 * @copyright   Copyright (c) 2008-2010 CONTEJO. All rights reserved.
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

var div_co, show_co, hide_co, curr_hash;

$(function() {
	div_co  	= $('div#comments');
    show_co 	= $('#show_comments');
    hide_co 	= $('#hide_comments');
	curr_hash 	= location.hash.substr(1);

    if (!show_co.is(':hidden') && hide_co.is(':hidden')){
        show_co_list(show_co);
    }
	else if (show_co.is(':hidden') && !hide_co.is(':hidden')) {
        hide_co_list(hide_co);
    }

	if(curr_hash.match(/comment/i)){
		show_co.unbind('click');
        div_co.slideDown(1200, function(){
            show_co.fadeOut(200);
            hide_co_list(hide_co);

			if ($.fn.scrollTo){
				$('html,body').scrollTo('a[name='+curr_hash+']', {speed:600, axis:'y'});
			}
			else {
				alert('The CONTEJO Comments-AddOn requires jQuery.ScrollTo!\n\nPlease, use the CONTEJO jQuery-AddOn to install the missing plugin!');
				return false;
			}
        });
    }
});

function show_co_list(obj){
    obj.fadeIn(200);
    obj.bind('click', function(){
        obj.unbind('click');
        div_co.slideDown(1200, function(){
            obj.fadeOut(200, function(){
                hide_co_list(hide_co);
            });
        });
        return false;
    });
}

function hide_co_list(obj){
    obj.fadeIn(200);
    obj.bind('click', function(){
        obj.unbind('click');
        div_co.slideUp(800, function() {
            obj.fadeOut(200, function() {
                show_co_list(show_co);
            });
        });
        return false;
    });
}