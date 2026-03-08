<?php

namespace Gt\Cli\Test\Argument;

use Gt\Cli\Argument\Argument;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\ArgumentValueListNotSetException;
use Gt\Cli\Argument\NamedArgumentValue;
use PHPUnit\Framework\TestCase;

class ArgumentValueListTest extends TestCase {
	public function testGetNotSet() {
		$avl = new ArgumentValueList();
		$this->expectException(ArgumentValueListNotSetException::class);
		$avl->get("test");
	}

	public function testGetSet() {
		$avl = new ArgumentValueList();
		$avl->set("test", "example-value");
		self::assertEquals(
			"example-value",
			$avl->get("test")
		);
	}

	public function testContains() {
		$avl = new ArgumentValueList();
		self::assertFalse($avl->contains("test"));
		$avl->set("test", "example-value");
		self::assertTrue($avl->contains("test"));
	}

	public function testGetWithDefaultReturnsDefaultArgumentValue():void {
		$avl = new ArgumentValueList();
		$default = $avl->get("missing", "fallback");
		self::assertSame("fallback", (string)$default);
	}

	public function testSetSameKeyQueuesValues():void {
		$avl = new ArgumentValueList();
		$avl->set("name", "one");
		$avl->set("name", "two");
		$value = $avl->get("name");

		self::assertSame("one", $value->get());
		self::assertSame("two", $value->get());
		self::assertSame(["one", "two"], $value->getAll());
	}

	public function testIteratorAndFirst():void {
		$avl = new ArgumentValueList();
		$avl->set("one", "alpha");
		$avl->set("two", "beta");

		self::assertSame("alpha", (string)$avl->first());
		$avl->rewind();
		self::assertSame(0, $avl->key());
		self::assertTrue($avl->valid());
		self::assertSame("alpha", (string)$avl->current());

		$avl->next();
		self::assertSame(1, $avl->key());
		self::assertSame("beta", (string)$avl->current());

		$avl->next();
		self::assertFalse($avl->valid());

		$avl->rewind();
		self::assertSame("alpha", (string)$avl->current());
	}

	public function testSetUserDataCreatesNamedArgumentValue():void {
		$avl = new ArgumentValueList();
		$avl->set(Argument::USER_DATA, "payload");
		$value = $avl->get(Argument::USER_DATA);

		self::assertInstanceOf(NamedArgumentValue::class, $value);
		self::assertSame("payload", (string)$value);
	}
}
