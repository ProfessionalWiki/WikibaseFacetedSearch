<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;

class DelegatingFacetQueryBuilder implements FacetQueryBuilder {

	/**
	 * @var array<string, FacetQueryBuilder>
	 */
	private array $buildersPerType;

	public function addBuilder( FacetType $type, FacetQueryBuilder $builder ): void {
		$this->buildersPerType[$type->value] = $builder;
	}

	public function buildQuery( FacetConfig $config, PropertyConstraints $constraints ): ?AbstractQuery {
		if ( !array_key_exists( $config->type->value, $this->buildersPerType ) ) {
			throw new \RuntimeException( 'No query builder for facet type ' . $config->type->value );
		}

		return $this->buildersPerType[$config->type->value]->buildQuery( $config, $constraints );
	}

}
