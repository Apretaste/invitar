// activate share if supported
$(document).ready(function(){
	if (navigator.share) {
		$('#share').addClass('green-text');
	}
});

// copy invitation link to the clipboard
function copy() {
	// copy the text in the input
	var copyText = document.getElementById("link");
	copyText.select();
	copyText.setSelectionRange(0, 99999); // for mobile devices
	document.execCommand("copy");

	// display confimation message
	M.toast({html: 'El link de invitación fue copiado'});
}

// open share modal
function share() {
	if (navigator.share) {
		// copy the share link
		var link = $('#link').val();
		var text = "Instala Apretaste, la red de amistad de todos los cubanos y gánate §3 de bienvenida: " + link;

		// open the window
		navigator.share({
			title: "Invita a tu gente",
			text: text,
			url: window.location.href
		});
	} else {
		M.toast({html: 'Tu dispositivo no permite mostrar la lista de redes sociales. Puedes copiar el vínculo y compartirlo manualmente.'});
	}
}
