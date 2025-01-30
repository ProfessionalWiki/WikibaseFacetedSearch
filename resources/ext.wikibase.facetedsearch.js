let specialSearchInput;

/**
 * Main entry point for the JavaScript code.
 */
function init() {
	specialSearchInput = document.querySelector( '#searchText > input' );
	if ( !specialSearchInput ) {
		return;
	}

	const facets = document.querySelector( '.wikibase-faceted-search__facets' );
	const instances = document.querySelector( '.wikibase-faceted-search__instances' );

	if ( facets ) {
		facets.addEventListener( 'input', onFacetsInput );
		facets.addEventListener( 'click', onFacetsInput );

		const
			button = document.querySelector( '.wikibase-faceted-search__dialog-button' ),
			content = document.querySelector( '.wikibase-faceted-search__facets' ),
			teleportTarget = require( 'mediawiki.page.ready' ).teleportTarget;

		require( './dialog.js' ).init( button, content, teleportTarget );
	}

	if ( instances ) {
		instances.addEventListener( 'click', ( event ) => onInstancesClick( event, instances.dataset.instanceId ) );
	}
}

/**
 * Handles the input or click event for any elements in the facets.
 *
 * @param {Event} event
 */
function onFacetsInput( event ) {
	const target = event.target;
	if ( !target ) {
		return;
	}

	const facet = target.closest( '.wikibase-faceted-search__facet' );
	if ( !facet ) {
		return;
	}

	const propertyId = facet.dataset.propertyId;
	if ( !propertyId ) {
		return;
	}

	// TODO: Clean up the facet type detection logic after MVP or when we have more facet types
	if ( target.classList.contains( 'cdx-checkbox__input' ) ) {
		onListFacetInput( facet, propertyId );
	} else if ( target.classList.contains( 'cdx-button' ) ) {
		onListFacetInput( facet, propertyId, target.value );
	} else if ( target.classList.contains( 'cdx-text-input__input' ) ) {
		onRangeFacetInput( facet, propertyId );
	}
}

function onInstancesClick( event, instanceId ) {
	if ( !event.target ) {
		return;
	}

	const instance = event.target.closest( '.wikibase-faceted-search__instance' );
	if ( !instance ) {
		return;
	}

	submitSearchForm( buildQueryString(
		specialSearchInput.value,
		[ instance.value ? `haswbfacet:${ instanceId }=${ instance.value }` : '' ]
	) );
}

/**
 * Handles the input event for a list facet.
 *
 * @param {HTMLDivElement} facet
 * @param {string} propertyId
 * @param {?string} mode
 */
function onListFacetInput( facet, propertyId, mode ) {
	mode = mode || getListFacetQueryMode( facet );
	const newQueries = getListFacetQuerySegments( facet, propertyId, mode );
	submitSearchForm( buildQueryString( specialSearchInput.value, newQueries, propertyId ) );
}

/**
 * Determines the query mode for a list facet based on the selected toggle button.
 *
 * @param {HTMLDivElement} facet
 * @return {string}
 */
function getListFacetQueryMode( facet ) {
	const selectedButton = facet.querySelector( '.wikibase-faceted-search__facet-toggle > .cdx-button--action-progressive' );
	return selectedButton ? selectedButton.value : 'AND';
}

/**
 * Handles the input event for a range facet.
 *
 * @param {HTMLDivElement} facet
 * @param {string} propertyId
 */
function onRangeFacetInput( facet, propertyId ) {
	const applyButton = facet.querySelector( '.wikibase-faceted-search__facet-item-range-apply' );
	const minInput = facet.querySelector( '.wikibase-faceted-search__facet-item-range-min > .cdx-text-input__input' );
	const maxInput = facet.querySelector( '.wikibase-faceted-search__facet-item-range-max > .cdx-text-input__input' );

	if ( !applyButton || !minInput || !maxInput ) {
		return;
	}

	if ( minInput.value.length < 1 && maxInput.value.length < 1 ) {
		applyButton.disabled = true;
		return;
	}

	minInput.max = maxInput.value || '';
	maxInput.min = minInput.value || '';
	updateErrorState( minInput );
	updateErrorState( maxInput );

	if ( !minInput.validity.valid || !maxInput.validity.valid ) {
		applyButton.disabled = true;
		return;
	}

	applyButton.disabled = false;
	applyButton.addEventListener( 'click', () => {
		const newQueries = getRangeFacetQuerySegments( minInput.value, maxInput.value, propertyId );
		submitSearchForm(
			buildQueryString( specialSearchInput.value, newQueries, propertyId )
		);
	} );
}

/**
 * Updates the error state of a Codex text input.
 *
 * @param {HTMLInputElement} input
 */
function updateErrorState( input ) {
	if ( !input.validity.valid ) {
		input.parentElement.classList.add( 'cdx-text-input--status-error' );
	} else {
		input.parentElement.classList.remove( 'cdx-text-input--status-error' );
	}
}

/**
 * Extracts the queries from a list facet element.
 *
 * @param {HTMLDivElement} facet
 * @param {string} propertyId
 * @param {string} mode
 * @return {string[]}
 */
function getListFacetQuerySegments( facet, propertyId, mode ) {
	const checkedValues = [];

	[ ...facet.querySelectorAll( '.wikibase-faceted-search__facet-item' ) ].forEach( ( facetItem ) => {
		const checkbox = facetItem.querySelector( '.cdx-checkbox__input' );
		if ( !checkbox || !checkbox.checked || !checkbox.value ) {
			return;
		}
		checkedValues.push( checkbox.value );
	} );

	if ( checkedValues.length === 0 ) {
		return [];
	}

	const segments = [];
	if ( mode === 'AND' ) {
		checkedValues.forEach( ( value ) => {
			segments.push( `haswbfacet:${ propertyId }=${ value }` );
		} );
	} else {
		segments.push( `haswbfacet:${ propertyId }=${ checkedValues.join( '|' ) }` );
	}

	return segments;
}

/**
 * Constructs an array of range facet queries based on the provided minimum
 * and maximum input values.
 *
 * @param {string} min
 * @param {string} max
 * @param {string} propertyId
 * @return {string[]}
 */
function getRangeFacetQuerySegments( min, max, propertyId ) {
	const segments = [];
	if ( min !== '' ) {
		segments.push( `haswbfacet:${ propertyId }>=${ min }` );
	}
	if ( max !== '' ) {
		segments.push( `haswbfacet:${ propertyId }<=${ max }` );
	}
	return segments;
}

/**
 * Builds a new query string from the given old query and facet.
 *
 * @param {string} oldQuery
 * @param {Array} newQueries
 * @param {?string} propertyId
 *
 * @return {string}
 */
function buildQueryString( oldQuery, newQueries, propertyId ) {
	const queries = getFilteredQueries( oldQuery, propertyId );
	queries.push( ...newQueries );
	return queries.join( ' ' );
}

/**
 * Filters out wbfs queries that already include the given property ID
 * Remove all wbfs queries if no property ID is given
 *
 * @param {string} query
 * @param {?string} propertyId
 *
 * @return {string[]}
 */
function getFilteredQueries( query, propertyId ) {
	const propertyIdPattern = propertyId || 'P\\d+';
	return query.split( /\s+/ ).filter(
		( item ) => !( new RegExp( `^(haswbfacet|\\-haswbfacet):${ propertyIdPattern }(=|>=|<=)` ) ).test( item )
	);
}

/**
 * Submits the search form after modifying the search query to include the currently selected facet.
 *
 * @param {string} query The query to add to/remove from the search form.
 */
function submitSearchForm( query ) {
	specialSearchInput.value = query.trim();
	// Submit the search form
	specialSearchInput.form.submit();
}

init();

// Export for unit tests
module.exports = {
	getListFacetQueryMode,
	getListFacetQuerySegments,
	getRangeFacetQuerySegments,
	buildQueryString
};
