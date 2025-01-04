<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnboundedQuantityValue;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\DataValueTranslator;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\DataValueTranslator
 */
class DataValueTranslatorTest extends TestCase {

	public function testTranslatesUnboundedQuantityValue(): void {
		$value = UnboundedQuantityValue::newFromNumber( 42 );
		$this->assertSame( 42.0, $this->newTranslator()->translate( $value ) );
	}

	private function newTranslator(): DataValueTranslator {
		return new DataValueTranslator();
	}

	public function testTranslatesStringValue(): void {
		$value = new StringValue( 'Foo' );
		$this->assertSame( 'Foo', $this->newTranslator()->translate( $value ) );
	}

	public function testTranslatesTimeValue(): void {
		$value = new TimeValue(
			'+2020-01-01T00:00:00Z',
			0,
			0,
			0,
			TimeValue::PRECISION_DAY,
			TimeValue::CALENDAR_GREGORIAN
		);

		$this->assertSame(
			'2020-01-01T00:00:00Z',
			$this->newTranslator()->translate( $value )
		);
	}

	public function testTranslatesEntityIdValue(): void {
		$itemId = new ItemId( 'Q42' );
		$value = new EntityIdValue( $itemId );

		$this->assertSame( 'Q42', $this->newTranslator()->translate( $value ) );
	}

	public function testReturnsNullForUnsupportedValue(): void {
		$value = new MultilingualTextValue( [
			new MonolingualTextValue( 'en', 'Foo' ),
			new MonolingualTextValue( 'de', 'Bar' ),
		] );
		$this->assertNull( $this->newTranslator()->translate( $value ) );
	}

}
