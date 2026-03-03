<?php
use Gt\Cli\Application;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgumentList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$greeterCommand = new class extends Command {
	public function run(ArgumentValueList $arguments = null):void {
		$name = (string)$arguments->get("name", "you");
		$this->output("Hello, $name!");
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
