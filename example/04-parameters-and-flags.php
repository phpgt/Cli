<?php
use Gt\Cli\Application;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgumentList;
use Gt\Cli\Command\Command;
use Gt\Cli\Palette;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$deployCommand = new class extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		$environment = (string)$arguments->get("environment");
		$service = (string)$arguments->get("service", "web");
		$tag = (string)$arguments->get("tag");
		$dryRun = $arguments->contains("dry-run");
		$verbose = $arguments->contains("verbose");

		if($dryRun) {
			$this->output("DRY RUN: no deployment will happen", Palette::YELLOW);
		}
		else {
			$this->output("Starting deployment...", Palette::GREEN);
		}

		$this->output("Environment: $environment");
		$this->output("Service: $service");
		$this->output("Tag: $tag");

		if($verbose) {
			$this->setOutputPalette(Palette::CYAN);
			$this->output("Verbose mode enabled");
			$this->output("Simulating build, upload and health checks");
			$this->resetOutputPalette();
		}

		$this->output("Done.");
		return 0;
	}

	public function getName():string {
		return "deploy";
	}

	public function getDescription():string {
		return "Example of required parameters, options and flags";
	}

	public function getRequiredNamedParameterList():array {
		return [
			new NamedParameter("environment"),
		];
	}

	public function getOptionalNamedParameterList():array {
		return [
			new NamedParameter("service"),
		];
	}

	public function getRequiredParameterList():array {
		return [
			new Parameter(true, "tag", "t", "Release tag to deploy"),
		];
	}

	public function getOptionalParameterList():array {
		return [
			new Parameter(false, "dry-run", "d", "Preview only"),
			new Parameter(false, "verbose", "v", "Show extra detail"),
		];
	}
};

$app = new Application(
	"Parameters and flags example",
	new CommandArgumentList("deploy", ...$argv),
	$deployCommand
);
$app->run();
