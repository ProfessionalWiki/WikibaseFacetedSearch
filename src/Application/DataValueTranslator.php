<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use DataValues\DataValue;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnboundedQuantityValue;
use Wikibase\DataModel\Entity\EntityIdValue;

class DataValueTranslator {

	public function translate( DataValue $dataValue ): mixed {
		if ( $dataValue instanceof UnboundedQuantityValue ) {
			return $dataValue->getAmount()->getValueFloat();
		}

		if ( $dataValue instanceof StringValue ) {
			return $dataValue->getValue();
		}

		if ( $dataValue instanceof TimeValue ) {
			// TODO: handle date precision explicitly.
			return str_replace( '-00', '-01', ltrim( $dataValue->getTime(), '+-' ) );
		}

		if ( $dataValue instanceof EntityIdValue ) {
			return $dataValue->getEntityId()->getSerialization();
		}

		return null;
	}

}
