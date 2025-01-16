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
	}
	if ( instances ) {
		instances.addEventListener( 'click', ( event ) => onInstancesClick( event, instances.dataset.instanceId ) );
	}
}

/**
 * Handles the change event for any input elements in the facets.
 *
 * @param {Event} event
 */
function onFacetsInput( event ) {
	const input = event.target;
	if ( !input ) {
		return;
	}

	const facet = input.closest( '.wikibase-faceted-search__facet' );
	if ( !facet ) {
		return;
	}

	const propertyId = facet.dataset.propertyId;
	if ( !propertyId ) {
		return;
	}

	if ( input.classList.contains( 'cdx-checkbox__input' ) ) {
		onListFacetInput( facet, propertyId );
	} else if ( input.classList.contains( 'cdx-text-input__input' ) ) {
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
		[ instance.value ? `haswbstatement:${ instanceId }=${ instance.value }` : '' ]
	) );
}

/**
 * Handles the input event for a list facet.
 *
 * @param {HTMLDivElement} facet - The facet element.
 * @param {string} propertyId - The ID of the property on the facet.
 */
function onListFacetInput( facet, propertyId ) {
	const newQueries = getListFacetQueries( facet, propertyId );
	submitSearchForm( buildQueryString( specialSearchInput.value, newQueries, propertyId ) );
}

/**
 * Handles the input event for a range facet.
 *
 * @param {HTMLDivElement} facet - The facet element.
 * @param {string} propertyId - The ID of the property on the facet.
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

	applyButton.disabled = false;
	applyButton.addEventListener( 'click', () => {
		const newQueries = getRangeFacetQueries( minInput, maxInput, propertyId );
		submitSearchForm(
			buildQueryString( specialSearchInput.value, newQueries, propertyId )
		);
	} );
}

/**
 * Extracts the queries from a list facet element.
 *
 * @param {HTMLDivElement} facet
 * @param {string} propertyId
 * @return {string[]} List of queries that can be used in a search URL.
 */
function getListFacetQueries( facet, propertyId ) {
	const queries = [];
	[ ...facet.querySelectorAll( '.wikibase-faceted-search__facet-item' ) ].forEach( ( facetItem ) => {
		const checkbox = facetItem.querySelector( '.cdx-checkbox__input' );
		if ( !checkbox || !checkbox.checked || !checkbox.value ) {
			return;
		}
		// TODO: Support other operators
		// TODO: Support OR values
		queries.push( `haswbfacet:${ propertyId }=${ checkbox.value }` );
	} );
	return queries;
}

/**
 * Constructs an array of range facet queries based on the provided minimum
 * and maximum input values.
 *
 * @param {HTMLInputElement} minInput - The input element for the minimum range value.
 * @param {HTMLInputElement} maxInput - The input element for the maximum range value.
 * @param {string} propertyId - The ID of the property on the facet
 * @return {string[]} An array of query strings representing the range constraints.
 */
function getRangeFacetQueries( minInput, maxInput, propertyId ) {
	const queries = [];
	if ( minInput.value !== '' ) {
		queries.push( `haswbfacet:${ propertyId }>=${ minInput.value }` );
	}
	if ( maxInput.value !== '' ) {
		queries.push( `haswbfacet:${ propertyId }<=${ maxInput.value }` );
	}
	return queries;
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
		( item ) => !( new RegExp( `^(haswbfacet|\\-haswbfacet|haswbstatement):${ propertyIdPattern }(=|>=|<=)` ) ).test( item )
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
