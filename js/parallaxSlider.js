/* parallax slider -- http://tympanus.net/codrops/2011/01/03/parallax-slider/ */
(function($) {
	$.fn.parallaxSlider = function(options) {
		var opts = $.extend({}, $.fn.parallaxSlider.defaults, options);
		return this.each(function() {
			var $pxs_container 	= $(this),
			o = $.meta ? $.extend({}, opts, $pxs_container.data()) : opts;
						
			/* ul tag, animation container */
			var $pxs_slider	= $('.pxs_slider',$pxs_container),
				$elems	= $pxs_slider.children(),				//the li elements of the ul
				total_elems	= $elems.length,					//total number of li elements
				$pxs_next = $('.pxs_next',$pxs_container),		//the navigation buttons
				//$pxs_prev = $('.pxs_prev',$pxs_container), 	//not using navigation for splash
				current	= 0,
				slideshow,
				$pxs_loading = $('.pxs_loading',$pxs_container), //the loading image
				$pxs_slider_wrapper = $('.pxs_slider_wrapper',$pxs_container);
							
			/* first preload all the images */
			var loaded	= 0,
				$images	= $pxs_slider_wrapper.find('img');
							
				$images.each(function() {
					var $img	= $(this);
					$('<img/>').load(function() {
					++loaded;
						/* modified this code block */
						if(loaded == total_elems) {
							$pxs_loading.hide();
							$pxs_slider_wrapper.show();	
							/* sets all image width based on first image */
							var one_image_w	= $pxs_slider.find('img:first').width();
							//console.log("Image width " + one_image_w);
							/*
							* need to set width of the slider, of each one of its elements, 
							* and of the navigation buttons
							*/
							setWidths($pxs_slider,
								$elems,
								total_elems,
								one_image_w,
								$pxs_next
								//$pxs_prev						//not using previous nav button
							);
							/*
							* modified all thumbnails reference, 
							* see original for thumbs thumbnail nav code went here
							*
							* bind user click behavior to nav button
							*/
							$pxs_next.bind('click',function(){
								++current;
								if(current >= total_elems)
									if(o.circular)
										current = 0;
									else {
										--current;
										return false;
									}
									/* modified from original */
									highlight(current);
									slide(current,
									$pxs_slider,
									o.speed,
									o.easing,
									o.easingBg);
							});
							/*
							$pxs_prev.bind('click',function() {
								--current;
								if(current < 0)
									if(o.circular)
										current = total_elems - 1;
									else{
										++current;
										return false;
									}
									//modified from original
									highlight(current);
									slide(current,
									$pxs_slider,
									o.speed,
									o.easing,
									o.easingBg);
							});
							*/
							/*
							* modified thumbnail actions here, see original
							*
							* activate the autoplay mode if
							* that option was specified
							*/
							if(o.auto != 0) {
								o.circular	= true;
								o.slideshow	= setInterval(function() { $pxs_next.trigger('click'); },o.auto);
							}
							/*
							* when resizing the window, we need to recalculate the widths of the
							* slider elements, based on the new windows width.
							*
							* we need to slide again to the current one,
							* since the left of the slider is no longer correct
							*/
							$(window).resize(function(){
								w_w	= $(window).width();
								/* setWidths($pxs_slider,$elems,total_elems,one_image_w,$pxs_next,$pxs_prev); */
								setWidths($pxs_slider,$elems,total_elems,one_image_w,$pxs_next);
								slide(current,
									$pxs_slider,
									1,
									o.easing,
									o.easingBg);
							});

						} // close if L29
					}).error(function(){		// close JQuery img object - anonymous function L26
							alert('No images loaded, check file path and image name.')
					}).attr('src',$img.attr('src'));
				}); 		//close image each loop L24	
		}); 				//close anon func L5
	}; 						//close anon func L3
				
	/* Helper Variables, calculates window width, height, frames */
	var w_w	= $(window).width();
	var slide = function(current,
		$pxs_slider,
		speed,
		easing,
		easingBg) {
			var slide_to	= parseInt(-w_w * current);
			$pxs_slider.stop().animate({
			left : slide_to + 'px'
			},speed, easing);
		}
	/* leave for dependancies, empty function body */
	var highlight = function($elem){
		//$elem.siblings().removeClass('selected');
		//$elem.removeClass('selected');
		//$elem.addClass('selected');
	}
	var setWidths = function($pxs_slider,
		$elems,
		total_elems,
		one_image_w,
		$pxs_next
		//$pxs_prev
	){
	/*
	* the width of the slider is the windows width
	* times the total number of elements in the slider
	*/
	var pxs_slider_w = w_w * total_elems;
		$pxs_slider.width(pxs_slider_w + 'px');
		$elems.width(w_w + 'px');	//each element will have a width = windows width
	/*
	* we also set the width of each bg image div.
	* The value is the same calculated for the pxs_slider
	* both the right and left of the
	* navigation next and previous buttons will be:
	* windowWidth/2 - imgWidth/2 + some margin (not to touch the image borders)
	*/
	var position_nav	= w_w/2 - one_image_w/2 + 3;
		$pxs_next.css('right', position_nav + 'px');
		//$pxs_prev.css('left', position_nav + 'px');
	}
	/*
	* define function parallaxSlider parameters
	* this is where you would change default behaviors
	*/
	$.fn.parallaxSlider.defaults = {
		auto : 5000,			//how many seconds before transition starts, 0 autoplay is turned off.
		speed : 2000,			//speed of each slide animation
		easing : 'jswing',		//easing effect for the slide animation
		easingBg : 'jswing',	//easing effect for the background animation
		circular : false,		//circular slider
	};
})(jQuery);