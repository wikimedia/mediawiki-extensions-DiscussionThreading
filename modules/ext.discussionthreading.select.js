'use strict';
$( function () {
	var ctrl = document.getElementById( 'wpTextbox1' );
	if ( ctrl.setSelectionRange ) {
		ctrl.focus();
		var end = ctrl.value.length;
		var replaceMsgLength = mw.message( 'discussionthreading-replacetext' ).plain().length;
		ctrl.setSelectionRange( end - replaceMsgLength - 1, end - 1 );
		ctrl.scrollTop = ctrl.scrollHeight;
	}
} );
