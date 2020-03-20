$(document).ready(function(){
	$('.tabs').tabs();
});

function invite() {
	var email = $('#email').val();
	remind(email);
}

function remind(email) {
	if (isEmail(email)) {
		apretaste.send({
			'command': 'INVITAR INVITAR',
			'data': {'email':email}
		});
	} else M.toast({'html':"Ingrese un email válido"});
}

function isEmail(email) {
	var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return regex.test(email);
}