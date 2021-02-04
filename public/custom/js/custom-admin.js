(function($) { "use strict";
	//Preloader
	Royal_Preloader.config({
		mode           : "progress",
		background     : "#aaaaaa",
		showProgress   : true,
		showPercentage : false
	});
	
	/* Scroll Animation */
	
	window.scrollReveal = new scrollReveal();
		
		
	//Parallax & fade on scroll
	function scrollBanner() {
		$(document).on("scroll", function(){
			var scrollPos = $(this).scrollTop();
			$(".parallax-fade-top").css({
				"top": (scrollPos/2) + "px",
				"opacity": 1-(scrollPos/250)
			});
		});
	}
	scrollBanner();
	
	$(document).ready(function() {
		/* Scroll Too */
	
		$(".scroll").on("click", function(event){

			event.preventDefault();

			let fullUrl = this.href;
			let parts = fullUrl.split("#");
			let trgt = parts[1];
			let targetOffset = $("#"+trgt).offset();
			let targetTop = targetOffset.top - 68;

			$("html, body").animate({scrollTop:targetTop}, 800);
		});

			
		//Scroll back to top
		let offset = 300;
		let duration = 600;
		jQuery(window).on("scroll", function() {
			if (jQuery(this).scrollTop() > offset) {
				jQuery(".scroll-to-top").fadeIn(duration);
			} else {
				jQuery(".scroll-to-top").fadeOut(duration);
			}
		});
				
		jQuery(".scroll-to-top").on("click", function(event) {
			event.preventDefault();
			jQuery("html, body").animate({scrollTop: 0}, duration);
			return false;
		});
		
		
		//Parallax
		
		$(".parallax").parallax("50%", 0.3);
	
	
		// Facts Counter 
	
		$(".counter-numb").counterUp({
			delay: 20,
			time: 2000
		});
		
		
		/* Video */
		
		$(".container").fitVids();
						
		$(".vimeo a,.youtube a").on("click", function (e) {
			e.preventDefault();
			let videoLink = $(this).attr("href");
			let classeV = $(this).parent();
			let PlaceV = $(this).parent();
			if ($(this).parent().hasClass("youtube")) {
				$(this).parent().wrapAll("<div class='video-wrapper'>");
				$(PlaceV).html("<iframe frameborder='0' height='333' src='" + videoLink + "?autoplay=1&showinfo=0' title='YouTube video player' width='547'></iframe>");
			} else {
				$(this).parent().wrapAll("<div class='video-wrapper'>");
				$(PlaceV).html("<iframe src='" + videoLink + "?title=0&amp;byline=0&amp;portrait=0&amp;autoplay=1&amp;color=6dc234' width='500' height='281' frameborder='0'></iframe>");
			}
		});	
		
		
		/* Portfolio Sorting */

		(function ($) { 	
			let container = $(".grid-wraper");
			
			function getNumbColumns() { 
				let winWidth = $(window).width(),
					columnNumb = 1;
				
				if (winWidth > 1500) {
					columnNumb = 4;
				} else if (winWidth > 1200) {
					columnNumb = 3;
				} else if (winWidth > 900) {
					columnNumb = 2;
				} else if (winWidth > 600) {
					columnNumb = 1;
				} else if (winWidth > 300) {
					columnNumb = 1;
				}
				
				return columnNumb;
			}
			function setColumnWidth() { 
				let winWidth = $(window).width(),
					columnNumb = getNumbColumns(), 
					postWidth = Math.floor(winWidth / columnNumb);

			}
			function reArrangeProjects() {
				setColumnWidth();
				container.isotope("layout");
			}
			$(".grid-filter .filter a").click(function () {
				let selector = $(this).attr("data-filter");
				
				$(this).parent().parent().find("a").removeClass("current");
				$(this).addClass("current");
				
				container.isotope( { 
					filter : selector 
				});
				
				setTimeout(function () { 
					reArrangeProjects();
				}, 300);
				
				
				return false;
			});
			container.imagesLoaded(function () { 
				setColumnWidth();
				
				
				container.isotope( { 
					itemSelector : ".grid-box",
					layoutMode : "masonry",
					resizable : false 
				} );
			} );		
			$(window).on("debouncedresize", function () {
				reArrangeProjects();
				
			} );
		
		} )(jQuery);
					
	});	
 
	/* DebouncedResize Function */
		(function ($) { 
			let $event = $.event,
				$special, 
				resizeTimeout;
			
			
			$special = $event.special.debouncedresize = { 
				setup() {
					$(this).on("resize", $special.handler);
				}, 
				teardown() {
					$(this).off("resize", $special.handler);
				}, 
				handler(event, execAsap) {
					let context = this,
						args = arguments, 
						dispatch = function () { 
							event.type = "debouncedresize";
							
							$event.dispatch.apply(context, args);
						};					
					if (resizeTimeout) {
						clearTimeout(resizeTimeout);
					}
					execAsap ? dispatch() : resizeTimeout = setTimeout(dispatch, $special.threshold);
				}, 
				threshold : 150 
			};
		} )(jQuery);

  })(jQuery); 