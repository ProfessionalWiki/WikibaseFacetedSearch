<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

class QueryStringParser {

	public function parse( string $queryString ): Query {
		$constraints = new PropertyConstraintsList();
		$freeText = [];
		$instance = [];

		foreach ( $this->splitQueryString( $queryString ) as $part ) {
			if ( $this->isFacetPart( $part ) ) {
				$constraints = $constraints->withConstraint( $this->handleFacetPart( $part, $constraints ) );
			} elseif ( $this->isInstancePart( $part ) ) {
				$instance = $this->handleInstancePart( $part, $instance );
			}
			else {
				$freeText[] = $part;
			}
		}

		return new Query(
			$constraints,
			implode( ' ', $freeText ),
			$instance
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

	private function isInstancePart( string $part ): bool {
		return str_starts_with( $part, 'haswbstatement:' );
	}

	private function isFacetPart( string $part ): bool {
		return str_starts_with( $part, 'haswbfacet:' )
			|| str_starts_with( $part, '-haswbfacet:' );
	}

	/**
	 * @param array<string, NumericPropertyId|ItemId> $instance
	 * @return array<string, NumericPropertyId|ItemId>
	 */
	private function handleInstancePart( string $part, array &$instance ): array {
		$part = substr( $part, strlen( 'haswbstatement:' ) );
		[ $propertyIdString, $itemIdString ] = explode( '=', $part, 2 );

		if ( $propertyIdString === '' || $itemIdString === '' ) {
			return [];
		}

		try {
			$instance['propertyId'] = new NumericPropertyId( $propertyIdString );
			$instance['itemId'] = new ItemId( $itemIdString );
		} catch ( InvalidArgumentException ) {
			return [];
		}

		return $instance;
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
