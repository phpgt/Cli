<?php
namespace Gt\Cli\Argument;

class CommandArgument extends Argument {
	public function __construct(string $commandName) {
		parent::__construct("", $commandName);
	}

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	protected function processRawKey(string $rawKey): string {
		return "";
	}
}
