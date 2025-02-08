<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Presentation;

use MediaWiki\Html\TemplateParser;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\Application\ItemTypeLabelLookup;
use ProfessionalWiki\WikibaseFacetedSearch\Application\Query;
use ProfessionalWiki\WikibaseFacetedSearch\Application\QueryStringParser;
use Wikibase\DataModel\Entity\ItemId;

class TabsHtmlBuilder {

	public function __construct(
		private readonly Config $config,
		private readonly ItemTypeLabelLookup $itemTypeLabelLookup,
		private readonly TemplateParser $templateParser,
		private readonly QueryStringParser $queryStringParser,
	) {
	}

	public function createHtml( string $searchQuery ): string {
		$query = $this->parseQuery( $searchQuery );
		$itemType = $query->getItemTypes()[0] ?? null;

		return $this->renderTemplate(
			$this->buildTabsViewModel(
				selectedItemType: $itemType
			)
		);
	}

	private function renderTemplate( array $instancesViewModel ): string {
		return $this->templateParser->processTemplate(
			'Topbar',
			[
				'instanceId' => $this->config->getItemTypeProperty()->getSerialization(),
				'instances' => $instancesViewModel,
				'msg-filters' => wfMessage( 'wikibase-faceted-search-filters' )->text(),
			]
		);
	}

	private function parseQuery( string $searchQuery ): Query {
		return $this->queryStringParser->parse( $searchQuery );
	}

	private function buildTabsViewModel( ?ItemId $selectedItemType ): array {
		$tabs = [];

		foreach ( $this->config->getItemTypes() as $itemType ) {
			$tabs[] = [
				'label' => $this->itemTypeLabelLookup->getLabel( $itemType ),
				'value' => $itemType->getSerialization(),
				'selected' => $itemType->equals( $selectedItemType )
			];
		}

		return [
			[
				'label' => wfMessage( 'wikibase-faceted-search-instance-type-all' )->text(),
				'value' => '',
				'selected' => $this->noTabsAreSelected( $tabs )
			],
			...$tabs
		];
	}

	/**
	 * @param array<array{selected: bool}> $tabs
	 */
	private function noTabsAreSelected( array $tabs ): bool {
		return !array_reduce( $tabs, ( fn( $carry, $tab ) => $carry || $tab['selected'] ), false );
	}

}
