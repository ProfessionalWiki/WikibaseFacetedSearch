<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Tests\TestDoubles;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Services\Lookup\LabelLookup;
use Wikibase\DataModel\Term\Term;

class StubLabelLookup implements LabelLookup {

	public function __construct(
		private readonly ?Term $label
	) {
	}

	public function getLabel( EntityId $entityId ): ?Term {
		return $this->label;
	}

}
