describe( 'ext.wikibase.facetedsearch.js', () => {
	// TODO: Add more tests when the JS is more stable
	const renderedHTML = `
		<div class="wikibase-faceted-search__facets"></div>
		<div id="searchText"><input type="text" /></div>
	`;

	beforeEach( () => {
		document.body.innerHTML = renderedHTML;
	} );

	it( 'init', () => {
		const facets = document.querySelector( '.wikibase-faceted-search__facets' );
		const specialSearchInput = document.querySelector( '#searchText > input' );

		expect( facets ).not.toBeNull();
		expect( specialSearchInput ).not.toBeNull();
	} );
} );
