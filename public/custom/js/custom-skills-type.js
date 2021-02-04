$(document).ready(function() {
	// Type text

	function getTypedElements() {
		let typed;
		$.ajax({
			url: "/ajax/typedElements",
			method: "POST",
			dataType: "json",
			success(data) {
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
			error() {
				alert("Typed error");
			}
		});
	}
	getTypedElements();
});