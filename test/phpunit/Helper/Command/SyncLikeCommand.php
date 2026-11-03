<?php
namespace GT\Cli\Test\Helper\Command;

use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Command\Command;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;

class SyncLikeCommand extends Command {
	public function run(?ArgumentValueList $arguments = null):int {
		unset($arguments);
		return 0;
	}

	public function getName():string {
		return "sync";
	}

	public function getDescription():string {
		return "A sync-like command for argument parsing tests.";
	}

	/** @return NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [
			new NamedParameter("source"),
			new NamedParameter("dest"),
		];
	}

	/** @return NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [];
	}

	/** @return Parameter[] */
	public function getRequiredParameterList():array {
		return [];
	}

	/** @return Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(true, "pattern"),
			new Parameter(false, "symlink"),
		];
	}
}
