<?php
namespace Gt\Cli\Test\Command;

use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgument;
use Gt\Cli\Argument\LongOptionArgument;
use Gt\Cli\Argument\NamedArgument;
use Gt\Cli\Argument\NotEnoughArgumentsException;
use Gt\Cli\CliException;
use Gt\Cli\Command\HelpCommand;
use Gt\Cli\Palette;
use Gt\Cli\Parameter\MissingRequiredParameterException;
use Gt\Cli\Parameter\MissingRequiredParameterValueException;
use Gt\Cli\Stream;
use Gt\Cli\Test\Helper\ArgumentMockTestCase;
use Gt\Cli\Test\Helper\Command\AllParameterTypesCommand;
use Gt\Cli\Test\Helper\Command\ComboRequiredOptionalParameterCommand;
use Gt\Cli\Test\Helper\Command\MultipleRequiredParameterCommand;
use Gt\Cli\Test\Helper\Command\SingleRequiredNamedParameterCommand;
use Gt\Cli\Test\Helper\Command\TestCommand;
use PHPUnit\Framework\MockObject\MockObject;

class CommandTest extends ArgumentMockTestCase {
	public function testSetOutput() {
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
				$buffer[$streamName] []= $message;
			});

		$command = new HelpCommand("UnitTest");
		$command->run($args);
		self::assertEmpty( $buffer[Stream::OUT]);

		$command->setStream($stream);
		$command->run($args);
		self::assertNotEmpty($buffer[Stream::OUT]);
		self::assertEmpty($buffer[Stream::ERROR]);
	}

	public function testGetName() {
		$command = new TestCommand();
		self::assertEquals("test", $command->getName());

		foreach(["first", "second", "third"] as $prefix) {
			$command = new TestCommand($prefix);

			self::assertEquals(
				"{$prefix}-test",
				$command->getName()
			);
		}
	}

	public function testGetDescription() {
		$command = new TestCommand();
		self::assertEquals(
			"A test command for unit testing",
			$command->getDescription()
		);
	}

	public function testCheckArgumentsSingleGood() {
		$args = [
			self::createMock(NamedArgument::class),
		];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createIteratorMock(
			ArgumentList::class,
			$args
		);

		$command = new SingleRequiredNamedParameterCommand();
		$exception = null;

		try {
			$command->checkArguments($argList);
		}
		catch(CliException $exception) {}
		self::assertNull($exception, "No exception should be thrown");
	}

	public function testCheckArgumentsSingleBad() {
// The first and only argument is now an incorrect "CommandArgument".
		$args = [
			self::createMock(CommandArgument::class),
		];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createIteratorMock(
			ArgumentList::class,
			$args
		);

		$command = new SingleRequiredNamedParameterCommand();
		$this->expectException(NotEnoughArgumentsException::class);
		$command->checkArguments($argList);
	}

	public function testCheckArgumentsMissingRequiredValue() {
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$longArgs = [
			null,
			null,
			["framework" => null],
			"example",
		];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock(
			$args,
			$longArgs
		);

		$command = new MultipleRequiredParameterCommand();

		$this->expectException(MissingRequiredParameterValueException::class);
		$command->checkArguments($argList);
	}

	public function testCheckArgumentsMultipleGood() {
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$longArgs = [
			null,
			null,
			["framework" => "php.gt"],
			"example"
		];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock(
			$args,
			$longArgs
		);

		$command = new MultipleRequiredParameterCommand();

		$exception = null;
		try {
			$command->checkArguments($argList);
		}
		catch(CliException $exception) {}
		self::assertNull($exception, "No exception should be thrown");
	}

	public function testCheckArgumentsMultipleBad() {
// Almost identical test to above, but the "framework" arg will not be provided.
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$longArgs = [
			null,
			null,
			["age" => "123"],
			"example"
		];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock(
			$args,
			$longArgs
		);

		$command = new MultipleRequiredParameterCommand();
		$this->expectException(MissingRequiredParameterException::class);
		$command->checkArguments($argList);
	}

	public function testGetRequiredNamedParameterList() {
		$command = new MultipleRequiredParameterCommand();
		$list = $command->getRequiredNamedParameterList();
		$requiredNames = [];

		foreach($list as $item) {
			$requiredNames []= $item->getOptionName();
		}

		self::assertContains("id", $requiredNames);
		self::assertContains("name", $requiredNames);
		self::assertCount(2, $requiredNames);
	}

	public function testGetRequiredParameterList() {
		$command = new ComboRequiredOptionalParameterCommand();
		$list = $command->getRequiredParameterList();
		$requiredLongOptions = [];

		foreach($list as $item) {
			$requiredLongOptions []= $item->getLongOption();
		}

		self::assertContains("type", $requiredLongOptions);
		self::assertCount(1, $requiredLongOptions);
	}

	public function testGetParameterListWhenThereIsNone() {
		$command = new MultipleRequiredParameterCommand();
		$list = $command->getOptionalNamedParameterList();
		self::assertEmpty($list);

		$list = $command->getOptionalParameterList();
		self::assertEmpty($list);
	}

	public function testGetUsageSingleRequiredNamedParameter() {
		$command = new SingleRequiredNamedParameterCommand();
		self::assertEquals(
			"Usage: single-required-named-parameter-command id",
			$command->getUsage()
		);
	}

	public function testGetUsageMultipleRequiredParameter() {
		$command = new MultipleRequiredParameterCommand();
		self::assertEquals(
			"Usage: multiple-required-parameter-command id name --framework|-f FRAMEWORK --example",
			$command->getUsage()
		);
	}

	public function testGetUsageComboRequiredOptionalParameter() {
		$command = new ComboRequiredOptionalParameterCommand();
		self::assertEquals(
			"Usage: combo-required-optional-parameter-command id [name] --type|-t TYPE [--verbose|-v]",
			$command->getUsage()
		);
	}

	public function testGetUsageAllParameterTypes() {
		$command = new AllParameterTypesCommand();
		self::assertEquals(
			"Usage: all-parameter-types-command id [name] --type|-t TYPE [--log|-l LOG_PATH] [--verbose|-v]",
			$command->getUsage()
		);
	}

	public function testGetArgumentValueList() {
		$idArgument = self::createMock(NamedArgument::class);
		$idArgument->method("getValue")
			->willReturn("test-id");
		$nameArgument = self::createMock(NamedArgument::class);
		$nameArgument->method("getValue")
			->willReturn("Test name!");
		$frameworkArgument = self::createMock(LongOptionArgument::class);
		$frameworkArgument->method("getKey")
			->willReturn("framework");
		$frameworkArgument->method("getValue")
			->willReturn("test-scaffolding");
		$exampleArgument = self::createMock(LongOptionArgument::class);
		$exampleArgument->method("getValue")
			->willReturn("just-a-quick-example");

		$args = [
			$idArgument,
			$nameArgument,
			$frameworkArgument,
			$exampleArgument,
		];
		$longArgs = [
			null,
			null,
			["framework" => "php.gt"],
			"example"
		];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock(
			$args,
			$longArgs
		);

		$command = new MultipleRequiredParameterCommand();
		$argumentValueList = $command->getArgumentValueList($argList);

		self::assertEquals("test-id", $argumentValueList->get("id"));
		self::assertEquals("Test name!", $argumentValueList->get("name"));
		self::assertEquals("test-scaffolding", $argumentValueList->get("framework"));
	}

	public function testOutputUsesPaletteWhenProvided() {
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

	public function testSetAndResetOutputPalette() {
		$stream = $this->createMock(Stream::class);
		$stream->expects(self::once())
			->method("setOutputPalette")
			->with(Palette::RED, Palette::BLACK);
		$stream->expects(self::once())
			->method("resetOutputPalette");

		$command = new class extends TestCommand {
			public function setPalettePublic(?Palette $foreground, ?Palette $background):void {
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
}
