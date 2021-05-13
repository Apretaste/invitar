// activate share, if supported
$(document).ready(function(){
	if (navigator.share) {
		$('#share').css('display', 'inline-block');
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
	var link = $('#link').val();
	var text = "Instala Apretaste, la red de amistad de todos los cubanos y gánate §3 de bienvenida";

	apretaste.share({
		title: text,
		link: link
	});
}
