<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Persistence;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * This test covers the combination of ConfigJsonValidator and config-schema.json.
 *
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Persistence\ConfigJsonValidator
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class ConfigJsonValidatorTest extends TestCase {

	private function newValidator(): ConfigJsonValidator {
		return WikibaseFacetedSearchExtension::getInstance()->newConfigJsonValidator();
	}

	public function testEmptyJsonPassesValidation(): void {
		$this->assertTrue(
			$this->newValidator()->validate( '{}' )
		);
	}

	public function testValidJsonPassesValidation(): void {
		$this->assertTrue(
			$this->newValidator()->validate( Valid::configJson() )
		);
	}

	public function testStructurallyInvalidJsonFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '}{' )
		);
	}

	public function testInvalidJsonErrorsAreAvailable(): void {
		$validator = $this->newValidator();

		$validator->validate( '{ "linkTargetSitelinkSiteId": true }' );

		$this->assertSame(
			[ '/linkTargetSitelinkSiteId' => 'The data (boolean) must match the type: string, null' ],
			$validator->getErrors()
		);
	}

	public function testInvalidSiteIdFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"linkTargetSitelinkSiteId": 123
}
			' )
		);
	}

	public function testInvalidInstanceOfIdFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"instanceOfId": "Q42"
}
			' )
		);
	}

	public function testInvalidFacetsFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"facets": "invalid"
}
			' )
		);
	}

	public function testInvalidFacetItemIdFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"facets": {
		"P1": []
	}
}
			' )
		);
	}

	public function testInvalidFacetConfigStructureFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"facets": {
		"Q1": "invalid"
	}
}
			' )
		);
	}

	public function testInvalidFacetConfigPropertyIdFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"facets": {
		"Q1": [
			{
				"property": "Q1",
				"type": "list"
			}
		]
	}
}
			' )
		);
	}

	public function testInvalidFacetConfigTypeFailsValidation(): void {
		$this->assertFalse(
			$this->newValidator()->validate( '
{
	"facets": {
		"Q1": [
			{
				"property": "P1",
				"type": 42
			}
		]
	}
}
			' )
		);
	}

	public function testExampleConfigIsValid(): void {
		$this->assertTrue(
			$this->newValidator()->validate(
				file_get_contents( WikibaseFacetedSearchExtension::getInstance()->getExampleConfigPath() )
			)
		);
	}

}
