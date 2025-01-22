class Dialog {
	constructor( button, content, teleportTarget ) {
		this.button = button;
		this.content = content;
		this.teleportTarget = teleportTarget;
		this.originalTarget = content.parentNode;
		this.opened = false;
		this.onButtonClick = this.onButtonClick.bind( this );
		this.onClickOutside = this.onClickOutside.bind( this );
	}

	init() {
		const backdrop = document.createElement( 'div' );
		backdrop.classList.add( 'wikibase-faceted-search__dialog-backdrop' );
		this.backdrop = backdrop;

		this.button.addEventListener( 'click', this.onButtonClick );
	}

	onButtonClick() {
		if ( this.opened ) {
			this.close();
		} else {
			this.open();
		}
	}

	close() {
		this.content.classList.remove( 'wikibase-faceted-search__dialog' );
		this.originalTarget.appendChild( this.content );
		this.backdrop.remove();
		this.opened = false;
		document.removeEventListener( 'click', this.onClickOutside );
	}

	open() {
		this.content.classList.add( 'wikibase-faceted-search__dialog' );
		this.backdrop.appendChild( this.content );
		this.teleportTarget.appendChild( this.backdrop );
		this.opened = true;
		document.addEventListener( 'click', this.onClickOutside );
	}

	onClickOutside( event ) {
		if (
			!this.opened ||
			this.content.contains( event.target ) ||
			this.button.contains( event.target )
		) {
			return;
		}
		this.close();
	}
}

/**
 * Initializes a new Dialog instance with the provided button, content, and teleport target.
 * Attaches event listeners to the button to toggle the dialog's open/close state.
 *
 * @param {HTMLButtonElement} button - The button element that triggers the dialog.
 * @param {HTMLElement} content - The content element of the dialog.
 * @param {HTMLDivElement} teleportTarget - The target element where the dialog's backdrop
 * and content will be appended.
 */
function init( button, content, teleportTarget ) {
	if ( !button || !content || !teleportTarget ) {
		return;
	}

	new Dialog( button, content, teleportTarget ).init();
}

module.exports = {
	init: init,
	Dialog: Dialog // For unit tests
};
