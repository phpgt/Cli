<?php
namespace Gt\Cli\Command;

use Gt\Cli\CliException;

class InvalidCommandException extends CliException {
	public function __construct(string $message) {
		parent::__construct("Invalid command: \"$message\"");
	}
}
