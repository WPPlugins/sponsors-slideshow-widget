jQuery(document).ready(function($) {
	jQuery('.cycle-slideshow').on('cycle-bootstrap', function(e, opts, API) {
		// add a new method to the C2 API: find image src tag 
		API.customGetImageSrc = function( slideOpts, opts, slideEl ) {
			return jQuery( slideEl ).find('img').attr('src');
		},
		// add a new method to the C2 API: find slide class
		API.customGetImageClass = function( slideOpts, opts, slideEl ) {
			return jQuery( slideEl ).attr('class');
		}
	});
				
	// mobile responsive for text slideshows with carousel fade effect. Set to minimum client width of 736px
	if (document.documentElement.clientWidth < 736) {
		jQuery(".fancy-slideshow-container.text-slideshow").each(function() {
			var el = jQuery(this).children(".fancy-slideshow");
			if ( el.data("cycle-fx") == "carousel" ) {
				el.data("cycle-fx", "scrollHorz");
				el.data("cycle-carousel-visible", 0);
			}
		});
	}
});