<?php
namespace Gt\Cli\Argument;

class NamedArgument extends Argument {
	public function __construct(string $value) {
		parent::__construct("", $value);
	}

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	protected function processRawKey(string $rawKey): string {
		return "";
	}
}
