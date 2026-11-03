<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\Argument;
use GT\Cli\Argument\ArgumentList;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Argument\CommandArgument;
use GT\Cli\Argument\CommandArgumentList;
use GT\Cli\Argument\InvalidArgumentException;
use GT\Cli\Argument\LongOptionArgument;
use GT\Cli\Argument\NamedArgument;
use GT\Cli\Argument\NotEnoughArgumentsException;
use GT\Cli\Command\Command;
use GT\Cli\Command\HelpCommand;
use GT\Cli\Palette;
use GT\Cli\Parameter\MissingRequiredParameterException;
use GT\Cli\Parameter\MissingRequiredParameterValueException;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;
use GT\Cli\ProgressBar;
use GT\Cli\Stream;
use GT\Cli\StreamName;
use GT\Cli\Test\Helper\ArgumentMockTestCase;
use GT\Cli\Test\Helper\Command\AllParameterTypesCommand;
use GT\Cli\Test\Helper\Command\ComboRequiredOptionalParameterCommand;
use GT\Cli\Test\Helper\Command\MultipleRequiredParameterCommand;
use GT\Cli\Test\Helper\Command\SingleRequiredNamedParameterCommand;
use GT\Cli\Test\Helper\Command\SyncLikeCommand;
use GT\Cli\Test\Helper\Command\TestCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

class CommandTest extends ArgumentMockTestCase {
	public function testGetName():void {
		$command = new TestCommand();
		self::assertEquals("test", $command->getName());

		foreach(["first", "second", "third"] as $prefix) {
			$command = new TestCommand($prefix);
			self::assertEquals("{$prefix}-test", $command->getName());
		}
	}

	public function testGetDescription():void {
		$command = new TestCommand();
		self::assertEquals(
			"A test command for unit testing",
			$command->getDescription()
		);
	}

	public function testCheckArguments_singleGood():void {
		$args = [self::createMock(NamedArgument::class)];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createIteratorMock(ArgumentList::class, $args);

		$command = new SingleRequiredNamedParameterCommand();
		$command->checkArguments($argList);
		self::assertTrue(true);
	}

	public function testCheckArguments_singleBad():void {
		$args = [self::createMock(CommandArgument::class)];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createIteratorMock(ArgumentList::class, $args);

		$command = new SingleRequiredNamedParameterCommand();
		$this->expectException(NotEnoughArgumentsException::class);
		$command->checkArguments($argList);
	}

	public function testCheckArguments_missingRequiredValue():void {
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$longArgs = [null, null, ["framework" => null], "example"];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock($args, $longArgs);

		$command = new MultipleRequiredParameterCommand();
		$this->expectException(MissingRequiredParameterValueException::class);
		$command->checkArguments($argList);
	}

	public function testCheckArguments_multipleGood():void {
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$args[2]->method("getKey")->willReturn("framework");
		$args[3]->method("getKey")->willReturn("example");
		$longArgs = [null, null, ["framework" => "php.gt"], "example"];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock($args, $longArgs);

		$command = new MultipleRequiredParameterCommand();
		$command->checkArguments($argList);
		self::assertTrue(true);
	}

	public function testCheckArguments_multipleBad():void {
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$longArgs = [null, null, ["age" => "123"], "example"];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock($args, $longArgs);

		$command = new MultipleRequiredParameterCommand();
		$this->expectException(MissingRequiredParameterException::class);
		$command->checkArguments($argList);
	}

	public function testGetRequiredNamedParameterList():void {
		$command = new MultipleRequiredParameterCommand();
		$list = $command->getRequiredNamedParameterList();
		$requiredNames = [];

		foreach($list as $item) {
			$requiredNames[] = $item->getOptionName();
		}

		self::assertContains("id", $requiredNames);
		self::assertContains("name", $requiredNames);
		self::assertCount(2, $requiredNames);
	}

	public function testGetRequiredParameterList():void {
		$command = new ComboRequiredOptionalParameterCommand();
		$list = $command->getRequiredParameterList();
		$requiredLongOptions = [];

		foreach($list as $item) {
			$requiredLongOptions[] = $item->getLongOption();
		}

		self::assertContains("type", $requiredLongOptions);
		self::assertCount(1, $requiredLongOptions);
	}

	public function testGetArgumentValueList_positionalUserData():void {
		$command = new TestCommand();
		$arguments = new ArgumentList(
			"script",
			"test",
			"id-value",
			"option-value",
			"user-extra",
			"--must-have-value",
			"required",
			"-n"
		);

		$argumentValueList = $command->getArgumentValueList($arguments);

		self::assertSame("id-value", (string)$argumentValueList->get("id"));
		self::assertSame(
			"option-value",
			(string)$argumentValueList->get("option")
		);
		self::assertSame(
			"required",
			(string)$argumentValueList->get("must-have-value")
		);
		self::assertSame(
			"user-extra",
			(string)$argumentValueList->get(Argument::USER_DATA)
		);
		self::assertTrue($argumentValueList->contains("no-value"));
	}

	#[DataProvider("unknownOptionProvider")]
	public function testCheckArguments_unknownOption(
		string $option,
		string $reportedOption
	):void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList(
			"script", $command->getName(), "id", "--type=example", $option
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			"Invalid argument supplied: \"$reportedOption\""
		);
		$command->checkArguments($arguments);
	}

	#[DataProvider("unknownOptionProvider")]
	public function testGetArgumentValueList_unknownOption(
		string $option,
		string $reportedOption
	):void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList(
			"script", $command->getName(), "id", "--type=example", $option
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			"Invalid argument supplied: \"$reportedOption\""
		);
		$command->getArgumentValueList($arguments);
	}

	public static function unknownOptionProvider():array {
		return [
			"long" => ["--unknown", "--unknown"],
			"long with value" => ["--unknown=value", "--unknown"],
			"short" => ["-x", "-x"],
			"short with value" => ["-x=value", "-x"],
			"chained" => ["-vx", "-x"],
			"short name with long prefix" => ["--v", "--v"],
			"long name with short prefix" => ["-verbose", "-e"],
		];
	}

	#[DataProvider("knownOptionProvider")]
	public function testCheckArguments_definedOptions(array $options):void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList(
			"script", $command->getName(), "id", ...$options
		);
		$command->checkArguments($arguments);
		self::assertTrue(true);
	}

	#[DataProvider("knownOptionProvider")]
	public function testGetArgumentValueList_definedOptions(array $options):void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList(
			"script", $command->getName(), "id", ...$options
		);
		$values = $command->getArgumentValueList($arguments);
		self::assertSame("example", (string)$values->get("type"));
		self::assertSame("log.txt", (string)$values->get("log"));
		self::assertTrue($values->contains("verbose"));
	}

	public static function knownOptionProvider():array {
		return [
			"long" => [["--type", "example", "--log=log.txt", "--verbose"]],
			"short" => [["-t=example", "-l", "log.txt", "-v"]],
			"chained" => [["-t", "example", "-vl", "log.txt"]],
		];
	}
	public function testCheckArguments_allowsFlagBeforeNamedArguments():void {
		$arguments = new ArgumentList(
			"script",
			"sync",
			"--pattern",
			"*",
			"--symlink",
			"path/to/source",
			"path/to/dest"
		);

		$command = new SyncLikeCommand();
		$command->checkArguments($arguments);
		self::assertTrue(true);
	}

	public function testGetArgumentValueList_allowsFlagBeforeNamedArguments():void {
		$arguments = new ArgumentList(
			"script",
			"sync",
			"--pattern",
			"*",
			"--symlink",
			"path/to/source",
			"path/to/dest"
		);

		$command = new SyncLikeCommand();
		$argumentValues = $command->getArgumentValueList($arguments);

		self::assertSame("*", $argumentValues->get("pattern")->get());
		self::assertNull($argumentValues->get("symlink")->get());
		self::assertSame("path/to/source", $argumentValues->get("source")->get());
		self::assertSame("path/to/dest", $argumentValues->get("dest")->get());
	}

	public function testGetArgumentValueList_allowsFlagBetweenNamedArguments():void {
		$arguments = new ArgumentList(
			"script",
			"sync",
			"path/to/source",
			"--symlink",
			"path/to/dest",
			"--pattern",
			"*"
		);

		$command = new SyncLikeCommand();
		$argumentValues = $command->getArgumentValueList($arguments);

		self::assertSame("*", $argumentValues->get("pattern")->get());
		self::assertNull($argumentValues->get("symlink")->get());
		self::assertSame("path/to/source", $argumentValues->get("source")->get());
		self::assertSame("path/to/dest", $argumentValues->get("dest")->get());
	}

	#[DataProvider("optionPlacementProvider")]
	public function testGetArgumentValueList_optionPlacementPreservesValues(array $tokens):void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList("script", $command->getName(), ...$tokens);

		$values = $command->getArgumentValueList($arguments);

		self::assertTrue($values->contains("id"), "The flag must not consume the source.");
		self::assertSame("source", (string)$values->get("id"));
		self::assertTrue($values->contains("name"), "The flag must not consume the destination.");
		self::assertSame("destination", (string)$values->get("name"));
		self::assertSame("*", (string)$values->get("type"));
		self::assertTrue($values->contains("verbose"));
		self::assertNull($values->get("verbose")->get());
	}

	public static function optionPlacementProvider():array {
		return [
			"long options after positionals" => [[
				"source", "destination", "--type", "*", "--verbose",
			]],
			"long options before positionals" => [[
				"--type", "*", "--verbose", "source", "destination",
			]],
			"long options between positionals" => [[
				"source", "--type", "*", "--verbose", "destination",
			]],
			"chained flag and value option before positionals" => [[
				"-vt", "*", "source", "destination",
			]],
			"inline value before positionals" => [[
				"--type=*", "--verbose", "source", "destination",
			]],
			"short options after positionals" => [[
				"source", "destination", "-t", "*", "-v",
			]],
			"short options before positionals" => [[
				"-t", "*", "-v", "source", "destination",
			]],
			"short options between positionals" => [[
				"source", "-t", "*", "-v", "destination",
			]],
		];
	}

	public function testGetArgumentValueList_chainedFlagsPreservePositionals():void {
		$command = new class extends AllParameterTypesCommand {
			public function getOptionalParameterList():array {
				return array_merge(
					parent::getOptionalParameterList(),
					[new Parameter(false, "quiet", "q")]
				);
			}
		};
		$arguments = new ArgumentList(
			"script", $command->getName(), "--type", "*", "-qv", "source", "destination"
		);

		$command->checkArguments($arguments);
		$values = $command->getArgumentValueList($arguments);

		self::assertSame("source", (string)$values->get("id"));
		self::assertSame("destination", (string)$values->get("name"));
		self::assertNull($values->get("quiet")->get());
		self::assertNull($values->get("verbose")->get());
	}

	public function testCheckArguments_flagBeforeRequiredPositional():void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList(
			"script", $command->getName(), "--type", "*", "--verbose", "source"
		);

		$command->checkArguments($arguments);
		self::assertTrue($arguments->contains($command->getRequiredParameterList()[0]));
	}

	public function testGetArgumentValueList_forcedCommandPreservesPositionals():void {
		$command = new AllParameterTypesCommand();
		$arguments = new CommandArgumentList(
			$command->getName(), "script", "--type", "*", "--verbose", "source"
		);

		$values = $command->getArgumentValueList($arguments);

		self::assertTrue($values->contains("id"), "The flag must not consume the source.");
		self::assertSame("source", (string)$values->get("id"));
		self::assertSame("*", (string)$values->get("type"));
		self::assertTrue($values->contains("verbose"));
		self::assertNull($values->get("verbose")->get());
	}

	public function testGetOptionalParameterList_empty():void {
		$command = new MultipleRequiredParameterCommand();
		$list = $command->getOptionalNamedParameterList();
		self::assertEmpty($list);

		$list = $command->getOptionalParameterList();
		self::assertEmpty($list);
	}

	public function testGetUsage_singleRequiredNamedParameter():void {
		$command = new SingleRequiredNamedParameterCommand();
		self::assertEquals(
			"Usage: single-required-named-parameter-command id",
			$command->getUsage()
		);
	}

	public function testGetUsage_multipleRequiredParameter():void {
		$command = new MultipleRequiredParameterCommand();
		self::assertEquals(
			"Usage: multiple-required-parameter-command id name "
			. "--framework|-f FRAMEWORK --example",
			$command->getUsage()
		);
	}

	public function testGetUsage_comboRequiredOptionalParameter():void {
		$command = new ComboRequiredOptionalParameterCommand();
		self::assertEquals(
			"Usage: combo-required-optional-parameter-command id [name] "
			. "--type|-t TYPE [--verbose|-v]",
			$command->getUsage()
		);
	}

	public function testGetUsage_allParameterTypes():void {
		$command = new AllParameterTypesCommand();
		self::assertEquals(
			"Usage: all-parameter-types-command id [name] --type|-t TYPE "
			. "[--log|-l LOG_PATH] [--verbose|-v]",
			$command->getUsage()
		);
	}

	public function testGetArgumentValueList():void {
		$idArgument = self::createMock(NamedArgument::class);
		$idArgument->method("getValue")->willReturn("test-id");
		$nameArgument = self::createMock(NamedArgument::class);
		$nameArgument->method("getValue")->willReturn("Test name!");
		$frameworkArgument = self::createMock(LongOptionArgument::class);
		$frameworkArgument->method("getKey")->willReturn("framework");
		$frameworkArgument->method("getValue")->willReturn("test-scaffolding");
		$exampleArgument = self::createMock(LongOptionArgument::class);
		$exampleArgument->method("getKey")->willReturn("example");
		$exampleArgument->method("getValue")
			->willReturn("just-a-quick-example");

		$args = [
			$idArgument,
			$nameArgument,
			$frameworkArgument,
			$exampleArgument,
		];
		$longArgs = [null, null, ["framework" => "php.gt"], "example"];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock($args, $longArgs);

		$command = new MultipleRequiredParameterCommand();
		$argumentValueList = $command->getArgumentValueList($argList);

		self::assertEquals("test-id", $argumentValueList->get("id"));
		self::assertEquals("Test name!", $argumentValueList->get("name"));
		self::assertEquals(
			"test-scaffolding",
			$argumentValueList->get("framework")
		);
	}

	public function testOutput_helpersWithNoStream():void {
		$command = new class extends TestCommand {
			public function exerciseOutputHelpers():void {
				$this->output("one");
				$this->setOutputPalette();
				$this->resetOutputPalette();
				$this->saveCursorPosition();
				$this->restoreCursorPosition();
				$this->moveCursorUp();
				$this->moveCursorDown();
				$this->moveCursorForward();
				$this->moveCursorBack();
				$this->setCursorColumn();
				$this->rewindCursor();
				$this->clearLine();
			}
		};

		$command->exerciseOutputHelpers();
		self::assertTrue(true);
	}

	public function testReadLine_withNoStreamUsesDefault():void {
		$command = new class extends TestCommand {
			public function readLinePublic(?string $default = null):string {
				return $this->readLine($default);
			}
		};
		$command->setStream();

		self::assertSame("fallback", $command->readLinePublic("fallback"));
		self::assertSame("", $command->readLinePublic());
	}

	public function testReadLine_withStream():void {
		$tmpDir = sys_get_temp_dir();
		$inPath = tempnam($tmpDir, "cli-in-");
		$outPath = tempnam($tmpDir, "cli-out-");
		$errPath = tempnam($tmpDir, "cli-err-");
		file_put_contents($inPath, "\nprovided-value\n");
		$stream = new Stream($inPath, $outPath, $errPath);

		$command = new class extends TestCommand {
			public function readLinePublic(?string $default = null):string {
				return $this->readLine($default);
			}
		};
		$command->setStream($stream);

		self::assertSame("provided-value", $command->readLinePublic("fallback"));

		unlink($inPath);
		unlink($outPath);
		unlink($errPath);
	}

	public function testCreateProgressBar_withNoStream():void {
		$command = new class extends TestCommand {
			public function createProgressBarPublic():ProgressBar {
				return $this->createProgressBar(10, "Work", 10);
			}
		};

		$progressBar = $command->createProgressBarPublic();
		self::assertInstanceOf(ProgressBar::class, $progressBar);
	}

	public function testGetUsage_withDocumentation():void {
		$command = new class extends Command {
			public function run(?ArgumentValueList $arguments = null):int {
				unset($arguments);
				return 0;
			}

			public function getName():string {
				return "doc-test";
			}

			public function getDescription():string {
				return "Documentation test command";
			}

			public function getRequiredNamedParameterList():array {
				return [];
			}

			public function getOptionalNamedParameterList():array {
				return [];
			}

			public function getRequiredParameterList():array {
				return [
					new Parameter(
						true,
						"framework",
						"f",
						"This is a required parameter with enough words "
						. "to trigger wrapping in docs output."
					),
				];
			}

			public function getOptionalParameterList():array {
				return [
					new Parameter(
						false,
						"verbose",
						null,
						"Optional verbose flag."
					),
				];
			}
		};

		$usage = $command->getUsage(true);
		self::assertStringContainsString("--framework|-f FRAMEWORK", $usage);
		self::assertStringContainsString("--verbose", $usage);
		self::assertStringContainsString("-f, --framework", $usage);
		self::assertStringContainsString("(Optional) Optional verbose flag.", $usage);
	}

	public function testGetUsage_includesNamedParameterDocumentation():void {
		$command = new class extends Command {
			public function run(?ArgumentValueList $arguments = null):int {
				unset($arguments);
				return 0;
			}

			public function getName():string {
				return "named-docs";
			}

			public function getDescription():string {
				return "Named docs test command";
			}

			public function getRequiredNamedParameterList():array {
				return [
					new class("target") extends NamedParameter {
						public function __construct(string $name) {
							parent::__construct($name);
							$this->documentation = "Required target name.";
						}
					},
				];
			}

			public function getOptionalNamedParameterList():array {
				return [
					new class("mode") extends NamedParameter {
						public function __construct(string $name) {
							parent::__construct($name);
							$this->documentation = "Optional mode selector.";
						}
					},
				];
			}

			public function getRequiredParameterList():array {
				return [];
			}

			public function getOptionalParameterList():array {
				return [];
			}
		};

		$usage = $command->getUsage(true);
		self::assertStringContainsString("target", $usage);
		self::assertStringContainsString("Required target name.", $usage);
		self::assertStringContainsString(
			"(Optional) Optional mode selector.",
			$usage
		);
	}

	public function testSetStream_output():void {
		/** @var Stream|MockObject $stream */
		$stream = $this->createMock(Stream::class);
		/** @var ArgumentValueList|MockObject $args */
		$args = $this->createMock(ArgumentValueList::class);

		$buffer = [
			StreamName::OUT->value => [],
			StreamName::ERROR->value => [],
		];
		$stream->method("writeLine")
			->willReturnCallback(function(
				string $message,
				StreamName $streamName
			)use(&$buffer) {
				$buffer[$streamName->value][] = $message;
			});

		$command = new HelpCommand("UnitTest");
		$command->run($args);
		self::assertEmpty($buffer[StreamName::OUT->value]);

		$command->setStream($stream);
		$command->run($args);
		self::assertNotEmpty($buffer[StreamName::OUT->value]);
		self::assertEmpty($buffer[StreamName::ERROR->value]);
	}

	public function testOutput_usesPaletteWhenProvided():void {
		$stream = $this->createMock(Stream::class);
		$stream->expects(self::once())
			->method("writeLine")
			->with(
				"single green message",
				StreamName::OUT,
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

	public function testWriteLine_usesStreamWriteLine():void {
		$stream = $this->createMock(Stream::class);
		$stream->expects(self::once())
			->method("writeLine")
			->with(
				"line output",
				StreamName::OUT
			);

		$command = new class extends TestCommand {
			public function writeLinePublic(string $message):void {
				$this->writeLine($message);
			}
		};
		$command->setStream($stream);
		$command->writeLinePublic("line output");
	}

	public function testSetOutputPalette_setAndReset():void {
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

	public function testSaveCursorPosition_cursorHelpers():void {
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
