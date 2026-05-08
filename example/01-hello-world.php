<?php
use GT\Cli\Application;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Argument\CommandArgumentList;
use GT\Cli\Command\Command;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$helloWorldCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$this->output("Hello, world!");
		return 0;
	}

	public function getName():string {
		return "hello";
	}

	public function getDescription():string {
		return "Output a hello world message";
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
	"Hello world example",
	new CommandArgumentList("hello", ...$argv),
	$helloWorldCommand,
);
$app->run();
