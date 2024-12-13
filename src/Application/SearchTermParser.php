<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

class SearchTermParser {

	public function __construct(
		private readonly Config $config
	) {
	}

	public function parse( string $term ): array {
		preg_match_all( '/[^"\s]*"[^"]*"[^"\s]*|\S*/', $term, $tokens );

		$allValues = [];
		$ambiguous = [];

		foreach ( $tokens[0] as $token ) {
			if ( str_contains( $token, 'haswbstatement:' ) ) {
				$facetValues = $this->parseHasWbStatement( $token );

				foreach ( $facetValues as $facet => $values ) {
					if ( array_key_exists( $facet, $allValues ) ) {
						// TODO: this was OR, but becomes an AND
						$ambiguous[] = $facet;
					} else {
						$allValues[$facet] = array_unique( array_merge( $allValues[$facet] ?? [], $values ) );
					}
				}
			}
		}

		// Remove unconfigured facet values.
		$configuredValues = array_intersect_key( $allValues, array_flip( $this->getUniqueConfiguredFacetIds() ) );

		// Remove ambiguous facet values.
		$safe = array_diff_key( $configuredValues, array_flip( $ambiguous ) );

//		print('<pre>');
//		print("\nAll values:\n");
//		print_r($allValues);
//		print("\nConfigured values:\n");
//		print_r($configuredValues);
//		print("\nAmbiguous:\n");
//		print_r($ambiguous);
//		print("\nSafe:\n");
//		print_r( $safe );
//		print('</pre>');

		return $safe;
	}

	private function parseHasWbStatement( string $token ): array {
		$values = [];

		$parts = explode( ':', $token );
		$negated = str_starts_with( $parts[0], '-' );
		$statementStrings = explode( '|', $parts[1] );

		foreach ( $statementStrings as $statementString ) {
			// TODO: trim only both quotes exist.
			$statementString = trim( $statementString, '"' );
			if ( $this->statementContainsOnlyWildcard( $statementString ) ) {
				// Skip generic "has statement"
				continue;
			}
			if ( $this->statementContainsPropertyOnly( $statementString ) ) {
				// Boolean
				$values[ $statementString . '-' . FacetType::BOOLEAN->value ][] = !$negated;
				continue;
			}
			if ( $this->statementEndsWithWildcard( $statementString ) ) {
				// Boolean
				$statementParts = explode( '=', $statementString );
				$values[ $statementParts[0] . '-' . FacetType::BOOLEAN->value ][] = !$negated;
				continue;
			}
			// List
			$statementParts = explode( '=', $statementString );
			$values[ $statementParts[0] . '-' . FacetType::LIST->value ][] = $statementParts[1];
		}

		return $values;
	}

	private function statementContainsPropertyOnly( $statementString ) {
		if ( !str_contains( $statementString, '=' ) ) {
			return true;
		}
		return false;
	}

	private function statementEndsWithWildcard( $statementString ) {
		if ( str_ends_with( $statementString, '*' ) ) {
			return true;
		}
		return false;
	}

	private function statementContainsOnlyWildcard( $statementString ) {
		return $statementString === '*';
	}

	private function getUniqueConfiguredFacetIds(): array {
		return array_unique(
			array_map(
				fn( FacetConfig $facetConfig ) => $facetConfig->propertyId->getSerialization() . '-' . $facetConfig->type->value,
				$this->config->getFacets()->asFlatArray()
			)
		);
	}

}
