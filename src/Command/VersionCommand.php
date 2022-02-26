<?php
namespace Gt\Cli\Command;

use Composer\InstalledVersions;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

class VersionCommand extends Command {
	public function run(ArgumentValueList $arguments = null):void {
		$this->writeLine(
			$this->getVersion($arguments?->get(
				"command"
			)
		));
	}

	public function getName():string {
		return "version";
	}

	public function getDescription():string {
		return "Get the version of the application";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [];
	}

	protected function getVersion(string $command = null):string {
		$package = InstalledVersions::getRootPackage()["name"];
		return InstalledVersions::getVersion($package) ?? "";
	}
}
