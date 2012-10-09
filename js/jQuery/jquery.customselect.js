(function($){

    $.extend({
        selectpath: new function() {

            this.defaults = {
                imgdir	: 'img/linkmap_icons/',
                action	: false,
                lines	: { vline	: 'vline.gif',
                            hline	: 'hline.gif',
                            cline	: 'cline.gif'
                          },
                types	: {root		      : 'root',
                           folder	      : 'folder', 
                           locked         : 'locked',
                           folder_locked  : 'locked',
                           file_locked 	  : 'locked',
                           folder_admin   : 'folder',      
                           folder_by_user : 'folder', 
                           file	          : 'file',
                           file_admin     : 'file',  
                           file_by_user   : 'file'                        
                          },
			    devider : '<span></span>',
                selected : 0
            };

            function execute($this, el){
				var $this = $($this);
                if ($this.is('.cs_disabled_class') ||
                    $this.attr('class') == 'locked') return false;

				if(!el.conf.action) return false;
				var id = $this.attr('href').replace(/.*#action/gi,'');
				var type = $this.attr('class');
                eval(el.conf.action[type]);
            }

            function setselected(el){

				var all   = $('#'+el.conf.options_id).children();
                var $this = all.filter('.pos_'+el.conf.selected);
                var span  = $this.find('span');

                if (span.is('.cs_disabled_class') ||
                    span.attr('class') == 'locked') return false;
                
				var id    = $this.attr('id');
                var path  = getpath($this, el);
                var value = id.substr(el.conf.options.attr('id').length+1);
               
                all.removeClass('cs_selected_class');

				if (el.conf.selected == null) return false;
				
				$this.addClass('cs_selected_class');
                parse_back(el);

                $(el.conf.input).val(value);
                $(el.conf.select).html(path);
                $(el.conf.holder).hide('fast');
                $(el.conf.select).find('a:last').css('font-weight', 'bold');
            }

            function toggleholder(el){

                $this = el.conf.holder

				var display = $this.css('display') == 'block' ? true : false;

                if (display){
                    $this.hide('fast');
                    $('body').unbind('mouseup');
                }
                else {
                    $this.show('fast');
					$this.mouseup(function(){ return false; });

                    $('body').mouseup(function(){
                        $this.hide('fast');
                        $('body').unbind('mouseup');
                    });
                }
                parse_back(el);
            }

            function togglefiles($this, el){

				var $this = $($this);
				var next = $this.next();

				if (next.length < 1) next = $this;

                var level  = getlevel($this);
                var next_level = getlevel(next);

                if (level >= next_level){
					var num = getpos($this);
                    var next_num = getpos(next);

                    if((num+1) != next_num){
						append_elm(num,el);
						next_level = level+1;
                    }
                }

                var all    = $this.nextAll();
				var root   = el.conf.types.root;
				var isroot = $this.is('.' + root);
				var show   = $this.next().is(':hidden') ? true : false;

                all.each(function(i){

					var $this = $(this);

                    if (getlevel($this) < next_level) {
                        return false;
                    }
					if (show) {
						if (getlevel($this) > next_level) return true;
						$this.show('normal');
					}
					else {
						$this.hide('normal');
					}
                });
                return true;
            }

            function append_elm(num,el){

				var html = '';
				var selector = '';
				var loop_pos = '';
				var cur = el.conf.opt_items[num];

				for(var i=0; i<el.conf.opt_items.length; i++){

					if(num != 0 && i <= num) continue;
					var opt = el.conf.opt_items[i];
					var pos = '.pos_'+opt.num;

					if (opt.level != 0){
						if (opt.level > cur.level+1) continue;
						if (opt.level <= cur.level) break;
					}

					html 	 += get_append_html(opt, el);
					selector += (selector != '') ? ',' : '';
					selector += pos;
					if(i == 0 || opt.is_cur) {
						loop_pos = pos;
					}
				}
            	if(num < 1){
            		el.conf.options.append(html);
            	}
            	else {
            		el.conf.options.find('.pos_'+num).after(html);
            	}
            	bindevents($('#'+el.conf.options_id+' .cs_selectitems').filter(selector), el);

				if(loop_pos != '' && el.conf.opt_items.length > 1){
					togglefiles($('#'+el.conf.options_id+' '+loop_pos), el);
				}
            }

            function get_append_html(opt, el){

                var vline  	  = el.conf.imgdir+el.conf.lines.vline;
                var cline  	  = el.conf.imgdir+el.conf.lines.cline;
                var hline  	  = el.conf.imgdir+el.conf.lines.hline;

				var disabled  = opt.is_dis ? 'cs_disabled_class ' : '';
				var text	  = opt.text.replace(/.*\|→|.*\|&rarr;/,'');
				var imgs	  = opt.text.substr(0,opt.text.search(/\s*&rarr;|\s*→/)+1)
	                                    .replace(/\s*\|/g,'<img src="'+vline+'" class="vline" />')
	                                    .replace(/\s*&rarr;|\s*→/,'<img src="'+hline+'" class="hline"  />');

            	var string = '<div id="'+opt.new_id+'" '+
	            			   'class="cs_selectitems '+
	            			   opt.type+' '+
	            			   'level_'+opt.level+' '+
	            			   'pos_'+opt.num+'" '+ 
	            			   'style="display:none">'+
                               "\r\n"+
	            			   imgs+
                               "\r\n"+
	            			   '<span class="'+
	            			   disabled+
	            			   opt.type+'\">'+
                               "\r\n"+
	            			   text+
                               "\r\n"+
	            			   '</span></div>'+
                               "\r\n";
            	return string;
            }

            function bindevents(opt_items, el){

            	$(opt_items)
            		.unbind('mouseover')
            		.unbind('mouseout')
           			.unbind('click')
           			.unbind('dblclick');

            	$(opt_items)
            		.mouseover(function(){
                        $(this).addClass('cs_hover_class');
                    })
                    .mouseout(function(){
                        $(this).removeClass('cs_hover_class');
                    })
					.click(function(){
						var $this = $(this);

                        if ($this.is('.cs_disabled_class, .locked:not(.folder, .files, .categories, .category)')) return false;

                        if ($this.is('.'+el.conf.types.folder+', .'+el.conf.types.root)){
                            togglefiles($this, el);
                            parse_back(el);
                        }
                        else {
                            el.conf.selected = getpos($this);
                            setselected(el);
                            execute(el.conf.select.find('a:last'), el);
                        }
                    })
					.dblclick(function(){

						var $this = $(this);

                        if ($this.is('.cs_disabled_class, .locked')) return false;

                        el.conf.selected = getpos($(this));
                        setselected(el);
                        execute(el.conf.select.find('a:last'), el);
                    })
					.filter('.'+el.conf.types.root)
					.dblclick(function(){
						setselected(el);
                    	execute(el.conf.select.find('a.'+el.conf.types.root), el);
                    });
            }

            function getpos($this){
				var pos = $($this).attr('class').match(/\bpos_([0-9]+)\b/);
				return pos[1]*1;
			}

            function getlevel($this){
				var level = $($this).attr('class').match(/\blevel_([0-9]+)\b/);
				return level[1]*1;
			}

            function getlevel_init(el, $this, str){

				var $this = $($this);

				if($this.is('.'+ el.conf.types.root)) return 1;
                if($this.text() == '') return 0;
                if(str == '|'){
                    html = $this.html();
                    matches = html.match(/&nbsp;\|&nbsp;|&nbsp;\|&rarr;/g);
                    return (matches) ? matches.length : 0;
                }
                return	$this.find(str).length;
            }

            function gettype($this,el){
				var $this = $($this);
                if($this.text() == '') return false;
                var type = $this.attr('title');
                return	el.conf.types[type];
            }

            function getitem($this){
				var $this = $($this);
                var event = '';
                var text  = $this.find('span').text();
                var css   = $this.find('span').attr('class');
                var id    = $this.attr('id');
					id    = id.substr(id.lastIndexOf('_')+1);
                return '<a href="#action'+id+'" class="'+css+'">'+text+'</a>';
            }

            function getpath($this, el){

				var $this 	= $($this);
                var level   = getlevel($this)-1;
                var folder  = el.conf.types.folder;
                var root    = el.conf.types.root;
				var devider = el.conf.devider;
                var all     = $this.prevAll();
				var path    = getitem($this);

                all.each(function(){

					if ($(this).is('.level_' + (level)) || $(this).is('.'+ root)) {
						if (el.conf.path_len != 'short') {
                        	path = getitem($(this))+devider+path;
						}
						showpath(el,$this,level);
                        level--;
                    }
                });
                return path;
            }

			function showpath(el, $this, level){

				var $this = $($this);
				$this.show();
				level++;

				var prevall = $this.prevAll();
				var nextall = $this.nextAll();

                 prevall.each(function(i){
                	var $this = $(this);            	 
                    if (getlevel($this) == level) {
						$(this).show();
					} else if (getlevel($this) < level) {
						return false;
					}
                });

				nextall.each(function(i){
               	 	var $this = $(this);    
                    if (getlevel($this) == level) {
						$(this).show();
					} else if (getlevel($this) < level) {
						return false;
					}
                });
            }

            function parse_back(el){

    			var row, pre, row_v, pre_v, row_l, pre_l, dif;
                var sel = $(el.conf.options).find('.cs_selectitems:visible')
                var len = sel.length-1;

                for(i=len;i>0;i--){

                    row = sel.eq(i);
                    pre = sel.eq(i+1);
                    prr = sel.eq(i+2);

                    row_v = row.find('img');
                    pre_v = pre.find('img');
					prr_v = prr.find('img');

                    row_l = row_v.length != undefined ? row_v.length : 0;
                    pre_l = pre_v.length != undefined ? pre_v.length : 0;

                    row.each(function(i){

                        dif = row_l - pre_l;

                        if (dif > 0) {
                            row.find('img.vline:last')
                               .attr('src', el.conf.imgdir + el.conf.lines.cline);

                            if (dif > 1) {
                                if (pre_l > 0) {
                                    row.find('img.vline:gt(' + (pre_l - 2) + '):not(:last)').css('visibility', 'hidden');
                                }
                                else {
                                    row.find('img.vline:not(:last)').css('visibility', 'hidden');
                                }
                            }
                        }
                        else {
                            pre.find('img.vline').each(function(ii){

                                if ($(this).css('visibility') == 'hidden'){

                                    if (dif >= 0 ||
                                    	pre_v.eq((ii+1)).css('visibility') == 'hidden'){
                                        row_v.eq(ii).css('visibility', 'hidden');
                                    }
                                    else{
                                        row_v.eq(ii).attr('src', el.conf.imgdir + el.conf.lines.cline);
                                    }
                                }
                                else if ($(this).is('img[src$="'+el.conf.lines.cline+'"]') && dif==0) {
									row_v.eq(ii).attr('src', el.conf.imgdir + el.conf.lines.vline);
                                }
                                else if (prr_v.eq(ii).css('visibility') == 'hidden' &&
                                		 pre_v.eq((ii+1)).is('img.vline'))
                                {
                                     pre_v.eq(ii).css('visibility', 'hidden');
                                }
                            });
                        }
                    });
                }
                return false;
            }

            /* public methods */
            this.construct = function(settings, i) {

                return this.each(function(i) {

					var el;
					
					// store common expression for speed
                    el = $(this);

					this.conf 			= {};
					el.conf 			= $.extend(this.conf, $.selectpath.defaults, settings);
                    el.conf.style 	   	= el.attr('style');
                    el.conf.css 	   	= el.attr('class') != undefined ? el.attr('class').replace(/custom_select|hide_me/g,'') : '';
					el.conf.disabled   	= el.attr('disabled');
					el.conf.width		= el.width();
					el.conf.num			= $('.cs_holder_class').length;
                    el.conf.options_id 	= 'cs_options'+el.conf.num;
                    el.conf.select_id  	= 'cs_select'+el.conf.num;
                    el.conf.holder_id  	= 'cs_holder'+el.conf.num;
                    el.conf.cs_input_id	= 'cs_input'+el.conf.num;

                    el.after('<div id="'+el.conf.options_id+'"> </div>')
                       .before('<input id="'+el.conf.cs_input_id+'" type="hidden" value ="" name="'+this.name+'" />')
                       .after('<div id="'+el.conf.holder_id+'" class="cs_holder_class"> </div>')
                       .after('<div id="'+el.conf.select_id+'" class="cs_select_class">'+this.title+'</div>');

					el.removeAttr('name').hide();
                    el.conf.options = $('#'+el.conf.options_id);
                    el.conf.select  = $('#'+el.conf.select_id);
                    el.conf.holder  = $('#'+el.conf.holder_id);
                    el.conf.input   = $('#'+el.conf.cs_input_id);

					el.conf.opt_items = new Array();

                    el
                    .attr('name','cs_hidden'+i)
                    .addClass('xcs_hidden');

                    if(el.conf.width > 300){
                        el.conf.select.width(el.conf.width+7);
                    }
					else {
                        el.conf.select.width(300);
					}

                    el.conf.select.attr('style', el.conf.style);
                    el.conf.select.addClass(el.conf.css);
					el.conf.holder.css('width', el.conf.select.css('width'));

					var opt_items = el.find('option');

					if(opt_items.length < 1) return;

                    opt_items.each(function(ii){

                        var opt		= $(this);
						var new_id  = el.conf.options_id+'_'+opt.attr('value');

                        el.conf.opt_items[ii] = {
                        	num  		: ii,
                        	new_id		: new_id,
                        	level  		: getlevel_init(el,opt, '|'),
                        	is_cur		: opt.is('.current'),
                        	is_sel		: opt.is(':selected'),
                        	is_dis		: opt.is(':disabled'),
                        	type		: gettype(opt,el),
	                        text		: opt.text()
	                    };

                        if (opt.is(':selected')) {
                             el.conf.selected = ii;
                        }
                    });

                    append_elm(0, el);
                    setselected(el);

                    el.conf.select
                    .click(function(){
                        toggleholder(el);
                    })
					.mouseover(function(){
                        $(this).find('a').each(function(){
                            $(this).bind('click', function(){
                                execute(this, el);
                                $(this).unbind('click');
                                return false;
                            });
                        });
                    })
                    .find('a')
                    .click(function(){
                        $(el.conf.holder).hide();
                    });

                    el.conf.holder.append(el.conf.options[0]);

                    cjo.showScripttime('SELECTPATH');
                });
            }
        }
    });

    // extend plugin scope
    $.fn.extend({
        selectpath: $.selectpath.construct
    });

})(jQuery);