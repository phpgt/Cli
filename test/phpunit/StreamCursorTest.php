<?php
namespace GT\Cli\Test;

use GT\Cli\Stream;
use PHPUnit\Framework\TestCase;

class StreamCursorTest extends TestCase {
	public function testRewindCursor():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();

		$stream->write("abc");
		$stream->cursor->rewind();
		$stream->write("z");

		$out->rewind();
		self::assertSame("abc\rz", $out->fread(1024));
	}
}
