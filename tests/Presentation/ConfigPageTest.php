<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\WikibaseFacetedSearchIntegrationTest;

/**
 * @group Database
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\WikibaseFacetedSearchHooks
 */
class ConfigPageTest extends WikibaseFacetedSearchIntegrationTest {

	public function tearDown(): void {
		$this->deleteConfigPage();
		parent::tearDown();
	}

	public function testPageShowsPersistedConfig(): void {
		$this->editConfigPage( config: Valid::configJson() );
		$this->assertStringContainsString(
			'instanceOfId',
			$this->getPageHtml( 'MediaWiki:WikibaseFacetedSearch' )
		);
	}

	public function testEditingTabShowsDocumentation(): void {
		$html = $this->getEditPageHtml( 'MediaWiki:WikibaseFacetedSearch' );

		// Intro text
		$this->assertStringContainsString(
			'view the configuration documentation',
			$html
		);

		// Documentation section
		$this->assertStringContainsString(
			'Configuration documentation',
			$html
		);
	}

	public function testEditingTabShowsDefaultValues(): void {
		$html = $this->getEditPageHtml( 'MediaWiki:WikibaseFacetedSearch' );

		$this->assertStringContainsString(
			'"instanceOfId": null',
			$html
		);
	}

}
