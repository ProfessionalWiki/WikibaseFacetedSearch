<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use Elastica\Query\AbstractQuery;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCount;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounter;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ValueCounts;
use Wikibase\DataModel\Entity\PropertyId;

class StubValueCounter implements ValueCounter {

	public const FIRST_VALUE = 'Bob';
	public const FIRST_COUNT = 5;

	public const SECOND_VALUE = 'Alice';
	public const SECOND_COUNT = 3;

	public const THIRD_VALUE = 'Charlie';
	public const THIRD_COUNT = 1;

	public function countValues( PropertyId $property, AbstractQuery $currentQuery): ValueCounts {
		return new ValueCounts( [
			new ValueCount( self::FIRST_VALUE, self::FIRST_COUNT ),
			new ValueCount( self::SECOND_VALUE, self::SECOND_COUNT ),
			new ValueCount( self::THIRD_VALUE, self::THIRD_COUNT ),
		] );
	}

}
