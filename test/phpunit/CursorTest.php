<?php
namespace GT\Cli\Test;

use GT\Cli\Stream;
use PHPUnit\Framework\TestCase;

class CursorTest extends TestCase {
	public function testRewind():void {
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

	public function testSavePosition_cursorMovement():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();

		$stream->cursor->savePosition();
		$stream->cursor->moveUp(2);
		$stream->cursor->moveDown(3);
		$stream->cursor->moveForward(4);
		$stream->cursor->moveBack(5);
		$stream->cursor->setColumn(6);
		$stream->cursor->clearLine();
		$stream->cursor->restorePosition();

		$out->rewind();
		self::assertSame(
			"\e[s\e[2A\e[3B\e[4C\e[5D\e[6G\e[2K\e[u",
			$out->fread(1024)
		);
	}
}
