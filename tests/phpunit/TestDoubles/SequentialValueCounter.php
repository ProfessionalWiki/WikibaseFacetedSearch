<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounts;
use Wikibase\DataModel\Entity\PropertyId;

class SequentialValueCounter implements ValueCounter {

	public function __construct(
		private int $count
	) {
	}

	public function countValues( PropertyId $property, PropertyConstraints $constraints ): ValueCounts {
		if ( $this->count === 0 ) {
			return new ValueCounts( [] );
		}

		return new ValueCounts( ...[
			array_map(
				fn( $i ) => new ValueCount( self::valueFor( $i ), $this->count + 100 ),
				range( 1, $this->count )
			)
		] );
	}

	public static function valueFor( int $index ): string {
		return "Value-$index";
	}

}
