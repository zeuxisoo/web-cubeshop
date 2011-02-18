$.fn.hover_items = function() {
	$(this).each(function() {
		$(this).fadeTo("fast", 0.4).hover(function() {
			$(this).fadeTo("slow", 1);
		}, function() {
			$(this).fadeTo("slow", 0.4);
		});
	});
}


(function($, undefined) {
	
	$(document).ready(function() {
	
		// Index :: cube list :: mouse hover event
		$("ul.cubes li").hover_items();
		
		// Product :: product list :: mouse hover event
		$("ul.products li").hover_items();
		
	});
	
})(jQuery);