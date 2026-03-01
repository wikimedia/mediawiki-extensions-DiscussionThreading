'use strict';
$( () => {
	const ctrl = document.getElementById( 'wpTextbox1' );
	if ( ctrl.setSelectionRange ) {
		ctrl.focus();
		const end = ctrl.value.length;
		const replaceMsgLength = mw.message( 'discussionthreading-replacetext' ).plain().length;
		ctrl.setSelectionRange( end - replaceMsgLength - 1, end - 1 );
		ctrl.scrollTop = ctrl.scrollHeight;
	}
} );
