<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\ArgumentValue;
use GT\Cli\Argument\DefaultArgumentValue;
use GT\Cli\Argument\NamedArgumentValue;
use PHPUnit\Framework\TestCase;

class ArgumentValueTest extends TestCase {
	public function testArgumentValueQueueAndToString():void {
		$value = new ArgumentValue("name");
		$value->push("first");
		$value->push("second");

		self::assertSame("name", $value->getKey());
		self::assertSame("first second", (string)$value);
		self::assertSame("first", $value->get());
		self::assertSame("second", $value->get());
		self::assertSame(["first", "second"], $value->getAll());
	}

	public function testGetString():void {
		$value = new ArgumentValue("name");
		$value->push("first");
		$value->push("second");

		self::assertSame("first", $value->getString());
		self::assertSame("second", $value->getString());
	}

	public function testGetInt():void {
		$value = new ArgumentValue("number");
		$value->push("123");

		self::assertSame(123, $value->getInt());
	}

	public function testGetFloat():void {
		$value = new ArgumentValue("number");
		$value->push("1.25");

		self::assertSame(1.25, $value->getFloat());
	}

	public function testGetBool():void {
		$value = new ArgumentValue("enabled");
		$value->push("1");

		self::assertTrue($value->getBool());
	}

	public function testDefaultArgumentValueUsesDefault():void {
		$value = new DefaultArgumentValue("fallback");
		self::assertSame("fallback", (string)$value);
	}

	public function testNamedArgumentValueExtendsArgumentValue():void {
		$value = new NamedArgumentValue("named");
		$value->push("data");
		self::assertSame("data", (string)$value);
	}
}
