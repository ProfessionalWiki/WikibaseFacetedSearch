<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\Valid;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator
 */
class ConfigJsonValidatorTest extends TestCase {

	public function testEmptyJsonPassesValidation(): void {
		$this->assertTrue(
			ConfigJsonValidator::newInstance()->validate( '{}' )
		);
	}

	public function testValidJsonPassesValidation(): void {
		$this->assertTrue(
			ConfigJsonValidator::newInstance()->validate( Valid::configJson() )
		);
	}

	public function testStructurallyInvalidJsonFailsValidation(): void {
		$this->assertFalse(
			ConfigJsonValidator::newInstance()->validate( '}{' )
		);
	}

	public function testInvalidJsonErrorsAreAvailable(): void {
		$validator = ConfigJsonValidator::newInstance();

		$validator->validate( '{ "linkTargetSitelinkSiteId": true }' );

		$this->assertSame(
			[ '/linkTargetSitelinkSiteId' => 'The data (boolean) must match the type: string, null' ],
			$validator->getErrors()
		);
	}

}
