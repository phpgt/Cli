<?php
namespace Gt\Cli\Test\Helper\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

class MultipleRequiredParameterCommand extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		return 0;
	}

	public function getName():string {
		return "multiple-required-parameter-command";
	}

	public function getDescription():string {
		return "A test class for testing purposes.";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [
			new NamedParameter("id"),
			new NamedParameter("name"),
		];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [
			new Parameter(
				true,
				"framework",
				"f",
				"The name of your framework"
			),
			new Parameter(
				false,
				"example"
			),
		];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [];
	}
}
