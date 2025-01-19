<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use MediaWiki\Html\TemplateParser;

class SpyTemplateParser extends TemplateParser {

	public string $templateName = '';
	public array $args = [];

	public function processTemplate( $templateName, $args, array $scopes = [] ): string {
		$this->templateName = $templateName;
		$this->args = $args;
		return '';
	}

	public function getTemplateName(): string {
		return $this->templateName;
	}

	public function getArgs(): array {
		return $this->args;
	}

}
