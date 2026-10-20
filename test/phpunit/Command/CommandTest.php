<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\ArgumentList;
use GT\Cli\Argument\CommandArgument;
use GT\Cli\Argument\Argument;
use GT\Cli\Argument\InvalidArgumentException;
use GT\Cli\Argument\LongOptionArgument;
use GT\Cli\Argument\NamedArgument;
use GT\Cli\Argument\NotEnoughArgumentsException;
use GT\Cli\Parameter\MissingRequiredParameterException;
use GT\Cli\Parameter\MissingRequiredParameterValueException;
use GT\Cli\Test\Helper\ArgumentMockTestCase;
use GT\Cli\Test\Helper\Command\ComboRequiredOptionalParameterCommand;
use GT\Cli\Test\Helper\Command\MultipleRequiredParameterCommand;
use GT\Cli\Test\Helper\Command\SingleRequiredNamedParameterCommand;
use GT\Cli\Test\Helper\Command\TestCommand;
use GT\Cli\Test\Helper\Command\AllParameterTypesCommand;
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
}
