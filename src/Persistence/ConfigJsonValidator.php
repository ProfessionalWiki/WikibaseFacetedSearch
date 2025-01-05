<?php

declare( strict_types = 1 );

namespace ProfessionalWiki\WikibaseFacetedSearch\Persistence;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Validator;

class ConfigJsonValidator {

	/**
	 * @var string[]
	 */
	private array $errors = [];

	public function __construct(
		private readonly object $jsonSchema
	) {
	}

	public function validate( string $config ): bool {
		$validator = new Validator();
		$validator->setMaxErrors( 10 );

		$validationResult = $validator->validate( json_decode( $config ), $this->jsonSchema );

		$error = $validationResult->error();

		if ( $error !== null ) {
			$this->errors = $this->formatErrors( $error );
		}

		return $error === null;
	}

	/**
	 * @return string[]
	 */
	public function getErrors(): array {
		return $this->errors;
	}

	/**
	 * @return string[]
	 */
	private function formatErrors( ValidationError $error ): array {
		return ( new ErrorFormatter() )->format( $error, false );
	}

}
