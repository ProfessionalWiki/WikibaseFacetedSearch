<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use Wikibase\DataModel\Entity\NumericPropertyId;

final class QueryStringParser {

	public function parse( string $queryString ): Query {
		$constraints = new PropertyConstraintsList();
		$freeText = [];

		foreach ( $this->splitQueryString( $queryString ) as $part ) {
			if ( $this->isFacetPart( $part ) ) {
				$constraints = $constraints->withConstraint( $this->handleFacetPart( $part, $constraints ) );
			}
			else {
				$freeText[] = $part;
			}
		}

		return new Query(
			$constraints,
			implode( ' ', $freeText )
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

	private function isFacetPart( string $part ): bool {
		return str_starts_with( $part, 'haswbfacet:' )
			|| str_starts_with( $part, '-haswbfacet:' );
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