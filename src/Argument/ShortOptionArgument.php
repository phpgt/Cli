<?php
namespace Gt\Cli\Argument;

class ShortOptionArgument extends Argument {
	protected function processRawKey(string $rawKey):string {
		$key = substr($rawKey, 1);
		$equalsPos = strpos($key, "=");

		if($equalsPos !== false) {
			$key = substr($key, 0, $equalsPos);
		}

		return $key;
	}
}
