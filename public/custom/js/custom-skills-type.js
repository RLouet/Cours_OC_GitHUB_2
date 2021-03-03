/*global Typed*/
$(document).ready(function() {
	// Type text

	function getTypedElements() {
		let typed;
		$.ajax({
			url: "/ajax/typedElements",
			method: "POST",
			dataType: "json",
			success(data) {
				if($.isArray(data) && data.length) {
					$("#typedSkills").html("");
					typed = new Typed("#typedSkills", {
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
				}
			},
			error() {
				alert("Typed error");
			}
		});
	}
	getTypedElements();
});