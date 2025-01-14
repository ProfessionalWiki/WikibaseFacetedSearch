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

function buildQueryString( oldQuery, facet ) {
	if ( !facet ) {
		return oldQuery;
	}

	const propertyId = facet.dataset.propertyId;
	if ( !propertyId ) {
		return oldQuery;
	}

	let queries = oldQuery.split( /\s+/ );
	const patternToRemove = new RegExp( `^(haswbfacet|\\-haswbfacet):${ propertyId }(=|>=|<=)` );
	queries = queries.filter( ( item ) => !patternToRemove.test( item ) );

	const facetItems = facet.querySelectorAll( '.wikibase-faceted-search__facet-item' );
	if ( !facetItems ) {
		return oldQuery;
	}

	facetItems.forEach( ( facetItem ) => {
		const value = facetItem.dataset.valueId;
		if ( !value ) {
			return;
		}

		// TODO: Support range facets
		const checkbox = facetItem.querySelector( '.cdx-checkbox__input' );
		if ( !checkbox || !checkbox.checked ) {
			return;
		}

		// TODO: Implement support for inverse queries
		// TODO: Implement support for OR queries
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
