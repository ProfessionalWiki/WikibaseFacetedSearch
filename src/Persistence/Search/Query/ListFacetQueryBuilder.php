<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Query;

use DateTime;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Exists;
use Elastica\Query\Terms;
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

		$values = array_filter(
			$this->getFacetValues( $constraints ),
			fn( $value ) => $value !== ''
		);

		if ( $values === [] ) {
			return null;
		}

		return match ( $this->dataTypeLookup->getDataTypeIdForProperty( $config->propertyId ) ) {
			'string' => $this->buildStringQuery( $name, $values ),
			'wikibase-item' => $this->buildStringQuery( $name, $values ),
			'quantity' => $this->buildQuantityQuery( $name, $values ),
			'time' => $this->buildTimeQuery( $name, $values ),
			default => null
		};
	}

	private function buildAnyValueQuery( string $name ): AbstractQuery {
		return new Exists( $name );
	}

	private function getFacetValues( PropertyConstraints $constraints ): array {
		if ( $constraints->getOrSelectedValues() !== [] ) {
			return $constraints->getOrSelectedValues();
		}

		return $constraints->getAndSelectedValues();
	}

	private function buildStringQuery( string $name, array $values ): AbstractQuery {
		return new Terms(
			$name,
			$values
		);
	}

	private function buildQuantityQuery( string $name, array $values ): AbstractQuery {
		return new Terms(
			$name,
			array_map(
				fn( $value ) => (float)$value,
				$values
			)
		);
	}

	private function buildTimeQuery( string $name, array $values ): AbstractQuery {
		return new Terms(
			$name,
			array_map(
				fn( $value ) => $this->timestampToIso8601( (int)$value ),
				$values
			)
		);
	}

	private function timestampToIso8601( int $timestampInMilliseconds ): string {
		$seconds = $timestampInMilliseconds / 1000;
		return ( new DateTime( "@$seconds" ) )->format( 'Y-m-d\TH:i:s\Z' );
	}

}
