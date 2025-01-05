<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use DateTime;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;

class ListFacetQueryBuilder implements FacetQueryBuilder {

	public function __construct(
		private readonly PropertyDataTypeLookup $dataTypeLookup
	) {
	}

	public function buildQuery( FacetConfig $config, PropertyConstraints $constraints ): ?AbstractQuery {
		$name = 'wbfs_' . $config->propertyId->getSerialization();

		if ( $constraints->hasAnyValue() ) {
			return $this->buildAnyValueQuery( $name );
		}

		return match ( $this->dataTypeLookup->getDataTypeIdForProperty( $config->propertyId ) ) {
			'string' => $this->buildStringQuery( $name, $constraints ),
			'wikibase-item' => $this->buildStringQuery( $name, $constraints ),
			'quantity' => $this->buildQuantityQuery( $name, $constraints ),
			'time' => $this->buildTimeQuery( $name, $constraints ),
			default => null
		};
	}

	private function buildAnyValueQuery( string $name ): AbstractQuery {
		return new Query\Exists( $name );
	}

	private function buildStringQuery( string $name, PropertyConstraints $constraints ): AbstractQuery {
		return new Query\Terms(
			$name,
			$this->getFacetValues( $constraints )
		);
	}

	private function getFacetValues( PropertyConstraints $constraints ): array {
		if ( $constraints->getOrSelectedValues() !== [] ) {
			return $constraints->getOrSelectedValues();
		}

		return $constraints->getAndSelectedValues();
	}

	private function buildQuantityQuery( string $name, PropertyConstraints $constraints ): AbstractQuery {
		return new Query\Terms(
			$name,
			array_map(
				fn( $value ) => (float)$value,
				$this->getFacetValues( $constraints )
			)
		);
	}

	private function buildTimeQuery( string $name, PropertyConstraints $constraints ): AbstractQuery {
		return new Query\Terms(
			$name,
			array_map(
				fn( $value ) => $this->timestampToIso8601( (int)$value ),
				$this->getFacetValues( $constraints )
			)
		);
	}

	private function timestampToIso8601( int $timestampInMilliseconds ): string {
		$seconds = $timestampInMilliseconds / 1000;
		return ( new DateTime( "@$seconds" ) )->format( 'Y-m-d\TH:i:s\Z' );
	}

}
