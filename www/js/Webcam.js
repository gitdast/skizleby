jQuery(document).ready(function($){
	
	$(".thumb").on("click.SKI", function(e){
		e.preventDefault();
		$(".display img").attr('src', this.href);
	});
});