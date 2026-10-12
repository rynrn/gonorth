( function () {
	'use strict';
	if ( window.__goNorthPreferredSourceLoaded ) return;
	window.__goNorthPreferredSourceLoaded = true;

	const googleScriptUrl = 'https://news.google.com/swg/js/v1/publisher.js';

	function hasConsent() {
		try {
			return '1' === window.localStorage.getItem( 'gn_cookie_ok' );
		} catch ( error ) {
			return false;
		}
	}

	function fallback( button ) {
		window.location.href = button.href;
	}

	function bindSdk( preferredSource ) {
		try {
			preferredSource.init( { theme: 'light' } );
			document.querySelectorAll( '[data-gn-preferred-source]' ).forEach( function ( button ) {
				if ( button.dataset.gnPreferredSourceBound ) return;
				button.dataset.gnPreferredSourceBound = '1';
				button.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					try {
						preferredSource.addPreferredSource();
					} catch ( error ) {
						fallback( button );
					}
				} );
			} );
		} catch ( error ) {
			// The deeplink remains usable if Google's SDK cannot initialize.
		}
	}

	function loadGoogleLibrary() {
		if ( ! hasConsent() || window.__goNorthPreferredSourceScriptRequested ) return;
		window.__goNorthPreferredSourceScriptRequested = true;
		( self.PREFERRED_SOURCE = self.PREFERRED_SOURCE || [] ).push( bindSdk );

		if ( document.querySelector( 'script[src="' + googleScriptUrl + '"]' ) ) return;
		const script = document.createElement( 'script' );
		script.src = googleScriptUrl;
		script.async = true;
		script.setAttribute( 'preferred-sources-control', 'manual' );
		script.addEventListener( 'error', function () {
			window.__goNorthPreferredSourceScriptFailed = true;
		}, { once: true } );
		document.head.appendChild( script );
	}

	function init() {
		loadGoogleLibrary();
		const consentButton = document.getElementById( 'gn-cookie-accept' );
		if ( consentButton && ! hasConsent() ) {
			consentButton.addEventListener( 'click', loadGoogleLibrary, { once: true } );
		}
	}

	if ( 'loading' === document.readyState ) document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	else init();
}() );
