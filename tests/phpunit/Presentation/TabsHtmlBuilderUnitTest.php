<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\TabsHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\FakeItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyTemplateParser;
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
		?QueryStringParser $queryStringParser = null
	): TabsHtmlBuilder {
		return new TabsHtmlBuilder(
			$config ?? new Config(),
			new FakeItemTypeLabelLookup(),
			$templateSpy ?? new SpyTemplateParser(),
			$queryStringParser ?? new StubQueryStringParser()
		);
	}

	public function testTabsViewModelContainsItemTypes(): void {
		$config = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer()->deserialize( <<<JSON
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
		$config = WikibaseFacetedSearchExtension::getInstance()->newConfigDeserializer()->deserialize( <<<JSON
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

}
