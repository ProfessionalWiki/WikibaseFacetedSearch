<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet;

interface Facet {

	public function getLabel(): string;

	public function getValues(): FacetValueList;

	public function getSearchQueryWithValue( FacetValue $value ): string;

	public function getSearchQueryWithoutValue( FacetValue $value ): string;

}
