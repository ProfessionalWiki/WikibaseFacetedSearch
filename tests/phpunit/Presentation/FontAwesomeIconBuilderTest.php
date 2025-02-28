<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\Presentation;

use PHPUnit\Framework\TestCase;
use ProfessionalWiki\WikibaseFacetedSearch\Presentation\FontAwesomeIconBuilder;

/**
 * @covers \ProfessionalWiki\WikibaseFacetedSearch\Presentation\FontAwesomeIconBuilder
 */
class FontAwesomeIconBuilderTest extends TestCase {

	private function newFontAwesomeIconBuilder( ?string $style = null, ?string $family = null ): FontAwesomeIconBuilder {
		return new FontAwesomeIconBuilder(
			...array_filter(
				[ 'style' => $style, 'family' => $family ],
				fn( $value ) => $value !== null
			)
		);
	}

	public function testBuildHtml(): void {
		$html = $this->newFontAwesomeIconBuilder()->buildHtml( 'bug-slash' );

		$this->assertStringContainsString( '<span ', $html );
		$this->assertStringContainsString( 'wikibase-faceted-search__icon-fontawesome', $html );
		$this->assertStringContainsString( 'fa-solid', $html );
		$this->assertStringContainsString( 'fa-bug-slash', $html );
	}

	public function testBuildHtmlWithCustomOptions(): void {
		$html = $this->newFontAwesomeIconBuilder(
			style: 'regular',
			family: 'duotone',
		)->buildHtml( 'bug-slash' );

		$this->assertStringContainsString( 'fa-duotone', $html );
		$this->assertStringContainsString( 'fa-regular', $html );
	}

	public function testBuildHtmlWithCustomLocalOptions(): void {
		$html = $this->newFontAwesomeIconBuilder()->buildHtml( 'bug-slash', [
			'style' => 'regular',
			'family' => 'duotone',
		] );

		$this->assertStringContainsString( 'fa-duotone', $html );
		$this->assertStringContainsString( 'fa-regular', $html );
	}

	public function testBuildHtmlWithDeprecatedStyle(): void {
		$html = $this->newFontAwesomeIconBuilder( style: 'solid' )->buildHtml( 'bug-slash' );

		$this->assertStringContainsString( 'fas', $html );
	}

	public function testBuildHtmlWithClassicFamily(): void {
		$html = $this->newFontAwesomeIconBuilder( family: 'classic' )->buildHtml( 'bug-slash' );

		$this->assertStringNotContainsString( 'fa-classic', $html );
	}

}
