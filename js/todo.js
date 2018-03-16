function show_registerpro(){
	$('#show_register').click(function(){
		$('.login_form').hide();
		$('.register_form').show();
		return false;
	});
	$('#show_login').click(function(){
		$('.register_form').hide();
		$('.login_form').show();
		return false;
	});
	/*$( "#datepicker" ).datepicker({
		//appendText: "(dd-mm-yyyy)"
		//dateFormat: 'dd-MM-yy'
	});/**/
}

$(document).ready(function(){
	show_registerpro();
});