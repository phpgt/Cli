<?php
use GT\Cli\Application;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Argument\CommandArgumentList;
use GT\Cli\Command\Command;
use GT\Cli\Parameter\NamedParameter;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$greeterCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$name = (string)$arguments->get("name", "you");
		$this->output("Hello, $name!");
		return 0;
	}

	public function getName():string {
		return "greet";
	}

	public function getDescription():string {
		return "Greet a person by name";
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
		return [];
	}
};

$app = new Application(
	"Greeter example",
	new CommandArgumentList("greet", ...$argv),
	$greeterCommand
);
$app->run();
