<?php
namespace Gt\Cli\Parameter;

class NamedParameter extends Parameter {
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(string $optionName) {
		$this->longOption = $optionName;
	}

	public function getOptionName():string {
		return $this->longOption;
	}
}
