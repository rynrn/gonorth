( function () {
	'use strict';

	const weekdayNumbers = {
		Mon: '1',
		Tue: '2',
		Wed: '3',
		Thu: '4',
		Fri: '5',
		Sat: '6',
		Sun: '7',
	};

	function israelWeekday() {
		try {
			const weekday = new Intl.DateTimeFormat( 'en-US', {
				weekday: 'short',
				timeZone: 'Asia/Jerusalem',
			} ).format( new Date() );

			return weekdayNumbers[ weekday ] || '';
		} catch ( error ) {
			return '';
		}
	}

	function markToday() {
		const currentWeekday = israelWeekday();

		document.querySelectorAll( '.gn-practical-data__hours-row' ).forEach( function ( row ) {
			const isToday = '' !== currentWeekday && row.dataset.weekday === currentWeekday;
			const badge = row.querySelector( '.gn-practical-data__today' );

			row.classList.toggle( 'is-today', isToday );
			if ( isToday ) {
				row.setAttribute( 'aria-current', 'date' );
			} else {
				row.removeAttribute( 'aria-current' );
			}
			if ( badge ) {
				badge.hidden = ! isToday;
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', markToday, { once: true } );
	} else {
		markToday();
	}

	window.setInterval( markToday, 300000 );
}() );
