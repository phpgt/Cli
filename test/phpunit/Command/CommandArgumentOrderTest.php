<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\ArgumentList;
use GT\Cli\Argument\CommandArgumentList;
use GT\Cli\Parameter\Parameter;
use GT\Cli\Test\Helper\Command\AllParameterTypesCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CommandArgumentOrderTest extends TestCase {
	#[DataProvider("optionPlacementProvider")]
	public function testOptionPlacementPreservesArgumentValues(array $tokens):void {
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

	public function testChainedFlagsDoNotConsumeFollowingPositional():void {
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

	public function testFlagBeforeRequiredPositionalPassesValidation():void {
		$command = new AllParameterTypesCommand();
		$arguments = new ArgumentList(
			"script", $command->getName(), "--type", "*", "--verbose", "source"
		);

		$command->checkArguments($arguments);
		self::assertTrue($arguments->contains($command->getRequiredParameterList()[0]));
	}

	public function testForcedCommandPreservesPositionalFollowingFlag():void {
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
}
