<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\ArgumentList;
use GT\Cli\Argument\LongOptionArgument;
use GT\Cli\Argument\NamedArgument;
use GT\Cli\Test\Helper\ArgumentMockTestCase;
use GT\Cli\Test\Helper\Command\AllParameterTypesCommand;
use GT\Cli\Test\Helper\Command\ComboRequiredOptionalParameterCommand;
use GT\Cli\Test\Helper\Command\MultipleRequiredParameterCommand;
use GT\Cli\Test\Helper\Command\SingleRequiredNamedParameterCommand;
use PHPUnit\Framework\MockObject\MockObject;

class CommandUsageTest extends ArgumentMockTestCase {
	public function testGetParameterListWhenThereIsNone():void {
		$command = new MultipleRequiredParameterCommand();
		$list = $command->getOptionalNamedParameterList();
		self::assertEmpty($list);

		$list = $command->getOptionalParameterList();
		self::assertEmpty($list);
	}

	public function testGetUsageSingleRequiredNamedParameter():void {
		$command = new SingleRequiredNamedParameterCommand();
		self::assertEquals(
			"Usage: single-required-named-parameter-command id",
			$command->getUsage()
		);
	}

	public function testGetUsageMultipleRequiredParameter():void {
		$command = new MultipleRequiredParameterCommand();
		self::assertEquals(
			"Usage: multiple-required-parameter-command id name "
			. "--framework|-f FRAMEWORK --example",
			$command->getUsage()
		);
	}

	public function testGetUsageComboRequiredOptionalParameter():void {
		$command = new ComboRequiredOptionalParameterCommand();
		self::assertEquals(
			"Usage: combo-required-optional-parameter-command id [name] "
			. "--type|-t TYPE [--verbose|-v]",
			$command->getUsage()
		);
	}

	public function testGetUsageAllParameterTypes():void {
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
}
