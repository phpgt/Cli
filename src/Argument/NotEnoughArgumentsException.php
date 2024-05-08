<?php
namespace Gt\Cli\Argument;

use Gt\Cli\CliException;

class NotEnoughArgumentsException extends CliException {
	public function __construct(string $message) {
		parent::__construct("Not enough arguments passed. $message.");
	}
}
