const actual = require( '../../resources/dialog.js' );

beforeEach( () => {
	const button = document.createElement( 'button' );
	const content = document.createElement( 'div' );
	const originalTarget = document.createElement( 'div' );
	const teleportTarget = document.createElement( 'div' );

	originalTarget.appendChild( content );

	this.dialog = new actual.Dialog( button, content, teleportTarget );
} );

describe( 'close', () => {
	beforeEach( () => {
		this.dialog.init();
		this.dialog.open();
	} );

	test( 'wikibase-faceted-search__dialog class is removed', () => {
		this.dialog.close();
		expect( this.dialog.content.classList.contains( 'wikibase-faceted-search__dialog' ) ).toBe( false );
	} );

	test( 'backdrop is removed from teleportTarget', () => {
		this.dialog.close();
		expect( this.dialog.backdrop.parentNode === this.dialog.teleportTarget ).toBe( false );
	} );

	test( 'content is removed from backdrop', () => {
		this.dialog.close();
		expect( this.dialog.content.parentNode === this.dialog.backdrop ).toBe( false );
	} );

	test( 'opened is set to false', () => {
		this.dialog.close();
		expect( this.dialog.opened ).toBe( false );
	} );

	test( 'document event listener is removed', () => {
		const spy = jest.spyOn( document, 'removeEventListener' );
		this.dialog.close();
		expect( spy ).toHaveBeenCalledWith( 'click', this.dialog.onClickOutside );
	} );
} );

describe( 'open', () => {
	beforeEach( () => {
		this.dialog.init();
	} );

	test( 'wikibase-faceted-search__dialog class is added', () => {
		this.dialog.open();
		expect( this.dialog.content.classList.contains( 'wikibase-faceted-search__dialog' ) ).toBe( true );
	} );

	test( 'backdrop is appened to teleportTarget', () => {
		this.dialog.open();
		expect( this.dialog.backdrop.parentNode === this.dialog.teleportTarget ).toBe( true );
	} );

	test( 'content is appended to backdrop', () => {
		this.dialog.open();
		expect( this.dialog.content.parentNode === this.dialog.backdrop ).toBe( true );
	} );

	test( 'opened is set to true', () => {
		this.dialog.open();
		expect( this.dialog.opened ).toBe( true );
	} );

	test( 'document event listener is added', () => {
		const spy = jest.spyOn( document, 'addEventListener' );
		this.dialog.open();
		expect( spy ).toHaveBeenCalledWith( 'click', this.dialog.onClickOutside );
	} );
} );
