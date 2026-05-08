<?php
namespace GT\Cli\Command;

use GT\Cli\CliException;

class InvalidCommandException extends CliException {
	public function __construct(string $message) {
		parent::__construct("Invalid command: \"$message\"");
	}
}
