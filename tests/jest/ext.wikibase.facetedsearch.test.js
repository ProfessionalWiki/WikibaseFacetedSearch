const actual = require( '../../resources/ext.wikibase.facetedsearch.js' );

describe( 'ext.wikibase.facetedsearch.js', () => {
	test( 'Query segements for list facet with no checked items', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q1"></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q2"></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q3"></div>
		`;

		expect( actual.getListFacetQuerySegments( document.body, 'P1' ) )
			.toEqual( [] );
	} );

	test( 'Query segements for list facet with multiple checked items', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q1"></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q2" checked></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q3" checked></div>
		`;

		expect( actual.getListFacetQuerySegments( document.body, 'P1' ) )
			.toEqual( [ 'haswbfacet:P1=Q2', 'haswbfacet:P1=Q3' ] );
	} );

	test( 'Query segements for range facet with no values', () => {
		expect( actual.getRangeFacetQuerySegments( '', '', 'P1' ) )
			.toEqual( [] );
	} );

	test( 'Query segements for range facet with only min value', () => {
		expect( actual.getRangeFacetQuerySegments( '10', '', 'P1' ) )
			.toEqual( [
				'haswbfacet:P1>=10'
			] );
	} );

	test( 'Query segements for range facet with only max value', () => {
		expect( actual.getRangeFacetQuerySegments( '', '99', 'P1' ) )
			.toEqual( [
				'haswbfacet:P1<=99'
			] );
	} );

	test( 'Query segements for range facet with both min and max value', () => {
		expect( actual.getRangeFacetQuerySegments( '10', '99', 'P1' ) )
			.toEqual( [
				'haswbfacet:P1>=10',
				'haswbfacet:P1<=99'
			] );
	} );

	test( 'Query string with new instance query', () => {
		expect( actual.buildQueryString( 'freetext haswbstatement:P1=Q10 haswbfacet:P2=Q20 haswbfacet:P3=Q30', [ 'haswbstatement:P1=Q11' ] ) )
			.toEqual( 'freetext haswbstatement:P1=Q11' );
	} );

	test( 'Query string with new facet query', () => {
		expect( actual.buildQueryString( 'freetext haswbstatement:P1=Q10 haswbfacet:P2=Q20 haswbfacet:P3=Q30', [ 'haswbfacet:P2=Q21' ], 'P2' ) )
			.toEqual( 'freetext haswbstatement:P1=Q10 haswbfacet:P3=Q30 haswbfacet:P2=Q21' );
	} );
} );
