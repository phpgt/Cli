<?php

namespace Gt\Cli\Test\Argument;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\ArgumentValueListNotSetException;
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
}
