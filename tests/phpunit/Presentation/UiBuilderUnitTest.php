<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraintsList;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\FakeItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\SpyTemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles\StubQueryStringParser;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\UiBuilder
 */
class UiBuilderUnitTest extends TestCase {

	public function testTabsViewModelContainsItemTypeProperty(): void {
		$config = new Config( itemTypeProperty: new NumericPropertyId( 'P1337' ) );
		$templateSpy = new SpyTemplateParser();

		$this->newUiBuilder( config: $config, templateSpy: $templateSpy )
			->createHtml( 'unimportant' );

		$this->assertSame(
			'P1337',
			$templateSpy->getArgs()['instanceId']
		);
	}

	private function newUiBuilder(
		?Config $config = null,
		?SpyTemplateParser $templateSpy = null,
		?QueryStringParser $queryStringParser = null
	): UiBuilder {
		return new UiBuilder(
			$config ?? new Config(),
			new SpyFacetHtmlBuilder(),
			new StubLabelLookup( null ),
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

		$templateSpy = new SpyTemplateParser();

		$this->newUiBuilder( config: $config, templateSpy: $templateSpy )
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
			"label": "whatever1",
			"facets": { "P1": { "type": "list" } }
		},
		"Q2": {
			"label": "whatever2",
			"facets": { "P1": { "type": "list" } }
		},
		"Q3": {
			"label": "whatever3",
			"facets": { "P1": { "type": "list" } }
		}
	}
}
JSON );

		$templateSpy = new SpyTemplateParser();

		$this->newUiBuilder(
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
