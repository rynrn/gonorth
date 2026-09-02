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
		const payload = clean( Object.assign( {}, parameters, {
			page_location: window.location.href,
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

		if ( config.currentPlace ) window.GoNorthAnalytics.trackPlaceView();
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
		document.addEventListener( 'click', onClick, true );
		document.addEventListener( 'toggle', onToggle, true );
	}

	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	else init();
}() );
