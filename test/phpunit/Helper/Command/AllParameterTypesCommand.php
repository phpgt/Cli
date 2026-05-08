<?php
namespace GT\Cli\Test\Helper\Command;

use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Command\Command;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;

class AllParameterTypesCommand extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		unset($arguments);
		return 0;
	}

	public function getName():string {
		return "all-parameter-types-command";
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
				"t"
			),
		];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(
				true,
				"log",
				"l",
				"The path of your log file",
				"LOG_PATH"
			),
			new Parameter(
				false,
				"verbose",
				"v"
			),
		];
	}
}
