(function($) { "use strict";
	$(document).ready(function() {

		// Type text
		let typed;

		function getTypedElements() {
			$.ajax({
				url: "/ajax/typedElements",
				method: "POST",
				dataType: "json",
				success: function (data) {
					typed = new Typed("#typed-1", {
						strings: data,
						typeSpeed:45,
						backSpeed:0,
						startDelay:200,
						backDelay:2200,
						loop:true,
						loopCount:false,
						showCursor:true,
						cursorChar:"_",
						attr:null
					});
					//setTypedElements(data) ;
				},
				error: function () {
					alert("Typed error");
				}
			})
		}
		getTypedElements();
	});
})(jQuery);