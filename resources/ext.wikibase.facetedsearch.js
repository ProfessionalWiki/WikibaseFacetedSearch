let input;

/**
 * Main entry point for the JavaScript code.
 */
function init() {
	const facets = document.querySelector( '.wikibase-faceted-search__facets' );
	if ( !facets ) {
		return;
	}

	input = document.querySelector( '#searchText > input' );

	facets.addEventListener( 'change', onFacetsChange );
}

/**
 * Handles the change event for any input elements in the facets.
 *
 * @param {Event} event
 */
function onFacetsChange( event ) {
	const facet = event.target.closest( '.wikibase-faceted-search__facet' );
	// TODO: Support range facets
	if ( event.target.classList.contains( 'cdx-checkbox__input' ) ) {
		submitSearchForm( buildQueryString( input.value, facet ) );
	}
}

/**
 * Builds a new query string from the given old query and facet.
 *
 * @param {string} oldQuery The original query string.
 * @param {HTMLElement} facet The facet element.
 *
 * @return {string} The new query string.
 */
function buildQueryString( oldQuery, facet ) {
	if ( !facet ) {
		return oldQuery;
	}

	const propertyId = facet.dataset.propertyId;
	if ( !propertyId ) {
		return oldQuery;
	}

	const queries = getFilteredQueries( oldQuery, propertyId );

	// Add selected facet items to the query string
	[ ...facet.querySelectorAll( '.wikibase-faceted-search__facet-item' ) ].forEach( ( facetItem ) => {
		const checkbox = facetItem.querySelector( '.cdx-checkbox__input' );
		if ( !checkbox || !checkbox.checked || !checkbox.value ) {
			return;
		}
		// TODO: Support other operators
		// TODO: Support OR values
		queries.push( `haswbfacet:${ propertyId }=${ checkbox.value }` );
	} );

	return queries.join( ' ' );
}

/**
 * Filters out queries that already include the given property ID
 *
 * @param {string} query The query string to filter.
 * @param {string} propertyId The property ID to filter out.
 *
 * @return {string[]} The filtered queries.
 */
function getFilteredQueries( query, propertyId ) {
	return query.split( /\s+/ ).filter(
		( item ) => !new RegExp( `^(haswbfacet|\\-haswbfacet):${ propertyId }(=|>=|<=)` ).test( item )
	);
}

/**
 * Submits the search form after modifying the search query to include the currently selected facet.
 *
 * @param {string} query The query to add to/remove from the search form.
 */
function submitSearchForm( query ) {
	input.value = query;
	// Submit the search form
	input.form.submit();
}

init();
