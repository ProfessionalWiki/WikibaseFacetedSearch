const actual = require( '../../resources/ext.wikibase.facetedsearch.js' );

describe( 'getListFacetQueryMode', () => {
	test( 'Get list facet query mode when AND is selected', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-toggle">
				<button class="cdx-button--action-progressive" value="AND"></button>
				<button value="OR"></button>
			</div>
		`;

		expect( actual.getListFacetQueryMode( document.body ) )
			.toEqual( 'AND' );
	} );

	test( 'Get list facet query mode when OR is selected', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-toggle">
				<button value="AND"></button>
				<button class="cdx-button--action-progressive" value="OR"></button>
			</div>
		`;

		expect( actual.getListFacetQueryMode( document.body ) )
			.toEqual( 'OR' );
	} );

	test( 'Get list facet query mode when selectedButton is missing', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-toggle">
				<button value="AND"></button>
				<button value="OR"></button>
			</div>
		`;

		expect( actual.getListFacetQueryMode( document.body ) )
			.toEqual( 'AND' );
	} );
} );

describe( 'getListFacetSelectedValues', () => {
	test( 'List facet with no checked items', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q1"></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q2"></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q3"></div>
		`;

		expect( actual.getListFacetSelectedValues( document.body ) )
			.toEqual( [] );
	} );

	test( 'List facet with multiple checked items', () => {
		document.body.innerHTML = `
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q1"></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q2" checked></div>
			<div class="wikibase-faceted-search__facet-item"><input class="cdx-checkbox__input" type="checkbox" value="Q3" checked></div>
		`;

		expect( actual.getListFacetSelectedValues( document.body ) )
			.toEqual( [ 'Q2', 'Q3' ] );
	} );
} );

describe( 'getListFacetQuerySegments', () => {
	test( 'Query segements for list facet with no selected values', () => {
		expect( actual.getListFacetQuerySegments( [], 'P1' ) )
			.toEqual( [ 'haswbfacet:P1=' ] );
	} );

	test( 'Query segements for list facet with single selected value in AND mode', () => {
		expect( actual.getListFacetQuerySegments( [ 'Q2' ], 'P1', 'AND' ) )
			.toEqual( [ 'haswbfacet:P1=Q2' ] );
	} );

	test( 'Query segements for list facet with multiple selected values in AND mode', () => {
		expect( actual.getListFacetQuerySegments( [ 'Q2', 'Q3' ], 'P1', 'AND' ) )
			.toEqual( [ 'haswbfacet:P1=Q2', 'haswbfacet:P1=Q3' ] );
	} );

	test( 'Query segements for list facet with single selected value in OR mode', () => {
		expect( actual.getListFacetQuerySegments( [ 'Q2' ], 'P1', 'OR' ) )
			.toEqual( [ 'haswbfacet:P1=Q2' ] );
	} );

	test( 'Query segements for list facet with multiple selected values in OR mode', () => {
		expect( actual.getListFacetQuerySegments( [ 'Q2', 'Q3' ], 'P1', 'OR' ) )
			.toEqual( [ 'haswbfacet:P1=Q2|Q3' ] );
	} );
} );

describe( 'getRangeFacetQuerySegments', () => {
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
		expect(
			actual.buildQueryString(
				'freetext haswbfacet:P1=Q10 haswbfacet:P2=Q20 haswbfacet:P3=Q30',
				[ 'haswbfacet:P1=Q11' ]
			)
		).toEqual( 'freetext haswbfacet:P1=Q11' );
	} );

	test( 'Query string with new facet query', () => {
		expect(
			actual.buildQueryString(
				'freetext haswbfacet:P1=Q10 haswbfacet:P2=Q20 haswbfacet:P3=Q30',
				[ 'haswbfacet:P2=Q21' ],
				'P2'
			)
		).toEqual( 'freetext haswbfacet:P1=Q10 haswbfacet:P3=Q30 haswbfacet:P2=Q21' );
	} );
} );

describe( 'buildQueryString', () => {
	test( 'Query string with new instance query', () => {
		expect(
			actual.buildQueryString(
				'freetext haswbfacet:P1=Q10 haswbfacet:P2=Q20 haswbfacet:P3=Q30',
				[ 'haswbfacet:P1=Q11' ]
			)
		).toEqual( 'freetext haswbfacet:P1=Q11' );
	} );

	test( 'Query string with new facet query', () => {
		expect(
			actual.buildQueryString(
				'freetext haswbfacet:P1=Q10 haswbfacet:P2=Q20 haswbfacet:P3=Q30',
				[ 'haswbfacet:P2=Q21' ],
				'P2'
			)
		).toEqual( 'freetext haswbfacet:P1=Q10 haswbfacet:P3=Q30 haswbfacet:P2=Q21' );
	} );
} );
