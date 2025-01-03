<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\Query
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints
 */
final class QueryTest extends TestCase {

	public function testPropertyConstraintsCanBeRetrieved(): void {
		$p1Constraint = new PropertyConstraints( new NumericPropertyId( 'P1' ) );
		$p2Constraint = new PropertyConstraints( new NumericPropertyId( 'P2' ) );
		$p3Constraint = new PropertyConstraints( new NumericPropertyId( 'P3' ) );

		$query = new Query( new PropertyConstraintsList( $p1Constraint, $p2Constraint, $p3Constraint ) );

		$this->assertEquals(
			$p2Constraint,
			$query->getConstraintsForProperty( new NumericPropertyId( 'P2' ) )
		);
	}

	public function testGetUnknownPropertyReturnsNull(): void {
		$p1Constraint = new PropertyConstraints( new NumericPropertyId( 'P1' ) );
		$query = new Query( new PropertyConstraintsList( $p1Constraint ) );

		$this->assertNull(
			$query->getConstraintsForProperty( new NumericPropertyId( 'P2' ) )
		);
	}

	public function testGetConstraintsPerProperty(): void {
		$p1Constraint = new PropertyConstraints( new NumericPropertyId( 'P1' ) );
		$p2Constraint = new PropertyConstraints( new NumericPropertyId( 'P2' ) );
		$p3Constraint = new PropertyConstraints( new NumericPropertyId( 'P3' ) );

		$query = new Query( new PropertyConstraintsList( $p1Constraint, $p2Constraint, $p3Constraint ) );

		$this->assertEquals(
			[
				'P1' => $p1Constraint,
				'P2' => $p2Constraint,
				'P3' => $p3Constraint,
			],
			$query->getConstraintsPerProperty()
		);
	}

	public function testLatestConstraintOverridesPrevious(): void {
		$p1ConstraintA = new PropertyConstraints( new NumericPropertyId( 'P1' ), min: 42 );
		$p1ConstraintB = new PropertyConstraints( new NumericPropertyId( 'P1' ), min: 9001 );

		$query = new Query( new PropertyConstraintsList( $p1ConstraintA, $p1ConstraintB ) );

		$this->assertEquals(
			$p1ConstraintB,
			$query->getConstraintsForProperty( new NumericPropertyId( 'P1' ) )
		);
	}

}
