<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetConfig;
use ProfessionalWiki\WikibaseFacetedSearch\Application\FacetType;
use ProfessionalWiki\WikibaseFacetedSearch\Application\PropertyConstraints;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\RangeFacetHtmlBuilder;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\RangeFacetHtmlBuilder
 */
class RangeFacetHtmlBuilderTest extends TestCase {

	private const FACET_PROPERTY_ID = 'P42';

	public function testRendersTemplateWithMinAndMax(): void {
		$html = $this->newRangeFacetHtmlBuilder()->buildHtml(
			config: $this->newConfig(),
			state: $this->newPropertyConstraints()->withInclusiveMinimum( 1337 )->withInclusiveMaximum( 9001 )
		);

		//$this->assertStringContainsString( self::FACET_PROPERTY_ID, $html );

		$this->assertMatchesRegularExpression(
			'/range-min.*?value="1337"/s',
			$html,
			'Min value 1337 not found in min input'
		);

		$this->assertMatchesRegularExpression(
			'/range-max.*?value="9001"/s',
			$html,
			'Max value 9001 not found in max input'
		);
	}

	private function newConfig(): FacetConfig {
		return new FacetConfig(
			instanceTypeId: new ItemId( 'Q123' ),
			propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ),
			type: FacetType::RANGE
		);
	}

	private function newPropertyConstraints(): PropertyConstraints {
		return new PropertyConstraints( propertyId: new NumericPropertyId( self::FACET_PROPERTY_ID ) );
	}

	private function newRangeFacetHtmlBuilder(): RangeFacetHtmlBuilder {
		return new RangeFacetHtmlBuilder(
			parser: WikibaseFacetedSearchExtension::getInstance()->getTemplateParser()
		);
	}

	public function testRendersTemplateWithNoMinAndMax(): void {
		$html = $this->newRangeFacetHtmlBuilder()->buildHtml(
			config: $this->newConfig(),
			state: $this->newPropertyConstraints()
		);

		$this->assertMatchesRegularExpression( '/range-min.*?value=""/s', $html );
		$this->assertMatchesRegularExpression( '/range-max.*?value=""/s', $html );
	}

}
