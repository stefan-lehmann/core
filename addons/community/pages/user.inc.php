<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
 * @version     2.6.0
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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

set_time_limit(120);
ini_set("memory_limit", "512M");

echo '<div id="cjo_cat_path">'.cjoCommunityGroups::getSelectGroups($group_id, true).'</div>';

if ($function == 'edit' || $function == 'add'){
	include_once $CJO['ADDON_PATH'].'/'.$mypage.'/pages/user_form.inc.php';
}
if ($function != 'edit' && $function != 'add'){
	include_once $CJO['ADDON_PATH'].'/'.$mypage.'/pages/user_list.inc.php';
}
?>

<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('select[name=status]').bind('change', function(){
			if ($(this).find("option:selected").val() != $("input[name=status]").val())
				$("input[name=c_status]").val(1);
		});

		$('#custom_select').selectpath({
			action : {root   : "location.href='index.php?page=<?php echo $mypage; ?>&subpage=<?php echo $subpage; ?>&clang=<?php echo $clang; ?>'",
                      categories : "location.href='index.php?page=<?php echo $mypage; ?>&subpage=<?php echo $subpage; ?>&clang=<?php echo $clang; ?>&group_id='+id",
                      folder 	 : "location.href='index.php?page=<?php echo $mypage; ?>&subpage=<?php echo $subpage; ?>&clang=<?php echo $clang; ?>&group_id='+id"
                   },
            types  : {root	 : 'root',
            		  folder : 'categories',
               		  file	 : 'folder'
               	   }
		});
	});

/* ]]> */
</script>