<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Application;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Application\Config
 */
class ConfigTest extends TestCase {

	private function createOriginalConfig(): Config {
		return new Config(
			linkTargetSitelinkSiteId: 'enwiki'
		);
	}

	private function createNewConfig(): Config {
		return new Config(
			linkTargetSitelinkSiteId: 'dewiki'
		);
	}

	public function testOriginalValuesAreKeptWhenCombined(): void {
		$original = $this->createOriginalConfig();
		$new = new Config();

		$combined = $original->combine( $new );

		$this->assertEquals( $original, $combined );
	}

	public function testOriginalValuesAreReplacedWhenCombined(): void {
		$original = $this->createOriginalConfig();
		$new = $this->createNewConfig();

		$combined = $original->combine( $new );

		$this->assertEquals( $new, $combined );
	}

}
