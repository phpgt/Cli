<?php
namespace GT\Cli\Argument;

use GT\Cli\CliException;

class InvalidArgumentException extends CliException {
	public function __construct(string $option) {
		parent::__construct("Invalid argument supplied: \"$option\"");
	}
}
