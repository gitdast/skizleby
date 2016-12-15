jQuery(document).ready(function($){
	
	$(".working a.ajax").on("click.ajax", function(e){
		e.preventDefault();
		$.post(this.href, {working: $("input[name='working']").is(":checked")}).done(function(data){
			if(data.redirect)
				document.location.href = data.redirect;
		});
	});
});