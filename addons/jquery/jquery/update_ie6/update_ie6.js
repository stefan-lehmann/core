// (c) 2011 contejo.com - Stefan Lehmann
// inspred by SilvesterWebDesigns.com - Stuart Silvester. Thanks to the SevenUp Project.
;(function($){
    $.extend({ update_ie6: new function() {
                
            this.defaults = {                       
                    headline: "Wissen Sie, dass Ihr Internet Explorer nicht mehr aktuell ist?",
                    text1:    "Um unsere Webseite bestmöglich zu nutzen, empfehlen wir Ihnen Ihren Browser auf eine aktuellere Version zu aktualisieren oder einen anderen Webbrowser zu nutzen. Eine Liste der populärsten Browser finden Sie weiter unten.",
                    text2:    "Klicken Sie auf eines der Symbole um auf die Download-Seite zu gelangen",
                    close:    "Schließen",                  
                    browser:  {firefox:  ['Firefox', 'http://www.mozilla.com/firefox/'],
                                chrome:   ['Chrome', 'http://www.google.com/chrome'],
                                safari:   ['Safari', 'http://www.apple.com/safari/'],
                                opera:    ['Opera', 'http://www.opera.com/download/'],
                                ie:       ['Internet Explorer 10', 'http://www.microsoft.com/windows/Internet-explorer/default.aspx']
                              },                              
                    template: '<div id="upie6" style="display:none">'+
                              '<div class="upie6_headline">{HEADLINE}</div>'+
                              '<div class="upie6_text">{TEXT1}</div>'+
                              '<ul class="up_browser">'+
                              '<li><a href="{FIREFOX_URL}" class="upie6_firefox" targe="_blank" title="{FIREFOX}">{FIREFOX}</a></li>'+
                              '<li><a href="{CHROME_URL}" class="upie6_chrome" targe="_blank" title="{CHROME}">{CHROME}</a></li>'+
                              '<li><a href="{SAFARI_URL}" class="upie6_safari" targe="_blank" title="{SAFARI}">{SAFARI}</a></li>'+
                              '<li><a href="{OPERA_URL}" class="upie6_opera" targe="_blank" title="{OPERA}">{OPERA}</a></li>'+
                              '<li><a href="{IE_URL}" class="upie6_ie" targe="_blank" title="{IE}">{IE}</a></li>'+
                              '</ul>'+
                              '<div class="upie6_text">{TEXT2}</div>'+
                              '<a href="#" id="upie6_close" title="{CLOSE}">{CLOSE}</a>'+
                              '</div>'
            };
        
            function showDialog() {
                
                if (!$.browser.msie || $.browser.version > 7) {
                    return false;
                }               
                if (document.cookie.length > 0) {
                    var i = document.cookie.indexOf("upie6=");
                    return (i != -1) ? false : true;
                }
                return true;
            };
        
            /* public methods */
            this.construct = function(settings, i) {
    
                return this.each(function(i) {
    
                    var el;
                    // store common expression for speed
                    el = $(this);
                    this.conf           = {};
                    el.conf             = $.extend(this.conf, $.update_ie6.defaults, settings);
                    
                    if (showDialog()) {                     
                        var html = el.conf.template;
                        html = html.replace(/{HEADLINE}/g, el.conf.headline);
                        html = html.replace(/{TEXT1}/g, el.conf.text1);
                        html = html.replace(/{TEXT2}/g, el.conf.text2);                     
                        html = html.replace(/{CLOSE}/g, el.conf.close); 
                        html = html.replace(/{FIREFOX}/g, el.conf.browser.firefox[0]);
                        html = html.replace(/{FIREFOX_URL}/g, el.conf.browser.firefox[1]);  
                        html = html.replace(/{CHROME}/g, el.conf.browser.chrome[0]);
                        html = html.replace(/{CHROME_URL}/g, el.conf.browser.chrome[1]);                            
                        html = html.replace(/{SAFARI}/g, el.conf.browser.safari[0]);
                        html = html.replace(/{SAFARI_URL}/g, el.conf.browser.safari[1]);                        
                        html = html.replace(/{OPERA}/g, el.conf.browser.opera[0]);
                        html = html.replace(/{OPERA_URL}/g, el.conf.browser.opera[1]);                          
                        html = html.replace(/{IE}/g, el.conf.browser.ie[0]);
                        html = html.replace(/{IE_URL}/g, el.conf.browser.ie[1]);                            
                        
                        $(html).prependTo("body").slideDown("fast");
                        
                        $("a#upie6_close")
                            .click(function() {
                                $("#upie6")
                                    .slideUp("fast", function() {
                                        $(this).remove()
                                    });
                                
                                    var exp = new Date();
                                    exp.setTime(exp.getTime() + (7 * 24 * 3600000));
                                    document.cookie = "upie6=hide; expires="+ exp.toUTCString();
                                });
                    }
                });
            };
        }
    });    
    // extend plugin scope
    $.fn.extend({ update_ie6: $.update_ie6.construct });
})(jQuery);


upie6_lang = {};

if(window.CLANG !== undefined) {
    
    switch(CLANG[1]) {
            
        case 'gb':
        case 'us':
        case 'au':  
        case 'ie':
        case 'in':          
        case 'za':  
            upie6_lang = {  headline: "Did you know that your Internet Explorer is out of date?",
                            text1:    "To get the best possible experience using our website we recommend that you upgrade to a newer version or other web browser. A list of the most popular web browsers can be found below.",
                            text2:    "Just click on the icons to get to the download page.",
                            close:    "Close"
                            }; break;
        case 'es':
        case 'cl':      
        case 'ar':          
            upie6_lang = {  headline: "Sabía usted que su Internet Explorer está desactualizado?",
                            text1:    "Para lograr la mejor experiencia posible usando nuestro sitio web, le recomendamos actualizar a una nueva versión de Internet Explorer o usar otro navegador web. Le entregamos una lista de los navegadores web más populares a continuación.",
                            text2:    "Haga click en los íconos para obtener el vínculo de descarga.",
                            close:    "Cerrar"
                            }; break;
        case 'fr':      
            upie6_lang = {  headline: "Saviez-vous que votre version d'Internet Explorer est périmée?",
                            text1:    "Pour obtenir la meilleur expérience de navigation possible sur notre site web, nous vous recommandons de mettre à jour votre navigateur ou d'en choisir un autre. Une liste des navigateurs les plus populaires se trouve ci-dessous.",
                            text2:    "Il suffit de cliquer sur l'icône du navigateur correspondant pour se rendre à sa page de téléchargement.",
                            close:    "Fermer"
                            }; break;   
        case 'it':                      
            upie6_lang = {  headline: "Sapevi che la tua versione di Internet Explorer è obsoleta?",
                            text1:    "Per usufruire della migliore esperienza possibile nell' utilizzare il nostro sito, ti raccomandiamo di aggiornarlo con la nuova versione o un altro browser. Una lista dei browser più popolari si trova qui sotto.",
                            text2:    "E' sufficiente cliccare su una delle icone per andare alla pagina di download.",
                            close:    "Chiudi"
                            }; break;   
        case 'pt':      
        case 'br':          
            upie6_lang = {  headline: "Sabia que o seu Internet Explorer está desactualizado?",
                            text1:    "Para usufruir da melhor experiência de navegação na nossa página web recomendamos que actualize para uma nova versão ou para outro navegador web. Uma lista dos navegadores web mais populares encontra-se mais abaixo.",
                            text2:    "Carregue nas imagens para ir à página de download",
                            close:    "Cerrar"
                            }; break;   
    }
}
$(function () { $('body').update_ie6(upie6_lang);});