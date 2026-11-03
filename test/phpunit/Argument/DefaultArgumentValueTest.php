<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\DefaultArgumentValue;
use PHPUnit\Framework\TestCase;

class DefaultArgumentValueTest extends TestCase {
	public function test__construct_usesDefault():void {
		$value = new DefaultArgumentValue("fallback");
		self::assertSame("fallback", (string)$value);
	}
}
