<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\LongOptionArgument;
use GT\Cli\Argument\NamedArgument;
use GT\Cli\Argument\ShortOptionArgument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase {
	public function test__toString_named() {
		$value = uniqid();
		$arg = new NamedArgument($value);

		self::assertEquals($value, (string)$arg);
	}

	public function test__toString_longOption() {
		$name = uniqid();
		$value = uniqid();
		$arg = new LongOptionArgument("--$name", $value);

		self::assertEquals("$name:$value", (string)$arg);
	}

	public function testGetKey_shortOption() {
		$value = uniqid();
		$arg = new ShortOptionArgument("-d", $value);

		self::assertEquals("d", $arg->getKey());
		self::assertEquals($value, $arg->getValue());
	}
}
