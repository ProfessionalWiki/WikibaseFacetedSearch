<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use MediaWiki\MediaWikiServices;
use MediaWiki\Utils\UrlUtils;
use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\SearchUrlBuilder;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\SearchUrlBuilder
 */
class SearchUrlBuilderTest extends TestCase {

	private function newSearchUrlBuilder(): SearchUrlBuilder {
		return new SearchUrlBuilder(
			urlUtils: MediaWikiServices::getInstance()->getUrlUtils()
		);
	}

	public function testBuildUrl(): void {
		$url = 'https://example.org/w/index.php?search=foo';

		$urlBuilder = $this->newSearchUrlBuilder();
		$urlBuilder->setUrlParts( $url );
		$urlBuilder->setUrlQuery();

		$this->assertEquals( $url, $urlBuilder->buildUrl() );

		$facetQueryToAdd = 'haswbfacet:P200=Q200';
		$this->assertEquals( "$url+$facetQueryToAdd", $urlBuilder->buildUrl( $facetQueryToAdd ) );

		$facetQueryToRemove = 'haswbfacet:P100=Q100';
		$urlBuilder->setUrlParts( "$url+$facetQueryToRemove" );
		$urlBuilder->setUrlQuery();
		$this->assertEquals( $url, $urlBuilder->buildUrl( $facetQueryToRemove ) );
	}

}
