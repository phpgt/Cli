<?php
namespace Gt\Cli\Test\Argument;

use Gt\Cli\Argument\LongOptionArgument;
use Gt\Cli\Argument\NamedArgument;
use Gt\Cli\Argument\ShortOptionArgument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase {
	public function testToStringNamed() {
		$value = uniqid();
		$arg = new NamedArgument($value);

		self::assertEquals($value, (string)$arg);
	}

	public function testToStringLongOption() {
		$name = uniqid();
		$value = uniqid();
		$arg = new LongOptionArgument("--$name", $value);

		self::assertEquals("$name:$value", (string)$arg);
	}

	public function testShortOption() {
		$value = uniqid();
		$arg = new ShortOptionArgument("-d", $value);

		self::assertEquals("d", $arg->getKey());
		self::assertEquals($value, $arg->getValue());
	}
}
