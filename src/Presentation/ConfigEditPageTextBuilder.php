<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Context\IContextSource;
use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;

class ConfigEditPageTextBuilder {

	public function __construct(
		private readonly IContextSource $context,
		private readonly string $exampleConfigPath,
		private readonly TemplateParser $templateParser,
		private readonly Config $config
	) {
	}

	public function createTopHtml(): string {
		return $this->templateParser->processTemplate(
			'ConfigEditPageTop',
			[
				'msg-wikibase-faceted-search-config-help-documentation' => $this->context->msg( 'wikibase-faceted-search-config-help-documentation' )->parse()
			]
		);
	}

	public function createBottomHtml(): string {
		return $this->templateParser->processTemplate(
			'ConfigEditPageBottom',
			[
				'msg-wikibase-faceted-search-config-help' => $this->context->msg( 'wikibase-faceted-search-config-help' )->escaped(),
				'msg-wikibase-faceted-search-config-help-example' => $this->context->msg( 'wikibase-faceted-search-config-help-example' )->escaped(),
				'exampleContents' => $this->getExampleContents(),
				'array-itemTypes' => $this->getItemTypesData()
			]
		);
	}

	private function getExampleContents(): string {
		$example = file_get_contents( $this->exampleConfigPath );

		if ( !is_string( $example ) ) {
			return '';
		}

		return $example;
	}

	private function getItemTypesData(): array {
		$itemTypes = $this->config->getItemTypes();
		if ( $itemTypes === [] ) {
			return [];
		}

		$data = [];
		foreach ( $itemTypes as $itemType ) {
			$id = $itemType->getSerialization();
			if ( !$id ) {
				continue;
			}

			$exists = $this->context->msg( "WikibaseFacetedSearch-item-type-$id" )->exists();
			$actionKey = $exists ? 'edit' : 'create';

			$data[] = [
				'id' => $id,
				'exists' => $exists,
				'label' => $exists ? $this->context->msg( "WikibaseFacetedSearch-item-type-$id" )->plain() : null,
				'action' => $this->context->msg( $actionKey )->plain(),
			];
		}

		return $data;
	}

}
