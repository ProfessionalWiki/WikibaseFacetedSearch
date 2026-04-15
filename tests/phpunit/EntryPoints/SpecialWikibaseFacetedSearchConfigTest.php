<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\EntryPoints;

use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\SpecialWikibaseFacetedSearchConfig
 */
class SpecialWikibaseFacetedSearchConfigTest extends \MediaWikiIntegrationTestCase {

	public function testRedirect(): void {
		$specialPage = $this->getServiceContainer()->getSpecialPageFactory()->getPage( 'WikibaseFacetedSearchConfig' );

		$specialPage->execute( null );

		$this->assertEquals(
			Title::newFromText( WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE, NS_MEDIAWIKI )->getFullURL(),
			$specialPage->getOutput()->getRedirect()
		);
	}

}
