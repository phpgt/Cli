<?php
namespace Gt\Cli\Test;

use Gt\Cli\InvalidStreamNameException;
use Gt\Cli\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase {
	public function testGetSetStream() {
		$tmp = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"cli",
		]);
		if(!is_dir($tmp)) {
			mkdir($tmp, 0775, true);
		}

		$inPath = implode(DIRECTORY_SEPARATOR, [$tmp, "in"]);
		$outPath = implode(DIRECTORY_SEPARATOR, [$tmp, "out"]);
		$errPath = implode(DIRECTORY_SEPARATOR, [$tmp, "err"]);
		touch($inPath);
		touch($outPath);
		touch($errPath);

		$stream = new Stream(
			$inPath,
			$outPath,
			$errPath
		);

		$in = $stream->getInStream();
		$out = $stream->getOutStream();
		$err = $stream->getErrorStream();

		self::assertEquals($inPath, $in->getRealPath());
		self::assertEquals($outPath, $out->getRealPath());
		self::assertEquals($errPath, $err->getRealPath());

		$stream = $in = $out = $err = null;
		foreach(scandir($tmp) as $file) {
			if($file[0] === ".") {
				continue;
			}

			$fullPath = implode(DIRECTORY_SEPARATOR, [
				$tmp,
				$file,
			]);
			unlink($fullPath);
		}
		rmdir($tmp);
	}

	public function testWrite() {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();
		$stream->write("test");
		$out->rewind();
		self::assertEquals("test", $out->fread(1024));
	}

	public function testWriteLine() {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();
		$stream->writeLine("test");
		$out->rewind();
		self::assertMatchesRegularExpression(
			"/^test\r?\n$/",
			$out->fread(1024)
		);
	}

	public function testWriteToError() {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();
		$err = $stream->getErrorStream();

		$stream->write("this should go to error", Stream::ERROR);
		$out->rewind();
		$err->rewind();
		self::assertEmpty($out->fread(1024));
		self::assertEquals("this should go to error", $err->fread(1024));
	}

	public function testWriteToIn() {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$in = $stream->getInStream();

		$stream->write("can't write to stdin", Stream::IN);
		$in->rewind();
		self::assertEmpty($in->fread(1024));
	}

	public function testInvalidStreamName() {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$this->expectException(InvalidStreamNameException::class);
		$stream->write("this does not exist", "nothing");
	}

	public function testRepeatingLineSuppressed():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory",
		);
		$out = $stream->getOutStream();

		for($i = 1; $i <= 10; $i++) {
			$stream->writeLine("This is message $i, and should appear individually.");
		}

		for($i = 1; $i <= 5; $i++) {
			$stream->writeLine("This message is sent 5 times but should only appear once.");
		}

		for($i = 11; $i <= 20; $i++) {
			$stream->writeLine("This is message $i, and should appear after the repeating message individually.");
		}

		$out->rewind();
		$fullStreamContents = $out->fread(1024);

		self::assertSame(10, substr_count($fullStreamContents, "should appear individually"), "Unique messages should appear individually");
		self::assertSame(1, substr_count($fullStreamContents, "should only appear once"), "Similar messages should only appear once");
		self::assertSame(4, substr_count($fullStreamContents, Stream::REPEAT_CHAR));
	}
}
