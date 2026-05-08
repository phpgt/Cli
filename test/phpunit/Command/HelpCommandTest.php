<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Command\HelpCommand;
use GT\Cli\Stream;
use GT\Cli\Test\Helper\Command\TestCommand;
use PHPUnit\Framework\TestCase;

class HelpCommandTest extends TestCase {
	public function testPublicMetadataMethods():void {
		$command = new HelpCommand("Example app", "cli", [new TestCommand()]);
		self::assertSame("help", $command->getName());
		self::assertSame(
			"Display information about available commands",
			$command->getDescription()
		);
		self::assertSame([], $command->getRequiredNamedParameterList());
		self::assertCount(1, $command->getOptionalNamedParameterList());
		self::assertSame([], $command->getRequiredParameterList());
		self::assertCount(1, $command->getOptionalParameterList());
	}

	public function testRunForAllCommands():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$command = new HelpCommand("Example app", "cli", [new TestCommand()]);
		$command->setStream($stream);

		$args = new ArgumentValueList();
		$command->run($args);

		$out = $stream->getOutStream();
		$out->rewind();
		$output = $out->fread(4096);
		self::assertStringContainsString("Available commands:", $output);
		self::assertStringContainsString("test", $output);
		self::assertStringContainsString("help", $output);
	}

	public function testRunForKnownCommand():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$command = new HelpCommand("Example app", "cli", [new TestCommand()]);
		$command->setStream($stream);

		$args = new ArgumentValueList();
		$args->set("command", "test");
		$command->run($args);

		$out = $stream->getOutStream();
		$out->rewind();
		$output = $out->fread(4096);
		self::assertStringContainsString("test: A test command", $output);
		self::assertStringContainsString("Usage: test id", $output);
	}

	public function testRunForUnknownCommand():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$command = new HelpCommand("Example app", "cli", [new TestCommand()]);
		$command->setStream($stream);

		$args = new ArgumentValueList();
		$args->set("command", "missing");
		$command->run($args);

		$out = $stream->getOutStream();
		$out->rewind();
		$output = $out->fread(4096);
		self::assertStringContainsString("No help for command `missing`.", $output);
	}

	public function testRunWithNullArguments():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$command = new HelpCommand("Example app", "cli", [new TestCommand()]);
		$command->setStream($stream);
		$command->run(null);

		$out = $stream->getOutStream();
		$out->rewind();
		$output = $out->fread(4096);
		self::assertStringContainsString("No help for command ``.", $output);
	}

	public function testRunWithNoCommandsAvailableMessage():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$command = new HelpCommand("Example app", "cli");
		$command->setStream($stream);

		$reflection = new \ReflectionClass($command);
		$property = $reflection->getProperty("applicationCommandList");
		$property->setValue($command, []);

		$args = new ArgumentValueList();
		$command->run($args);

		$out = $stream->getOutStream();
		$out->rewind();
		$output = $out->fread(4096);
		self::assertStringContainsString(
			"There are no commands available",
			$output
		);
	}
}
