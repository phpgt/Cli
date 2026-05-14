<?php
namespace GT\Cli\Test;

use GT\Cli\Application;
use GT\Cli\Argument\ArgumentList;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Argument\LongOptionArgument;
use GT\Cli\Argument\NamedArgument;
use GT\Cli\Argument\NotEnoughArgumentsException;
use GT\Cli\Command\Command;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;
use GT\Cli\StreamName;
use GT\Cli\Test\Helper\ArgumentMockTestCase;
use GT\Cli\Test\Helper\Command\TestCommand;
use PHPUnit\Framework\MockObject\MockObject;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ApplicationTest extends ArgumentMockTestCase {
	protected $tmp;
	protected $inPath;
	protected $outPath;
	protected $errPath;

	public function setUp():void {
		$this->tmp = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"cli",
		]);
		if(!is_dir($this->tmp)) {
			mkdir($this->tmp, 0775, true);
		}

		$this->inPath = implode(
			DIRECTORY_SEPARATOR,
			[$this->tmp, StreamName::IN->value]
		);
		$this->outPath = implode(
			DIRECTORY_SEPARATOR,
			[$this->tmp, StreamName::OUT->value]
		);
		$this->errPath = implode(
			DIRECTORY_SEPARATOR,
			[$this->tmp, StreamName::ERROR->value]
		);
		touch($this->inPath);
		touch($this->outPath);
		touch($this->errPath);
	}

	public function tearDown():void {
		$fileList = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$this->tmp,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach($fileList as $fileInfo) {
			$function = $fileInfo->isDir()
				? "rmdir"
				: "unlink";

			$function($fileInfo->getRealPath());
		}

		rmdir($this->tmp);
	}

	public function testSetStream() {
		$application = new Application("test-app");
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->setExitHandler(fn() => null);
		$application->run();

		self::assertStreamContains(
			"Application has received no commands",
			StreamName::ERROR
		);
		self::assertStreamEmpty(StreamName::OUT);
	}

	public function testCommandArgumentInvalid() {
		/** @var ArgumentList|MockObject $arguments */
		$arguments = self::createMock(ArgumentList::class);
		$arguments->method("getCommandName")
			->willReturn("test-command");

		$application = new Application(
			"test-app",
			$arguments
		);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->setExitHandler(fn() => null);
		$application->run();

		self::assertStreamContains(
			"Invalid command: \"test-command\"",
			StreamName::ERROR
		);
	}

	public function testCommandArgumentsInvalid() {
		/** @var MockObject|ArgumentList $arguments */
		$arguments = self::createMock(ArgumentList::class);
		$arguments->method("getCommandName")
			->willReturn("invalid-test");

		$application = new Application(
			"test-app",
			$arguments,
			new TestCommand("invalid")
		);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$actualErrCode = null;
		$application->setExitHandler(function(int $errCode)use(&$actualErrCode) {
			$actualErrCode = $errCode;
		});
		$application->run();

		self::assertStreamContains(
			"Usage: invalid-test",
			StreamName::ERROR
		);
		self::assertSame(1, $actualErrCode);
	}

	public function testCommandRun() {
		$idArgument = self::createMock(NamedArgument::class);
		$idArgument->method("getValue")
			->willReturn("abcde");
		$mustHaveValueArgument = self::createMock(LongOptionArgument::class);
		$mustHaveValueArgument->method("getKey")
			->willReturn("must-have-value");
		$mustHaveValueArgument->method("getValue")
			->willReturn("1234");

		$args = [
			$idArgument,
			$mustHaveValueArgument,
		];
		$longArgs = [
			"abcde",
			["must-have-value" => "1234"],
		];

		/** @var MockObject|ArgumentList $arguments */
		$arguments = self::createArgumentListMock(
			$args,
			$longArgs
		);

		$arguments->method("getCommandName")
			->willReturn("valid-test");

		$application = new Application(
			"test-app",
			$arguments,
			new TestCommand("valid")
		);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->run();

		self::assertStreamEmpty(StreamName::ERROR);

		self::assertStreamContains(
			"Command ID: abcde",
			StreamName::OUT
		);
		self::assertStreamContains(
			"Command running successfully",
			StreamName::OUT
		);
		self::assertStreamContains(
			"No Option set",
			StreamName::OUT
		);
		self::assertStreamContains(
			"Must-have-value: 1234",
			StreamName::OUT
		);
		self::assertStreamContains(
			"No-value argument not set",
			StreamName::OUT
		);
	}

	public function testExitCodeNotEnoughArguments() {
		$parameter = self::createMock(NamedParameter::class);
		$argumentsList = self::createMock(ArgumentList::class);
		$argumentsList->method("getCommandName")
			->willReturn("example");
		$command1 = new class($parameter) extends Command {
			private array $unitTestRequiredParams;

			public function __construct(Parameter...$requiredParams) {
				$this->unitTestRequiredParams = $requiredParams;
			}

				public function run(?ArgumentValueList $arguments = null):int {
					unset($arguments);
					return 0;
				}

			public function getName():string {
				return "example";
			}

			public function getDescription():string {
				return "Just an example";
			}

			public function getRequiredNamedParameterList():array {
				return [
					$this->unitTestRequiredParams[0],
				];
			}

			public function getOptionalNamedParameterList():array {
				return [];
			}

			public function getRequiredParameterList():array {
				return [];
			}

			public function getOptionalParameterList():array {
				return [];
			}
		};
		$actualErrorCode = null;
		$sut = new Application("Test app", $argumentsList, $command1);
		$sut->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$sut->setExitHandler(function(int $errorCode) use(&$actualErrorCode) {
			$actualErrorCode = $errorCode;
		});
		$sut->run();
		self::assertSame(1, $actualErrorCode);
		self::assertStreamContains(
			"Error: Not enough arguments passed. Passed: 0 required: 1.",
			StreamName::ERROR
		);
	}

	public function testExitCodeReturnedFromCommand():void {
		/** @var ArgumentList|MockObject $arguments */
		$arguments = self::createArgumentListMock();
		$arguments->method("getCommandName")
			->willReturn("example");

		$command = new class extends Command {
				public function run(?ArgumentValueList $arguments = null):int {
					unset($arguments);
					return 9;
				}

			public function getName():string {
				return "example";
			}

			public function getDescription():string {
				return "A command that returns an exit code.";
			}

			public function getRequiredNamedParameterList():array {
				return [];
			}

			public function getOptionalNamedParameterList():array {
				return [];
			}

			public function getRequiredParameterList():array {
				return [];
			}

			public function getOptionalParameterList():array {
				return [];
			}
		};

		$actualExitCode = null;
		$application = new Application("test-app", $arguments, $command);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->setExitHandler(function(int $exitCode) use(&$actualExitCode) {
			$actualExitCode = $exitCode;
		});
		$application->run();

		self::assertSame(9, $actualExitCode);
	}

	public function testExitCodeReturnedFromHelpFlag():void {
		$helpArgument = self::createMock(LongOptionArgument::class);
		$helpArgument->method("getKey")->willReturn("help");
		$helpArgument->method("getValue")->willReturn("");
		$arguments = self::createArgumentListMock([$helpArgument]);
		$arguments->method("getCommandName")->willReturn("valid-test");

		$actualExitCode = null;
		$application = new Application(
			"test-app",
			$arguments,
			new TestCommand("valid")
		);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->setExitHandler(function(int $exitCode) use(&$actualExitCode) {
			$actualExitCode = $exitCode;
		});
		$application->run();

		self::assertSame(0, $actualExitCode);
		self::assertStreamContains(
			"valid-test: A test command for unit testing",
			StreamName::OUT
		);
		self::assertStreamEmpty(StreamName::ERROR);
	}

	public function testExitCodeReturnedFromVersionFlag():void {
		$versionArgument = self::createMock(LongOptionArgument::class);
		$versionArgument->method("getKey")->willReturn("version");
		$versionArgument->method("getValue")->willReturn("");
		$arguments = self::createArgumentListMock([$versionArgument]);
		$arguments->method("getCommandName")->willReturn("valid-test");

		$actualExitCode = null;
		$application = new Application(
			"test-app",
			$arguments,
			new TestCommand("valid")
		);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->setExitHandler(function(int $exitCode) use(&$actualExitCode) {
			$actualExitCode = $exitCode;
		});
		$application->run();

		self::assertSame(0, $actualExitCode);
		self::assertStreamEmpty(StreamName::ERROR);
		self::assertStringNotContainsString(
			"Command running successfully",
			(string)file_get_contents($this->outPath)
		);
	}

	public function testMissingRequiredParameterIsReportedByApplication():void {
		$arguments = new ArgumentList(
			"script-name",
			"requires-parameter",
			"--flag"
		);

		$command = new class extends Command {
			public function run(?ArgumentValueList $arguments = null):int {
				unset($arguments);
				return 0;
			}

			public function getName():string {
				return "requires-parameter";
			}

			public function getDescription():string {
				return "Command requiring --required.";
			}

			public function getRequiredNamedParameterList():array {
				return [];
			}

			public function getOptionalNamedParameterList():array {
				return [];
			}

			public function getRequiredParameterList():array {
				return [new Parameter(false, "required", "r")];
			}

			public function getOptionalParameterList():array {
				return [new Parameter(false, "flag", "f")];
			}
		};

		$actualExitCode = null;
		$application = new Application("test-app", $arguments, $command);
		$application->setStream(
			$this->inPath,
			$this->outPath,
			$this->errPath
		);
		$application->setExitHandler(function(int $exitCode) use(&$actualExitCode) {
			$actualExitCode = $exitCode;
		});
		$application->run();

		self::assertSame(1, $actualExitCode);
		self::assertStreamContains(
			"Error - Missing required parameter: Error: required (r)",
			StreamName::ERROR
		);
	}

	protected function assertStreamContains(
		string $message,
		StreamName $streamName
	):void {
		$streamPath = $this->getStreamPathByName($streamName);
		$streamContents = file_get_contents($streamPath);
		self::assertStringContainsString(
			$message,
			$streamContents,
			"Stream should contain message."
		);
	}

	protected function assertStreamEmpty(
		StreamName $streamName
	):void {
		$streamPath = $this->getStreamPathByName($streamName);
		$streamContents = trim(file_get_contents($streamPath));
		self::assertEmpty($streamContents, "Contents: \"$streamContents\"");
	}

	protected function getStreamPathByName(StreamName $name):string {
		return implode(DIRECTORY_SEPARATOR, [
			$this->tmp,
			$name->value,
		]);
	}
}
