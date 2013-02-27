var $j = jQuery.noConflict();

$j(document).ready(function () {
	$j('.social_wrap').bind('mouseenter', function () {
		$j(this).find('.lsms_social_bar').show();
	});
	$j('.social_wrap').bind('mouseleave', function () {
		$j(this).find('.lsms_social_bar').hide();
	});
});