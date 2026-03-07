<?php
use Gt\Cli\Application;
use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Command\CommandException;
use Gt\Cli\Palette;
use Gt\Cli\Parameter\NamedParameter;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$sumCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$left = (string)$arguments->get("left");
		$right = (string)$arguments->get("right");

		if(!is_numeric($left) || !is_numeric($right)) {
			throw new CommandException("Both values must be numeric");
		}

		$total = (float)$left + (float)$right;
		$this->output("$left + $right = $total", Palette::GREEN);
		return 0;
	}

	public function getName():string {
		return "sum";
	}

	public function getDescription():string {
		return "Add two numbers";
	}

	public function getRequiredNamedParameterList():array {
		return [
			new NamedParameter("left"),
			new NamedParameter("right"),
		];
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

$repeatCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$text = (string)$arguments->get("text");
		$count = (int)(string)$arguments->get("count", "1");

		if($count < 1) {
			throw new CommandException("Count must be at least 1");
		}

		for($i = 0; $i < $count; $i++) {
			$this->output($text);
		}

		return 0;
	}

	public function getName():string {
		return "repeat";
	}

	public function getDescription():string {
		return "Repeat text N times";
	}

	public function getRequiredNamedParameterList():array {
		return [
			new NamedParameter("text"),
		];
	}

	public function getOptionalNamedParameterList():array {
		return [
			new NamedParameter("count"),
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
	"Multi-command example",
	new ArgumentList(...$argv),
	$sumCommand,
	$repeatCommand
);
$app->run();
