<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyTemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubQueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class UiBuilderTest extends TestCase {

	public function testIntegrationSmoke(): void {
		$html = WikibaseFacetedSearchExtension::getInstance()->getUiBuilder()->createHtml( 'foo' );
		$this->assertStringContainsString( 'topbar', $html );
		$this->assertStringContainsString( 'sidebar', $html );
	}

	public function testTabsViewModelContainsItemTypeProperty(): void {
		$config = new Config( instanceOfId: new NumericPropertyId( 'P1337' ) );
		$templatePsy = new SpyTemplateParser();

		$uiBuilder = new UiBuilder(
			$config,
			new SpyFacetHtmlBuilder(),
			$templatePsy,
			new StubQueryStringParser()
		);

		$uiBuilder->createHtml('unimportant' );

		$this->assertSame(
			'P1337',
			$templatePsy->getArgs()['instanceId']
		);
	}

}
