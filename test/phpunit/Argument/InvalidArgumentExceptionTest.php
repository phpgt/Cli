<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase {
	public function test__construct_invalidOption():void {
		$exception = new InvalidArgumentException("--unknown");
		self::assertSame(5, $exception->getCode());
		self::assertSame(
			'Error: Invalid argument supplied: "--unknown"',
			$exception->getMessage()
		);
	}

}
