<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Entity\PropertyId;

class QueryStringParser {

	public function __construct(
		private readonly PropertyId $itemTypeProperty
	) {
	}

	public function parse( string $queryString ): Query {
		$constraints = new PropertyConstraintsList();
		$freeText = [];
		$itemTypes = [];

		foreach ( $this->splitQueryString( $queryString ) as $part ) {
			if ( $this->isInstanceOfPart( $part ) ) {
				$itemTypes = $this->extractItemTypes( $part, $itemTypes );
			} elseif ( $this->isFacetPart( $part ) ) {
				$constraints = $constraints->withConstraint( $this->handleFacetPart( $part, $constraints ) );
			}
			else {
				$freeText[] = $part;
			}
		}

		return new Query(
			$constraints,
			implode( ' ', $freeText ),
			$itemTypes
		);
	}

	/**
	 * @return string[]
	 */
	private function splitQueryString( string $queryString ): array {
		preg_match_all( '/"[^"]+"|[^\s]+/', $queryString, $matches );
		return array_map(
			fn( string $part ) => trim( $part, '"' ),
			$matches[0]
		);
	}

	private function isInstanceOfPart( string $part ): bool {
		return str_starts_with( $part, 'haswbfacet:' . $this->itemTypeProperty->getSerialization() . '=' );
	}

	private function isFacetPart( string $part ): bool {
		return str_starts_with( $part, 'haswbfacet:' )
			|| str_starts_with( $part, '-haswbfacet:' );
	}

	/**
	 * @param ItemId[] $itemTypes
	 * @return ItemId[]
	 */
	private function extractItemTypes( string $part, array &$itemTypes ): array {
		$itemTypeStr = substr( $part, strlen( 'haswbfacet:' . $this->itemTypeProperty->getSerialization() . '=' ) );

		if ( $itemTypeStr === '' ) {
			return $itemTypes;
		}

		try {
			$itemTypes[] = new ItemId( $itemTypeStr );
		} catch ( InvalidArgumentException ) {
			return $itemTypes;
		}

		return $itemTypes;
	}

	private function handleFacetPart( string $part, PropertyConstraintsList $constraintsList ): PropertyConstraints {
		$isNegated = str_starts_with( $part, '-' );
		$part = ltrim( $part, '-' );
		$part = substr( $part, strlen( 'haswbfacet:' ) );

		[ $propertyIdString, $constraintString ] = $this->splitPropertyConstraint( $part );

		$propertyConstraints = $constraintsList->getOrCreateConstraints( new NumericPropertyId( $propertyIdString ) );

		if ( $constraintString === '' ) {
			return $isNegated ? $propertyConstraints->requireNoValue() : $propertyConstraints->requireAnyValue();
		}

		$operator = $this->findOperator( $constraintString );
		$value = substr( $constraintString, strlen( $operator ) );

		if ( $operator === '=' ) {
			if ( str_contains( $value, '|' ) ) {
				return $propertyConstraints->withOrValues( ...explode( '|', $value ) );
			}

			return $propertyConstraints->withAdditionalAndValue( $value );
		}

		$numericValue = (float)$value;

		if ( $operator === '>=' || $operator === '>' ) { // TODO: correct handling for non-inclusive > operator
			return $propertyConstraints->withInclusiveMinimum( $numericValue );
		}

		if ( $operator === '<=' || $operator === '<' ) { // TODO: correct handling for non-inclusive < operator
			return $propertyConstraints->withInclusiveMaximum( $numericValue );
		}

		return $propertyConstraints;
	}

	/**
	 * @return array{0: string, 1: string}
	 */
	private function splitPropertyConstraint( string $part ): array {
		$operatorPosition = $this->findFirstOperatorPosition( $part );

		if ( $operatorPosition === false ) {
			return [ $part, '' ];
		}

		return [
			substr( $part, 0, $operatorPosition ),
			substr( $part, $operatorPosition )
		];
	}

	private function findFirstOperatorPosition( string $str ): false|int {
		$positions = [];

		foreach ( [ '>=', '<=', '>', '<', '=' ] as $operator ) {
			$position = strpos( $str, $operator );
			if ( $position !== false ) {
				$positions[$position] = strlen( $operator );
			}
		}

		if ( $positions === [] ) {
			return false;
		}

		return min( array_keys( $positions ) );
	}

	private function findOperator( string $constraint ): string {
		foreach ( [ '>=', '<=', '>', '<', '=' ] as $operator ) {
			if ( str_starts_with( $constraint, $operator ) ) {
				return $operator;
			}
		}

		return '';
	}

}
