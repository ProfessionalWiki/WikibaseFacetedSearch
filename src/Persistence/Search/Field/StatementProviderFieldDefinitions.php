<?php

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence\Search\Field;

use ProfessionalWiki\WikibaseFacetedSearch\Application\Config;
use ProfessionalWiki\WikibaseFacetedSearch\WikibaseFacetedSearchExtension;
use Psr\Log\LoggerInterface;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Lib\SettingsArray;
use Wikibase\Repo\Search\Fields\FieldDefinitions;
use Wikibase\Repo\Search\Fields\WikibaseIndexField;

class StatementProviderFieldDefinitions implements FieldDefinitions {

	/**
	 * @param callable[] $searchIndexDataFormatters
	 * @param array $propertyIds
	 * @param array $indexedTypes
	 * @param array $excludedIds
	 */
	public function __construct(
		private readonly PropertyDataTypeLookup $propertyDataTypeLookup,
		private readonly array $searchIndexDataFormatters,
		private array $propertyIds,
		private array $indexedTypes,
		private array $excludedIds,
		private readonly Config $config,
		private ?LoggerInterface $logger = null
	) {
	}

	/**
	 * Get the list of definitions
	 * @return WikibaseIndexField[] key is field name, value is WikibaseIndexField
	 */
	public function getFields() {
		$fields = [];

		// TODO: add field for each configued facet
		// TODO: should we determine property type here and add a specific field?
		// TODO: or use a generic field and detect the property there?
		foreach( $this->config->getFacets()->asArray() as $facetConfig ) {
			$fields['wbfs_' . $facetConfig->propertyId->getSerialization()] = new FacetField(
				$facetConfig->propertyId
			);
		}

		return $fields;
	}

	/**
	 * Factory to create StatementProviderFieldDefinitions from configs
	 * @param PropertyDataTypeLookup $propertyDataTypeLookup
	 * @param callable[] $searchIndexDataFormatters
	 * @param SettingsArray $settings
	 * @param LoggerInterface|null $logger
	 * @return StatementProviderFieldDefinitions
	 */
	public static function newFromSettings(
		PropertyDataTypeLookup $propertyDataTypeLookup,
		array $searchIndexDataFormatters,
		SettingsArray $settings,
		?LoggerInterface $logger = null
	) {
		return new static(
			$propertyDataTypeLookup,
			$searchIndexDataFormatters,
			$settings->getSetting( 'searchIndexProperties' ),
			$settings->getSetting( 'searchIndexTypes' ),
			$settings->getSetting( 'searchIndexPropertiesExclude' ),
			WikibaseFacetedSearchExtension::getInstance()->getConfig(),
			$logger
		);
	}

}
