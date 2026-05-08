<?php
use GT\Cli\Application;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Argument\CommandArgumentList;
use GT\Cli\Command\Command;
use GT\Cli\Palette;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$colourCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$this->output("Single green message", Palette::GREEN);

		$this->setOutputPalette(Palette::RED, Palette::BLACK);
		$this->output("This line uses the default red-on-black palette");
		$this->setOutputPalette(Palette::BLACK, Palette::YELLOW);
		$this->output("What does black on yellow look like?");

		$this->resetOutputPalette();
		$this->output("Palette reset to terminal default");
		return 0;
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
