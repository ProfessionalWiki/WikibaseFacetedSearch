<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;

class DelegatingFacetHtmlBuilder implements FacetHtmlBuilder {

	/**
	 * @var array<string, FacetHtmlBuilder>
	 */
	private array $buildersPerType;

	public function addBuilder( FacetType $type, FacetHtmlBuilder $builder ): void {
		$this->buildersPerType[$type->value] = $builder;
	}

	public function buildHtml( FacetConfig $config, PropertyConstraints $state, AbstractQuery $query ): string {
		if ( !array_key_exists( $config->type->value, $this->buildersPerType ) ) {
			throw new \RuntimeException( 'No builder for facet type ' . $config->type->value );
		}

		return $this->buildersPerType[$config->type->value]->buildHtml( $config, $state, $query );
	}

}
