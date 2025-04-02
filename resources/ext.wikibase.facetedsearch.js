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
	if ( target.classList.contains( 'wikibase-faceted-search__facet-item-checkbox' ) ) {
		onListFacetInput( facet, propertyId );
	} else if ( target.classList.contains( 'wikibase-faceted-search__facet-mode' ) ) {
		onModeSelectInput( facet, propertyId, target.value, target.dataset.defaultValue );
	} else if ( target.classList.contains( 'wikibase-faceted-search__facet-item-input' ) ) {
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
 */
function onListFacetInput( facet, propertyId ) {
	const selectedValues = getListFacetSelectedValues( facet );
	const newQueries = getListFacetQuerySegments( selectedValues, propertyId, getListFacetQueryMode( facet ) );
	submitSearchForm( buildQueryString( specialSearchInput.value, newQueries, propertyId ) );
}

/**
 * Handles the input event for the mode select.
 *
 * @param {HTMLDivElement} facet
 * @param {string} propertyId
 * @param {string} mode
 * @param {string} defaultMode
 */
function onModeSelectInput( facet, propertyId, mode, defaultMode ) {
	if ( mode === defaultMode ) {
		return;
	}

	const selectedValues = getListFacetSelectedValues( facet );
	const newQueries = getListFacetQuerySegments( selectedValues, propertyId, mode );
	submitSearchForm( buildQueryString( specialSearchInput.value, newQueries, propertyId ) );
}

/**
 * Determines the query mode for a list facet based on the selected mode.
 *
 * @param {HTMLDivElement} facet
 * @return {string}
 */
function getListFacetQueryMode( facet ) {
	const selectElement = facet.querySelector( '.wikibase-faceted-search__facet-mode' );
	return selectElement ? selectElement.value : 'AND';
}

/**
 * Handles the input event for a range facet.
 *
 * @param {HTMLDivElement} facet
 * @param {string} propertyId
 */
function onRangeFacetInput( facet, propertyId ) {
	const applyButton = facet.querySelector( '.wikibase-faceted-search__facet-item-range-apply' );
	const minInput = facet.querySelector( '.wikibase-faceted-search__facet-item-range-min > .wikibase-faceted-search__facet-item-input' );
	const maxInput = facet.querySelector( '.wikibase-faceted-search__facet-item-range-max > .wikibase-faceted-search__facet-item-input' );

	if ( !applyButton || !minInput || !maxInput ) {
		return;
	}

	if ( minInput.value === minInput.defaultValue && maxInput.value === maxInput.defaultValue ) {
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

	const clickOnEnter = ( event ) => {
		if ( event.key === 'Enter' ) {
			applyButton.click();
		}
	};

	minInput.addEventListener( 'keydown', clickOnEnter );
	maxInput.addEventListener( 'keydown', clickOnEnter );

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
 * Extracts the selected facet values from a list facet element.
 *
 * @param {HTMLDivElement} facet
 * @return {string[]}
 */
function getListFacetSelectedValues( facet ) {
	const selectedValues = [];

	[ ...facet.querySelectorAll( '.wikibase-faceted-search__facet-item' ) ].forEach( ( facetItem ) => {
		const checkbox = facetItem.querySelector( '.wikibase-faceted-search__facet-item-checkbox' );
		if ( !checkbox || !checkbox.checked || !checkbox.value ) {
			return;
		}
		selectedValues.push( checkbox.value );
	} );

	return selectedValues;
}

/**
 * Constructs an array of list facet queries based on the provided selected values.
 *
 * @param {string[]} selectedValues
 * @param {string} propertyId
 * @param {string} mode
 * @return {string[]}
 */
function getListFacetQuerySegments( selectedValues, propertyId, mode ) {
	const segments = [];
	switch ( mode ) {
		case 'AND':
			if ( selectedValues.length === 0 ) {
				segments.push( `haswbfacet:${ propertyId }=` );
			}
			selectedValues.forEach( ( value ) => {
				segments.push( `haswbfacet:${ propertyId }=${ value }` );
			} );
			break;
		case 'OR': {
			const suffix = selectedValues.length <= 1 ? '|' : '';
			segments.push( `haswbfacet:${ propertyId }=${ selectedValues.join( '|' ) }${ suffix }` );
			break;
		}
		case 'ANY':
			segments.push( `haswbfacet:${ propertyId }` );
			break;
		case 'NONE':
			segments.push( `-haswbfacet:${ propertyId }` );
			break;
		default:
			segments.push( `haswbfacet:${ propertyId }=|` );
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
		( item ) => !( new RegExp( `^(haswbfacet|\\-haswbfacet):${ propertyIdPattern }(=|>=|<=)?\\b` ) ).test( item )
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
	getListFacetSelectedValues,
	getListFacetQuerySegments,
	getRangeFacetQuerySegments,
	buildQueryString
};
