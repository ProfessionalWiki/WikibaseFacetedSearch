<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\MessageBuilder\UnknownMessageKey;
use ProfessionalWiki\WikibaseFacetedSearch\Application\MediaWikiMessageBuilder;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\MediaWikiMessageBuilder
 */
class MediaWikiMessageBuilderTest extends TestCase {

	public function testBuildMessage(): void {
		$builder = new MediaWikiMessageBuilder();

		$this->assertSame(
			'Your changes were not saved. They contain the following errors:',
			$builder->buildMessage( 'wikibase-faceted-search-config-invalid', '2' )
		);
	}

	public function testReturnsEmptyStringOnMessageNotFound(): void {
		$builder = new MediaWikiMessageBuilder();

		$this->expectException( UnknownMessageKey::class );
		$builder->buildMessage( 'wikibase-faceted-search-does-not-exist-404' );
	}

}
