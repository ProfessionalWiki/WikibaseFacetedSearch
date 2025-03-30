<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use Elastica\Query\MatchAll;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ElasticValueCounter
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class ElasticValueCounterTest extends TestCase {

	public function testCanExecuteValueCountQuery(): void {
		$counter = WikibaseFacetedSearchExtension::getInstance()->getValueCounter( new MatchAll() );
		$counter->countValues( new PropertyConstraints( new NumericPropertyId( 'P22' ) ) );
		$this->assertTrue( true );
	}

}
