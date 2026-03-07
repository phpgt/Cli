<?php
namespace Gt\Cli\Test\Helper\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

class TestCommand extends Command {
	protected $prefix;

	public function __construct(?string $prefix = null) {
		if(!is_null($prefix)) {
			$prefix .= "-";
		}

		$this->prefix = $prefix;
	}

	public function run(?ArgumentValueList $arguments = null):int {
		$this->writeLine("Command running successfully");

		$this->writeLine(
			"Command ID: "
			. $arguments->get("id")
		);

		if($arguments->contains("option")) {
			$this->writeLine(
				"Option: " . $arguments->get("option")
			);
		}
		else {
			$this->writeLine("No Option set");
		}

		$this->writeLine(
			"Must-have-value: "
			. $arguments->get("must-have-value")
		);

		if($arguments->contains("no-value")) {
			$this->writeLine("No-value argument set");
		}
		else {
			$this->writeLine("No-value argument not set");
		}

		return 0;
	}

	public function getName():string {
		return "{$this->prefix}test";
	}

	public function getDescription():string {
		return "A test command for unit testing";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [
			new NamedParameter("id"),
		];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [
			new NamedParameter("option"),
		];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [
			new Parameter(
				true,
				"must-have-value",
				"m"
			),
		];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(
				false,
				"no-value",
				"n"
			),
		];
	}
}
