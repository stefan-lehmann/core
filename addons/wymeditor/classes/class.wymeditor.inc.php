<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  wymeditor
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


cjoAddon::setProperty('settings|COUNTER', 0, $addon);

class WYMeditor {

    public $wymeditorSelector		= ".wymeditor";
    public $updateSelector			= ".wymupdate";
    public $add_updateSelector		= array(".cjo_form_button", ".cjo_imgslice_buttons" );
    public $lang					= 'de';
    public $updateEvent			    = "mousedown";    
    public $toolsItems				= array(

                                        array("name" => "Separator"),
                                        array("name" => "Bold", "title" => "Strong", "css" => "wym_tools_strong"),
                                        array("name" => "Italic", "title" => "Emphasis", "css" => "wym_tools_emphasis"),
                                        array("name" => "Underline", "title" => "Underline", "css" => "wym_tools_underline"),
                                        array("name" => "Superscript", "title" => "Superscript","css" => "wym_tools_superscript"),
                                        array("name" => "Subscript", "title" => "Subscript","css" => "wym_tools_subscript"),
                                        array("name" => "RemoveFormat", "title" => "RemoveFormat","css" => "wym_tools_removeformat"),
                                        array("name" => "Separator"),
                                        array("name" => "Left", "title" => "Left","css" => "wym_tools_align_left"),
                                        array("name" => "Center", "title" => "Center","css" => "wym_tools_align_center"),
                                        array("name" => "Right", "title" => "Right","css" => "wym_tools_align_right"),
                                        array("name" => "Justify", "title" => "Justify","css" => "wym_tools_align_justify"),
                                        array("name" => "Separator"),
                                        array("name" => "InsertOrderedList", "title" => "Ordered_List","css" => "wym_tools_ordered_list"),
                                        array("name" => "InsertUnorderedList", "title" => "Unordered_List","css" => "wym_tools_unordered_list"),
                                        array("name" => "Indent", "title" => "Indent", "css" => "wym_tools_indent"),
                                        array("name" => "Outdent", "title" => "Outdent", "css" => "wym_tools_outdent"),
                                        array("name" => "Separator"),
                                        array("name" => "CreateLink", "title" => "Link", "css" => "wym_tools_link"),
                                        array("name" => "Unlink", "title" => "Unlink", "css" => "wym_tools_unlink"),
                                        array("name" => "Separator"),
                                        array("name" => "InsertTable", "title" => "Table", "css" => "wym_tools_table"),
                                        array("name" => "Paste", "title" => "Paste_From_Word", "css" => "wym_tools_paste"),
                                        array("name" => "Separator"),
                                        array("name" => "ToggleHtml", "title" => "HTML", "css" => "wym_tools_html")
                                        );



    public $blindtext				= true;
    public $blindtext_button		= array("name" => "Blindtext", "title" => "Blindtext", "css" => "wym_tools_blind_text");
    public $blindtext_text			= "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod oluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum.";

    public $preferred_cjolinks		= array();
    public $width					= '100%';
    public $height					= '200';
    public $height_save_field		= "CJO_EXT_VALUE[wym]";
    public $content;
    public $id;
    
    public static $WYMPath			= "./js/wymeditor/wymeditor/";
    public static $jQueryPath       = "./js/jQuery/jquery-latest.min.js";
    public static $tidy				= false;
    
    public function __construct() {
        return true;
    }
    
    public function render($print = true) {

        $this->lang = cjoProp::get('LANG');

            $output = '';
            $output .= "\r\n".'<script type="text/javascript">'."\r\n";
            $output .= "\r\n".'/* <![CDATA[ */'."\r\n";
            $output .= "\r\n".'	$(function() { ';

        if (is_array($this->add_updateSelector)){
            $output .= "\r\n".'		$("'.implode(', ',$this->add_updateSelector).'").addClass("'.substr($this->updateSelector,1).'");';
        }
            $output .= "\r\n".'		$("'.$this->wymeditorSelector.'_'.$this->id.'").wymeditor({';
            $output .= "\r\n".'			jQueryPath: "'.self::$jQueryPath.'",';
            $output .= "\r\n".'			standardPath: "",';
            $output .= "\r\n".'			skin: \'contejo\',                //activate silver skin",';
            $output .= "\r\n".'			iframeHeight: "'.$this->height.'",';
            $output .= "\r\n".'			lang: "'.$this->lang.'",';
            $output .= "\r\n".'			updateSelector: "'.$this->updateSelector.'",';
            $output .= "\r\n".'			updateEvent: "'.$this->updateEvent.'",'."\r\n";

            $output .= "\r\n".'			boxHtml:   "<div id=\'boxHtml'.$this->id.'\' class=\'wym_box\'>"';
            $output .= "\r\n".'					 + "<div class=\'wym_area_top\'>"';
            $output .= "\r\n".'					 + WYMeditor.TOOLS';
            $output .= "\r\n".'					 + "</div>"';
            $output .= "\r\n".'					 + "<div class=\'wym_area_left\'></div>"';
            $output .= "\r\n".'					 + "<div class=\'wym_area_right\'>"';
            $output .= "\r\n".'					 + WYMeditor.CONTAINERS';
            $output .= "\r\n".'					 + WYMeditor.CLASSES';
            $output .= "\r\n".'					 + "</div>"';
            $output .= "\r\n".'					 + "<div class=\'wym_area_main\'>"';
            $output .= "\r\n".'					 + WYMeditor.HTML';
            $output .= "\r\n".'					 + WYMeditor.IFRAME';
            $output .= "\r\n".'					 + WYMeditor.STATUS';
            $output .= "\r\n".'					 + "</div>"';
            $output .= "\r\n".'					 + "<div class=\'wym_area_bottom\'>"';
            $output .= "\r\n".'					 + "<a class=\'wym_wymeditor_link\' "';
            $output .= "\r\n".'					 + "href=\'http://www.wymeditor.org/\'>WYMeditor</a>"';
            $output .= "\r\n".'					 + "</div>"';
            $output .= "\r\n".'					 + "</div>",'."\r\n";

            $output .= "\r\n".'			dialogHtml:      "<!DOCTYPE html PUBLIC \'-//W3C//DTD XHTML 1.0 Strict//EN\'"';
            $output .= "\r\n".'					 + " \'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\'>"';
            $output .= "\r\n".'					 + "<html dir=\'"+WYMeditor.DIRECTION+"\'>"';
            $output .= "\r\n".'					 + "<h"+"ead>"';
            $output .= "\r\n".'					 + "<l"+"ink rel=\'stylesheet\' type=\'text/css\' media=\'screen\' href=\''.cjoUrl::backend('css/contejo.css').'\' />"';
            $output .= "\r\n".'					 + "<l"+"ink rel=\'stylesheet\' type=\'text/css\' media=\'screen\' href=\'"+WYMeditor.CSS_PATH+"\' />"';
            $output .= "\r\n".'					 + "<title>"+ WYMeditor.DIALOG_TITLE+"</title>"';
            $output .= "\r\n".'					 + "<s"+"cript type=\'text/javas"+"cript\' src=\'"+WYMeditor.JQUERY_PATH+"\'></s"+"cript>"';
            $output .= "\r\n".'					 + "<s"+"cript type=\'text/javas"+"cript\' src=\'"+WYMeditor.WYM_PATH+"\'></s"+"cript>"';
            $output .= "\r\n".'					 + "</h"+"ead>"';
            $output .= "\r\n".'					 + WYMeditor.DIALOG_BODY';
            $output .= "\r\n".'					 + "</html>",';



        if (is_array($this->toolsItems)){

            if ($this->blindtext && is_array($this->blindtext_button))
                array_push($this->toolsItems, $this->blindtext_button);

            $output .= "\r\n".'			toolsItems: [';
          $temp_curr = '';
          $toolsItems = array();
          foreach ($this->toolsItems as $key => $toolsItem){
            $temp_prev = $temp_curr;
            $title = isset($toolsItem['title']) ? $toolsItem['title'] : '';
            $css   = isset($toolsItem['css']) ? $toolsItem['css'] : '';            
            $temp_curr = "\r\n".'				{"name": "'.$toolsItem['name'].'", "title": "'.$title.'", "css": "'.$css.'"}';
            if ($temp_prev == $temp_curr) continue;
            $toolsItems[] = "\r\n".'				{"name": "'.$toolsItem['name'].'", "title": "'.$title.'", "css": "'.$css.'"}';
          }
            $output .= implode(',',$toolsItems);

            $output .= "\r\n".'						],';
        }
            $output .= "\r\n".'			statusHtml:	"<div class=\'wym_status wym_section\'>{Status}</div>",';

            $output .= "\r\n".'			postInit: function(wym) {';
            $output .= "\r\n".'				wym.status(\'&nbsp;\');';
            $output .= "\r\n".'				wym.hovertools();';
            $output .= "\r\n".'				wym.resizable({handles: "s", maxHeight: 600});';

            $output .= "\r\n".'          	$(wym._box)';
            $output .= "\r\n".'          		.find(\'li[class^=wym_tools_align] a\').click(function() {';
            $output .= "\r\n".'						var sClass = $(this).parent().attr(\'class\').replace(/wym_tools_align_/,\'\');';
            $output .= "\r\n".'						var sName = \'p, td, th\';';
            $output .= "\r\n".'						var container = (wym._selected_image ? wym._selected_image : $(wym.selected()));';
    		$output .= "\r\n".'						';
            $output .= "\r\n".'						container = $(container).parentsOrSelf(sName);';
    		$output .= "\r\n".'						';
            $output .= "\r\n".'						$(container).removeClass(\'left\')';
            $output .= "\r\n".'									.removeClass(\'center\')';
            $output .= "\r\n".'									.removeClass(\'right\')';
            $output .= "\r\n".'									.removeClass(\'justify\');';
    		$output .= "\r\n".'						if(sClass != \'left\') $(container).addClass(sClass);';
    		$output .= "\r\n".'						';
    		$output .= "\r\n".'						if(!$(container).attr(WYMeditor.CLASS)) $(container).removeAttr(wym._class);';
            $output .= "\r\n".'                		return(false);';
            $output .= "\r\n".'            	});';

        if (self::$tidy) {
            $output .= "\r\n".'				var wymtidy = wym.tidy({';
            $output .= "\r\n".'				sUrl:          "'.self::$WYMPath.'plugins/tidy/tidy.php",';
            $output .= "\r\n".'				sButtonHtml:   "<li class=\'wym_tools_tidy\'>";
            $output .= "\r\n".'				+ "<a name=\'CleanUp\' href=\'#\'";
            $output .= "\r\n".'				+ " style=\'background-image:";
            $output .= "\r\n".'				+ " url('.self::$WYMPath.'plugins/tidy/wand.png)\'>";
            $output .= "\r\n".'				+ "Clean up HTML";
            $output .= "\r\n".'				+ "<\/a><\/li>";
            $output .= "\r\n".'				});';
            $output .= "\r\n".'				wymtidy.init();';
        }

        if ($this->blindtext){

            $output .= "\r\n".'          		$(wym._box)';
            $output .= "\r\n".'          			.find(\'li.wym_tools_blind_text a\').click(function() {';
            $output .= "\r\n".'               		wym.paste(\''.str_replace(array("\n","\r"), " ", $this->blindtext_text).'\');';
            $output .= "\r\n".'                		return(false);';
            $output .= "\r\n".'            		});';
        }
            $output .= "\r\n".'			},';

            $output .= "\r\n".'			postInitDialog: function(wym,wdw) {';
            $output .= "\r\n".'				var body = wdw.document.body;';
            $output .= "\r\n".'				var wym_link_input = "'.$this->getLinkInput().'";';
            $output .= "\r\n".'				var wym_href = $(wym.selected()).attr(WYMeditor.HREF);';

            $output .= "\r\n".'				var jbody = $(body);';
            $output .= "\r\n".'				jbody';
            $output .= "\r\n".'					.filter(\'.wym_dialog_link\').find(\'.row\').eq(0)';
            $output .= "\r\n".'					.before(wym.replaceStrings(wym_link_input));';

            $output .= "\r\n".'				var curr_id = 0;';
            $output .= "\r\n".'				if (wym_href) var curr_id = wym_href.replace(/(contejo:\/\/)(\d+)(.*)/, \'$2\');';

            $output .= "\r\n".'				jbody';
            $output .= "\r\n".'					.find(\'#wym_select_link option[value=\'+curr_id+\']\')';
            $output .= "\r\n".'					.attr(\'selected\',\'selected\');';

            $output .= "\r\n".'				jbody';
            $output .= "\r\n".'					.find(\'.wym_choose\')';
            $output .= "\r\n".'					.attr(\'disabled\',\'disabled\')';
            $output .= "\r\n".'					.click(function() {';
            $output .= "\r\n".'						var link = jbody.find(\'#wym_select_link option:selected\').val();';
            $output .= "\r\n".'						var clang = jbody.find(\'#wym_select_clang option:selected\').val();';
            $output .= "\r\n".'						var title = jbody.find(\'#wym_select_link option:selected\').text();';
            $output .= "\r\n".'						var language = jbody.find(\'#wym_select_clang option:selected\').text();';

            $output .= "\r\n".'						if (clang != "'.cjoProp::getClang().'") {';
            $output .= "\r\n".'						    link += "."+clang; ';
            $output .= "\r\n".'						    title += " ("+language+")"; ';
            $output .= "\r\n".'						}';
            $output .= "\r\n".'						jbody';
            $output .= "\r\n".'							.find(\'.wym_href\')';
            $output .= "\r\n".'							.val(\'contejo://\'+link);';
            $output .= "\r\n".'						jbody';
            $output .= "\r\n".'							.find(\'.wym_title\')';
            $output .= "\r\n".'							.val(title.replace(/(.*?→)/g,\'\'));';
            $output .= "\r\n".'						return false;';
            $output .= "\r\n".'					});';

            $output .= "\r\n".'					jbody';
            $output .= "\r\n".'						.filter(\'.wym_dialog_link\')';
        	$output .= "\r\n".'						.find(\'#wym_select_link, #wym_select_clang\')';
        	$output .= "\r\n".'						.change(function(){ jbody.find(\'.wym_choose\').removeAttr(\'disabled\');})';
        	$output .= "\r\n".'						.children()';
        	$output .= "\r\n".'						.each(function(){';
            $output .= "\r\n".'							if (wym_href != undefined && $(this).val() == wym_href.replace(/\\\D/g,\'\')){';
            $output .= "\r\n".'								jbody.find(\'.wym_choose\').removeAttr(\'disabled\');';
        	$output .= "\r\n".'								$(this).attr(\'selected\',\'selected\');';
        	$output .= "\r\n".'							}';
        	$output .= "\r\n".'						});';
            $output .= "\r\n".'			}';
            $output .= "\r\n".'		});';
          	$output .= "\r\n".'		$(".wym_box").show();';
          	$output .= "\r\n".'		cjo.showScripttime(\'WYMeditor\');';
            $output .= "\r\n".'	});';
            $output .= "\r\n".'/* ]]> */';
            $output .= "\r\n".'</script>'."\r\n";
        
            $output .= '<textarea rows="2" cols="10" name="WYMEDITOR['.$this->id.']"
        					  	  class="'.substr($this->wymeditorSelector,1).'_'.$this->id.'"
        					  	  style="width:100%;display:none!important">'.$this->content.'</textarea>';
        
        cjoAddon::setProperty('settings|COUNTER', cjoAddon::getProperty('settings|COUNTER', $addon)+1, $addon);
        
        cjoExtension::registerExtension('OUTPUT_FILTER', 'WYMeditor::insertScripts');

        if (!$print) { return $output; } else {  print $output; }
    }

    public function show($print=false) {
        return $this->render($print);
    }

    public static function insertScripts($params) {
    	
        $output  = "\r\n".'<script type="text/javascript" src="'.self::$WYMPath.'jquery.wymeditor.js"></script>';
        $output .= "\r\n".'<script type="text/javascript" src="'.self::$WYMPath.'plugins/hovertools/jquery.wymeditor.hovertools.js"></script>';
        $output .= "\r\n".'<script type="text/javascript" src="'.self::$WYMPath.'plugins/resizable/jquery.wymeditor.resizable.js"></script>';
        $output .= (self::$tidy) ? "\r\n".'<script type="text/javascript" src="'.self::$WYMPath.'plugins/tidy/jquery.wymeditor.tidy.js"></script>' : '';
        $output .= "\r\n";
        
        return preg_replace('/<\!-- CJO_INCLUDE_JS -->/',$output.'\0',$params['subject'],1);
    }
    
    private function getLinkInput() {

    	if (!is_object(cjoSelectArticle::$sel_article)){

    		new cjoSelectArticle;

    		if ($obj_articles = OOArticle::getRootArticles()) {
    			$article_ids = array();
    		    cjoSelectArticle::$sel_article->addOption(cjoI18N::translate('root'), 0, 0, '', 'root');
    		    foreach($obj_articles as $obj_article) {
    		        cjoLinkButtonField :: addCatOptions(cjoSelectArticle::$sel_article, $obj_article, $article_ids, "", "&nbsp;");
    		    }
    		}
    	}
    	else {
    		cjoSelectArticle::$sel_article->resetDisabled();
    		cjoSelectArticle::$sel_article->resetSelected();
    		cjoSelectArticle::$sel_article->resetSelectedPath();
    	}

    	cjoSelectArticle::$sel_article->setId('wym_select_link');
    	cjoSelectArticle::$sel_article->setName('wym_select_link');
    	cjoSelectArticle::$sel_article->setLabel(cjoI18N::translate('label_int_link'));
    	cjoSelectArticle::$sel_article->setStyle('width: 230px;');
    	cjoSelectArticle::$sel_article->setSize(1);
    	cjoSelectArticle::$sel_article->setDisabled(0);

    	$clang_sel = new cjoSelect();
    	$clang_sel->setId('wym_select_clang');
    	$clang_sel->setName('wym_select_clang');
        $clang_sel->setSize(1);

    	foreach(cjoProp::getClangs() as $clang_id) {
    	    $clang_sel->addOption(cjoProp::getClangName($clang_id),cjoProp::getClang($clang_id));
    	}
    	$clang_sel->setSelected(cjoProp::getClang());

        $output  = '<div class=\'row row_select_link\'>"';
        $output .= "\r\n".'				+ "'.str_replace(array('"',"\n","\r"),array("'"," "," "),cjoSelectArticle::$sel_article->_get()).'" ';
        $output .= "\r\n".'				+ "&nbsp; '.str_replace(array('"',"\n","\r"),array("'"," "," "),$clang_sel->get()).'"';
        $output .= "\r\n".'				+ " <input class=\'wym_choose\' type=\'button\'"';
        $output .= "\r\n".'				+ " value=\'{Choose}\' />"';
        $output .= "\r\n".'				+ "<\/div>';

        return $output;
    }
}