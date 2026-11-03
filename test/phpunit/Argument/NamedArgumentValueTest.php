<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\NamedArgumentValue;
use PHPUnit\Framework\TestCase;

class NamedArgumentValueTest extends TestCase {
	public function testPush_inheritedFromArgumentValue():void {
		$value = new NamedArgumentValue("named");
		$value->push("data");
		self::assertSame("data", (string)$value);
	}
}
