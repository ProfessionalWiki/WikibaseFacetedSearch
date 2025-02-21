<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Application;

use ProfessionalWiki\MessageBuilder\MessageBuilder;
use ProfessionalWiki\MessageBuilder\UnknownMessageKey;

class MediaWikiMessageBuilder implements MessageBuilder {

	public function buildMessage( string $messageKey, string ...$arguments ): string {
		$message = wfMessage( $messageKey, ...$arguments );

		if ( $message->exists() ) {
			return $message->text();
		}

		throw new UnknownMessageKey();
	}

}
