<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Domain\Facet;

interface FacetValue {

	public function getLabel(): string;

	public function getValue(): mixed;

	public function getCount(): int;

	public function isSelected(): bool;

	public function getSearchQuery(): string;

}
