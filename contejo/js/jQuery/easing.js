/**
 * Interface Elements for jQuery
 * Easing formulas
 *
 * http://interface.eyecon.ro
 *
 * Copyright (c) 2006 Stefan Petre
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 */

/**
 * Starting with jQuery 1.1  the fx function accepts easing formulas that can be used with .animation() and most of FX plugins from Interface. The object can be extended to accept new easing formulas
 */
 jQuery.extend({easing:{linear:function(c,e,a,d,b){return c*d+a},swing:function(c,e,a,d,b){return((-Math.cos(c*Math.PI)/2)+0.5)*d+a},easein:function(c,e,a,d,b){return d*(e/=b)*e*e+a},easeout:function(c,e,a,d,b){return -d*((e=e/b-1)*e*e*e-1)+a},easeboth:function(c,e,a,d,b){if((e/=b/2)<1){return d/2*e*e*e*e+a}return -d/2*((e-=2)*e*e*e-2)+a},bounceout:function(c,e,a,d,b){if((e/=b)<(1/2.75)){return d*(7.5625*e*e)+a}else{if(e<(2/2.75)){return d*(7.5625*(e-=(1.5/2.75))*e+0.75)+a}else{if(e<(2.5/2.75)){return d*(7.5625*(e-=(2.25/2.75))*e+0.9375)+a}else{return d*(7.5625*(e-=(2.625/2.75))*e+0.984375)+a}}}},bouncein:function(c,e,a,d,b){if(jQuery.easing.bounceout){return d-jQuery.easing.bounceout(c,b-e,0,d,b)+a}return a+d},bounceboth:function(c,e,a,d,b){if(jQuery.easing.bouncein&&jQuery.easing.bounceout){if(e<b/2){return jQuery.easing.bouncein(c,e*2,0,d,b)*0.5+a}}return jQuery.easing.bounceout(c,e*2-b,0,d,b)*0.5+d*0.5+a;return a+d},elasticin:function(f,h,c,g,e){var b,d;if(h==0){return c}if((h/=e)==1){return c+g}b=g*0.3;f=e*0.3;if(b<Math.abs(g)){b=g;d=f/4}else{d=f/(2*Math.PI)*Math.asin(g/b)}return -(b*Math.pow(2,10*(h-=1))*Math.sin((h*e-d)*(2*Math.PI)/f))+c},elasticout:function(f,h,c,g,e){var b,d;if(h==0){return c}if((h/=e/2)==2){return c+g}b=g*0.3;f=e*0.3;if(b<Math.abs(g)){b=g;d=f/4}else{d=f/(2*Math.PI)*Math.asin(g/b)}return b*Math.pow(2,-10*h)*Math.sin((h*e-d)*(2*Math.PI)/f)+g+c},elasticboth:function(f,h,c,g,e){var b,d;if(h==0){return c}if((h/=e/2)==2){return c+g}b=g*0.3;f=e*0.3;if(b<Math.abs(g)){b=g;d=f/4}else{d=f/(2*Math.PI)*Math.asin(g/b)}if(h<1){return -0.5*(b*Math.pow(2,10*(h-=1))*Math.sin((h*e-d)*(2*Math.PI)/f))+c}return b*Math.pow(2,-10*(h-=1))*Math.sin((h*e-d)*(2*Math.PI)/f)*0.5+g+c}}});