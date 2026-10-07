<?php
namespace GT\Cli\Test;

use GT\Cli\Argument\NotEnoughArgumentsException;
use GT\Cli\Argument\InvalidArgumentException;
use GT\Cli\CliException;
use GT\Cli\Command\InvalidCommandException;
use GT\Cli\ErrorCode;
use PHPUnit\Framework\TestCase;

class ErrorCodeTest extends TestCase {
	public function testGet_invalidArgumentException():void {
		self::assertSame(5, ErrorCode::get(InvalidArgumentException::class));
	}

	public function testGet_knownExceptionClassString():void {
		$code = ErrorCode::get(InvalidCommandException::class);
		self::assertSame(2, $code);
	}

	public function testGet_exceptionObject():void {
		$exception = new InvalidCommandException("test");
		$code = ErrorCode::get($exception);
		self::assertSame(2, $code);
	}

	public function testGet_unknownClassReturnsDefault():void {
		$code = ErrorCode::get(CliException::class);
		self::assertSame(ErrorCode::DEFAULT_CODE, $code);
	}

	public function testGet_firstListClassReturnsDefault():void {
		$code = ErrorCode::get(NotEnoughArgumentsException::class);
		self::assertSame(ErrorCode::DEFAULT_CODE, $code);
	}
}
