/*
	--------------------------------------------------------------------------
	Version: 1.01
	Release date: 13/05/2006
	Last update: 13/07/2006

	(c) 2006 SpamSpan (www.spamspan.com)

	This program is distributed under the terms of the GNU General Public
	Licence version 2, available at http://www.gnu.org/licenses/gpl.txt
	--------------------------------------------------------------------------
*/

var spamSpanMainClass="s";var spamSpanUserClass="u";var spamSpanDomainClass="d";var spamSpanAnchorTextClass="t";function init_spamSpan(){var c=getElementsByClass(spamSpanMainClass,document,"span");for(var f=0;f<c.length;f++){var b=getSpanValue(spamSpanUserClass,c[f]);var h=getSpanValue(spamSpanDomainClass,c[f]);var g=getSpanValue(spamSpanAnchorTextClass,c[f]);var d=cleanSpan(b)+String.fromCharCode(32*2)+cleanSpan(h);var a=document.createTextNode(g?g:d);var e=document.createElement("a");e.className=spamSpanMainClass;e.setAttribute("href",String.fromCharCode(109,97,105,108,116,111,58)+d);e.setAttribute("class","mail");e.appendChild(a);c[f].parentNode.replaceChild(e,c[f])}try{pngfix.fix(".s")}catch(err){}}function getElementsByClass(e,h,k){var g=new Array();if(h==null){node=document}if(k==null){k="*"}var c=h.getElementsByTagName(k);var a=c.length;var f=new RegExp("(^|s)"+e+"(s|$)");for(var d=0,b=0;d<a;d++){if(f.test(c[d].className)){g[b]=c[d];b++}}return g}function getSpanValue(c,b){var a=getElementsByClass(c,b,"span");if(a[0]){return a[0].firstChild.nodeValue}else{return false}}function cleanSpan(a){a=a.replace(/[\[\(\{]?[dD][oO0][tT][\}\)\]]?/g,".");a=a.replace(/\s+/g,"");return a};

$(function () {
	init_spamSpan();
});