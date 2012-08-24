if (typeof $ != 'undefined') {
	$(function () {
		$("a.cjo_oauth").click(function() {
			var $this = $(this);
			var popup = window.open($this.attr('href'), "cjo_oauth", "location=no,status=no,scrollbars=yes,height=450px,width=450px");
	
			var interval = setInterval(function() { 
				if(popup.closed) { 
					if (typeof cjo_oauth_onfinished == 'function') {
						cjo_oauth_onfinished();
					}
					clearInterval(interval);
				}  
			}, 1000);
			popup.focus();
	
			return false;
		});
	});
}
else {
	alert('ERROR: cjoOAuth requires jQuery!');
}
