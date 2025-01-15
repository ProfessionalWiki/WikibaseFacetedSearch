<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticValueCounter
 */
class ElasticValueCounterTest extends TestCase {

	public function testTODO(): void {
		$counter = WikibaseFacetedSearchExtension::getInstance()->getValueCounter();
		$counter->countValues( new NumericPropertyId( 'P22' ) );
	}

}
