$(document).ready(function() {
	$('#typeselect').change(function() {
		if ($(this).val() == '0')
		{
			$('#content1').css('display', 'none');
			$('#content2').css('display', 'none');
		}
		else if ($(this).val() == '1')
		{
			$('#content1').css('display', 'block');
			$('#content2').css('display', 'none');
		}
		else
		{
			$('#content1').css('display', 'none');
			$('#content2').css('display', 'block');
		}
	});
	
	$('.expand').click(function() {
		if ($(this).hasClass('open'))
		{
			$(this).removeClass('open');
			$(this).attr('src', 'tree_expand.png');
			$('div.node' + $(this).attr('id').substr(-2)).slideUp(100);
		}
		else
		{
			$(this).addClass('open');
			$(this).attr('src', 'tree_collapse.png');
			$('div.node' + $(this).attr('id').substr(-2)).slideDown(100);
		}
	});
	
	$('.expand_a').click(function() {
		$('#imagenode' + $(this).attr('id').substr(-2)).click();
	});
});

