<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use Elastica\Query\AbstractQuery;
use Elastica\Query\Range;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

class RangeFacetQueryBuilder implements FacetQueryBuilder {

	public function __construct(
		private readonly PropertyDataTypeLookup $dataTypeLookup
	) {
	}

	public function buildQuery( FacetConfig $config, PropertyConstraints $constraints ): ?AbstractQuery {
		$name = 'wbfs_' . $config->propertyId->getSerialization();

		return match ( $this->dataTypeLookup->getDataTypeIdForProperty( $config->propertyId ) ) {
			'quantity' => $this->buildQuantityQuery( $name, $constraints ),
			'time' => $this->buildTimeQuery( $name, $constraints ),
			default => null
		};
	}

	private function buildQuantityQuery( string $name, PropertyConstraints $constraints ): AbstractQuery {
		return new Range(
			$name,
			[
				'gte' => $constraints->getInclusiveMinimum(),
				'lte' => $constraints->getInclusiveMaximum()
			]
		);
	}

	private function buildTimeQuery( string $name, PropertyConstraints $constraints ): AbstractQuery {
		return new Range(
			$name,
			[
				'gte' => $constraints->getInclusiveMinimum() === null ? null : (int)$constraints->getInclusiveMinimum() . '-01-01',
				'lte' => $constraints->getInclusiveMaximum() === null ? null : (int)$constraints->getInclusiveMaximum() . '-12-31',
			]
		);
	}

}
