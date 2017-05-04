window.onload = function( ) {
	if( window.self !== window.top ) {
		body = document.getElementById( "body-dokuwiki" ).className += " iframe";
	}
};