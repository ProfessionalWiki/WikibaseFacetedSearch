let input;

/**
 * Main entry point for the JavaScript code.
 */
function init() {
	const checkboxItems = document.querySelectorAll( '.wikibase-faceted-search__facet-item-checkbox' );
	if ( !checkboxItems ) {
		return;
	}

	input = document.querySelector( '#searchText > input' );

	checkboxItems.forEach( ( item ) => item.addEventListener( 'change', onCheckboxItemChange ) );
}

/**
 * Handles the change event for a checkbox item in the faceted search.
 *
 * @param {Event} event - The change event from the checkbox.
 */
function onCheckboxItemChange( event ) {
	const facet = event.currentTarget.closest( '.wikibase-faceted-search__facet' );
	submitSearchForm( buildQueryString( input.value, facet ) );
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

	// Remove existing facet filters for the same property ID
	const queries = oldQuery.split( /\s+/ ).filter(
		( item ) => !new RegExp( `^(haswbfacet|\\-haswbfacet):${ propertyId }(=|>=|<=)` ).test( item )
	);

	// Add selected facet items to the query string
	[ ...facet.querySelectorAll( '.wikibase-faceted-search__facet-item' ) ].forEach( ( facetItem ) => {
		const value = facetItem.dataset.valueId;
		const checkbox = facetItem.querySelector( '.cdx-checkbox__input' );
		if ( !value || !checkbox || !checkbox.checked ) {
			return;
		}
		// TODO: Support range facet and other operators
		// TODO: Support OR values
		queries.push( `haswbfacet:${ propertyId }=${ value }` );
	} );

	return queries.join( ' ' );
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
