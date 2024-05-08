<?php

namespace Gt\Cli\Argument;

class CommandArgumentList extends ArgumentList {
	public function __construct(
		string $forcedCommand,
		string $script,
		string...$arguments
	) {
		$commandArguments = array_merge(
			[$forcedCommand],
			$arguments
		);
		parent::__construct($script, ...$commandArguments);
	}
}
