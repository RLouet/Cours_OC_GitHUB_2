let customBase = (function($) {
	"use strict";

	//Preloader
	Royal_Preloader.config({
		mode: 'progress',
		background: '#aaaaaa',
		showProgress: true,
		showPercentage: false
	});

	//Page Scroll to id
	$(window).on("load",function(){

		/* Page Scroll to id fn call */
		$("a.nav-link,a[href='#top'],a[data-gal='m_PageScroll2id']").mPageScroll2id({
			highlightSelector:"a.nav-link",
			offset: 67,
			scrollSpeed:800
		});

		/* demo functions */
		$("a[rel='next']").click(function(e){
			e.preventDefault();
			var to=$(this).parent().parent("section").next().attr("id");
			$.mPageScroll2id("scrollTo",to);
		});

	});

	//Parallax & fade on scroll
	function scrollBanner() {
		$(document).scroll(function(){
			var scrollPos = $(this).scrollTop();
			$('.parallax-fade-top').css({
				'top' : (scrollPos/2)+'px',
				'opacity' : 1-(scrollPos/750)
			});
		});
	}
	scrollBanner();


	$(document).ready(function() {


		$(".tipped").tipper();

		/* Scroll Too */
		$(".scroll").on('click', function(event){

			event.preventDefault();

			var full_url = this.href;
			var parts = full_url.split("#");
			var trgt = parts[1];
			var target_offset = $("#"+trgt).offset();
			var target_top = target_offset.top - 68;

			$('html, body').animate({scrollTop:target_top}, 800);
		});

		//Scroll back to top
		var offset = 300;
		var duration = 600;
		jQuery(window).on('scroll', function() {
			if (jQuery(this).scrollTop() > offset) {
				jQuery('.scroll-to-top').fadeIn(duration);
			} else {
				jQuery('.scroll-to-top').fadeOut(duration);
			}
		});

		jQuery('.scroll-to-top').on('click', function(event) {
			event.preventDefault();
			jQuery('html, body').animate({scrollTop: 0}, duration);
			return false;
		});

		//Parallax
		$('.parallax').parallax("50%", 0.3);
	});

})(jQuery);