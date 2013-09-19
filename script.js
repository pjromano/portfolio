$(document).ready(function() {
	$('#portfolio_menu li a').hover(
	function() {
		// Mouse enter
		$('#menu_hint' + $(this).attr('id').substring(8)).slideDown(80);
	},
	function() {
		// Mouse leave
		$('#menu_hint' + $(this).attr('id').substring(8)).slideUp(80);
	});
	
	$('#menuitem_return').hover(
	function() {
		// Mouse enter
		$('#return_img').attr('src', 'img/return_hover.png');
	},
	function() {
		// Mouse leave
		$('#return_img').attr('src', 'img/return.png');
	});
});

