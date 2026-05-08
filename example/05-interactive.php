<?php
use GT\Cli\Application;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Argument\CommandArgumentList;
use GT\Cli\Command\Command;
use GT\Cli\Palette;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$interactiveCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$name = "";
		if($arguments->contains("name")) {
			$name = (string)$arguments->get("name");
		}
		else {
			$this->output("What's your name?", Palette::BLUE);
			$name = $this->readLine("you");
		}

		$greeting = "Hello, $name!";
		if($arguments->contains("shout")) {
			$greeting = strtoupper($greeting);
		}

		$this->setOutputPalette(Palette::GREEN);
		$this->output($greeting);
		$this->resetOutputPalette();
		return 0;
	}

	public function getName():string {
		return "interactive";
	}

	public function getDescription():string {
		return "Example of interactive input with optional flags";
	}

	public function getRequiredNamedParameterList():array {
		return [];
	}

	public function getOptionalNamedParameterList():array {
		return [
			new NamedParameter("name"),
		];
	}

	public function getRequiredParameterList():array {
		return [];
	}

	public function getOptionalParameterList():array {
		return [
			new Parameter(false, "shout", "s", "Output uppercase greeting"),
		];
	}
};

$app = new Application(
	"Interactive example",
	new CommandArgumentList("interactive", ...$argv),
	$interactiveCommand
);
$app->run();
