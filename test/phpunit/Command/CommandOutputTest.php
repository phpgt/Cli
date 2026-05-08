<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Command\HelpCommand;
use GT\Cli\Palette;
use GT\Cli\ProgressBar;
use GT\Cli\Stream;
use GT\Cli\Test\Helper\ArgumentMockTestCase;
use GT\Cli\Test\Helper\Command\TestCommand;
use PHPUnit\Framework\MockObject\MockObject;

class CommandOutputTest extends ArgumentMockTestCase {
	public function testSetOutput():void {
		/** @var Stream|MockObject $stream */
		$stream = $this->createMock(Stream::class);
		/** @var ArgumentValueList|MockObject $args */
		$args = $this->createMock(ArgumentValueList::class);

		$buffer = [
			Stream::OUT => [],
			Stream::ERROR => [],
		];
		$stream->method("write")
			->willReturnCallback(function(
				string $message,
				string $streamName
			)use(&$buffer) {
				$buffer[$streamName][] = $message;
			});

		$command = new HelpCommand("UnitTest");
		$command->run($args);
		self::assertEmpty($buffer[Stream::OUT]);

		$command->setStream($stream);
		$command->run($args);
		self::assertNotEmpty($buffer[Stream::OUT]);
		self::assertEmpty($buffer[Stream::ERROR]);
	}

	public function testOutputUsesPaletteWhenProvided():void {
		$stream = $this->createMock(Stream::class);
		$stream->expects(self::once())
			->method("writeLine")
			->with(
				"single green message",
				Stream::OUT,
				Palette::GREEN,
				null
			);

		$command = new class extends TestCommand {
			public function outputPublic(string $message, ?Palette $colour = null):void {
				$this->output($message, $colour);
			}
		};
		$command->setStream($stream);
		$command->outputPublic("single green message", Palette::GREEN);
	}

	public function testSetAndResetOutputPalette():void {
		$stream = $this->createMock(Stream::class);
		$stream->expects(self::once())
			->method("setOutputPalette")
			->with(Palette::RED, Palette::BLACK);
		$stream->expects(self::once())
			->method("resetOutputPalette");

		$command = new class extends TestCommand {
			public function setPalettePublic(
				?Palette $foreground,
				?Palette $background
			):void {
				$this->setOutputPalette($foreground, $background);
			}

			public function resetPalettePublic():void {
				$this->resetOutputPalette();
			}
		};
		$command->setStream($stream);
		$command->setPalettePublic(Palette::RED, Palette::BLACK);
		$command->resetPalettePublic();
	}

	public function testCreateProgressBar():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();

		$command = new class extends TestCommand {
			public function createProgressBarPublic(int $max):ProgressBar {
				return $this->createProgressBar($max, "Work", 10);
			}
		};
		$command->setStream($stream);
		$progressBar = $command->createProgressBarPublic(10);
		$progressBar->setProgress(5);

		$out->rewind();
		self::assertSame(
			"\r\e[2KWork [=====     ]  50% (5/10)",
			$out->fread(1024)
		);
	}

	public function testCursorHelpers():void {
		$stream = new Stream(
			"php://memory",
			"php://memory",
			"php://memory"
		);
		$out = $stream->getOutStream();

		$command = new class extends TestCommand {
			public function useCursorHelpers():void {
				$this->saveCursorPosition();
				$this->moveCursorUp(1);
				$this->moveCursorDown(1);
				$this->moveCursorForward(1);
				$this->moveCursorBack(1);
				$this->setCursorColumn(1);
				$this->rewindCursor();
				$this->clearLine();
				$this->restoreCursorPosition();
			}
		};
		$command->setStream($stream);
		$command->useCursorHelpers();

		$out->rewind();
		self::assertSame(
			"\e[s\e[1A\e[1B\e[1C\e[1D\e[1G\r\e[2K\e[u",
			$out->fread(1024)
		);
	}
}
