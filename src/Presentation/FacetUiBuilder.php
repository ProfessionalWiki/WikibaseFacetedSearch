<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use Html;
use ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet\Facet;
use ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet\FacetList;
use ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet\FacetValue;

class FacetUiBuilder {

	public function createHtml( FacetList $facets ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'wikibase-faceted-search__facets' ],
			implode( '', array_map( fn( Facet $facet ) => $this->createFacetHtml( $facet ), $facets->asArray() ) )
		);
	}

	private function createFacetHtml( Facet $facet ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'wikibase-faceted-search__facet' ],
			Html::element( 'h3', [], $facet->getLabel() ) .
			$this->createFacetValuesHtml( $facet )
		);
	}

	private function createFacetValuesHtml( Facet $facet ): string {
		return Html::rawElement(
			'ul',
			[],
			implode( '',
				array_map(
					fn( FacetValue $value ) => $this->createFacetValueHtml( $facet, $value ),
					$facet->getValues()->asArray()
				)
			)
		);
	}

	private function createFacetValueHtml( Facet $facet, FacetValue $value ): string {
		$classes = [ 'wikibase-faceted-search__facet-value' ];

		if ( $value->isSelected() ) {
			$classes[] = 'selected';
		}

		return Html::rawElement(
			'li',
			[ 'class' => $classes ],
			Html::rawElement(
				'a',
				[ 'href' => $this->createFacetValueSearchUrl( $facet, $value ) ],
				implode( ' ', [
					$this->createFacetValueCheckbox( $value ),
					$this->createFacetValueLabel( $value ),
					$this->createFacetValueCount( $value )
				] )
			)
		);
	}

	private function createFacetValueSearchUrl( Facet $facet, FacetValue $value ): string {
		if ( $value->isSelected() ) {
			return $facet->getSearchQueryWithoutValue( $value );
		}
		return $facet->getSearchQueryWithValue( $value );
	}

	private function createFacetValueCheckbox( FacetValue $value ): string {
		$attributes = [ 'type' => 'checkbox' ];

		if ( $value->isSelected() ) {
			$attributes['checked'] = 'checked';
		}

		return Html::element( 'input', $attributes );
	}

	private function createFacetValueLabel( FacetValue $value ): string {
		return Html::element(
			'span',
			[ 'class' => 'wikibase-faceted-search__facet-value-label' ],
			$value->getLabel()
		);
	}

	private function createFacetValueCount( FacetValue $value ): string {
		return Html::element(
			'span',
			[ 'class' => 'wikibase-faceted-search__facet-value-count' ],
			'(' . $value->getCount() . ')'
		);
	}

}
