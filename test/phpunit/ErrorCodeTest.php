<?php
namespace GT\Cli\Test;

use GT\Cli\Argument\NotEnoughArgumentsException;
use GT\Cli\CliException;
use GT\Cli\Command\InvalidCommandException;
use GT\Cli\ErrorCode;
use PHPUnit\Framework\TestCase;

class ErrorCodeTest extends TestCase {
	public function testGetFromKnownExceptionClassString():void {
		$code = ErrorCode::get(InvalidCommandException::class);
		self::assertSame(2, $code);
	}

	public function testGetFromExceptionObject():void {
		$exception = new InvalidCommandException("test");
		$code = ErrorCode::get($exception);
		self::assertSame(2, $code);
	}

	public function testGetUnknownClassReturnsDefault():void {
		$code = ErrorCode::get(CliException::class);
		self::assertSame(ErrorCode::DEFAULT_CODE, $code);
	}

	public function testGetFirstListClassReturnsDefaultByCurrentLogic():void {
		$code = ErrorCode::get(NotEnoughArgumentsException::class);
		self::assertSame(ErrorCode::DEFAULT_CODE, $code);
	}
}
