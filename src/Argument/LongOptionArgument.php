<?php
namespace GT\Cli\Argument;

class LongOptionArgument extends Argument {
	protected function processRawKey(string $rawKey):string {
		return substr($rawKey, 2);
	}
}
