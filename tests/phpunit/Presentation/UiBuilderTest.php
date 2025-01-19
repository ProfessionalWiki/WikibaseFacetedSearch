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
use RuntimeException;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class UiBuilderTest extends TestCase {

	public function testIntegrationSmoke(): void {
		try {
			WikibaseFacetedSearchExtension::getInstance()->getConfig()->getInstanceOfId();
		} catch ( RuntimeException ) {
			$this->markTestSkipped( 'No valid config available' );
		}

		$html = WikibaseFacetedSearchExtension::getInstance()->getUiBuilder()->createHtml( 'foo' );
		$this->assertStringContainsString( 'topbar', $html );
		$this->assertStringContainsString( 'sidebar', $html );
	}

	public function testTabsViewModelContainsItemTypeProperty(): void {
		$config = new Config( instanceOfId: new NumericPropertyId( 'P1337' ) );
		$templatePsy = new SpyTemplateParser();

		$this->newUiBuilder( config: $config, templatePsy: $templatePsy )
			->createHtml( 'unimportant' );

		$this->assertSame(
			'P1337',
			$templatePsy->getArgs()['instanceId']
		);
	}

	private function newUiBuilder(
		?Config $config = null,
		?SpyTemplateParser $templatePsy = null
	): UiBuilder {
		return new UiBuilder(
			$config ?? new Config(),
			new SpyFacetHtmlBuilder(),
			$templatePsy ?? new SpyTemplateParser(),
			new StubQueryStringParser()
		);
	}

	public function testTabsViewModelContainsItemTypes(): void {
		$config = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer()->deserialize( <<<JSON
{
	"instanceOfId": "P1337",
	"instanceOfValues": {
		"Q5976445": {
			"label": "People",
			"facets": {
				"P592": {
					"type": "list"
				},
				"P593": {
					"type": "range"
				}
			}
		},
		"Q5976449": {
			"label": "Documents",
			"facets": {
				"P22": {
					"type": "list"
				}
			}
		}
	}
}
JSON );

		$templatePsy = new SpyTemplateParser();

		$this->newUiBuilder( config: $config, templatePsy: $templatePsy )
			->createHtml( 'unimportant' );

		$this->assertEquals(
			[
				[
					'label' => 'All',
					'value' => '',
					'selected' => 'true'
				],
				[
					'label' => 'Q5976445',
					'value' => 'Q5976445',
					'selected' => 'false'
				],
				[
					'label' => 'Q5976449',
					'value' => 'Q5976449',
					'selected' => 'false'
				],
			],
			$templatePsy->getArgs()['instances']
		);
	}

}
