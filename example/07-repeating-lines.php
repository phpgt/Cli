<?php
use Gt\Cli\Application;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgumentList;
use Gt\Cli\Command\Command;
use Gt\Cli\Stream;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$repeatingCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):?int {
		$this->output("Demonstrating repeating line suppression...");
		$this->output("");

		for($i = 0; $i < 10; $i++) {
			$jMax = max(1, rand(-10, 10));
			for($j = 1; $j <= $jMax; $j++) {
				$this->output("Downloading chunk $i...");
				usleep(120_000);
			}
		}

		$this->output("Download complete.");
		$this->output("");
		$this->output("Repeated lines are marked with '" . Stream::REPEAT_CHAR . "'.");
		return 0;
	}

	public function getName():string {
		return "repeat";
	}

	public function getDescription():string {
		return "Demonstrate repeat markers for identical consecutive output lines";
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

$app = new Application(
	"Repeating line example",
	new CommandArgumentList("repeat", ...$argv),
	$repeatingCommand
);
$app->run();
