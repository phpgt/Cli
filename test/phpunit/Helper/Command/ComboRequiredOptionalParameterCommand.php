<?php
namespace Gt\Cli\Test\Helper\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

class ComboRequiredOptionalParameterCommand extends Command {
	public function run(?ArgumentValueList $arguments = null):?int {
		return null;
	}

	public function getName():string {
		return "combo-required-optional-parameter-command";
	}

	public function getDescription():string {
		return "A test class for testing purposes.";
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
			new NamedParameter("name"),
		];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [
			new Parameter(
				true,
				"type",
				"t",
				"What type of thing you are using"
			),
		];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(
				false,
				"verbose",
				"v"
			),
		];
	}
}
