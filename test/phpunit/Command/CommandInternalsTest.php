<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\Argument;
use GT\Cli\Argument\ArgumentList;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Command\Command;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;
use GT\Cli\ProgressBar;
use GT\Cli\Stream;
use GT\Cli\Test\Helper\Command\TestCommand;
use PHPUnit\Framework\TestCase;

class CommandInternalsTest extends TestCase {
	public function testOutputHelpersWithNoStreamDoNotError():void {
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

	public function testReadLineWithNoStreamUsesDefault():void {
		$command = new class extends TestCommand {
			public function readLinePublic(?string $default = null):string {
				return $this->readLine($default);
			}
		};
		$command->setStream();

		self::assertSame("fallback", $command->readLinePublic("fallback"));
		self::assertSame("", $command->readLinePublic());
	}

	public function testReadLineWithStream():void {
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

	public function testCreateProgressBarWithNoStream():void {
		$command = new class extends TestCommand {
			public function createProgressBarPublic():ProgressBar {
				return $this->createProgressBar(10, "Work", 10);
			}
		};

		$progressBar = $command->createProgressBarPublic();
		self::assertInstanceOf(ProgressBar::class, $progressBar);
	}

	public function testGetArgumentValueListIncludesUserAndUnknownOptions():void {
		$command = new TestCommand();
		$arguments = new ArgumentList(
			"script",
			"test",
			"id-value",
			"option-value",
			"user-extra",
			"--must-have-value",
			"required",
			"--custom",
			"custom-value",
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
		self::assertSame("custom-value", (string)$argumentValueList->get("custom"));
		self::assertSame(
			"user-extra",
			(string)$argumentValueList->get(Argument::USER_DATA)
		);
		self::assertTrue($argumentValueList->contains("no-value"));
	}

	public function testGetUsageWithDocumentation():void {
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

	public function testGetUsageIncludesNamedParameterDocumentation():void {
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
}
