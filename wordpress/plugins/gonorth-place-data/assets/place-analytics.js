( function () {
	'use strict';
	if ( window.__goNorthAnalyticsLoaded ) return;
	window.__goNorthAnalyticsLoaded = true;

	const config = window.GoNorthAnalyticsConfig || {};
	const sentOnce = new Set();
	const observedCards = new WeakSet();
	const impressedCards = new WeakSet();
	let observer;

	function clean( values ) {
		return Object.keys( values || {} ).reduce( function ( result, key ) {
			const value = values[ key ];
			if ( value !== '' && value !== null && typeof value !== 'undefined' ) result[ key ] = value;
			return result;
		}, {} );
	}

	function send( eventName, parameters ) {
		let consentGranted = false;
		try { consentGranted = '1' === window.localStorage.getItem( 'gn_analytics_consent_v2' ); } catch ( error ) {}
		if ( ! consentGranted ) return;
		const payload = clean( Object.assign( {}, parameters, {
			page_location: window.location.origin + window.location.pathname,
			debug_mode: config.debug ? true : undefined,
		} ) );
		try {
			if ( typeof window.gtag === 'function' ) {
				window.gtag( 'event', eventName, payload );
			} else if ( Array.isArray( window.dataLayer ) ) {
				window.dataLayer.push( Object.assign( { event: eventName }, payload ) );
			}
		} catch ( error ) {
			// Analytics must never interfere with the visitor's action.
		}
		if ( config.debug && window.console ) window.console.info( '[GoNorth analytics]', eventName, JSON.stringify( payload ) );
	}

	function sendOnce( key, eventName, parameters ) {
		try { if ( '1' !== window.localStorage.getItem( 'gn_analytics_consent_v2' ) ) return; } catch ( error ) { return; }
		if ( sentOnce.has( key ) ) return;
		sentOnce.add( key );
		send( eventName, parameters );
	}

	function currentPlace() {
		return clean( config.currentPlace || {} );
	}

	function slugFromLink( link ) {
		try {
			const parts = new URL( link.href, window.location.href ).pathname.split( '/' ).filter( Boolean );
			return decodeURIComponent( parts[ parts.length - 1 ] || '' );
		} catch ( error ) {
			return '';
		}
	}

	function cardData( card ) {
		const link = card.matches( 'a' ) ? card : card.querySelector( 'a[href*="/places/"]' );
		const title = card.querySelector( '.gn-card__title, strong' );
		const category = card.querySelector( '.gn-card__badge, [data-place-category]' );
		const city = card.querySelector( '.gn-card__location, [data-place-city]' );
		return clean( {
			place_id: card.dataset.placeId || card.dataset.postId || card.dataset.targetPlaceId || '',
			place_slug: card.dataset.placeSlug || ( link ? slugFromLink( link ) : '' ),
			place_name: card.dataset.placeName || ( title ? title.textContent.trim() : '' ),
			place_category: card.dataset.placeCategory || ( category ? category.textContent.trim() : '' ),
			place_city: card.dataset.placeCity || ( city ? city.textContent.trim() : '' ),
			discovery_surface: card.dataset.discoverySurface || config.discoverySurface || 'editorial',
			position: Number( card.dataset.position || Array.from( card.parentElement ? card.parentElement.children : [] ).indexOf( card ) + 1 ) || 1,
		} );
	}

	const searchEventKey = 'gn_search_event_v2';
	const allowedCategories = new Set( [ 'atraktziot-main', 'teva', 'accommodation', 'restaurants-sub', 'yakavim', 'agalot-kafe', 'tours' ] );
	const allowedRegions = new Set( [ 'galil', 'golan', 'carmel' ] );
	const allowedSaturday = new Set( [ 'yes', 'no', 'unknown' ] );

	function approved() {
		try { return '1' === window.localStorage.getItem( 'gn_analytics_consent_v2' ); } catch ( error ) { return false; }
	}

	function categorySlug( value ) {
		return allowedCategories.has( value ) ? value : '';
	}

	function regionSlug( value ) {
		return allowedRegions.has( value ) ? value : '';
	}

	function saturdayState( value ) {
		return allowedSaturday.has( value ) ? value : '';
	}

	function onSearchSubmit( event ) {
		const form = event.target;
		if ( ! form.matches || ! form.matches( '.gn-hero__search, #gn-archive-search' ) || ! approved() ) return;
		const input = form.querySelector( 'input[name="s"]' );
		const select = form.querySelector( 'select[name="category"]' );
		const hasQuery = !! ( input && input.value.trim() );
		const category = categorySlug( select ? select.value : '' );
		const regionInput = form.querySelector( 'select[name="region"]' );
		const saturdayInput = form.querySelector( 'select[name="saturday"]' );
		const region = regionSlug( regionInput ? regionInput.value : '' );
		const saturday = saturdayState( saturdayInput ? saturdayInput.value : '' );
		if ( ! hasQuery && ! category && ! region && ! saturday ) return;
		const source = form.matches( '.gn-hero__search' ) ? 'home' : 'archive';
		try {
			window.sessionStorage.setItem( searchEventKey, JSON.stringify( { hasQuery: hasQuery, category: category, region: region, saturday: saturday, source: source } ) );
		} catch ( error ) {
			// The visitor's search must work even when session storage is unavailable.
		}
	}

	function flushSearchSubmit() {
		let pending;
		try {
			pending = window.sessionStorage.getItem( searchEventKey );
			window.sessionStorage.removeItem( searchEventKey );
		} catch ( error ) { return; }
		if ( ! pending || ! approved() || ! /^\/places(?:\/|$)/.test( window.location.pathname ) ) return;
		try { pending = JSON.parse( pending ); } catch ( error ) { return; }
		const category = categorySlug( pending.category );
		const region = regionSlug( pending.region );
		const saturday = saturdayState( pending.saturday );
		const source = pending.source === 'home' ? 'home' : 'archive';
		if ( pending.hasQuery === true ) {
			sendOnce( 'search_submit', 'search_submit', { search_scope: source, has_query: true, category_slug: category, region_slug: region, saturday_state: saturday } );
		}
		if ( category ) {
			sendOnce( 'filter_apply_category', 'filter_apply', { search_scope: source, filter_name: 'category', category_slug: category } );
		}
		if ( region ) {
			sendOnce( 'filter_apply_region', 'filter_apply', { search_scope: source, filter_name: 'region', region_slug: region } );
		}
		if ( saturday ) {
			sendOnce( 'filter_apply_saturday', 'filter_apply', { search_scope: source, filter_name: 'open_saturday', saturday_state: saturday } );
		}
	}

	function trackZeroResults() {
		if ( ! document.querySelector( 'main#gn-search-results.gn-search-empty' ) ) return;
		const params = new URL( window.location.href ).searchParams;
		const hasQuery = !! ( params.get( 's' ) || '' ).trim();
		const categoryPath = window.location.pathname.match( /^\/places\/category\/([^/]+)\/?$/ );
		const category = categorySlug( params.get( 'category' ) || ( categoryPath ? categoryPath[ 1 ] : '' ) );
		const region = regionSlug( params.get( 'region' ) );
		const saturday = saturdayState( params.get( 'saturday' ) );
		if ( ! hasQuery && ! category && ! region && ! saturday ) return;
		sendOnce( 'zero_results', 'zero_results', { search_scope: 'archive', has_query: hasQuery, category_slug: category, region_slug: region, saturday_state: saturday, results_count: 0 } );
	}

	function cardSelector() {
		return '[data-gn-place-card], .gn-card-col[data-post-id]';
	}

	function observeCards( root ) {
		if ( ! observer || ! root.querySelectorAll ) return;
		const cards = Array.from( root.querySelectorAll( cardSelector() ) );
		if ( root.matches && root.matches( cardSelector() ) ) cards.unshift( root );
		cards.forEach( function ( card ) {
			if ( ! observedCards.has( card ) ) {
				observedCards.add( card );
				observer.observe( card );
			}
		} );
	}

	function actionFromLink( link ) {
		const href = link.href || '';
		if ( href.indexOf( 'tel:' ) === 0 ) return 'phone';
		if ( href.indexOf( 'wa.me/' ) !== -1 || href.indexOf( 'whatsapp.' ) !== -1 ) return link.dataset.gnAction || 'share';
		if ( href.indexOf( 'waze.com/' ) !== -1 ) return 'waze';
		if ( href.indexOf( 'google.com/maps' ) !== -1 ) return 'google_maps';
		return link.dataset.gnAction || 'official_website';
	}

	function onClick( event ) {
		if ( event.__goNorthAnalyticsHandled ) return;
		event.__goNorthAnalyticsHandled = true;
		const link = event.target.closest ? event.target.closest( 'a' ) : null;
		if ( ! link ) return;

		const preferredSource = link.closest( '[data-gn-preferred-source]' );
		if ( preferredSource ) {
			let consentGranted = false;
			try {
				consentGranted = '1' === window.localStorage.getItem( 'gn_analytics_consent_v2' );
			} catch ( error ) {
				consentGranted = false;
			}
			if ( consentGranted ) {
				send( 'preferred_source_click', clean( {
					placement: preferredSource.dataset.placement || 'content',
					page_type: preferredSource.dataset.pageType || 'other',
					place_id: preferredSource.dataset.placeId || ( config.currentPlace ? config.currentPlace.place_id : '' ),
				} ) );
			}
		}

		if ( config.currentGuide ) {
			try {
				const destination = new URL( link.href, window.location.href );
				const parts = destination.pathname.split( '/' ).filter( Boolean );
				if ( destination.origin === window.location.origin && parts[ 0 ] === 'places' && parts.length === 2 && parts[ 1 ] !== 'category' ) {
					send( 'guide_to_place_click', {
						guide_id: config.currentGuide.guide_id,
						target_type: 'place',
					} );
				}
			} catch ( error ) {
				// Navigation remains unchanged if a link cannot be parsed.
			}
		}

		const card = link.closest( cardSelector() );
		if ( card ) {
			const clickTarget = link.classList.contains( 'gn-card__img-link' ) ? 'image' : ( link.closest( '.gn-card__title' ) ? 'title' : 'card' );
			send( 'place_card_click', Object.assign( cardData( card ), { click_target: clickTarget } ) );
		}

		if ( link.closest( '.gn-place-overview__actions' ) ) {
			send( 'place_cta_click', Object.assign( currentPlace(), { action: actionFromLink( link ), cta_location: 'hero' } ) );
		}

		const related = link.closest( '[data-target-place-id]' );
		if ( related && config.currentPlace ) {
			const target = cardData( related );
			const section = related.dataset.relatedSection || related.dataset.discoverySurface || 'related';
			send( 'place_related_click', Object.assign( currentPlace(), {
				target_place_id: target.place_id || '',
				target_place_name: target.place_name || '',
				related_section: section,
			} ) );
			if ( section === 'plan_around' ) {
				send( 'place_plan_around_click', Object.assign( currentPlace(), {
					action: 'plan_around', cta_location: 'plan_around', target_place_id: target.place_id || '',
				} ) );
			}
		}
	}

	function onToggle( event ) {
		const item = event.target;
		if ( ! item.matches( '.gn-place-faq__item' ) || ! item.open ) return;
		send( 'place_faq_open', Object.assign( currentPlace(), {
			faq_id: item.dataset.faqId || '', faq_topic: item.dataset.faqTopic || 'other',
		} ) );
	}

	function init() {
		window.GoNorthAnalytics = Object.freeze( {
			track: send,
			trackOnce: sendOnce,
			trackPlaceView: function ( place ) { sendOnce( 'place_view', 'place_view', place || currentPlace() ); },
			trackPlaceAction: function ( action, place, extra ) {
				send( 'place_cta_click', Object.assign( {}, place || currentPlace(), extra || {}, { action: action } ) );
			},
		} );

		if ( config.currentPlace ) {
			window.GoNorthAnalytics.trackPlaceView();
			window.addEventListener( 'gn-consent-granted', function () { window.GoNorthAnalytics.trackPlaceView(); }, { once: true } );
		}
		if ( 'IntersectionObserver' in window ) {
			observer = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting && entry.intersectionRatio >= 0.35 && ! impressedCards.has( entry.target ) ) {
						impressedCards.add( entry.target );
						send( 'place_impression', cardData( entry.target ) );
						observer.unobserve( entry.target );
					}
				} );
			}, { threshold: [ 0.35 ] } );
			observeCards( document );
			new MutationObserver( function ( mutations ) {
				mutations.forEach( function ( mutation ) {
					mutation.addedNodes.forEach( function ( node ) { if ( node.nodeType === 1 ) observeCards( node ); } );
				} );
			} ).observe( document.body, { childList: true, subtree: true } );
		}
		flushSearchSubmit();
		trackZeroResults();
		window.addEventListener( 'gn-consent-granted', trackZeroResults, { once: true } );
		document.addEventListener( 'submit', onSearchSubmit, true );
		document.addEventListener( 'click', onClick, true );
		document.addEventListener( 'toggle', onToggle, true );
	}

	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	else init();
}() );
