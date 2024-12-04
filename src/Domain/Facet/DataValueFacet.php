<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet;

use Wikibase\DataModel\Entity\PropertyId;

class DataValueFacet implements Facet {

	public function __construct(
		private readonly string $label,
		private readonly PropertyId $propertyId,
		private readonly FacetValueList $values
	) {
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getValues(): FacetValueList {
		return $this->values;

	}

	public function getSearchQueryWithValue( FacetValue $value ): string {
		return 'TODO';
	}

	public function getSearchQueryWithoutValue( FacetValue $value ): string {
		return 'TODO';
	}

}
