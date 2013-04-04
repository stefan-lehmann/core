/**
 * Flash (http://jquery.lukelutman.com/plugins/flash)
 * A jQuery plugin for embedding Flash movies.
 *
 * Version 1.0
 * November 9th, 2006
 *
 * Copyright (c) 2006 Luke Lutman (http://www.lukelutman.com)
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Inspired by:
 * SWFObject (http://blog.deconcept.com/swfobject/)
 * UFO (http://www.bobbyvandersluis.com/ufo/)
 * sIFR (http://www.mikeindustries.com/sifr/)
 *
 * IMPORTANT:
 * The packed version of jQuery breaks ActiveX control
 * activation in Internet Explorer. Use JSMin to minifiy
 * jQuery (see: http://jquery.lukelutman.com/plugins/flash#activex).
 *
 **/
;(function(){var b;b=jQuery.fn.flash=function(g,f,d,i){var h=d||b.replace;f=b.copy(b.pluginOptions,f);if(!b.hasFlash(f.version)){if(f.expressInstall&&b.hasFlash(6,0,65)){var e={flashvars:{MMredirectURL:location,MMplayerType:"PlugIn",MMdoctitle:jQuery("title").text()}}}else{if(f.update){h=i||b.update}else{return this}}}g=b.copy(b.htmlOptions,e,g);return this.each(function(){h.call(this,b.copy(g))})};b.copy=function(){var f={},e={};for(var g=0;g<arguments.length;g++){var d=arguments[g];if(d==undefined){continue}jQuery.extend(f,d);if(d.flashvars==undefined){continue}jQuery.extend(e,d.flashvars)}f.flashvars=e;return f};b.hasFlash=function(){if(/hasFlash\=true/.test(location)){return true}if(/hasFlash\=false/.test(location)){return false}var e=b.hasFlash.playerVersion().match(/\d+/g);var f=String([arguments[0],arguments[1],arguments[2]]).match(/\d+/g)||String(b.pluginOptions.version).match(/\d+/g);for(var d=0;d<3;d++){e[d]=parseInt(e[d]||0);f[d]=parseInt(f[d]||0);if(e[d]<f[d]){return false}if(e[d]>f[d]){return true}}return true};b.hasFlash.playerVersion=function(){try{try{var d=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");try{d.AllowScriptAccess="always"}catch(f){return"6,0,0"}}catch(f){}return new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version").replace(/\D+/g,",").match(/^,?(.+),?$/)[1]}catch(f){try{if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){return(navigator.plugins["Shockwave Flash 2.0"]||navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g,",").match(/^,?(.+),?$/)[1]}}catch(f){}}return"0,0,0"};b.htmlOptions={height:240,flashvars:{},pluginspage:"http://www.adobe.com/go/getflashplayer",src:"#",type:"application/x-shockwave-flash",width:320,wmode:"transparent"};b.pluginOptions={expressInstall:false,update:true,version:"9.0.22.05"};b.replace=function(d){this.innerHTML='';jQuery(this).addClass("flash-replaced").prepend(b.transform(d))};b.update=function(e){var d=String(location).split("?");d.splice(1,0,"?hasFlash=true&");d=d.join("");var f='<div id="flash_error" class="error">Zur Anzeige dieses Mediums wird das Flash-Plugin benoetigt. / This content requires the Flash-Plugin.<br/><br/><a href="http://www.adobe.com/go/getflashplayer">Download Flash-Plugin</a>.</div>';if(this.innerHTML!=""){this.innerHTML='';}jQuery(this).addClass("flash-update").prepend(f)};function a(){var e="";for(var d in this){if(typeof this[d]!="function"){e+=d+'="'+this[d]+'" '}}return e}function c(){var e="";for(var d in this){if(typeof this[d]!="function"){e+=d+"="+escape(this[d])+"&"}}return e.replace(/&$/,"")}b.transform=function(d){d.toString=a;if(d.flashvars){d.flashvars.toString=c}return"<embed "+String(d)+"/>"};if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){}})}})();