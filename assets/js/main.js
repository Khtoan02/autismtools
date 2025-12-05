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

	function initMenuToggle() {
		const toggle = document.querySelector( '.menu-toggle' );
		const nav = document.querySelector( '.site-nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		const closeMenu = () => {
			nav.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		};

		const toggleMenu = () => {
			const isExpanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			toggle.setAttribute( 'aria-expanded', isExpanded ? 'false' : 'true' );
			nav.classList.toggle( 'is-open', ! isExpanded );
		};

		toggle.addEventListener( 'click', toggleMenu );

		nav.querySelectorAll( 'a' ).forEach( ( link ) => {
			link.addEventListener( 'click', closeMenu );
		} );

		window.addEventListener( 'resize', () => {
			if ( window.innerWidth > 768 ) {
				closeMenu();
			}
		} );
	}

	ready( function () {
		initMenuToggle();
	} );
} )();

