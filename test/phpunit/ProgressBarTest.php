<?php
namespace Gt\Cli\Test;

use Gt\Cli\ProgressBar;
use Gt\Cli\Stream;
use PHPUnit\Framework\TestCase;

class ProgressBarTest extends TestCase {
	public function testSetProgressRendersOnSingleLine():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();
		$bar = new ProgressBar($stream, 10, "Installing", 10);

		$bar->setProgress(4);
		$bar->setProgress(10);

		$out->rewind();
		self::assertSame(
			"\r\e[2KInstalling [====      ]  40% (4/10)"
			. "\r\e[2KInstalling [==========] 100% (10/10)",
			$out->fread(1024)
		);
	}

	public function testAdvanceAndFinish():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();
		$bar = new ProgressBar($stream, 5, "Download", 5);

		$bar->advance();
		$bar->advance(2, "Download data");
		$bar->finish("Done");

		$out->rewind();
		self::assertSame(
			"\r\e[2KDownload [==        ]  20% (1/5)"
			. "\r\e[2KDownload data [======    ]  60% (3/5)"
			. "\r\e[2KDone [==========] 100% (5/5)"
			. PHP_EOL,
			$out->fread(1024)
		);
	}
}
