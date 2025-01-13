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
	const checkbox = event.currentTarget;
	const queryType = 'haswbfacet';
	const valueId = checkbox.dataset.valueId;
	const facet = checkbox.closest( '.wikibase-faceted-search__facet' );
	const propertyId = facet.dataset.propertyId;

	// TODO: Implement support for inverse queries
	// TODO: Implement support for OR queries
	submitSearchForm( `${ queryType }:${ propertyId }=${ valueId }` );
}

/**
 * Submits the search form after modifying the search query to include the currently selected facet.
 *
 * @param {string} query The query to add to/remove from the search form.
 */
function submitSearchForm( query ) {
	// Check if the query is already in the search input field
	if ( input.value.indexOf( query ) > -1 ) {
		// If it is, remove it from the query
		input.value = input.value.replace( query, '' ).trim();
	} else {
		// If it's not, append it to the query
		input.value += ` ${ query }`;
	}

	// Submit the search form
	input.form.submit();
}

init();
