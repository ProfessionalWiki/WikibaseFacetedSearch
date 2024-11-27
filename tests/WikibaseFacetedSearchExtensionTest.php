<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension
 */
class WikibaseFacetedSearchExtensionTest extends TestCase {

	public function testGetInstanceIsSingleton(): void {
		$this->assertSame( WikibaseFacetedSearchExtension::getInstance(), WikibaseFacetedSearchExtension::getInstance() );
	}

}
