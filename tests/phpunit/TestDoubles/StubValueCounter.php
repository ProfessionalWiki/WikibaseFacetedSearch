<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounts;
use Wikibase\DataModel\Entity\PropertyId;

class StubValueCounter implements ValueCounter {

	public const FIRST_VALUE = 'Bob';
	public const FIRST_COUNT = 9;

	public const SECOND_VALUE = 'Alice';
	public const SECOND_COUNT = 8;

	public const THIRD_VALUE = 'Charlie';
	public const THIRD_COUNT = 7;

	public const FOURTH_VALUE = 'Dave';
	public const FOURTH_COUNT = 6;

	public const FIFTH_VALUE = 'Eve';
	public const FIFTH_COUNT = 5;

	public const SIXTH_VALUE = 'Frank';
	public const SIXTH_COUNT = 4;

	public const SEVENTH_VALUE = 'Grace';
	public const SEVENTH_COUNT = 3;

	public function countValues( PropertyId $property ): ValueCounts {
		return new ValueCounts( [
			new ValueCount( self::FIRST_VALUE, self::FIRST_COUNT ),
			new ValueCount( self::SECOND_VALUE, self::SECOND_COUNT ),
			new ValueCount( self::THIRD_VALUE, self::THIRD_COUNT ),
			new ValueCount( self::FOURTH_VALUE, self::FOURTH_COUNT ),
			new ValueCount( self::FIFTH_VALUE, self::FIFTH_COUNT ),
			new ValueCount( self::SIXTH_VALUE, self::SIXTH_COUNT ),
			new ValueCount( self::SEVENTH_VALUE, self::SEVENTH_COUNT ),
		] );
	}

}
