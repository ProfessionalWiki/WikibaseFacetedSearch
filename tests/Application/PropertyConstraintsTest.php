<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints
 */
final class PropertyConstraintsTest extends TestCase {

	private const PROPERTY_ID = 'P1';

	private PropertyConstraints $constraints;

	protected function setUp(): void {
		$this->constraints = new PropertyConstraints(
			new NumericPropertyId( self::PROPERTY_ID )
		);
	}

	public function testNewConstraintsHasNoValues(): void {
		$this->assertFalse( $this->constraints->hasAnyValue() );
		$this->assertFalse( $this->constraints->hasNoValue() );
		$this->assertSame( [], $this->constraints->getAndSelectedValues() );
		$this->assertSame( [], $this->constraints->getOrSelectedValues() );
		$this->assertNull( $this->constraints->getInclusiveMinimum() );
		$this->assertNull( $this->constraints->getInclusiveMaximum() );
	}

	public function testRequiringAnyValue(): void {
		$newConstraints = $this->constraints->requireAnyValue();

		$this->assertNotSame( $this->constraints, $newConstraints );
		$this->assertFalse( $this->constraints->hasAnyValue() );
		$this->assertTrue( $newConstraints->hasAnyValue() );
		$this->assertFalse( $newConstraints->hasNoValue() );
	}

	public function testRequiringNoValue(): void {
		$newConstraints = $this->constraints->requireNoValue();

		$this->assertNotSame( $this->constraints, $newConstraints );
		$this->assertFalse( $this->constraints->hasNoValue() );
		$this->assertTrue( $newConstraints->hasNoValue() );
		$this->assertFalse( $newConstraints->hasAnyValue() );
	}

	public function testAddingAndValue(): void {
		$newConstraints = $this->constraints->withAdditionalAndValue( 'foo' );

		$this->assertNotSame( $this->constraints, $newConstraints );
		$this->assertSame( [], $this->constraints->getAndSelectedValues() );
		$this->assertSame( [ 'foo' ], $newConstraints->getAndSelectedValues() );
	}

	public function testAddingMultipleAndValues(): void {
		$constraints = $this->constraints
			->withAdditionalAndValue( 'foo' )
			->withAdditionalAndValue( 'bar' );

		$this->assertSame( [ 'foo', 'bar' ], $constraints->getAndSelectedValues() );
	}

	public function testAddingDuplicateAndValueReturnsOriginalObject(): void {
		$constraints = $this->constraints->withAdditionalAndValue( 'foo' );
		$sameConstraints = $constraints->withAdditionalAndValue( 'foo' );

		$this->assertSame( $constraints, $sameConstraints );
	}

	public function testAddingOrValues(): void {
		$newConstraints = $this->constraints->withOrValues( 'foo', 'bar' );

		$this->assertNotSame( $this->constraints, $newConstraints );
		$this->assertSame( [], $this->constraints->getOrSelectedValues() );
		$this->assertSame( [ 'foo', 'bar' ], $newConstraints->getOrSelectedValues() );
	}

	public function testAddingDuplicateOrValuesRemovesDuplicates(): void {
		$constraints = $this->constraints->withOrValues( 'foo', 'foo', 'bar' );

		$this->assertSame( [ 'foo', 'bar' ], $constraints->getOrSelectedValues() );
	}

	public function testSettingMinValue(): void {
		$newConstraints = $this->constraints->withInclusiveMinimum( 42.0 );

		$this->assertNotSame( $this->constraints, $newConstraints );
		$this->assertNull( $this->constraints->getInclusiveMinimum() );
		$this->assertSame( 42.0, $newConstraints->getInclusiveMinimum() );
	}

	public function testSettingMaxValue(): void {
		$newConstraints = $this->constraints->withInclusiveMaximum( 42.0 );

		$this->assertNotSame( $this->constraints, $newConstraints );
		$this->assertNull( $this->constraints->getInclusiveMaximum() );
		$this->assertSame( 42.0, $newConstraints->getInclusiveMaximum() );
	}

	public function testConstructWithInitialValues(): void {
		$constraints = new PropertyConstraints(
			new NumericPropertyId( self::PROPERTY_ID ),
			[ 'foo', 'bar' ],
			[ 'baz', 'qux' ],
			42.0,
			123.0
		);

		$this->assertSame( [ 'foo', 'bar' ], $constraints->getAndSelectedValues() );
		$this->assertSame( [ 'baz', 'qux' ], $constraints->getOrSelectedValues() );
		$this->assertSame( 42.0, $constraints->getInclusiveMinimum() );
		$this->assertSame( 123.0, $constraints->getInclusiveMaximum() );
	}

}
