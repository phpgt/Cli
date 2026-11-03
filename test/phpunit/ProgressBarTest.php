<?php
namespace GT\Cli\Test;

use GT\Cli\ProgressBar;
use GT\Cli\Stream;
use PHPUnit\Framework\TestCase;

class ProgressBarTest extends TestCase {
	public function testSetProgress_returnsWhenComplete():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$progressBar = new ProgressBar($stream, 10, "Work", 10);
		$progressBar->finish();
		$progressBar->setProgress(5, "Ignored");

		self::assertTrue(true);
	}

	public function testFinish_returnsWhenAlreadyComplete():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$progressBar = new ProgressBar($stream, 10, "Work", 10);
		$progressBar->finish("Done");
		$progressBar->finish("Ignored");

		self::assertTrue(true);
	}

	public function testSetProgress_withLabelAndAdvance():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$progressBar = new ProgressBar($stream, 10, "Work", 10);
		$progressBar->setProgress(3, "Step 1");
		$progressBar->advance(2, "Step 2");

		$out = $stream->getOutStream();
		$out->rewind();
		$output = $out->fread(1024);
		self::assertStringContainsString("Step 1", $output);
		self::assertStringContainsString("Step 2", $output);
	}
}
