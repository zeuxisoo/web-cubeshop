(function($) {

	// Plugin
	$.fn.toggle_navigation = function() {
		$("ul", this).each(function() {
			var ul_li = this;
				
			$("h2", ul_li).toggle(function() {
				$("li", ul_li).hide();
			}, function() {
				$("li", ul_li).show();
			});
		});
	}

	// Startup
	$(function() {
		$("div.navigation").toggle_navigation();
		$(".tooltip").tipTip();
		$("#submit-form").validationEngine();
	});

})(jQuery);