jQuery(document).ready(function($){
	$('.slickGallery .display').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		fade: true,
		asNavFor: '.thumbs',
		adaptiveHeight: true
	});
	$('.slickGallery .thumbs').slick({
		/*slidesToShow: 6,*/
		slidesToScroll: 1,
		variableWidth: true,
		asNavFor: '.display',
		dots: false,
		arrows: false,
		centerMode: true,
		centerPadding: '0px',
		focusOnSelect: true,
		infinite:true
	});
});