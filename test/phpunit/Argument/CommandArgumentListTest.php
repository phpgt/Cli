<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\CommandArgumentList;
use PHPUnit\Framework\TestCase;

class CommandArgumentListTest extends TestCase {
	public function testForcedCommandIsFirstArgument():void {
		$argumentList = new CommandArgumentList(
			"forced-command",
			"script-name",
			"--one",
			"two"
		);

		self::assertSame("script-name", $argumentList->getScript());
		self::assertSame("forced-command", $argumentList->getCommandName());
	}
}
