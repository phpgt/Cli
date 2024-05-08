<?php
namespace Gt\Cli\Argument;

class DefaultArgumentValue extends ArgumentValue {
	public function __construct(?string $default) {
		parent::__construct("__DEFAULT__");
		$this->push($default);
	}
}
