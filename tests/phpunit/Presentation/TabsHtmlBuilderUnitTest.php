<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\TitleFactory;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\TabsHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\Valid;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\FakeItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyTemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubConfigAuthorizer;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubIconBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubQueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\TabsHtmlBuilder
 */
class TabsHtmlBuilderUnitTest extends TestCase {

	public function testTabsViewModelContainsItemTypeProperty(): void {
		$config = new Config( itemTypeProperty: new NumericPropertyId( 'P1337' ) );
		$templateSpy = new SpyTemplateParser();

		$this->newTabsHtmlBuilder( config: $config, templateSpy: $templateSpy )
			->createHtml( 'unimportant' );

		$this->assertSame(
			'P1337',
			$templateSpy->getArgs()['instanceId']
		);
	}

	private function newTabsHtmlBuilder(
		?Config $config = null,
		?SpyTemplateParser $templateSpy = null,
		?QueryStringParser $queryStringParser = null,
		?ConfigAuthorizer $configAuthorizer = null,
		?TitleFactory $titleFactory = null,
		?StubIconBuilder $iconBuilder = null
	): TabsHtmlBuilder {
		return new TabsHtmlBuilder(
			$config ?? new Config(),
			new FakeItemTypeLabelLookup(),
			$templateSpy ?? new SpyTemplateParser(),
			$queryStringParser ?? new StubQueryStringParser(),
			$configAuthorizer ?? $this->newConfigAuthorizer(),
			$titleFactory ?? MediaWikiServices::getInstance()->getTitleFactory(),
			$iconBuilder ?? new StubIconBuilder(),
		);
	}

	private function newConfigAuthorizer( bool $canEditConfig = false ): ConfigAuthorizer {
		return new StubConfigAuthorizer( isAuthorized: $canEditConfig );
	}

	public function testTabsViewModelContainsItemTypes(): void {
		$config = $this->newConfigFromJson( <<<JSON
{
	"itemTypeProperty": "P1337",
	"configPerItemType": {
		"Q5976445": {
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
			"facets": {
				"P22": {
					"type": "list"
				}
			}
		}
	}
}
JSON );

		$templateSpy = new SpyTemplateParser();

		$this->newTabsHtmlBuilder( config: $config, templateSpy: $templateSpy )
			->createHtml( 'unimportant' );

		$this->assertEquals(
			[
				[
					'label' => 'All',
					'value' => '',
					'selected' => true
				],
				[
					'label' => 'Q5976445Label',
					'value' => 'Q5976445',
					'selected' => false
				],
				[
					'label' => 'Q5976449Label',
					'value' => 'Q5976449',
					'selected' => false
				],
			],
			$templateSpy->getArgs()['instances']
		);
	}

	public function testTabsViewModelSelectsCurrentItemType(): void {
		$config = $this->newConfigFromJson( <<<JSON
{
	"itemTypeProperty": "P1337",
	"configPerItemType": {
		"Q1": {
			"facets": { "P1": { "type": "list" } }
		},
		"Q2": {
			"facets": { "P1": { "type": "list" } }
		},
		"Q3": {
			"facets": { "P1": { "type": "list" } }
		}
	}
}
JSON );

		$templateSpy = new SpyTemplateParser();

		$this->newTabsHtmlBuilder(
			config: $config,
			templateSpy: $templateSpy,
			queryStringParser: new StubQueryStringParser( new Query(
				new PropertyConstraintsList(),
				itemTypes: [ new ItemId( 'Q2' ) ]
			) )
		)->createHtml( 'unimportant' );

		$this->assertEquals(
			[
				[
					'label' => 'All',
					'value' => '',
					'selected' => false
				],
				[
					'label' => 'Q1Label',
					'value' => 'Q1',
					'selected' => false
				],
				[
					'label' => 'Q2Label',
					'value' => 'Q2',
					'selected' => true
				],
				[
					'label' => 'Q3Label',
					'value' => 'Q3',
					'selected' => false
				],
			],
			$templateSpy->getArgs()['instances']
		);
	}

	public function testSettingsViewModelIsEmptyWhenWikiConfigIsDisabled(): void {
		$templateSpy = new SpyTemplateParser();

		$this->newTabsHtmlBuilder(
			config: Valid::config(),
			templateSpy: $templateSpy,
			configAuthorizer: $this->newConfigAuthorizer( canEditConfig: false )
		)->createHtml( 'unimportant' );

		$this->assertSame(
			[],
			$templateSpy->getArgs()['settings']
		);
	}

	public function testSettingsViewModelContainsValuesWhenUserCanEditConfigurations(): void {
		$templateSpy = new SpyTemplateParser();

		$this->newTabsHtmlBuilder(
			config: Valid::config(),
			templateSpy: $templateSpy,
			configAuthorizer: $this->newConfigAuthorizer( canEditConfig: true )
		)->createHtml( 'unimportant' );

		$settings = $templateSpy->getArgs()['settings'];

		$this->assertArrayHasKey( 'url', $settings );
		$this->assertArrayHasKey( 'label', $settings );
		$this->assertStringContainsString(
			WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE,
			$settings['url']
		);
	}

	public function testSettingsViewModelIsEmptyWhenUserCannotEditConfigurations(): void {
		$templateSpy = new SpyTemplateParser();

		$this->newTabsHtmlBuilder(
			config: Valid::config(),
			templateSpy: $templateSpy,
			configAuthorizer: $this->newConfigAuthorizer( canEditConfig: false )
		)->createHtml( 'unimportant' );

		$this->assertSame(
			[],
			$templateSpy->getArgs()['settings']
		);
	}

	public function testTabsViewModelContainsIcon(): void {
		$config = $this->newConfigFromJson( <<<JSON
{
	"itemTypeProperty": "P1337",
	"configPerItemType": {
		"Q1": {
			"icon": "bug-slash",
			"facets": { "P1": { "type": "list" } }
		}
	}
}
JSON );

		$templateSpy = new SpyTemplateParser();

		$iconBuilder = new StubIconBuilder( '<span class="icon"></span>' );

		$this->newTabsHtmlBuilder(
			config: $config,
			templateSpy: $templateSpy,
			iconBuilder: $iconBuilder
		)->createHtml( 'unimportant' );

		$itemTypeTab = $templateSpy->getArgs()['instances'][1];

		$this->assertArrayHasKey( 'icon', $itemTypeTab );
		$this->assertSame( '<span class="icon"></span>', $itemTypeTab['icon'] );
	}

	private function newConfigFromJson( string $json ): Config {
		return WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer()->deserialize( $json );
	}

}
