/**
 * Placeholder cho script frontend.
 */
( function () {
	function ready( fn ) {
		if ( document.readyState !== 'loading' ) {
			fn();
			return;
		}
		document.addEventListener( 'DOMContentLoaded', fn );
	}

	ready( function () {
		// Giữ chỗ cho tương tác JS trong tương lai.
		console.log( 'Autism Tools theme ready' );
	} );
} )();

