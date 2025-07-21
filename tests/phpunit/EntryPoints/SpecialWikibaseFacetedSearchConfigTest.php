<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\EntryPoints;

use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use SpecialPageTestBase;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\EntryPoints\SpecialWikibaseFacetedSearchConfig
 */
class SpecialWikibaseFacetedSearchConfigTest extends SpecialPageTestBase {

	protected function newSpecialPage(): SpecialPage {
		return $this->getServiceContainer()->getSpecialPageFactory()->getPage( 'WikibaseFacetedSearchConfig' );
	}

	public function testRedirect(): void {
		$specialRules = $this->newSpecialPage();

		$specialRules->execute( null );

		$this->assertEquals(
			Title::newFromText( WikibaseFacetedSearchExtension::CONFIG_PAGE_TITLE, NS_MEDIAWIKI )->getFullURL(),
			$specialRules->getOutput()->getRedirect()
		);
	}

}
