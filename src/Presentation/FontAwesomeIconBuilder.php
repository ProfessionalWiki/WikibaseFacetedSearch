<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\Html;

/**
 * This only supports the CSS Pseudo-elements method for Font Awesome
 * @see https://docs.fontawesome.com/web/add-icons/pseudo-elements
 *
 * TODO: Look into a proper implementation with Extension:FontAwesome
 * after updating that extension.
 */
class FontAwesomeIconBuilder implements IconBuilder {

	/**
	 * Mapping of style names to their short form classes
	 * TODO: Remove this once Extension:FontAwesome is updated to Font Awesome 6
	 *
	 * @see https://docs.fontawesome.com/web/setup/upgrade/whats-changed#full-style-names
	 */
	private const STYLE_SHORT_FORMS = [
		'solid' => 'fas',
		'regular' => 'far',
		'light' => 'fal',
		'thin' => 'fat',
		'duotone' => 'fad',
		'sharp-solid' => 'fass',
		'sharp-regular' => 'fasr',
		'sharp-light' => 'fasl',
		'sharp-thin' => 'fast',
		'brands' => 'fab',
	];

	public function __construct(
		private readonly string $style = 'solid',
		private readonly string $family = 'classic'
	) {
	}

	public function buildHtml( string $iconName, ?array $options = [] ): string {
		$family = $options['family'] ?? $this->family;
		$style = $options['style'] ?? $this->style;

		$classes = [
			'wikibase-faceted-search__icon-fontawesome',
			"fa-$style",
			"fa-$iconName"
		];

		if ( isset( self::STYLE_SHORT_FORMS[$style] ) ) {
			$classes[] = self::STYLE_SHORT_FORMS[$style];
		}

		// Font Awesome omits the family class for the 'classic' style
		if ( $family !== 'classic' ) {
			$classes[] = "fa-$family";
		}

		return Html::element( 'span', [
			'class' => implode( ' ', $classes )
		] );
	}

}
