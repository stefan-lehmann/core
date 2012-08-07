<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.7.x
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
?>

<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery-1.7.0.min.js"></script>
<script type="text/javascript" src="<?php echo $CJO['BACKEND_PATH']; ?>/js/contejo.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/iutil.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/easing.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery.blockUI.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/ui/jquery.ui.costum.min.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/ui/i18n/jquery.ui.datepicker-<?php echo $CJO['CLANG_ISO'][$clang]; ?>.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery.customselect.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery.flash.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery.tablednd.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery.fancybox.js"></script>
<script type="text/javascript" src="<?php echo $CJO['JQUERY_PATH']; ?>/jquery.jgrowl.js"></script>
<script type="text/javascript" src="<?php echo $CJO['BACKEND_PATH']; ?>/js/codepress/codepress.js"></script>
<script type="text/javascript" src="<?php echo $CJO['BACKEND_PATH']; ?>/js/jpicker/jpicker-1.1.5.js"></script>
<script type="text/javascript">
/* <![CDATA[ */

    cjo.conf.contejo     = true;
    cjo.conf.clang       = '<?php echo $CJO['CUR_CLANG']; ?>';
    cjo.conf.jQuery_path = '<?php echo $CJO['JQUERY_PATH']; ?>';
    cjo.conf.xtime       = '<?php echo !empty($_GET['cjo.xtime']) ? $_GET['cjo.xtime'] : ''; ?>';
    cjo.conf.url         = 'index.php?page=<?php echo $page; ?>&subpage=<?php echo $subpage; ?>&article_id=<?php echo $article_id; ?>&mode=edit',
    cjo.conf.article_id  = '<?php echo cjo_request('article_id', 'cjo-article-id'); ?>';
    cjo.conf.slice_id    = '<?php echo cjo_request('slice_id', 'cjo-slice-id') ?>';
    cjo.conf.ctype       = '<?php echo cjo_request('ctype', 'cjo-ctype-id') ?>';
    cjo.conf.clang       = '<?php echo cjo_request('clang', 'cjo-clang-id') ?>';
    cjo.conf['function'] = '<?php echo cjo_request('function', 'string') ?>';
    cjo.conf.mode        = '<?php echo cjo_request('mode', 'string') ?>';

    cjo.jconfirm = function(message, action, params){

        if (typeof( message ) != 'string') {
            var el = message;
            message = el.attr('title');
            if (message == '') message = el.find('img:first').attr('title');
        }

        if (!message.match(/\?/))  message += '?';

        if(params.length > 0) {
            action += '(';
            for (var i=0; i<params.length; i++) {
                action += (i>0) ? ', params['+i+']' : 'params['+i+']';
            }
            action += ')';
        }
        var jdialog = cjo.appendJDialog(message);

        $(jdialog)
            .dialog({
                buttons: {
                    '<?php echo $I18N->msg('label_ok'); ?>': function() {
                        $(this).dialog('close'); eval(action);
                    }, '<?php echo $I18N->msg('label_cancel'); ?>': function() {
                        $(this).dialog('close');
                    } }
            });

            if ($(jdialog).dialog('isOpen')) {
                $('.ui-dialog-buttonpane button:first').focus();
            }
    }

    if (cjo.conf.xtime == '1') {
    	cjo.conf.start_time = new Date().getTime();
    }
    
    cjo.showScripttime = function(text){

        if (cjo.conf.xtime != '1' ||
            text == undefined ||
            cjo.conf.start_time == undefined) return false;

        cjo.conf.end_time=new Date().getTime();
        var result = (cjo.conf.end_time-cjo.conf.start_time)/1000;
        var html = '<br/>'+text+': '+result+' seconds.';
        var newdiv = document.createElement("div");

        newdiv.innerHTML = html;
        document.getElementById("cjo_main").appendChild(newdiv);
    }
/* ]]> */
</script>
<!-- CJO_INCLUDE_JS -->