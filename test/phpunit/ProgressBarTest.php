<?php
namespace Gt\Cli\Test;

use Gt\Cli\ProgressBar;
use Gt\Cli\Stream;
use PHPUnit\Framework\TestCase;

class ProgressBarTest extends TestCase {
	public function testSetProgressReturnsWhenComplete():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$progressBar = new ProgressBar($stream, 10, "Work", 10);
		$progressBar->finish();
		$progressBar->setProgress(5, "Ignored");

		self::assertTrue(true);
	}

	public function testFinishReturnsWhenAlreadyComplete():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$progressBar = new ProgressBar($stream, 10, "Work", 10);
		$progressBar->finish("Done");
		$progressBar->finish("Ignored");

		self::assertTrue(true);
	}

	public function testSetProgressWithLabelAndAdvance():void {
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
