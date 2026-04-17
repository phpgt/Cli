<?php
namespace Gt\Cli\Test\Command;

use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Argument\CommandArgument;
use Gt\Cli\Argument\LongOptionArgument;
use Gt\Cli\Argument\NamedArgument;
use Gt\Cli\Argument\NotEnoughArgumentsException;
use Gt\Cli\Parameter\MissingRequiredParameterException;
use Gt\Cli\Parameter\MissingRequiredParameterValueException;
use Gt\Cli\Test\Helper\ArgumentMockTestCase;
use Gt\Cli\Test\Helper\Command\ComboRequiredOptionalParameterCommand;
use Gt\Cli\Test\Helper\Command\MultipleRequiredParameterCommand;
use Gt\Cli\Test\Helper\Command\SingleRequiredNamedParameterCommand;
use Gt\Cli\Test\Helper\Command\SyncLikeCommand;
use Gt\Cli\Test\Helper\Command\TestCommand;
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

	public function testCheckArgumentsSingleGood():void {
		$args = [self::createMock(NamedArgument::class)];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createIteratorMock(ArgumentList::class, $args);

		$command = new SingleRequiredNamedParameterCommand();
		$command->checkArguments($argList);
		self::assertTrue(true);
	}

	public function testCheckArgumentsSingleBad():void {
		$args = [self::createMock(CommandArgument::class)];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createIteratorMock(ArgumentList::class, $args);

		$command = new SingleRequiredNamedParameterCommand();
		$this->expectException(NotEnoughArgumentsException::class);
		$command->checkArguments($argList);
	}

	public function testCheckArgumentsMissingRequiredValue():void {
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

	public function testCheckArgumentsMultipleGood():void {
		$args = [
			self::createMock(NamedArgument::class),
			self::createMock(NamedArgument::class),
			self::createMock(LongOptionArgument::class),
			self::createMock(LongOptionArgument::class),
		];
		$longArgs = [null, null, ["framework" => "php.gt"], "example"];

		/** @var ArgumentList|MockObject $argList */
		$argList = $this->createArgumentListMock($args, $longArgs);

		$command = new MultipleRequiredParameterCommand();
		$command->checkArguments($argList);
		self::assertTrue(true);
	}

	public function testCheckArgumentsMultipleBad():void {
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

	public function testCheckArgumentsAllowsFlagBeforeNamedArguments():void {
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

	public function testGetArgumentValueListAllowsFlagBeforeNamedArguments():void {
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

	public function testGetArgumentValueListAllowsFlagBetweenNamedArguments():void {
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
}
