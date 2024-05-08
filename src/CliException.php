<?php
namespace Gt\Cli;

use RuntimeException;

class CliException extends RuntimeException {
	/** @SuppressWarnings(PHPMD.StaticAccess) */
	public function __construct(string $message) {
		$code = ErrorCode::get(get_class($this));

		parent::__construct(
			"Error: " . $message,
			$code
		);
	}
}
