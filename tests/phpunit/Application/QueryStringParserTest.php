<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser
 */
class QueryStringParserTest extends TestCase {

	private const INSTANCE_TYPE_ID = 'P90';

	/**
	 * @dataProvider freeTextProvider
	 */
	public function testParsesFreeText( string $queryString, string $expectedFreeText ): void {
		$this->assertSame(
			$expectedFreeText,
			( $this->newQueryStringParser() )->parse( $queryString )->getFreeText()
		);
	}

	public function freeTextProvider(): iterable {
		yield 'no constraints' => [ '', '' ];
		yield 'single word' => [ 'kittens', 'kittens' ];
		yield 'multiple words' => [ 'fluffy kittens', 'fluffy kittens' ];
		yield 'omits keywords' => [ 'haswbfacet:P1=foo fluffy -haswbfacet:P2 kittens haswbfacet:P3=A|B|C', 'fluffy kittens' ];
		// TODO: should other keywords be omitted?
	}

	public function testParsesExistenceConstraint(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertTrue( $constraints->hasAnyValue() );
	}

	public function testParsesNonExistenceConstraint(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( '-haswbfacet:P42' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertTrue( $constraints->hasNoValue() );
	}

	public function testParsesItemTypes(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbstatement:' . self::INSTANCE_TYPE_ID . '=Q68' );

		$itemTypes = [
			new ItemId( 'Q68' )
		];

		$this->assertEquals( $itemTypes, $query->getInstanceItemTypes() );
	}

	public function testParsesAndValues(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42=foo haswbfacet:P42=bar' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertEquals(
			[ 'foo', 'bar' ],
			$constraints->getAndSelectedValues()
		);
	}

	public function testParsesOrValues(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42=foo|bar|baz' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertEquals(
			[ 'foo', 'bar', 'baz' ],
			$constraints->getOrSelectedValues()
		);
	}

	public function testParsesMinimum(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42>=42' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertSame( 42.0, $constraints->getInclusiveMinimum() );
	}

	public function testParsesMaximum(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42<=9001' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertSame( 9001.0, $constraints->getInclusiveMaximum() );
	}

	public function testParsesRange(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42>=42 haswbfacet:P1=unrelated haswbfacet:P42<=9001' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertSame( 42.0, $constraints->getInclusiveMinimum() );
		$this->assertSame( 9001.0, $constraints->getInclusiveMaximum() );
	}

	public function testHandlesMixedConstraintsAndFreeText(): void {
		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'kittens haswbfacet:P42=cute cats haswbfacet:P23>=9001' );

		$this->assertSame( 'kittens cats', $query->getFreeText() );

		$p42Constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );
		$this->assertEquals( [ 'cute' ], $p42Constraints->getAndSelectedValues() );

		$p23Constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P23' ) );
		$this->assertSame( 9001.0, $p23Constraints->getInclusiveMinimum() );
	}

	public function testHandlesSingleQuotedString(): void {
		$this->markTestSkipped( 'TODO' );

		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42="foo bar" baz' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertEquals(
			[ 'foo bar' ],
			$constraints->getAndSelectedValues()
		);
		$this->assertSame( 'baz', $query->getFreeText() );
	}

	public function testHandlesOrQuotedStrings(): void {
		$this->markTestSkipped( 'TODO' );

		$parser = $this->newQueryStringParser();
		$query = $parser->parse( 'haswbfacet:P42="foo bar"|second|"third value" baz' );

		$constraints = $query->getConstraintsForProperty( new NumericPropertyId( 'P42' ) );

		$this->assertEquals(
			[ 'foo bar', 'second', 'third value' ],
			$constraints->getOrSelectedValues()
		);
		$this->assertSame( 'baz', $query->getFreeText() );
	}

	private function newQueryStringParser(): QueryStringParser {
		return new QueryStringParser(
			instanceType: new Config(
				instanceOfId: new NumericPropertyId( INSTANCE_TYPE_ID )
			)->getInstanceOfId()
		);
	}

}
