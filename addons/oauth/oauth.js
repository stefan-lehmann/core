if (typeof $ != 'undefined') {
	if (typeof $.cookies != 'undefined') {
		var cjo_oauth_popup;
		
		$(function () {
			$("a.cjo_oauth").click(function() {
				var $this  = $(this);
				var size   = [450, 450];
				var href   = $this.attr('href');
				var anchor = href.match(/#.*$/); 
				
				if (anchor != null && anchor.length != 0) {
					anchor = anchor[0].substr(1, anchor[0].lenght).split('-');
					if (anchor.length == 2) {
						size = anchor;
					}
				};
				if (typeof cjo_oauth_popup == 'object') { cjo_oauth_popup.close(); }
				
				cjo_oauth_popup = window.open(href, 
											  "cjo_oauth", 
											  "location=no,status=no,scrollbars=yes,width="+size[0]+"px,height="+size[1]+"px");
		
				var interval = setInterval(function() { 
					
					if (cjo_oauth_popup.closed) { 
						if (typeof cjo_oauth_onfinished == 'function') {
							cjo_oauth_onfinished();
						}
						clearInterval(interval);
						cjo_oauth_popup = '';
					}  
				}, 1000);
				cjo_oauth_popup.focus();
		
				return false;
			});
		});
	}
	else {
		alert('ERROR: cjoOAuth requires jQuery cookies Plugin!');
	}
}
else {
	alert('ERROR: cjoOAuth requires jQuery!');
}

if (typeof cjo_oauth_onfinished == 'undefined') {
	var cjo_oauth_onfinished = function () {
		console.log($.cookies.get('cjo_oauth'));
	};
}
