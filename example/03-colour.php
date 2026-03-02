<?php
use Gt\Cli\Application;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgumentList;
use Gt\Cli\Command\Command;
use Gt\Cli\Palette;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$colourCommand = new class extends Command {
	public function run(ArgumentValueList $arguments = null):void {
		$this->output("Single green message", Palette::GREEN);

		$this->setOutputPalette(Palette::RED, Palette::BLACK);
		$this->output("This line uses the default red-on-black palette");
		$this->setOutputPalette(Palette::BLACK, Palette::YELLOW);
		$this->output("What does black on yellow look like?");

		$this->resetOutputPalette();
		$this->output("Palette reset to terminal default");
	}

	public function getName():string {
		return "colour";
	}

	public function getDescription():string {
		return "Demonstrate coloured output";
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
	"Colour example",
	new CommandArgumentList("colour", ...$argv),
	$colourCommand
);
$app->run();
