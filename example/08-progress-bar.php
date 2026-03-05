<?php
use Gt\Cli\Application;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgumentList;
use Gt\Cli\Command\Command;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$progressCommand = new class extends Command {
	public function run(ArgumentValueList $arguments = null):void {
		$total = 100;
		$progressBar = $this->createProgressBar(
			$total,
			"Installing",
			30
		);

		for($current = 0; $current <= $total; $current++) {
			$progressBar->setProgress($current);
			usleep(35_000);
		}

		$progressBar->finish("Installation complete");
	}

	public function getName():string {
		return "progress";
	}

	public function getDescription():string {
		return "Demonstrate a dynamic in-place progress bar";
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
	"Progress bar example",
	new CommandArgumentList("progress", ...$argv),
	$progressCommand
);
$app->run();
